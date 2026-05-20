import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:fl_chart/fl_chart.dart';
import '../services/app_services.dart';

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

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Detail Ternak')),
      body: FutureBuilder<Map<String, dynamic>>(
        future: _goatFuture,
        builder: (context, snapshot) {
          if (snapshot.connectionState == ConnectionState.waiting) return const Center(child: CircularProgressIndicator());
          if (snapshot.hasError) return Center(child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [const Icon(Icons.error_outline, size: 48, color: Colors.red), const SizedBox(height: 16), Text('Error: ${snapshot.error}')]));
          
          final goat = snapshot.data!;
          final weightLogs = (goat['weight_logs'] as List? ?? []).reversed.toList();
          final healthRecords = (goat['health_records'] as List? ?? []).reversed.toList();

          return DefaultTabController(
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
          );
        },
      ),
    );
  }

  Widget _buildHeader(Map<String, dynamic> goat) {
    // Collect all images for the gallery
    List<String> images = [];
    if (goat['image_url'] != null) images.add(goat['image_url']);
    final healthRecords = goat['health_records'] as List? ?? [];
    for (var record in healthRecords) {
      if (record['image_url'] != null) images.add(record['image_url']);
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
                  child: Container(
                    margin: const EdgeInsets.only(right: 12),
                    width: 120,
                    decoration: BoxDecoration(
                      borderRadius: BorderRadius.circular(16),
                      image: DecorationImage(image: NetworkImage(images[i]), fit: BoxFit.cover),
                      boxShadow: [BoxShadow(color: Colors.black12, blurRadius: 4, offset: const Offset(0, 2))],
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
          Text(goat['name'], style: const TextStyle(fontSize: 24, fontWeight: FontWeight.bold)),
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
          child: Image.network(imageUrl, fit: BoxFit.contain),
        ),
      ),
    )));
  }

  Widget _buildInfoTab(Map<String, dynamic> goat) {
    return ListView(
      padding: const EdgeInsets.all(20),
      children: [
        _AIPredictionCard(goatId: goat['id'].toString()),
        const SizedBox(height: 20),
        _buildInfoCard([
          _buildInfoRow('Jenis', goat['breed'] ?? '-'),
          _buildInfoRow('Jenis Kelamin', goat['gender'] == 'male' ? 'Jantan' : 'Betina'),
          _buildInfoRow('Tanggal Lahir', goat['date_of_birth'] ?? '-'),
          _buildInfoRow('Berat Terakhir', '${goat['weight'] ?? '-'} kg'),
        ]),
        const SizedBox(height: 24),
        const Text('Visual Silsilah (Pedigree)', style: TextStyle(fontWeight: FontWeight.bold, color: Colors.grey, fontSize: 12, letterSpacing: 1)),
        const SizedBox(height: 16),
        _buildPedigreeTree(goat),
        const SizedBox(height: 24),
        const Text('Catatan Tambahan', style: TextStyle(fontWeight: FontWeight.bold, color: Colors.grey, fontSize: 12, letterSpacing: 1)),
        const SizedBox(height: 12),
        Container(
          padding: const EdgeInsets.all(16),
          width: double.infinity,
          decoration: BoxDecoration(color: Colors.grey.shade50, borderRadius: BorderRadius.circular(12), border: Border.all(color: Colors.grey.shade200)),
          child: Text(goat['note']?.isEmpty == false ? goat['note'] : 'Tidak ada catatan.', style: const TextStyle(height: 1.5)),
        ),
      ],
    );
  }

  Widget _buildPedigreeTree(Map<String, dynamic> goat) {
    return Column(
      children: [
        Row(
          children: [
            Expanded(child: _buildParentNode('Bapak (Sire)', goat['sire']?['name'], Icons.male, Colors.blue)),
            const SizedBox(width: 16),
            Expanded(child: _buildParentNode('Induk (Dam)', goat['dam']?['name'], Icons.female, Colors.pink)),
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

  Widget _buildParentNode(String label, String? name, IconData icon, Color color) {
    bool hasParent = name != null;
    return Container(
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
    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: logs.length,
      itemBuilder: (context, i) {
        final log = logs[i];
        return Card(
          elevation: 0,
          margin: const EdgeInsets.only(bottom: 8),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12), side: BorderSide(color: Colors.grey.shade200)),
          child: ListTile(
            leading: const Icon(Icons.monitor_weight_outlined, color: Colors.blue),
            title: Text('${log['weight']} kg', style: const TextStyle(fontWeight: FontWeight.bold)),
            subtitle: Text(log['date_recorded']),
          ),
        );
      },
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
                    margin: const EdgeInsets.symmetric(horizontal: 16, bottom: 16),
                    decoration: BoxDecoration(
                      borderRadius: BorderRadius.circular(8),
                      image: DecorationImage(image: NetworkImage(record['image_url']), fit: BoxFit.cover),
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
      appBar: AppBar(title: const Text('Analisis AI Qandang'), backgroundColor: Colors.purple.shade700, foregroundColor: Colors.white),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text('GRAFIK FORECAST PERTUMBUHAN', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12, color: Colors.grey, letterSpacing: 1)),
            const SizedBox(height: 20),
            Container(
              height: 200, padding: const EdgeInsets.all(16), decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(16), border: Border.all(color: Colors.grey.shade100)),
              child: LineChart(LineChartData(
                gridData: const FlGridData(show: true, drawVerticalLine: false),
                titlesData: FlTitlesData(
                  leftTitles: const AxisTitles(sideTitles: SideTitles(showTitles: true, reservedSize: 40)),
                  bottomTitles: AxisTitles(sideTitles: SideTitles(showTitles: true, getTitlesWidget: (val, meta) => val == 0 ? const Text('Skrg', style: TextStyle(fontSize: 10)) : val == 1 ? const Text('Bulan Dpn', style: TextStyle(fontSize: 10)) : const SizedBox())),
                  rightTitles: const AxisTitles(sideTitles: SideTitles(showTitles: false)), topTitles: const AxisTitles(sideTitles: SideTitles(showTitles: false)),
                ),
                borderData: FlBorderData(show: false),
                lineBarsData: [LineChartBarData(spots: [FlSpot(0, (predictionData['current_weight'] as num).toDouble()), FlSpot(1, (predictionData['predicted_weight_next_month'] as num).toDouble())], isCurved: false, color: Colors.purple, barWidth: 4, isStrokeCapRound: true, dotData: const FlDotData(show: true), belowBarData: BarAreaData(show: true, color: Colors.purple.withOpacity(0.1)))],
              )),
            ),
            const SizedBox(height: 32),
            const Text('DETAIL ANALISIS & REKOMENDASI', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12, color: Colors.grey, letterSpacing: 1)),
            const SizedBox(height: 16),
            Container(
              width: double.infinity, padding: const EdgeInsets.all(20), decoration: BoxDecoration(color: Colors.purple.shade50, borderRadius: BorderRadius.circular(16), border: Border.all(color: Colors.purple.shade100)),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [const Text('Skor Kesehatan', style: TextStyle(fontWeight: FontWeight.bold)), Text('${(predictionData['confidence_score'] * 100).toInt()}/100', style: TextStyle(color: Colors.purple.shade700, fontWeight: FontWeight.bold, fontSize: 20))]),
                  const Divider(height: 32),
                  Text(predictionData['analysis'] ?? 'Gagal memuat analisis.', style: const TextStyle(fontSize: 15, height: 1.6)),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}
