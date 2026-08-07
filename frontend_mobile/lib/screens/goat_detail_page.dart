import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:fl_chart/fl_chart.dart';
import 'package:url_launcher/url_launcher.dart';
import 'package:flutter/services.dart';
import 'package:qr_flutter/qr_flutter.dart';
import 'package:share_plus/share_plus.dart';
import 'feed_calculator_page.dart';
import '../services/app_services.dart';
import '../widgets/premium_image.dart';

class GoatDetailPage extends StatefulWidget {
  final String id;
  const GoatDetailPage({super.key, required this.id});

  @override
  State<GoatDetailPage> createState() => _GoatDetailPageState();
}

class _GoatDetailPageState extends State<GoatDetailPage> {
  late Future<Map<String, dynamic>> _goatFuture;

  @override
  void initState() {
    super.initState();
    _goatFuture = _fetchGoatDetail();
  }

  Future<Map<String, dynamic>> _fetchGoatDetail() async {
    try {
      final res = await ApiService.get('/goats/${widget.id}');
      if (res.statusCode == 200) {
        final goat = jsonDecode(res.body);
        await DbHelper.saveGoats([goat]);
        return goat;
      }
    } catch (_) {
      // Fallback
    }
    final localGoat = await DbHelper.getGoat(widget.id);
    if (localGoat != null) return localGoat;
    throw Exception('Gagal memuat detail ternak (Offline)');
  }

  Future<void> _deleteGoat(BuildContext context, Map<String, dynamic> goat) async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Hapus Data Ternak'),
        content: Text('Apakah Anda yakin ingin menghapus data kambing "${goat['name']}"?'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context, false), child: const Text('BATAL')),
          ElevatedButton(
            onPressed: () => Navigator.pop(context, true),
            style: ElevatedButton.styleFrom(backgroundColor: Colors.red, foregroundColor: Colors.white),
            child: const Text('HAPUS'),
          ),
        ],
      ),
    );

    if (confirm != true) return;

    try {
      final res = await ApiService.delete('/goats/${goat['id']}');
      if (res.statusCode == 200) {
        await DbHelper.deleteGoatLocally(goat['id']);
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Data ternak berhasil dihapus')));
          Navigator.pop(context);
        }
      } else {
        String msg = 'Gagal menghapus (${res.statusCode})';
        try {
          final err = jsonDecode(res.body);
          if (err['message'] != null) msg = err['message'];
        } catch (_) {}
        if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(msg)));
      }
    } catch (_) {
      await DbHelper.deleteGoatLocally(goat['id']);
      await DbHelper.addToQueue('/goats/${goat['id']}', 'DELETE', null);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Offline: Antrean hapus disimpan di lokal. 📡'), backgroundColor: Colors.orange));
        Navigator.pop(context);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return FutureBuilder<Map<String, dynamic>>(
      future: _goatFuture,
      builder: (context, snapshot) {
        if (snapshot.connectionState == ConnectionState.waiting) {
          return Scaffold(
            appBar: AppBar(title: const Text('Detail Ternak')),
            body: const Center(child: CircularProgressIndicator()),
          );
        }
        if (snapshot.hasError) {
          return Scaffold(
            appBar: AppBar(title: const Text('Detail Ternak')),
            body: Center(child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [const Icon(Icons.error_outline, size: 48, color: Colors.red), const SizedBox(height: 16), Text('Error: ${snapshot.error}')])),
          );
        }
        
        List parseList(dynamic raw) {
          if (raw == null) return [];
          if (raw is List) return raw;
          if (raw is String && raw.isNotEmpty) {
            try {
              final d = jsonDecode(raw);
              if (d is List) return d;
            } catch (_) {}
          }
          return [];
        }

        final goat = snapshot.data!;
        final weightLogs = parseList(goat['weight_logs']).reversed.toList();
        final healthRecords = parseList(goat['health_records']).reversed.toList();

        return Scaffold(
          appBar: AppBar(
            title: Text(goat['name'] ?? 'Detail Ternak'),
            actions: [
              IconButton(
                icon: const Icon(Icons.delete_outline, color: Colors.red),
                onPressed: () => _deleteGoat(context, goat),
              ),
            ],
          ),
          body: DefaultTabController(
            length: 3,
            child: Column(
              children: [
                _buildHeader(goat),
                const TabBar(
                  labelColor: Color(0xFF4A6741),
                  indicatorColor: Color(0xFF4A6741),
                  tabs: [
                    Tab(text: 'Info'),
                    Tab(text: 'Berat'),
                    Tab(text: 'Kesehatan'),
                  ],
                ),
                Expanded(
                  child: TabBarView(
                    children: [
                      _buildInfoTab(goat),
                      _buildWeightTab(weightLogs),
                      _buildHealthTab(healthRecords),
                    ],
                  ),
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  Widget _buildHeader(Map<String, dynamic> goat) {
    List parseList(dynamic raw) {
      if (raw == null) return [];
      if (raw is List) return raw;
      if (raw is String && raw.isNotEmpty) {
        try {
          final d = jsonDecode(raw);
          if (d is List) return d;
        } catch (_) {}
      }
      return [];
    }

    List<String> images = [];
    if (goat['image_url'] != null) images.add(goat['image_url'].toString());
    final healthRecords = parseList(goat['health_records']);
    for (var record in healthRecords) {
      if (record is Map && record['image_url'] != null) images.add(record['image_url'].toString());
    }

    return Container(
      padding: const EdgeInsets.symmetric(vertical: 24),
      color: const Color(0xFF4A6741).withOpacity(0.05),
      child: Column(
        children: [
          if (images.isNotEmpty)
            SizedBox(
              height: 120,
              child: ListView.builder(
                scrollDirection: Axis.horizontal,
                padding: const EdgeInsets.symmetric(horizontal: 20),
                itemCount: images.length,
                itemBuilder: (context, i) => GestureDetector(
                  onTap: () => _showFullScreenImage(context, images[i]),
                  child: Hero(
                    tag: i == 0 ? 'goat_image_${goat['id']}' : 'goat_image_${goat['id']}_$i',
                    child: Container(
                      margin: const EdgeInsets.only(right: 12),
                      decoration: BoxDecoration(
                        borderRadius: BorderRadius.circular(16),
                        boxShadow: const [BoxShadow(color: Colors.black12, blurRadius: 4, offset: Offset(0, 2))],
                      ),
                      child: PremiumImage(
                        imageUrl: images[i],
                        width: 120,
                        height: 120,
                        borderRadius: BorderRadius.circular(16),
                      ),
                    ),
                  ),
                ),
              ),
            )
          else
            CircleAvatar(
              radius: 40,
              backgroundColor: const Color(0xFF4A6741).withOpacity(0.1),
              child: const Icon(Icons.pets, size: 40, color: Color(0xFF4A6741)),
            ),
          const SizedBox(height: 16),
          Text(goat['name'] ?? 'Kambing Tanpa Nama', style: const TextStyle(fontSize: 24, fontWeight: FontWeight.bold)),
          Text('Tag: ${goat['qr_code'] ?? '-'}', style: const TextStyle(color: Colors.grey)),
          const SizedBox(height: 12),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
            decoration: BoxDecoration(color: const Color(0xFF4A6741), borderRadius: BorderRadius.circular(20)),
            child: Text(
              goat['status'] ?? 'Sehat',
              style: const TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.bold),
            ),
          ),
        ],
      ),
    );
  }

  void _showFullScreenImage(BuildContext context, String imageUrl) {
    Navigator.push(context, MaterialPageRoute(builder: (_) => Scaffold(
      backgroundColor: Colors.black,
      appBar: AppBar(backgroundColor: Colors.transparent, foregroundColor: Colors.white, elevation: 0),
      body: Center(
        child: InteractiveViewer(
          minScale: 0.5,
          maxScale: 4.0,
          child: PremiumImage(imageUrl: imageUrl),
        ),
      ),
    )));
  }

  Widget _buildQurbanCard(Map<String, dynamic> goat) {
    if (goat['gender'] != 'male') return const SizedBox.shrink();

    final double currentWeight = double.tryParse(goat['weight']?.toString() ?? goat['current_weight']?.toString() ?? '0') ?? 0;
    final qurbanDate = DateTime(2026, 5, 27);
    final daysLeft = qurbanDate.difference(DateTime.now()).inDays;
    final days = daysLeft > 0 ? daysLeft : 0;
    final double estWeight = currentWeight + (0.15 * days);

    String qurbanClass = 'Belum Cukup Bobot';
    Color color = Colors.grey;

    if (estWeight >= 50) {
      qurbanClass = 'Kelas Super (≥50 kg) 🏆';
      color = Colors.purple;
    } else if (estWeight >= 40) {
      qurbanClass = 'Kelas A (40-50 kg) 🥇';
      color = Colors.green;
    } else if (estWeight >= 30) {
      qurbanClass = 'Kelas B (30-40 kg) 🥈';
      color = Colors.blue;
    } else if (estWeight >= 20) {
      qurbanClass = 'Kelas C (20-30 kg) 🥉';
      color = Colors.orange;
    }

    return Card(
      elevation: 0,
      color: color.withOpacity(0.08),
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(16),
        side: BorderSide(color: color.withOpacity(0.3)),
      ),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Icon(Icons.stars, color: color, size: 20),
                const SizedBox(width: 8),
                const Text('Target & Kesiapan Idul Adha (Qurban)', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                const Spacer(),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(color: color, borderRadius: BorderRadius.circular(12)),
                  child: Text('$daysLeft Hari Lagi', style: const TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.bold)),
                ),
              ],
            ),
            const Divider(height: 20),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Text('Proyeksi Kelas Qurban:', style: TextStyle(fontSize: 12, color: Colors.grey)),
                Text(qurbanClass, style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: color)),
              ],
            ),
            const SizedBox(height: 6),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Text('Estimasi Bobot Pasca-Fattening:', style: TextStyle(fontSize: 12, color: Colors.grey)),
                Text('${estWeight.toStringAsFixed(1)} kg', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12)),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildInfoTab(Map<String, dynamic> goat) {
    final double purchase = double.tryParse(goat['purchase_price']?.toString() ?? '0') ?? 0;
    final double feedCost = double.tryParse(goat['feeding_cost']?.toString() ?? '0') ?? 0;
    final double marketPrice = double.tryParse(goat['price']?.toString() ?? '0') ?? 0;
    final double totalCost = purchase + feedCost;
    final double estProfit = marketPrice > 0 ? (marketPrice - totalCost) : 0;

    return ListView(
      padding: const EdgeInsets.all(20),
      children: [
        _AIPredictionCard(goatId: goat['id'].toString()),
        const SizedBox(height: 16),
        _buildQurbanCard(goat),
        const SizedBox(height: 20),

        // Quick Actions Row
        Row(
          children: [
            Expanded(
              child: ElevatedButton.icon(
                onPressed: () {
                  final weight = double.tryParse(goat['weight']?.toString() ?? '0');
                  Navigator.push(
                    context,
                    MaterialPageRoute(
                      builder: (_) => FeedCalculatorPage(initialWeight: weight, initialPurpose: goat['purpose']),
                    ),
                  );
                },
                icon: const Icon(Icons.calculate_outlined, size: 18),
                label: const Text('Hitung Pakan', style: TextStyle(fontSize: 12)),
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF4A6741),
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                ),
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: OutlinedButton.icon(
                onPressed: () => _showDigitalIDCard(goat),
                icon: const Icon(Icons.qr_code, size: 18),
                label: const Text('Kartu & QR Tag', style: TextStyle(fontSize: 12)),
                style: OutlinedButton.styleFrom(
                  foregroundColor: const Color(0xFF4A6741),
                  side: const BorderSide(color: Color(0xFF4A6741)),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                ),
              ),
            ),
          ],
        ),
        const SizedBox(height: 20),

        // Financial & ROI Card
        Card(
          elevation: 0,
          color: Colors.green.shade50.withOpacity(0.5),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(16),
            side: BorderSide(color: Colors.green.shade200),
          ),
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Row(
                  children: [
                    Icon(Icons.account_balance_wallet_outlined, color: Color(0xFF4A6741), size: 20),
                    SizedBox(width: 8),
                    Text('Analisis Finansial & Estimasi ROI', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF4A6741))),
                  ],
                ),
                const Divider(height: 20),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Text('Harga Beli / Modal Awal:', style: TextStyle(fontSize: 12, color: Colors.grey)),
                    Text(purchase > 0 ? 'Rp ${purchase.toInt().toString().replaceAllMapped(RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'), (m) => '${m[1]}.')}' : '-', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12)),
                  ],
                ),
                const SizedBox(height: 6),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Text('Total Biaya Pakan & Medis:', style: TextStyle(fontSize: 12, color: Colors.grey)),
                    Text(feedCost > 0 ? 'Rp ${feedCost.toInt().toString().replaceAllMapped(RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'), (m) => '${m[1]}.')}' : '-', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12)),
                  ],
                ),
                const SizedBox(height: 6),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Text('Estimasi Harga Pasaran:', style: TextStyle(fontSize: 12, color: Colors.grey)),
                    Text(marketPrice > 0 ? 'Rp ${marketPrice.toInt().toString().replaceAllMapped(RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'), (m) => '${m[1]}.')}' : '-', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12, color: Colors.blue)),
                  ],
                ),
                const Divider(height: 20),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Text('Estimasi Keuntungan (Profit):', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                    Text(
                      estProfit != 0 ? 'Rp ${estProfit.toInt().toString().replaceAllMapped(RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'), (m) => '${m[1]}.')}' : '-',
                      style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15, color: estProfit >= 0 ? Colors.green.shade800 : Colors.red),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ),
        const SizedBox(height: 20),

        _buildInfoCard([
          _buildInfoRow('Jenis / Ras', goat['breed'] ?? '-'),
          _buildInfoRow('Sekat / Blok Kandang', goat['barn_block']?.isEmpty == false ? goat['barn_block'] : 'Utama'),
          _buildInfoRow('Jenis Kelamin', goat['gender'] == 'male' ? 'Jantan' : 'Betina'),
          _buildInfoRow('Tujuan Pemeliharaan', goat['purpose'] == 'breeding' ? 'Pembibitan (Breeding)' : 'Penggemukan (Fattening)'),
          _buildInfoRow('Tanggal Lahir', goat['date_of_birth'] ?? goat['birth_date'] ?? '-'),
          _buildInfoRow('Berat Terakhir', '${goat['weight'] ?? '-'} kg'),
        ]),
        const SizedBox(height: 24),
        const Text('Visual Silsilah (Pedigree - Klik untuk Navigasi)', style: TextStyle(fontWeight: FontWeight.bold, color: Colors.grey, fontSize: 12, letterSpacing: 1)),
        const SizedBox(height: 16),
        _buildPedigreeTree(goat),
        const SizedBox(height: 24),
        const Text('Catatan Tambahan', style: TextStyle(fontWeight: FontWeight.bold, color: Colors.grey, fontSize: 12, letterSpacing: 1)),
        const SizedBox(height: 12),
        Container(
          padding: const EdgeInsets.all(16),
          width: double.infinity,
          decoration: BoxDecoration(color: Colors.grey.shade50, borderRadius: BorderRadius.circular(12), border: Border.all(color: Colors.grey.shade200)),
          child: Text(goat['note']?.isEmpty == false ? goat['note'] : (goat['description']?.isEmpty == false ? goat['description'] : 'Tidak ada catatan.'), style: const TextStyle(height: 1.5)),
        ),
      ],
    );
  }

  Widget _buildPedigreeTree(Map<String, dynamic> goat) {
    // Get parent IDs safely
    final sireId = goat['sire_id'] ?? goat['sire']?['id'];
    final damId = goat['dam_id'] ?? goat['dam']?['id'];

    return Column(
      children: [
        Row(
          children: [
            Expanded(child: _buildParentNode('Bapak (Sire)', goat['sire']?['name'], sireId, Icons.male, Colors.blue)),
            const SizedBox(width: 16),
            Expanded(child: _buildParentNode('Induk (Dam)', goat['dam']?['name'], damId, Icons.female, Colors.pink)),
          ],
        ),
        const SizedBox(height: 12),
        const Icon(Icons.keyboard_double_arrow_down, color: Colors.grey, size: 24),
        const SizedBox(height: 12),
        Container(
          padding: const EdgeInsets.all(16),
          width: double.infinity,
          decoration: BoxDecoration(
            color: const Color(0xFF4A6741),
            borderRadius: BorderRadius.circular(16),
            boxShadow: [BoxShadow(color: const Color(0xFF4A6741).withOpacity(0.3), blurRadius: 8, offset: const Offset(0, 4))],
          ),
          child: Column(
            children: [
              const Icon(Icons.pets, color: Colors.white, size: 20),
              const SizedBox(height: 8),
              Text(goat['name'], style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 16)),
              Text(goat['breed'] ?? '-', style: const TextStyle(color: Colors.white70, fontSize: 12)),
            ],
          ),
        ),
      ],
    );
  }

  Widget _buildParentNode(String label, String? name, dynamic parentId, IconData icon, Color color) {
    bool hasParent = name != null && parentId != null;
    return GestureDetector(
      onTap: hasParent ? () async {
        await Navigator.push(context, MaterialPageRoute(builder: (_) => GoatDetailPage(id: parentId.toString())));
        // Refresh page data when returning from parent details
        setState(() {
          _goatFuture = _fetchGoatDetail();
        });
      } : null,
      child: Container(
        padding: const EdgeInsets.all(12),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: hasParent ? color.withOpacity(0.3) : Colors.grey.shade200),
        ),
        child: Column(
          children: [
            Text(label, style: TextStyle(fontSize: 10, color: color, fontWeight: FontWeight.bold)),
            const SizedBox(height: 8),
            Icon(icon, color: hasParent ? color : Colors.grey.shade300, size: 28),
            const SizedBox(height: 8),
            Text(
              name ?? 'Tidak Diketahui',
              textAlign: TextAlign.center,
              style: TextStyle(
                fontSize: 12,
                fontWeight: FontWeight.bold,
                color: hasParent ? Colors.black87 : Colors.grey,
              ),
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildInfoCard(List<Widget> children) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: Colors.grey.shade200),
      ),
      child: Column(children: children),
    );
  }

  Widget _buildInfoRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: const TextStyle(color: Colors.grey)),
          Text(value, style: const TextStyle(fontWeight: FontWeight.bold)),
        ],
      ),
    );
  }

  Widget _buildWeightTab(List logs) {
    if (logs.isEmpty) return const Center(child: Text('Belum ada data penimbangan.'));

    // Sort chronologically for line chart
    final sortedLogs = List.from(logs)..sort((a, b) => (a['date_recorded'] as String).compareTo(b['date_recorded'] as String));
    
    List<FlSpot> spots = [];
    for (int i = 0; i < sortedLogs.length; i++) {
      final w = sortedLogs[i]['weight'];
      final double weightVal = w is num ? w.toDouble() : double.tryParse(w.toString()) ?? 0.0;
      spots.add(FlSpot(i.toDouble(), weightVal));
    }

    return Column(
      children: [
        if (spots.length >= 2) ...[
          Container(
            height: 180,
            padding: const EdgeInsets.only(right: 24, left: 12, top: 24, bottom: 12),
            child: LineChart(
              LineChartData(
                gridData: const FlGridData(show: true, drawVerticalLine: false),
                titlesData: FlTitlesData(
                  leftTitles: const AxisTitles(sideTitles: SideTitles(showTitles: true, reservedSize: 40)),
                  bottomTitles: AxisTitles(
                    sideTitles: SideTitles(
                      showTitles: true,
                      getTitlesWidget: (val, meta) {
                        int idx = val.toInt();
                        if (idx >= 0 && idx < sortedLogs.length) {
                          final dateStr = sortedLogs[idx]['date_recorded'] as String;
                          try {
                            final date = DateTime.parse(dateStr);
                            return Padding(
                              padding: const EdgeInsets.only(top: 8.0),
                              child: Text('${date.day}/${date.month}', style: const TextStyle(fontSize: 8)),
                            );
                          } catch (_) {}
                        }
                        return const SizedBox();
                      },
                    ),
                  ),
                  rightTitles: const AxisTitles(sideTitles: SideTitles(showTitles: false)),
                  topTitles: const AxisTitles(sideTitles: SideTitles(showTitles: false)),
                ),
                borderData: FlBorderData(show: false),
                lineBarsData: [
                  LineChartBarData(
                    spots: spots,
                    isCurved: true,
                    color: const Color(0xFF4A6741),
                    barWidth: 3,
                    dotData: const FlDotData(show: true),
                    belowBarData: BarAreaData(show: true, color: const Color(0xFF4A6741).withOpacity(0.1)),
                  ),
                ],
              ),
            ),
          ),
          const Divider(),
        ],
        Expanded(
          child: ListView.builder(
            padding: const EdgeInsets.all(16),
            itemCount: logs.length,
            itemBuilder: (context, i) {
              final log = logs[i];
              return Card(
                elevation: 0,
                margin: const EdgeInsets.only(bottom: 8),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12), side: BorderSide(color: Colors.grey.shade200)),
                child: ListTile(
                  leading: const Icon(Icons.monitor_weight_outlined, color: Color(0xFF4A6741)),
                  title: Text('${log['weight']} kg', style: const TextStyle(fontWeight: FontWeight.bold)),
                  subtitle: Text(log['date_recorded']),
                ),
              );
            },
          ),
        ),
      ],
    );
  }

  Widget _buildHealthTab(List records) {
    if (records.isEmpty) return const Center(child: Text('Belum ada catatan kesehatan.'));
    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: records.length,
      itemBuilder: (context, i) {
        final record = records[i];
        return Card(
          elevation: 0,
          margin: const EdgeInsets.only(bottom: 12),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12), side: BorderSide(color: Colors.grey.shade200)),
          child: Column(
            children: [
              ListTile(
                leading: const Icon(Icons.medical_services_outlined, color: Colors.red),
                title: Text(record['action_type'] ?? record['type'] ?? 'Tindakan', style: const TextStyle(fontWeight: FontWeight.bold)),
                subtitle: Text(record['date_recorded']),
              ),
              if (record['image_url'] != null)
                GestureDetector(
                  onTap: () => _showFullScreenImage(context, record['image_url']),
                  child: Container(
                    height: 150, width: double.infinity,
                    margin: const EdgeInsets.only(left: 16, right: 16, bottom: 16),
                    child: PremiumImage(
                      imageUrl: record['image_url'],
                      width: double.infinity,
                      height: 150,
                      borderRadius: BorderRadius.circular(8),
                    ),
                  ),
                ),
              Padding(
                padding: const EdgeInsets.only(left: 16, right: 16, bottom: 16),
                child: Align(alignment: Alignment.centerLeft, child: Text(record['note'] ?? '', style: const TextStyle(fontSize: 13))),
              ),
            ],
          ),
        );
      },
    );
  }

  void _showDigitalIDCard(Map<String, dynamic> goat) {
    final qrCode = goat['qr_code'] ?? goat['id'];
    final catalogUrl = 'https://qandang.duckdns.org/catalog/$qrCode';

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) {
        return GestureDetector(
          behavior: HitTestBehavior.opaque,
          onTap: () => Navigator.pop(context),
          child: GestureDetector(
            onTap: () {}, // Mencegah tap diteruskan ke parent sehingga bottom sheet tidak tertutup
            child: Container(
              margin: const EdgeInsets.all(16),
              padding: const EdgeInsets.all(24),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(24),
                boxShadow: const [BoxShadow(color: Colors.black26, blurRadius: 20, offset: Offset(0, 10))],
              ),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Container(width: 40, height: 5, decoration: BoxDecoration(color: Colors.grey.shade300, borderRadius: BorderRadius.circular(10))),
                  const SizedBox(height: 24),
                  const Text('KARTU IDENTITAS TERNAK', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: Colors.grey, letterSpacing: 1.5)),
                  const SizedBox(height: 16),
                  Container(
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      gradient: LinearGradient(colors: [const Color(0xFF4A6741), Colors.green.shade800]),
                      borderRadius: BorderRadius.circular(16),
                    ),
                    child: Row(
                      children: [
                        CircleAvatar(radius: 30, backgroundColor: Colors.white24, child: const Icon(Icons.pets, color: Colors.white, size: 30)),
                        const SizedBox(width: 16),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(goat['name'] ?? 'Kambing', style: const TextStyle(color: Colors.white, fontSize: 22, fontWeight: FontWeight.bold)),
                              Text('${goat['breed'] ?? '-'} • ${goat['gender'] == 'male' ? 'Jantan' : 'Betina'}', style: const TextStyle(color: Colors.white70)),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 32),
                  GestureDetector(
                    onLongPress: () async {
                      await Clipboard.setData(ClipboardData(text: catalogUrl));
                      if (context.mounted) {
                        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Tautan QR disalin ke papan klip!')));
                      }
                      await Share.share('Lihat detail ternak ${goat['name']} di sini:\n$catalogUrl');
                    },
                    child: Container(
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(16), border: Border.all(color: Colors.grey.shade200, width: 2)),
                      child: QrImageView(
                        data: catalogUrl,
                        version: QrVersions.auto,
                        size: 200.0,
                        backgroundColor: Colors.white,
                        eyeStyle: const QrEyeStyle(eyeShape: QrEyeShape.square, color: Color(0xFF4A6741)),
                        dataModuleStyle: const QrDataModuleStyle(dataModuleShape: QrDataModuleShape.square, color: Color(0xFF4A6741)),
                      ),
                    ),
                  ),
                  const SizedBox(height: 16),
                  const Text('Tekan dan Tahan QR Code untuk Membagikan Tautan', style: TextStyle(color: Colors.grey, fontSize: 12), textAlign: TextAlign.center),
                  const SizedBox(height: 16),
                ],
              ),
            ),
          ),
        );
      }
    );
  }
}

class _AIPredictionCard extends StatefulWidget {
  final String goatId;
  const _AIPredictionCard({required this.goatId});

  @override
  State<_AIPredictionCard> createState() => _AIPredictionCardState();
}

class _AIPredictionCardState extends State<_AIPredictionCard> {
  bool _loading = false;

  _runAnalysis() async {
    setState(() => _loading = true);
    try {
      final res = await ApiService.get('/goats/${widget.goatId}/predict');
      if (res.statusCode == 200) {
        final data = jsonDecode(res.body);
        if (mounted) Navigator.push(context, MaterialPageRoute(builder: (_) => AIPredictionPage(predictionData: data)));
      }
    } catch (_) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Gagal menjalankan analisis AI')));
    }
    setState(() => _loading = false);
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(gradient: LinearGradient(colors: [Colors.purple.shade700, Colors.purple.shade400]), borderRadius: BorderRadius.circular(16)),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Row(children: [Icon(Icons.auto_awesome, color: Colors.white, size: 20), SizedBox(width: 8), Text('Analisis Kecerdasan AI', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold))]),
          const SizedBox(height: 12),
          SizedBox(
            width: double.infinity,
            child: ElevatedButton(
              onPressed: _loading ? null : _runAnalysis,
              style: ElevatedButton.styleFrom(backgroundColor: Colors.white.withOpacity(0.2), foregroundColor: Colors.white, elevation: 0, shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12))),
              child: _loading ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2)) : const Text('JALANKAN ANALISIS & FORECAST'),
            ),
          ),
        ],
      ),
    );
  }
}

class AIPredictionPage extends StatelessWidget {
  final Map<String, dynamic> predictionData;
  const AIPredictionPage({super.key, required this.predictionData});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('AI Insights 🧠', style: TextStyle(fontWeight: FontWeight.bold)),
        backgroundColor: Colors.transparent,
        elevation: 0,
        foregroundColor: Colors.purple.shade900,
      ),
      extendBodyBehindAppBar: true,
      body: Container(
        width: double.infinity,
        height: double.infinity,
        decoration: BoxDecoration(
          gradient: LinearGradient(begin: Alignment.topLeft, end: Alignment.bottomRight, colors: [Colors.purple.shade50, Colors.white]),
        ),
        child: SafeArea(
          child: SingleChildScrollView(
            padding: const EdgeInsets.all(24),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text('FORECAST PERTUMBUHAN (30 HARI)', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Colors.purple, letterSpacing: 1.2)),
                const SizedBox(height: 16),
                Container(
                  height: 220,
                  padding: const EdgeInsets.all(20),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(24),
                    boxShadow: [BoxShadow(color: Colors.purple.withOpacity(0.05), blurRadius: 20, offset: const Offset(0, 10))],
                  ),
                  child: LineChart(LineChartData(
                    gridData: const FlGridData(show: false),
                    titlesData: FlTitlesData(
                      leftTitles: const AxisTitles(sideTitles: SideTitles(showTitles: false)),
                      bottomTitles: AxisTitles(sideTitles: SideTitles(showTitles: true, getTitlesWidget: (val, meta) => val == 0 ? const Text('Sekarang', style: TextStyle(fontSize: 11, color: Colors.grey)) : val == 1 ? const Text('+30 Hari', style: TextStyle(fontSize: 11, color: Colors.grey, fontWeight: FontWeight.bold)) : const SizedBox())),
                      rightTitles: const AxisTitles(sideTitles: SideTitles(showTitles: false)), topTitles: const AxisTitles(sideTitles: SideTitles(showTitles: false)),
                    ),
                    borderData: FlBorderData(show: false),
                    lineBarsData: [LineChartBarData(
                      spots: [FlSpot(0, (predictionData['current_weight'] as num).toDouble()), FlSpot(1, (predictionData['predicted_weight_next_month'] as num).toDouble())],
                      isCurved: true,
                      color: Colors.purple.shade400,
                      barWidth: 5,
                      isStrokeCapRound: true,
                      dotData: const FlDotData(show: true),
                      belowBarData: BarAreaData(
                        show: true,
                        gradient: LinearGradient(colors: [Colors.purple.withOpacity(0.3), Colors.transparent], begin: Alignment.topCenter, end: Alignment.bottomCenter),
                      ),
                    )],
                  )),
                ),
                const SizedBox(height: 32),
                const Text('ANALISIS & REKOMENDASI AI', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Colors.purple, letterSpacing: 1.2)),
                const SizedBox(height: 16),
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.all(24),
                  decoration: BoxDecoration(
                    gradient: LinearGradient(colors: [Colors.purple.shade900, Colors.purple.shade700]),
                    borderRadius: BorderRadius.circular(24),
                    boxShadow: [BoxShadow(color: Colors.purple.withOpacity(0.3), blurRadius: 15, offset: const Offset(0, 8))],
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          const Text('Health Score', style: TextStyle(color: Colors.white70, fontSize: 16)),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                            decoration: BoxDecoration(color: Colors.white.withOpacity(0.2), borderRadius: BorderRadius.circular(20)),
                            child: Text('${(predictionData['confidence_score'] * 100).toInt()}/100', style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 18)),
                          ),
                        ],
                      ),
                      const Padding(padding: EdgeInsets.symmetric(vertical: 20), child: Divider(color: Colors.white24, height: 1)),
                      Text(predictionData['analysis'] ?? 'Gagal memuat analisis.', style: const TextStyle(fontSize: 16, height: 1.6, color: Colors.white)),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
