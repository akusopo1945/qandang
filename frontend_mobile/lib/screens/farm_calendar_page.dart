import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../services/app_services.dart';
import 'goat_detail_page.dart';

class FarmCalendarPage extends StatefulWidget {
  const FarmCalendarPage({super.key});

  @override
  State<FarmCalendarPage> createState() => _FarmCalendarPageState();
}

class _FarmCalendarPageState extends State<FarmCalendarPage> {
  List<Map<String, dynamic>> _events = [];
  bool _loading = true;
  String _filterType = 'Semua';

  @override
  void initState() {
    super.initState();
    _loadEvents();
  }

  Future<void> _loadEvents() async {
    setState(() => _loading = true);
    final goats = await DbHelper.getGoats();
    List<Map<String, dynamic>> events = [];

    for (var goat in goats) {
      // 1. Health schedule events
      final healthRecords = jsonDecode(goat['health_records'] as String? ?? '[]') as List;
      for (var record in healthRecords) {
        if (record['next_scheduled_date'] != null && record['next_scheduled_date'].toString().isNotEmpty) {
          try {
            final date = DateTime.parse(record['next_scheduled_date']);
            events.add({
              'goat_id': goat['id'],
              'goat_name': goat['name'],
              'title': record['action_type'] ?? record['type'] ?? record['title'] ?? 'Jadwal Kesehatan',
              'date': date,
              'type': 'health',
              'note': record['note'] ?? record['description'] ?? '',
            });
          } catch (_) {}
        }
      }

      // 2. Estimated delivery dates (HPL)
      if (goat['estimated_delivery_date'] != null && goat['estimated_delivery_date'].toString().isNotEmpty) {
        try {
          final date = DateTime.parse(goat['estimated_delivery_date']);
          events.add({
            'goat_id': goat['id'],
            'goat_name': goat['name'],
            'title': 'Estimasi Kelahiran (HPL)',
            'date': date,
            'type': 'hpl',
            'note': 'Perkiraan melahirkan untuk ${goat['name']}',
          });
        } catch (_) {}
      }
    }

    // Sort by date ascending
    events.sort((a, b) => (a['date'] as DateTime).compareTo(b['date'] as DateTime));

    setState(() {
      _events = events;
      _loading = false;
    });
  }

  @override
  Widget build(BuildContext context) {
    final filteredEvents = _events.where((e) {
      if (_filterType == 'Semua') return true;
      if (_filterType == 'HPL' && e['type'] == 'hpl') return true;
      if (_filterType == 'Kesehatan' && e['type'] == 'health') return true;
      return false;
    }).toList();

    return Scaffold(
      appBar: AppBar(
        title: const Text('Kalender Kesehatan & HPL'),
        backgroundColor: const Color(0xFF4A6741),
        foregroundColor: Colors.white,
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : Column(
              children: [
                // Filter bar
                Padding(
                  padding: const EdgeInsets.all(16),
                  child: Row(
                    children: ['Semua', 'Kesehatan', 'HPL'].map((type) {
                      final isSelected = _filterType == type;
                      return Padding(
                        padding: const EdgeInsets.only(right: 8),
                        child: ChoiceChip(
                          label: Text(type),
                          selected: isSelected,
                          onSelected: (s) {
                            if (s) setState(() => _filterType = type);
                          },
                          selectedColor: const Color(0xFF4A6741),
                          labelStyle: TextStyle(color: isSelected ? Colors.white : Colors.black),
                        ),
                      );
                    }).toList(),
                  ),
                ),

                Expanded(
                  child: filteredEvents.isEmpty
                      ? const Center(child: Text('Belum ada agenda terdekat.'))
                      : RefreshIndicator(
                          onRefresh: _loadEvents,
                          child: ListView.builder(
                            padding: const EdgeInsets.symmetric(horizontal: 16),
                            itemCount: filteredEvents.length,
                            itemBuilder: (context, i) {
                              final event = filteredEvents[i];
                              final DateTime date = event['date'];
                              final isHpl = event['type'] == 'hpl';
                              final diffDays = date.difference(DateTime.now()).inDays;
                              
                              Color color = isHpl ? Colors.pink : Colors.orange;
                              if (diffDays < 0) color = Colors.grey;

                              return Card(
                                elevation: 0,
                                margin: const EdgeInsets.only(bottom: 12),
                                shape: RoundedRectangleBorder(
                                  borderRadius: BorderRadius.circular(16),
                                  side: BorderSide(color: color.withOpacity(0.3), width: 1.5),
                                ),
                                child: ListTile(
                                  contentPadding: const EdgeInsets.all(12),
                                  leading: Container(
                                    padding: const EdgeInsets.all(12),
                                    decoration: BoxDecoration(color: color.withOpacity(0.1), borderRadius: BorderRadius.circular(12)),
                                    child: Icon(isHpl ? Icons.child_friendly : Icons.medical_services_outlined, color: color),
                                  ),
                                  title: Text('${event['title']} • ${event['goat_name']}', style: const TextStyle(fontWeight: FontWeight.bold)),
                                  subtitle: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      const SizedBox(height: 4),
                                      Text('Jadwal: ${DateFormat('dd MMMM yyyy').format(date)}', style: const TextStyle(fontSize: 12)),
                                      if (event['note'].toString().isNotEmpty) ...[
                                        const SizedBox(height: 2),
                                        Text(event['note'], style: const TextStyle(fontSize: 11, color: Colors.grey)),
                                      ],
                                    ],
                                  ),
                                  trailing: Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                    decoration: BoxDecoration(color: color.withOpacity(0.15), borderRadius: BorderRadius.circular(12)),
                                    child: Text(
                                      diffDays == 0
                                          ? 'HARI INI'
                                          : (diffDays > 0 ? '$diffDays Hari lagi' : 'Lewat'),
                                      style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: color),
                                    ),
                                  ),
                                  onTap: () {
                                    Navigator.push(context, MaterialPageRoute(builder: (_) => GoatDetailPage(id: event['goat_id'].toString())));
                                  },
                                ),
                              );
                            },
                          ),
                        ),
                ),
              ],
            ),
    );
  }
}
