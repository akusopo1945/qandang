import 'package:flutter/material.dart';
import 'dart:async';
import 'dart:math';

class BarnMonitoringPage extends StatefulWidget {
  const BarnMonitoringPage({super.key});

  @override
  State<BarnMonitoringPage> createState() => _BarnMonitoringPageState();
}

class _BarnMonitoringPageState extends State<BarnMonitoringPage> {
  double _temp = 28.5;
  double _humidity = 65.0;
  double _ammonia = 12.0;
  bool _isAutoFan = true;
  Timer? _timer;

  @override
  void initState() {
    super.initState();
    // Simulate real-time data updates
    _timer = Timer.periodic(const Duration(seconds: 3), (timer) {
      if (mounted) {
        setState(() {
          _temp += (Random().nextDouble() - 0.5) * 0.5;
          _humidity += (Random().nextDouble() - 0.5) * 1.0;
          _ammonia += (Random().nextDouble() - 0.5) * 0.2;
        });
      }
    });
  }

  @override
  void dispose() {
    _timer?.cancel();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Monitoring Kandang (IoT)')),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            _buildStatusHeader(),
            const SizedBox(height: 24),
            const Text('Kondisi Real-time', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
            const SizedBox(height: 16),
            GridView.count(
              crossAxisCount: 2,
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              mainAxisSpacing: 16,
              crossAxisSpacing: 16,
              childAspectRatio: 1.1,
              children: [
                _buildSensorCard('Suhu Udara', '${_temp.toStringAsFixed(1)}°C', Icons.thermostat, Colors.orange, 'Normal'),
                _buildSensorCard('Kelembaban', '${_humidity.toStringAsFixed(1)}%', Icons.water_drop, Colors.blue, 'Ideal'),
                _buildSensorCard('Kadar Amonia', '${_ammonia.toStringAsFixed(1)} ppm', Icons.air, Colors.green, 'Sehat'),
                _buildSensorCard('Intensitas Cahaya', '450 Lux', Icons.light_mode, Colors.yellow.shade800, 'Cukup'),
              ],
            ),
            const SizedBox(height: 32),
            const Text('Kontrol Perangkat', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
            const SizedBox(height: 16),
            _buildControlCard('Kipas Exhaust', 'Menjaga sirkulasi udara', _isAutoFan, (v) => setState(() => _isAutoFan = v)),
            _buildControlCard('Lampu Malam', 'Penerangan otomatis', false, (v) {}),
            const SizedBox(height: 40),
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(color: Colors.blue.shade50, borderRadius: BorderRadius.circular(16), border: Border.all(color: Colors.blue.shade100)),
              child: const Row(
                children: [
                  Icon(Icons.info_outline, color: Colors.blue),
                  SizedBox(width: 12),
                  Expanded(child: Text('Data ini disinkronkan secara real-time dari Gateway IoT Kandang Utama.', style: TextStyle(fontSize: 12, color: Colors.blue, fontWeight: FontWeight.w500))),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildStatusHeader() {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        gradient: LinearGradient(colors: [Colors.green.shade700, Colors.green.shade400]),
        borderRadius: BorderRadius.circular(24),
        boxShadow: [BoxShadow(color: Colors.green.withOpacity(0.3), blurRadius: 10, offset: const Offset(0, 4))],
      ),
      child: Row(
        children: [
          const CircleAvatar(radius: 30, backgroundColor: Colors.white24, child: Icon(Icons.router, color: Colors.white, size: 30)),
          const SizedBox(width: 16),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text('Gateway IoT: Online', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 18)),
                Text('Terakhir update: Baru saja', style: TextStyle(color: Colors.white.withOpacity(0.8), fontSize: 12)),
              ],
            ),
          ),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
            decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(20)),
            child: const Text('STABIL', style: TextStyle(color: Colors.green, fontWeight: FontWeight.bold, fontSize: 10)),
          ),
        ],
      ),
    );
  }

  Widget _buildSensorCard(String label, String value, IconData icon, Color color, String status) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(20), border: Border.all(color: Colors.grey.shade100), boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.02), blurRadius: 5, offset: const Offset(0, 2))]),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(icon, color: color, size: 28),
          const SizedBox(height: 12),
          Text(value, style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold)),
          Text(label, style: const TextStyle(fontSize: 11, color: Colors.grey)),
          const SizedBox(height: 8),
          Text(status, style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: color)),
        ],
      ),
    );
  }

  Widget _buildControlCard(String title, String subtitle, bool value, Function(bool) onChanged) {
    return Card(
      elevation: 0,
      margin: const EdgeInsets.only(bottom: 12),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16), side: BorderSide(color: Colors.grey.shade200)),
      child: SwitchListTile(
        title: Text(title, style: const TextStyle(fontWeight: FontWeight.bold)),
        subtitle: Text(subtitle, style: const TextStyle(fontSize: 12)),
        value: value,
        onChanged: onChanged,
        activeColor: Colors.green,
      ),
    );
  }
}
