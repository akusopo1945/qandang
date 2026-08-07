import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../services/app_services.dart';
import '../services/notification_service.dart';
import 'feed_calculator_page.dart';
import 'farm_calendar_page.dart';
import 'barn_monitoring_page.dart';

class DashboardPage extends StatefulWidget {
  const DashboardPage({super.key});

  @override
  State<DashboardPage> createState() => _DashboardPageState();
}

class _DashboardPageState extends State<DashboardPage> {
  List<dynamic> _upcomingVaksins = [];
  int _totalGoats = 0;
  double _weighingProgress = 0.0;
  int _weighedCount = 0;
  int _pendingSyncCount = 0;

  @override
  void initState() {
    super.initState();
    _loadDashboardData();
  }

  _loadDashboardData() async {
    await DbHelper.processQueue();
    final goats = await DbHelper.getGoats();
    List<dynamic> upcoming = [];
    int weighedThisWeek = 0;
    
    // Get start of this week
    DateTime now = DateTime.now();
    DateTime startOfWeek = now.subtract(Duration(days: now.weekday - 1));
    startOfWeek = DateTime(startOfWeek.year, startOfWeek.month, startOfWeek.day);

    for (var goat in goats) {
      final records = jsonDecode(goat['health_records'] as String) as List;
      for (var record in records) {
        if (record['next_scheduled_date'] != null) {
          final nextDate = DateTime.parse(record['next_scheduled_date']);
          if (nextDate.isAfter(DateTime.now().subtract(const Duration(days: 1))) && 
              nextDate.isBefore(DateTime.now().add(const Duration(days: 14)))) {
            upcoming.add({
              'goat_name': goat['name'],
              'action': record['action_type'] ?? record['type'],
              'date': record['next_scheduled_date'],
            });
          }
        }
      }

      // Check weight logs for this week
      final weightLogs = jsonDecode(goat['weight_logs'] as String) as List;
      bool weighed = weightLogs.any((log) {
        DateTime logDate = DateTime.parse(log['date_recorded']);
        return logDate.isAfter(startOfWeek) || logDate.isAtSameMomentAs(startOfWeek);
      });
      if (weighed) weighedThisWeek++;
    }

    upcoming.sort((a, b) => a['date'].compareTo(b['date']));
    
    // Check pending sync items
    final db = await DbHelper.db;
    final queue = await db.query('sync_queue');
    final pendingSync = queue.length;
    
    // Sync local notifications for health reminders
    await NotificationService.syncAllReminders();
    
    setState(() {
      _upcomingVaksins = upcoming;
      _totalGoats = goats.length;
      _weighedCount = weighedThisWeek;
      _weighingProgress = _totalGoats > 0 ? _weighedCount / _totalGoats : 0.0;
      _pendingSyncCount = pendingSync;
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Qandang', style: TextStyle(fontWeight: FontWeight.bold)),
        actions: [
          IconButton(
            icon: const Icon(Icons.sync), 
            onPressed: () async {
              ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Sinkronisasi data...')));
              await DbHelper.processQueue();
              _loadDashboardData();
              if (mounted) ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Sinkronisasi selesai')));
            }
          ),
          IconButton(icon: const Icon(Icons.notifications_none), onPressed: () {}),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: () async => _loadDashboardData(),
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              if (_pendingSyncCount > 0)
                Container(
                  margin: const EdgeInsets.only(bottom: 20),
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                  decoration: BoxDecoration(
                    color: Colors.orange.shade50,
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(color: Colors.orange.shade200),
                  ),
                  child: Row(
                    children: [
                      Icon(Icons.cloud_sync, color: Colors.orange.shade700),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Text(
                          '$_pendingSyncCount data menunggu sinkronisasi internet',
                          style: TextStyle(color: Colors.orange.shade900, fontWeight: FontWeight.bold),
                        ),
                      ),
                      IconButton(
                        icon: Icon(Icons.refresh, color: Colors.orange.shade700),
                        onPressed: () async {
                          ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Menyinkronkan...')));
                          await DbHelper.processQueue();
                          _loadDashboardData();
                        },
                      )
                    ],
                  ),
                ),
              const Text('Halo, Peternak! 👋', style: TextStyle(fontSize: 24, fontWeight: FontWeight.bold)),
              const Text('Bagaimana kondisi kandang hari ini?', style: TextStyle(color: Colors.grey)),
              const SizedBox(height: 24),

              if (_upcomingVaksins.isNotEmpty) ...[
                const Text('Jadwal Terdekat', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
                const SizedBox(height: 12),
                ..._upcomingVaksins.take(2).map((v) => Container(
                  margin: const EdgeInsets.only(bottom: 12),
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: Colors.orange.shade50,
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(color: Colors.orange.shade200),
                  ),
                  child: Row(
                    children: [
                      const Icon(Icons.event_note, color: Colors.orange),
                      const SizedBox(width: 16),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text('${v['action']} - ${v['goat_name']}', style: const TextStyle(fontWeight: FontWeight.bold)),
                            Text('Jadwal: ${DateFormat('dd MMM yyyy').format(DateTime.parse(v['date']))}', style: const TextStyle(fontSize: 12)),
                          ],
                        ),
                      ),
                    ],
                  ),
                )),
                const SizedBox(height: 12),
              ],

              const SizedBox(height: 24),

              // Progress Section
              Container(
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(20),
                  border: Border.all(color: Colors.grey.shade200),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text('Target Penimbangan', style: TextStyle(fontWeight: FontWeight.bold)),
                        Text('${(_weighingProgress * 100).toInt()}%', style: const TextStyle(color: Color(0xFF4A6741), fontWeight: FontWeight.bold)),
                      ],
                    ),
                    const SizedBox(height: 12),
                    LinearProgressIndicator(
                      value: _weighingProgress,
                      backgroundColor: Colors.grey.shade100,
                      color: const Color(0xFF4A6741),
                      borderRadius: BorderRadius.circular(10),
                      minHeight: 10,
                    ),
                    const SizedBox(height: 8),
                    Text('$_weighedCount dari $_totalGoats kambing sudah ditimbang minggu ini.', style: const TextStyle(fontSize: 12, color: Colors.grey)),
                  ],
                ),
              ),

              const SizedBox(height: 24),

              // Stats Grid
              GridView.count(
                crossAxisCount: 2,
                shrinkWrap: true,
                physics: const NeverScrollableScrollPhysics(),
                mainAxisSpacing: 12,
                crossAxisSpacing: 12,
                childAspectRatio: 1.5,
                children: [
                  _buildStatCard('Total Ternak', '$_totalGoats', Icons.pets, Colors.blue),
                  _buildStatCard('Kondisi Kandang', 'Optimal', Icons.wb_sunny, Colors.orange),
                ],
              ),              
              const SizedBox(height: 24),

              const Text('Fitur Unggulan Peternak', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
              const SizedBox(height: 12),
              Row(
                children: [
                  Expanded(
                    child: InkWell(
                      onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const FeedCalculatorPage())),
                      borderRadius: BorderRadius.circular(16),
                      child: Container(
                        padding: const EdgeInsets.all(16),
                        decoration: BoxDecoration(
                          color: const Color(0xFF4A6741).withOpacity(0.08),
                          borderRadius: BorderRadius.circular(16),
                          border: Border.all(color: const Color(0xFF4A6741).withOpacity(0.2)),
                        ),
                        child: const Column(
                          children: [
                            Icon(Icons.calculate_outlined, color: Color(0xFF4A6741), size: 28),
                            SizedBox(height: 8),
                            Text('Kalkulator Pakan', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12, color: Color(0xFF4A6741)), textAlign: TextAlign.center),
                          ],
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: InkWell(
                      onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const FarmCalendarPage())),
                      borderRadius: BorderRadius.circular(16),
                      child: Container(
                        padding: const EdgeInsets.all(16),
                        decoration: BoxDecoration(
                          color: Colors.pink.shade50,
                          borderRadius: BorderRadius.circular(16),
                          border: Border.all(color: Colors.pink.shade200),
                        ),
                        child: Column(
                          children: [
                            Icon(Icons.calendar_month_outlined, color: Colors.pink.shade700, size: 28),
                            const SizedBox(height: 8),
                            Text('Kalender HPL', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12, color: Colors.pink.shade700), textAlign: TextAlign.center),
                          ],
                        ),
                      ),
                    ),
                  ),
                ],
              ),

              const SizedBox(height: 24),

              // IoT Shortcut Card
              GestureDetector(
                onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const BarnMonitoringPage())),
                child: Container(
                  padding: const EdgeInsets.all(20),
                  decoration: BoxDecoration(
                    gradient: LinearGradient(colors: [Colors.blue.shade600, Colors.blue.shade400]),
                    borderRadius: BorderRadius.circular(20),
                    boxShadow: [BoxShadow(color: Colors.blue.withOpacity(0.3), blurRadius: 8, offset: const Offset(0, 4))],
                  ),
                  child: const Row(
                    children: [
                      Icon(Icons.sensors, color: Colors.white, size: 32),
                      SizedBox(width: 16),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text('Monitoring Kandang', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 16)),
                            Text('Suhu: 28°C | Lembab: 65%', style: TextStyle(color: Colors.white70, fontSize: 12)),
                          ],
                        ),
                      ),
                      Icon(Icons.chevron_right, color: Colors.white),
                    ],
                  ),
                ),
              ),

              const SizedBox(height: 24),
              
              const Text('Aktivitas Terakhir', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
              const SizedBox(height: 12),
              _buildRecentActivity('Scan Kambing #A12', 'Baru saja', Icons.qr_code),
              _buildRecentActivity('Catat Berat Kambing #B05', '2 jam yang lalu', Icons.monitor_weight),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildStatCard(String label, String value, IconData icon, Color color) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: color.withOpacity(0.1),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: color.withOpacity(0.2)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(icon, color: color),
          const SizedBox(height: 8),
          Text(value, style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: color)),
          Text(label, style: const TextStyle(fontSize: 12, color: Colors.grey)),
        ],
      ),
    );
  }

  Widget _buildRecentActivity(String title, String time, IconData icon) {
    return Card(
      elevation: 0,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12), side: BorderSide(color: Colors.grey.shade200)),
      child: ListTile(
        leading: Icon(icon, color: const Color(0xFF4A6741)),
        title: Text(title, style: const TextStyle(fontWeight: FontWeight.w600)),
        subtitle: Text(time, style: const TextStyle(fontSize: 12)),
        trailing: const Icon(Icons.chevron_right, size: 16),
      ),
    );
  }
}
