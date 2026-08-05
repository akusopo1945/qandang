import 'dart:convert';
import 'dart:io';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:vibration/vibration.dart';
import 'package:url_launcher/url_launcher.dart';
import '../services/app_services.dart';
import '../widgets/premium_image.dart';
import 'goat_detail_page.dart';

import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../services/providers.dart';

class GoatListPage extends ConsumerStatefulWidget {
  const GoatListPage({super.key});

  @override
  ConsumerState<GoatListPage> createState() => _GoatListPageState();
}

class _GoatListPageState extends ConsumerState<GoatListPage> {
  List<dynamic> _allGoats = [];
  List<dynamic> _filteredGoats = [];
  String _searchQuery = '';
  String _filterBreed = 'Semua';
  bool _isLoading = false;

  @override
  void initState() {
    super.initState();
    _refreshData();
  }

  Future<void> _refreshData() async {
    if (mounted) setState(() => _isLoading = true);
    try {
      final value = await _fetchGoats();
      if (mounted) {
        setState(() {
          _allGoats = value;
          _applyFilter();
        });
      }
    } catch (_) {
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  void _applyFilter() {
    setState(() {
      _filteredGoats = _allGoats.where((goat) {
        final matchesSearch = goat['name'].toLowerCase().contains(_searchQuery.toLowerCase()) || 
                             (goat['qr_code'] ?? '').toLowerCase().contains(_searchQuery.toLowerCase());
        final matchesBreed = _filterBreed == 'Semua' || goat['breed'] == _filterBreed;
        return matchesSearch && matchesBreed;
      }).toList();
    });
  }

  Future<List<dynamic>> _fetchGoats() async {
    try {
      final res = await ApiService.get('/goats');
      if (res.statusCode == 200) {
        final goats = jsonDecode(res.body);
        await DbHelper.saveGoats(goats);
        return goats;
      }
    } catch (_) {
    }
    final localGoats = await DbHelper.getGoats();
    return localGoats.map((g) => {
      ...g,
      'weight_logs': jsonDecode(g['weight_logs'] as String? ?? '[]'),
      'health_records': jsonDecode(g['health_records'] as String? ?? '[]'),
    }).toList();
  }

  void _showGoatForm(BuildContext context, {Map<String, dynamic>? goat}) {
    final bool isEdit = goat != null;
    final nameController = TextEditingController(text: goat?['name'] ?? '');
    final breedController = TextEditingController(text: goat?['breed'] ?? '');
    final blockController = TextEditingController(text: goat?['barn_block'] ?? '');
    final qrController = TextEditingController(text: goat?['qr_code'] ?? '');
    final initialWeightController = TextEditingController(text: goat?['initial_weight']?.toString() ?? '');
    final currentWeightController = TextEditingController(text: goat?['current_weight']?.toString() ?? goat?['weight']?.toString() ?? '');
    final heightController = TextEditingController(text: goat?['height']?.toString() ?? '');
    final targetWeightController = TextEditingController(text: goat?['target_weight']?.toString() ?? '');
    final purchasePriceController = TextEditingController(text: goat?['purchase_price']?.toString() ?? '');
    final feedingCostController = TextEditingController(text: goat?['feeding_cost']?.toString() ?? '');
    final marketPriceController = TextEditingController(text: goat?['price']?.toString() ?? '');
    final descController = TextEditingController(text: goat?['description'] ?? goat?['note'] ?? '');
    
    String gender = goat?['gender'] ?? 'male';
    String purpose = goat?['purpose'] ?? 'fattening';
    String reproStatus = goat?['reproduction_status'] ?? 'empty';
    DateTime? birthDate = goat?['birth_date'] != null ? DateTime.tryParse(goat!['birth_date']) : null;
    
    int? selectedDamId = goat?['dam_id'];
    int? selectedSireId = goat?['sire_id'];
    
    String damName = 'Pilih Induk (Dam)';
    String sireName = 'Pilih Bapak (Sire)';
    
    if (selectedDamId != null) {
      final dam = _allGoats.firstWhere((g) => g['id'] == selectedDamId, orElse: () => null);
      if (dam != null) damName = dam['name'];
    }
    if (selectedSireId != null) {
      final sire = _allGoats.firstWhere((g) => g['id'] == selectedSireId, orElse: () => null);
      if (sire != null) sireName = sire['name'];
    }

    File? goatImageFile;
    String? base64Image;

    bool isSaving = false;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
      builder: (context) => StatefulBuilder(
        builder: (context, setModalState) => DraggableScrollableSheet(
          initialChildSize: 0.85,
          maxChildSize: 0.95,
          minChildSize: 0.5,
          expand: false,
          builder: (context, scrollController) => Padding(
            padding: EdgeInsets.only(left: 20, right: 20, top: 20, bottom: MediaQuery.of(context).viewInsets.bottom + 20),
            child: ListView(
              controller: scrollController,
              children: [
                Text(isEdit ? 'Edit Data Ternak' : 'Tambah Ternak Baru (Form Lengkap)', style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
                const SizedBox(height: 16),
                
                Center(
                  child: GestureDetector(
                    onTap: () async {
                      final picked = await ImagePicker().pickImage(source: ImageSource.gallery, imageQuality: 75, maxWidth: 800);
                      if (picked != null) {
                        final file = File(picked.path);
                        final bytes = await file.readAsBytes();
                        setModalState(() {
                          goatImageFile = file;
                          base64Image = base64Encode(bytes);
                        });
                      }
                    },
                    child: Container(
                      width: 100,
                      height: 100,
                      decoration: BoxDecoration(
                        color: Colors.grey.shade100,
                        borderRadius: BorderRadius.circular(16),
                        border: Border.all(color: Colors.grey.shade300),
                        image: goatImageFile != null
                            ? DecorationImage(image: FileImage(goatImageFile!), fit: BoxFit.cover)
                            : (goat?['image_url'] != null
                                ? DecorationImage(image: NetworkImage(goat!['image_url']), fit: BoxFit.cover)
                                : null),
                      ),
                      child: (goatImageFile == null && goat?['image_url'] == null)
                          ? const Column(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                Icon(Icons.camera_alt_outlined, color: Colors.grey),
                                SizedBox(height: 4),
                                Text('Foto Kambing', style: TextStyle(fontSize: 10, color: Colors.grey)),
                              ],
                            )
                          : null,
                    ),
                  ),
                ),
                const SizedBox(height: 16),

                TextField(controller: nameController, decoration: const InputDecoration(labelText: 'Nama / No. Telinga *')),
                const SizedBox(height: 12),
                Row(
                  children: [
                    Expanded(child: TextField(controller: qrController, decoration: const InputDecoration(labelText: 'Kode QR / ID (Opsional)'))),
                    const SizedBox(width: 12),
                    Expanded(child: TextField(controller: blockController, decoration: const InputDecoration(labelText: 'Blok / Sekat Kandang', hintText: 'misal: A-01'))),
                  ],
                ),
                const SizedBox(height: 12),
                DropdownButtonFormField<String>(
                  value: ['Jawa Randu', 'Etawa', 'Boer', 'Saanen', 'Boran', 'Lokal'].contains(breedController.text) ? breedController.text : null,
                  decoration: const InputDecoration(labelText: 'Ras / Jenis Ternak'),
                  hint: Text(breedController.text.isNotEmpty ? breedController.text : 'Pilih Ras'),
                  items: ['Jawa Randu', 'Etawa', 'Boer', 'Saanen', 'Boran', 'Lokal'].map((b) => DropdownMenuItem(value: b, child: Text(b))).toList(),
                  onChanged: (v) => breedController.text = v ?? '',
                ),
                const SizedBox(height: 16),
                
                const Text('Jenis Kelamin *', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                Row(
                  children: [
                    Radio<String>(value: 'male', groupValue: gender, onChanged: (v) => setModalState(() => gender = v!)),
                    const Text('Jantan'),
                    const SizedBox(width: 20),
                    Radio<String>(value: 'female', groupValue: gender, onChanged: (v) => setModalState(() => gender = v!)),
                    const Text('Betina'),
                  ],
                ),
                const SizedBox(height: 12),

                DropdownButtonFormField<String>(
                  value: purpose,
                  decoration: const InputDecoration(labelText: 'Tujuan Pemeliharaan'),
                  items: const [
                    DropdownMenuItem(value: 'fattening', child: Text('Penggemukan (Fattening)')),
                    DropdownMenuItem(value: 'breeding', child: Text('Pembibitan (Breeding)')),
                  ],
                  onChanged: (v) => setModalState(() => purpose = v!),
                ),
                const SizedBox(height: 12),

                if (gender == 'female' && purpose == 'breeding') ...[
                  DropdownButtonFormField<String>(
                    value: reproStatus,
                    decoration: const InputDecoration(labelText: 'Status Reproduksi'),
                    items: const [
                      DropdownMenuItem(value: 'empty', child: Text('Kosong')),
                      DropdownMenuItem(value: 'heat', child: Text('Birahi (Heat)')),
                      DropdownMenuItem(value: 'pregnant', child: Text('Bunting')),
                      DropdownMenuItem(value: 'lactating', child: Text('Menyusui')),
                      DropdownMenuItem(value: 'dry', child: Text('Kering Susu')),
                    ],
                    onChanged: (v) => setModalState(() => reproStatus = v!),
                  ),
                  const SizedBox(height: 12),
                ],

                OutlinedButton.icon(
                  onPressed: () async {
                    final picked = await showDatePicker(
                      context: context,
                      initialDate: birthDate ?? DateTime.now().subtract(const Duration(days: 180)),
                      firstDate: DateTime(2010),
                      lastDate: DateTime.now(),
                    );
                    if (picked != null) setModalState(() => birthDate = picked);
                  },
                  icon: const Icon(Icons.calendar_today),
                  label: Text(birthDate == null ? 'Pilih Tanggal Lahir' : 'Tgl Lahir: ${birthDate!.year}-${birthDate!.month.toString().padLeft(2, '0')}-${birthDate!.day.toString().padLeft(2, '0')}'),
                ),
                const SizedBox(height: 16),

                const Text('Detail Fisik & Bobot', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Colors.grey)),
                const SizedBox(height: 8),
                Row(
                  children: [
                    Expanded(child: TextField(controller: initialWeightController, keyboardType: TextInputType.number, decoration: const InputDecoration(labelText: 'Berat Awal (kg)', suffixText: 'kg'))),
                    const SizedBox(width: 12),
                    Expanded(child: TextField(controller: currentWeightController, keyboardType: TextInputType.number, decoration: const InputDecoration(labelText: 'Berat Saat Ini (kg)', suffixText: 'kg'))),
                  ],
                ),
                const SizedBox(height: 12),
                Row(
                  children: [
                    Expanded(child: TextField(controller: heightController, keyboardType: TextInputType.number, decoration: const InputDecoration(labelText: 'Tinggi (cm)', suffixText: 'cm'))),
                    if (purpose == 'fattening') ...[
                      const SizedBox(width: 12),
                      Expanded(child: TextField(controller: targetWeightController, keyboardType: TextInputType.number, decoration: const InputDecoration(labelText: 'Target (kg)', suffixText: 'kg'))),
                    ],
                  ],
                ),
                const SizedBox(height: 16),

                const Text('Finansial & Modal (Rp)', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Colors.grey)),
                const SizedBox(height: 8),
                Row(
                  children: [
                    Expanded(child: TextField(controller: purchasePriceController, keyboardType: TextInputType.number, decoration: const InputDecoration(labelText: 'Harga Beli (Rp)', prefixText: 'Rp '))),
                    const SizedBox(width: 12),
                    Expanded(child: TextField(controller: feedingCostController, keyboardType: TextInputType.number, decoration: const InputDecoration(labelText: 'Biaya Pakan/Medis', prefixText: 'Rp '))),
                  ],
                ),
                const SizedBox(height: 12),
                TextField(controller: marketPriceController, keyboardType: TextInputType.number, decoration: const InputDecoration(labelText: 'Estimasi Harga Jual Pasaran (Rp)', prefixText: 'Rp ')),
                const SizedBox(height: 16),

                const Text('Silsilah (Pedigree)', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Colors.grey)),
                const SizedBox(height: 8),
                Row(
                  children: [
                    Expanded(
                      child: OutlinedButton(
                        onPressed: () => _pickParent(context, 'female', (id, name) => setModalState(() { selectedDamId = id; damName = name; })),
                        child: Text(damName, maxLines: 1, overflow: TextOverflow.ellipsis),
                      ),
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: OutlinedButton(
                        onPressed: () => _pickParent(context, 'male', (id, name) => setModalState(() { selectedSireId = id; sireName = name; })),
                        child: Text(sireName, maxLines: 1, overflow: TextOverflow.ellipsis),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 16),

                TextField(
                  controller: descController,
                  maxLines: 2,
                  decoration: const InputDecoration(labelText: 'Catatan / Deskripsi Tambahan'),
                ),
                const SizedBox(height: 24),

                isSaving
                    ? const Center(child: Padding(padding: EdgeInsets.all(8.0), child: CircularProgressIndicator()))
                    : ElevatedButton(
                        onPressed: () async {
                          if (nameController.text.trim().isEmpty) {
                            ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Nama kambing wajib diisi')));
                            return;
                          }

                          setModalState(() => isSaving = true);

                          final Map<String, dynamic> body = {
                            'name': nameController.text.trim(),
                            'breed': breedController.text.trim(),
                            'barn_block': blockController.text.trim(),
                            'qr_code': qrController.text.trim(),
                            'gender': gender,
                            'purpose': purpose,
                            'reproduction_status': reproStatus,
                            'birth_date': birthDate != null ? '${birthDate!.year}-${birthDate!.month.toString().padLeft(2, '0')}-${birthDate!.day.toString().padLeft(2, '0')}' : null,
                            'initial_weight': double.tryParse(initialWeightController.text.trim()),
                            'current_weight': double.tryParse(currentWeightController.text.trim()),
                            'weight': double.tryParse(currentWeightController.text.trim()),
                            'height': double.tryParse(heightController.text.trim()),
                            'target_weight': double.tryParse(targetWeightController.text.trim()),
                            'purchase_price': double.tryParse(purchasePriceController.text.trim()),
                            'feeding_cost': double.tryParse(feedingCostController.text.trim()),
                            'price': double.tryParse(marketPriceController.text.trim()),
                            'description': descController.text.trim(),
                            'note': descController.text.trim(),
                            'dam_id': selectedDamId,
                            'sire_id': selectedSireId,
                          };

                          if (base64Image != null) {
                            body['image'] = base64Image;
                          }

                          try {
                            if (isEdit) {
                              // Untuk edit tetap menggunakan pemrosesan langsung
                              final res = await ApiService.put('/goats/${goat!['id']}', body);
                              if (res.statusCode == 200 || res.statusCode == 201) {
                                final updatedGoat = jsonDecode(res.body);
                                await DbHelper.saveGoats([updatedGoat]);
                                ref.read(goatListProvider.notifier).loadGoats(); // Refresh state
                                try { Vibration.vibrate(duration: 80); } catch (_) {}
                                if (mounted) {
                                  ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Data ternak diperbarui! 🐐')));
                                  Navigator.pop(context);
                                }
                              } else {
                                final err = jsonDecode(res.body);
                                if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(err['message'] ?? 'Gagal menyimpan data')));
                                setModalState(() => isSaving = false);
                              }
                            } else {
                              // Menambahkan kambing baru menggunakan provider Riverpod
                              await ref.read(goatListProvider.notifier).addGoat(body, base64Image);
                              try { Vibration.vibrate(duration: 80); } catch (_) {}
                              if (mounted) {
                                ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Ternak berhasil didaftarkan! 🐐')));
                                Navigator.pop(context);
                              }
                            }
                          } catch (_) {
                            // Offline Fallback untuk Edit/Tambah
                            final targetId = isEdit ? goat!['id'] : DateTime.now().millisecondsSinceEpoch;
                            final localGoat = {
                              'id': targetId,
                              'name': body['name'],
                              'breed': body['breed'],
                              'qr_code': body['qr_code'],
                              'gender': body['gender'],
                              'weight': body['current_weight'],
                              'status': goat?['status'] ?? 'Sehat',
                              'note': body['description'],
                              'date_of_birth': body['birth_date'],
                              'weight_logs': goat?['weight_logs'] ?? jsonEncode([]),
                              'health_records': goat?['health_records'] ?? jsonEncode([]),
                              'dam_id': body['dam_id'],
                              'sire_id': body['sire_id'],
                            };
                            await DbHelper.saveGoats([localGoat]);
                            await DbHelper.addToQueue(
                              isEdit ? '/goats/${goat!['id']}' : '/goats',
                              isEdit ? 'PUT' : 'POST',
                              body,
                            );
                            ref.read(goatListProvider.notifier).loadGoats(); // Refresh state dari db lokal
                            try { Vibration.vibrate(duration: 80); } catch (_) {}
                            if (mounted) {
                              ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Offline: Antrean disimpan di database lokal. 📡'), backgroundColor: Colors.orange));
                              Navigator.pop(context);
                            }
                          }
                        },
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFF4A6741),
                          foregroundColor: Colors.white,
                          minimumSize: const Size(double.infinity, 50),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        ),
                        child: Text(isEdit ? 'SIMPAN PERUBAHAN' : 'DAFTARKAN TERNAK'),
                      ),
              ],
            ),
          ),
        ),
      ),
    );
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
        try { Vibration.vibrate(duration: 100); } catch (_) {}
        if (mounted) ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Data ternak berhasil dihapus')));
        _refreshData();
      } else {
        if (mounted) ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Gagal menghapus data ternak')));
      }
    } catch (_) {
      await DbHelper.deleteGoatLocally(goat['id']);
      await DbHelper.addToQueue('/goats/${goat['id']}', 'DELETE', null);
      try { Vibration.vibrate(duration: 100); } catch (_) {}
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Offline: Antrean hapus disimpan di lokal. 📡'), backgroundColor: Colors.orange));
        _refreshData();
      }
    }
  }

  void _showActionBottomSheet(BuildContext context, Map<String, dynamic> goat) {
    try { Vibration.vibrate(duration: 40); } catch (_) {}
    showModalBottomSheet(
      context: context,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
      builder: (context) => SafeArea(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Padding(
              padding: const EdgeInsets.symmetric(vertical: 16),
              child: Text(goat['name'] ?? 'Pilih Aksi', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
            ),
            const Divider(height: 1),
            ListTile(
              leading: const Icon(Icons.visibility_outlined, color: Colors.blue),
              title: const Text('Lihat Detail Ternak'),
              onTap: () {
                Navigator.pop(context);
                Navigator.push(context, MaterialPageRoute(builder: (_) => GoatDetailPage(id: goat['id'].toString()))).then((_) => _refreshData());
              },
            ),
            ListTile(
              leading: const Icon(Icons.edit_outlined, color: Color(0xFF4A6741)),
              title: const Text('Edit Data Ternak'),
              onTap: () {
                Navigator.pop(context);
                _showGoatForm(context, goat: goat);
              },
            ),
            ListTile(
              leading: const Icon(Icons.qr_code_outlined, color: Colors.purple),
              title: const Text('Lihat Kartu & Tag QR'),
              onTap: () async {
                Navigator.pop(context);
                final qrCode = goat['qr_code'] ?? goat['id'];
                final url = Uri.parse('https://qandang.duckdns.org/catalog/$qrCode');
                if (await canLaunchUrl(url)) {
                  await launchUrl(url, mode: LaunchMode.externalApplication);
                }
              },
            ),
            ListTile(
              leading: const Icon(Icons.delete_outline, color: Colors.red),
              title: const Text('Hapus Ternak', style: TextStyle(color: Colors.red)),
              onTap: () {
                Navigator.pop(context);
                _deleteGoat(context, goat);
              },
            ),
          ],
        ),
      ),
    );
  }

  void _pickParent(BuildContext context, String gender, Function(int, String) onPicked) {
    showModalBottomSheet(
      context: context,
      builder: (context) => Column(
        children: [
          Padding(
            padding: const EdgeInsets.all(16),
            child: Text('Pilih ${gender == 'male' ? 'Bapak' : 'Induk'}', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
          ),
          Expanded(
            child: ListView.builder(
              itemCount: _allGoats.where((g) => g['gender'] == gender).length,
              itemBuilder: (context, i) {
                final goat = _allGoats.where((g) => g['gender'] == gender).toList()[i];
                return ListTile(
                  title: Text(goat['name']),
                  subtitle: Text(goat['breed'] ?? '-'),
                  onTap: () {
                    onPicked(goat['id'], goat['name']);
                    Navigator.pop(context);
                  },
                );
              },
            ),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Data Ternak'),
        bottom: PreferredSize(
          preferredSize: const Size.fromHeight(110),
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
            child: Column(
              children: [
                TextField(
                  onChanged: (v) { _searchQuery = v; _applyFilter(); },
                  decoration: InputDecoration(
                    hintText: 'Cari Nama atau QR...',
                    prefixIcon: const Icon(Icons.search),
                    filled: true,
                    fillColor: Colors.white,
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide.none),
                    contentPadding: EdgeInsets.zero,
                  ),
                ),
                const SizedBox(height: 8),
                SingleChildScrollView(
                  scrollDirection: Axis.horizontal,
                  child: Row(
                    children: ['Semua', 'Jawa Randu', 'Etawa', 'Boer', 'Saanen', 'Boran', 'Lokal'].map((breed) {
                      final isSelected = _filterBreed == breed;
                      return Padding(
                        padding: const EdgeInsets.only(right: 8),
                        child: ChoiceChip(
                          label: Text(breed),
                          selected: isSelected,
                          onSelected: (s) { if(s) setState(() { _filterBreed = breed; _applyFilter(); }); },
                          selectedColor: const Color(0xFF4A6741),
                          labelStyle: TextStyle(color: isSelected ? Colors.white : Colors.black),
                        ),
                      );
                    }).toList(),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () => _showGoatForm(context),
        label: const Text('Tambah Ternak'),
        icon: const Icon(Icons.add),
        backgroundColor: const Color(0xFF4A6741),
        foregroundColor: Colors.white,
      ),
      floatingActionButtonLocation: FloatingActionButtonLocation.startFloat,
      body: ref.watch(goatListProvider).when(
        data: (goats) {
          // Lakukan filter data di UI secara real-time
          final filtered = goats.where((goat) {
            final matchesSearch = goat['name'].toLowerCase().contains(_searchQuery.toLowerCase()) || 
                                 (goat['qr_code'] ?? '').toLowerCase().contains(_searchQuery.toLowerCase());
            final matchesBreed = _filterBreed == 'Semua' || goat['breed'] == _filterBreed;
            return matchesSearch && matchesBreed;
          }).toList();

          if (filtered.isEmpty) {
            return const Center(child: Text('Tidak ada data ternak.'));
          }

          return RefreshIndicator(
            onRefresh: () => ref.read(goatListProvider.notifier).loadGoats(),
            child: ListView.builder(
              padding: const EdgeInsets.all(12),
              itemCount: filtered.length,
              itemBuilder: (context, i) {
                final goat = filtered[i];
                Color statusColor = Colors.green;
                if (goat['status'] == 'Sakit') statusColor = Colors.red;
                if (goat['status'] == 'Perlu Vaksin') statusColor = Colors.orange;

                return Dismissible(
                  key: Key(goat['id'].toString()),
                  direction: DismissDirection.horizontal,
                  confirmDismiss: (direction) async {
                    if (direction == DismissDirection.startToEnd) {
                      _showGoatForm(context, goat: goat);
                      return false;
                    } else {
                      final deleteConfirm = await showDialog<bool>(
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
                      if (deleteConfirm == true) {
                        await ref.read(goatListProvider.notifier).deleteGoat(goat);
                        return true;
                      }
                      return false;
                    }
                  },
                        background: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 20),
                          alignment: Alignment.centerLeft,
                          decoration: BoxDecoration(color: Colors.green.shade600, borderRadius: BorderRadius.circular(16)),
                          child: const Row(children: [Icon(Icons.edit, color: Colors.white), SizedBox(width: 8), Text('Edit', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold))]),
                        ),
                        secondaryBackground: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 20),
                          alignment: Alignment.centerRight,
                          decoration: BoxDecoration(color: Colors.red.shade600, borderRadius: BorderRadius.circular(16)),
                          child: const Row(mainAxisAlignment: MainAxisAlignment.end, children: [Text('Hapus', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)), SizedBox(width: 8), Icon(Icons.delete, color: Colors.white)]),
                        ),
                        child: Card(
                          elevation: 0,
                          margin: const EdgeInsets.only(bottom: 12),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(16), 
                            side: BorderSide(color: statusColor.withOpacity(0.3), width: 1.5)
                          ),
                          child: InkWell(
                            borderRadius: BorderRadius.circular(16),
                            onTap: () async {
                              await Navigator.push(context, MaterialPageRoute(builder: (_) => GoatDetailPage(id: goat['id'].toString())));
                              _refreshData();
                            },
                            onLongPress: () => _showActionBottomSheet(context, goat),
                            child: Padding(
                              padding: const EdgeInsets.all(12),
                              child: Row(
                                children: [
                                  Hero(
                                    tag: 'goat_image_${goat['id']}',
                                    child: goat['image_url'] != null && goat['image_url'].toString().isNotEmpty
                                        ? PremiumImage(
                                            imageUrl: goat['image_url'],
                                            width: 50,
                                            height: 50,
                                            borderRadius: BorderRadius.circular(12),
                                          )
                                        : Container(
                                            width: 50,
                                            height: 50,
                                            decoration: BoxDecoration(color: statusColor.withOpacity(0.1), borderRadius: BorderRadius.circular(12)),
                                            child: Icon(Icons.pets, color: statusColor),
                                          ),
                                  ),
                                  const SizedBox(width: 16),
                                  Expanded(
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        Text(goat['name'], style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
                                        const SizedBox(height: 4),
                                        Text('${goat['breed'] ?? '-'} • ${goat['gender'] == 'male' ? 'Jantan' : 'Betina'}', style: const TextStyle(fontSize: 13, color: Colors.grey)),
                                      ],
                                    ),
                                  ),
                                  Column(
                                    crossAxisAlignment: CrossAxisAlignment.end,
                                    children: [
                                      Container(
                                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                        decoration: BoxDecoration(color: statusColor.withOpacity(0.1), borderRadius: BorderRadius.circular(12)),
                                        child: Text(goat['status'] ?? 'Sehat', style: TextStyle(fontSize: 10, color: statusColor, fontWeight: FontWeight.bold)),
                                      ),
                                    ],
                                  ),
                                ],
                              ),
                            ),
                          ),
                        ),
                      );
                    },
                  ),
                );
              },
            ),
          );
        },
        loading: () => ListView.builder(
          padding: const EdgeInsets.all(16),
          itemCount: 5,
          itemBuilder: (context, i) => const ShimmerCard(),
        ),
        error: (err, stack) => Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Text('Gagal memuat data: $err'),
              const SizedBox(height: 16),
              ElevatedButton(
                onPressed: () => ref.read(goatListProvider.notifier).loadGoats(),
                child: const Text('Coba Lagi'),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class ShimmerCard extends StatefulWidget {
  const ShimmerCard({super.key});

  @override
  State<ShimmerCard> createState() => _ShimmerCardState();
}

class _ShimmerCardState extends State<ShimmerCard> with SingleTickerProviderStateMixin {
  late AnimationController _controller;
  late Animation<double> _animation;

  @override
  void initState() {
    super.initState();
    _controller = AnimationController(vsync: this, duration: const Duration(milliseconds: 1000))..repeat(reverse: true);
    _animation = Tween<double>(begin: 0.3, end: 0.8).animate(_controller);
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return FadeTransition(
      opacity: _animation,
      child: Container(
        margin: const EdgeInsets.only(bottom: 12),
        padding: const EdgeInsets.all(12),
        height: 80,
        decoration: BoxDecoration(
          color: Colors.grey.shade300,
          borderRadius: BorderRadius.circular(16),
        ),
      ),
    );
  }
}
