import 'dart:convert';
import 'dart:io';
import 'package:flutter/material.dart';
import 'package:mobile_scanner/mobile_scanner.dart';
import 'package:vibration/vibration.dart';
import 'package:intl/intl.dart';
import 'package:image_picker/image_picker.dart';
import 'package:fl_chart/fl_chart.dart';
import '../services/app_services.dart';
import 'goat_detail_page.dart';

class QRScannerPage extends StatefulWidget {
  const QRScannerPage({super.key});

  @override
  State<QRScannerPage> createState() => _QRScannerPageState();
}

class _QRScannerPageState extends State<QRScannerPage> {
  bool _isScanning = true;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Scan QR Ternak'),
        backgroundColor: Colors.transparent,
        foregroundColor: Colors.white,
        elevation: 0,
      ),
      extendBodyBehindAppBar: true,
      body: Stack(
        children: [
          MobileScanner(
            onDetect: (capture) {
              if (!_isScanning) return;
              final List<Barcode> barcodes = capture.barcodes;
              for (final barcode in barcodes) {
                if (barcode.rawValue != null) {
                  setState(() => _isScanning = false);
                  _onQRCodeScanned(barcode.rawValue!);
                  break;
                }
              }
            },
          ),
          Center(
            child: Container(
              width: 250, height: 250,
              decoration: BoxDecoration(border: Border.all(color: Colors.white, width: 2), borderRadius: BorderRadius.circular(24)),
              child: Stack(
                children: [
                  Positioned(top: 0, left: 0, child: Container(width: 40, height: 40, decoration: const BoxDecoration(border: Border(top: BorderSide(color: Colors.green, width: 4), left: BorderSide(color: Colors.green, width: 4)), borderRadius: BorderRadius.only(topLeft: Radius.circular(24))))),
                ],
              ),
            ),
          ),
          const Positioned(bottom: 100, left: 0, right: 0, child: Center(child: Text('Arahkan kamera ke QR Code', style: TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold)))),
        ],
      ),
    );
  }

  void _onQRCodeScanned(String code) async {
    if (await Vibration.hasVibrator() ?? false) Vibration.vibrate(duration: 100);

    String cleanCode = code.trim();
    if (cleanCode.startsWith('http://') || cleanCode.startsWith('https://')) {
      try {
        final uri = Uri.parse(cleanCode);
        final pathSegments = uri.pathSegments;
        if (pathSegments.contains('catalog')) {
          final index = pathSegments.indexOf('catalog');
          if (index != -1 && index + 1 < pathSegments.length) {
            cleanCode = pathSegments[index + 1];
          }
        } else if (pathSegments.isNotEmpty) {
          cleanCode = pathSegments.last;
        }
      } catch (_) {}
    }

    if (mounted) {
      showModalBottomSheet(
        context: context, 
        isScrollControlled: true, 
        backgroundColor: Colors.transparent, 
        builder: (context) => _QuickInfoBottomSheet(qrCode: cleanCode)
      ).then((_) {
        if (mounted) setState(() => _isScanning = true);
      });
    }
  }
}

class _QuickInfoBottomSheet extends StatefulWidget {
  final String qrCode;
  const _QuickInfoBottomSheet({required this.qrCode});

  @override
  State<_QuickInfoBottomSheet> createState() => _QuickInfoBottomSheetState();
}

class _QuickInfoBottomSheetState extends State<_QuickInfoBottomSheet> {
  bool _loading = true;
  Map<String, dynamic>? _goatData;
  final _weightController = TextEditingController();
  bool _saving = false;

  @override
  void initState() {
    super.initState();
    _fetchGoat();
  }

  _fetchGoat() async {
    try {
      final res = await ApiService.get('/goats/${widget.qrCode}'); 
      if (res.statusCode == 200) {
        final Map<String, dynamic> goat = jsonDecode(res.body);
        await DbHelper.saveGoats([goat]);
        setState(() { _goatData = goat; _loading = false; });
        return;
      }
    } catch (_) {}
    final localGoat = await DbHelper.getGoat(widget.qrCode);
    setState(() { _goatData = localGoat; _loading = false; });
  }

  _saveWeight() async {
    if (_weightController.text.isEmpty) return;
    setState(() => _saving = true);
    final body = { 'weight': double.parse(_weightController.text), 'date_recorded': DateFormat('yyyy-MM-dd').format(DateTime.now()), 'note': 'Input via Mobile Quick Scan' };
    try {
      final res = await ApiService.post('/goats/${_goatData!['id']}/weight', body);
      if (res.statusCode == 201) {
        if (mounted) { ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Berat berhasil dicatat! 🐐⚖️'))); Navigator.pop(context); }
        return;
      }
    } catch (_) {}
    await DbHelper.addToQueue('/goats/${_goatData!['id']}/weight', 'POST', body);
    if (mounted) { ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Offline: Berat disimpan di antrean sync. 📡'), backgroundColor: Colors.orange)); Navigator.pop(context); }
  }

  void _showHealthEntry(BuildContext context) {
    String selectedType = 'Vaksinasi';
    final noteController = TextEditingController();
    DateTime? nextSchedule;
    File? imageFile;

    showDialog(
      context: context,
      builder: (context) => StatefulBuilder(
        builder: (context, setDialogState) => AlertDialog(
          title: const Text('Catat Kesehatan'),
          content: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                DropdownButtonFormField<String>(
                  value: selectedType, items: ['Vaksinasi', 'Pemberian Vitamin', 'Obat Cacing', 'Pengobatan Sakit', 'Cek Rutin'].map((e) => DropdownMenuItem(value: e, child: Text(e))).toList(),
                  onChanged: (v) => selectedType = v!, decoration: const InputDecoration(labelText: 'Jenis Tindakan'),
                ),
                TextField(controller: noteController, decoration: const InputDecoration(labelText: 'Catatan / Nama Obat'), maxLines: 2),
                const SizedBox(height: 16),
                OutlinedButton.icon(
                  onPressed: () async {
                    final picked = await showDatePicker(context: context, initialDate: DateTime.now().add(const Duration(days: 30)), firstDate: DateTime.now(), lastDate: DateTime.now().add(const Duration(days: 365)));
                    if (picked != null) setDialogState(() => nextSchedule = picked);
                  },
                  icon: const Icon(Icons.calendar_today), label: Text(nextSchedule == null ? 'Pilih Jadwal Berikutnya' : DateFormat('dd MMM yyyy').format(nextSchedule!)),
                ),
                const SizedBox(height: 16),
                if (imageFile != null) Stack(children: [ClipRRect(borderRadius: BorderRadius.circular(12), child: Image.file(imageFile!, height: 150, width: double.infinity, fit: BoxFit.cover)), Positioned(right: 8, top: 8, child: CircleAvatar(backgroundColor: Colors.black54, child: IconButton(icon: const Icon(Icons.close, color: Colors.white), onPressed: () => setDialogState(() => imageFile = null))))])
                else OutlinedButton.icon(onPressed: () async { final photo = await ImagePicker().pickImage(source: ImageSource.camera, imageQuality: 50); if (photo != null) setDialogState(() => imageFile = File(photo.path)); }, icon: const Icon(Icons.camera_alt), label: const Text('AMBIL FOTO')),
              ],
            ),
          ),
          actions: [
            TextButton(onPressed: () => Navigator.pop(context), child: const Text('BATAL')),
            ElevatedButton(
              onPressed: () async {
                String? base64Image;
                if (imageFile != null) base64Image = base64Encode(await imageFile!.readAsBytes());
                final body = { 'type': selectedType, 'title': selectedType, 'note': noteController.text, 'date_recorded': DateFormat('yyyy-MM-dd').format(DateTime.now()), 'status': 'completed', 'next_scheduled_date': nextSchedule != null ? DateFormat('yyyy-MM-dd').format(nextSchedule!) : null, 'image': base64Image };
                if (context.mounted) Navigator.pop(context);
                setState(() => _saving = true);
                try { final res = await ApiService.post('/goats/${_goatData!['id']}/health', body); if (res.statusCode == 201) { if (mounted) ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Catatan kesehatan disimpan! 💉📸'))); } }
                catch (_) { await DbHelper.addToQueue('/goats/${_goatData!['id']}/health', 'POST', body); if (mounted) ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Offline: Antrean disimpan. 📡'), backgroundColor: Colors.orange)); }
                finally { setState(() => _saving = false); }
              },
              child: const Text('SIMPAN'),
            ),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: EdgeInsets.only(left: 24, right: 24, top: 24, bottom: MediaQuery.of(context).viewInsets.bottom + 24),
      decoration: const BoxDecoration(color: Colors.white, borderRadius: BorderRadius.vertical(top: Radius.circular(32))),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Container(width: 40, height: 4, decoration: BoxDecoration(color: Colors.grey.shade300, borderRadius: BorderRadius.circular(2))),
          const SizedBox(height: 24),
          if (_loading) const CircularProgressIndicator()
          else if (_goatData == null) const Text('Data Kambing Tidak Ditemukan')
          else ...[
            Row(children: [CircleAvatar(radius: 35, backgroundColor: const Color(0xFF4A6741).withOpacity(0.1), child: const Icon(Icons.pets, size: 30, color: Color(0xFF4A6741))), const SizedBox(width: 16), Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [Text(_goatData!['name'], style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold)), Text('QR: ${widget.qrCode}', style: const TextStyle(color: Colors.grey, fontSize: 12))]))]),
            const Divider(height: 32),
            TextField(controller: _weightController, keyboardType: TextInputType.number, decoration: InputDecoration(hintText: 'Masukkan berat (kg)', filled: true, fillColor: Colors.grey.shade100, border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide.none), prefixIcon: const Icon(Icons.monitor_weight_outlined))),
            const SizedBox(height: 12),
            SizedBox(width: double.infinity, child: ElevatedButton(onPressed: _saveWeight, style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFF4A6741), foregroundColor: Colors.white), child: const Text('SIMPAN BERAT'))),
            SizedBox(width: double.infinity, child: OutlinedButton.icon(onPressed: () => _showHealthEntry(context), icon: const Icon(Icons.medical_services_outlined), label: const Text('CATAT KESEHATAN'))),
            const SizedBox(height: 12),
            SizedBox(width: double.infinity, child: TextButton(onPressed: () { Navigator.pop(context); Navigator.push(context, MaterialPageRoute(builder: (_) => GoatDetailPage(id: _goatData!['id'].toString()))); }, child: const Text('LIHAT DETAIL LENGKAP'))),
          ]
        ],
      ),
    );
  }
}
