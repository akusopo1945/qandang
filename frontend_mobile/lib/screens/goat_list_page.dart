import 'dart:convert';
import 'package:flutter/material.dart';
import '../services/app_services.dart';
import 'goat_detail_page.dart';

class GoatListPage extends StatefulWidget {
  const GoatListPage({super.key});

  @override
  State<GoatListPage> createState() => _GoatListPageState();
}

class _GoatListPageState extends State<GoatListPage> {
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
      // Fail silently, fallback is local DB via _fetchGoats
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
      // Fallback
    }
    final localGoats = await DbHelper.getGoats();
    return localGoats.map((g) => {
      ...g,
      'weight_logs': jsonDecode(g['weight_logs'] as String? ?? '[]'),
      'health_records': jsonDecode(g['health_records'] as String? ?? '[]'),
    }).toList();
  }

  void _showAddGoat(BuildContext context) {
    final nameController = TextEditingController();
    final breedController = TextEditingController();
    final qrController = TextEditingController();
    String gender = 'male';
    int? selectedDamId;
    int? selectedSireId;
    String damName = 'Pilih Induk (Dam)';
    String sireName = 'Pilih Bapak (Sire)';

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
      builder: (context) => StatefulBuilder(
        builder: (context, setModalState) => Padding(
          padding: EdgeInsets.only(left: 20, right: 20, top: 20, bottom: MediaQuery.of(context).viewInsets.bottom + 20),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text('Tambah Ternak Baru', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
              const SizedBox(height: 16),
              TextField(controller: nameController, decoration: const InputDecoration(labelText: 'Nama / No. Telinga')),
              TextField(controller: breedController, decoration: const InputDecoration(labelText: 'Jenis (e.g. Jawa Randu)')),
              TextField(controller: qrController, decoration: const InputDecoration(labelText: 'QR Code ID')),
              const SizedBox(height: 16),
              const Text('Jenis Kelamin'),
              Row(
                children: [
                  Radio<String>(value: 'male', groupValue: gender, onChanged: (v) => setModalState(() => gender = v!)),
                  const Text('Jantan'),
                  const SizedBox(width: 20),
                  Radio<String>(value: 'female', groupValue: gender, onChanged: (v) => setModalState(() => gender = v!)),
                  const Text('Betina'),
                ],
              ),
              const SizedBox(height: 16),
              const Text('Silsilah (Pedigree)', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12, color: Colors.grey)),
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
              const SizedBox(height: 20),
              ElevatedButton(
                onPressed: () async {
                  if (nameController.text.trim().isEmpty) {
                    ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Nama tidak boleh kosong')));
                    return;
                  }

                  final body = {
                    'name': nameController.text.trim(),
                    'breed': breedController.text.trim(),
                    'qr_code': qrController.text.trim(),
                    'gender': gender,
                    'dam_id': selectedDamId,
                    'sire_id': selectedSireId,
                  };
                  
                  Navigator.pop(context);
                  try {
                    final res = await ApiService.post('/goats', body);
                    if (res.statusCode == 201) {
                      final newGoat = jsonDecode(res.body);
                      await DbHelper.saveGoats([newGoat]);
                      if (mounted) ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Ternak berhasil didaftarkan! 🐐')));
                      _refreshData();
                    } else {
                      final err = jsonDecode(res.body);
                      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(err['message'] ?? 'Gagal mendaftarkan ternak')));
                    }
                  } catch (_) {
                    final tempId = DateTime.now().millisecondsSinceEpoch;
                    final localGoat = {
                      'id': tempId,
                      'name': body['name'],
                      'breed': body['breed'],
                      'qr_code': body['qr_code'],
                      'gender': body['gender'],
                      'weight': null,
                      'status': 'Sehat',
                      'note': '',
                      'date_of_birth': null,
                      'weight_logs': [],
                      'health_records': [],
                      'dam_id': body['dam_id'],
                      'sire_id': body['sire_id'],
                    };
                    await DbHelper.saveGoats([localGoat]);
                    await DbHelper.addToQueue('/goats', 'POST', body);
                    if (mounted) {
                      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Offline: Antrean pendaftaran disimpan. 📡'), backgroundColor: Colors.orange));
                      _refreshData();
                    }
                  }
                },
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF4A6741),
                  foregroundColor: Colors.white,
                  minimumSize: const Size(double.infinity, 50),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                ),
                child: const Text('DAFTARKAN'),
              ),
            ],
          ),
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
                    children: ['Semua', 'Jawa Randu', 'Etawa', 'Peb', 'Boran'].map((breed) {
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
        onPressed: () => _showAddGoat(context),
        label: const Text('Tambah Ternak'),
        icon: const Icon(Icons.add),
        backgroundColor: const Color(0xFF4A6741),
        foregroundColor: Colors.white,
      ),
      floatingActionButtonLocation: FloatingActionButtonLocation.startFloat,
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _filteredGoats.isEmpty
              ? const Center(child: Text('Tidak ada data ternak.'))
              : RefreshIndicator(
                  onRefresh: _refreshData,
                  child: ListView.builder(
                    padding: const EdgeInsets.all(12),
                    itemCount: _filteredGoats.length,
                    itemBuilder: (context, i) {
                      final goat = _filteredGoats[i];
                      Color statusColor = Colors.green;
                      if (goat['status'] == 'Sakit') statusColor = Colors.red;
                      if (goat['status'] == 'Perlu Vaksin') statusColor = Colors.orange;

                      return Card(
                        elevation: 0,
                        margin: const EdgeInsets.only(bottom: 12),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(16), 
                          side: BorderSide(color: statusColor.withOpacity(0.3), width: 1.5)
                        ),
                        child: ListTile(
                          contentPadding: const EdgeInsets.all(12),
                          leading: Container(
                            padding: const EdgeInsets.all(8),
                            decoration: BoxDecoration(color: statusColor.withOpacity(0.1), borderRadius: BorderRadius.circular(12)),
                            child: Icon(Icons.pets, color: statusColor),
                          ),
                          title: Text(goat['name'], style: const TextStyle(fontWeight: FontWeight.bold)),
                          subtitle: Text('${goat['breed'] ?? '-'} • ${goat['gender'] == 'male' ? 'Jantan' : 'Betina'}'),
                          trailing: Column(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              const Icon(Icons.chevron_right),
                              const SizedBox(height: 4),
                              Text(goat['status'] ?? 'Sehat', style: TextStyle(fontSize: 10, color: statusColor, fontWeight: FontWeight.bold)),
                            ],
                          ),
                          onTap: () async {
                            await Navigator.push(context, MaterialPageRoute(builder: (_) => GoatDetailPage(id: goat['id'].toString())));
                            _refreshData(); // Refresh list if detail screen updated something
                          },
                        ),
                      );
                    },
                  ),
                ),
    );
  }
}
