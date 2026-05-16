import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import 'package:intl/intl.dart';
import 'package:mobile_scanner/mobile_scanner.dart';
import 'package:vibration/vibration.dart';
import 'package:fl_chart/fl_chart.dart';
import 'package:sqflite/sqflite.dart';
import 'package:path/path.dart' as p;
import 'package:image_picker/image_picker.dart';
import 'package:url_launcher/url_launcher.dart';
import 'dart:io';

void main() {
  runApp(const QandangApp());
}

// --- Database Helper ---
class DbHelper {
  static Database? _db;

  static Future<Database> get db async {
    _db ??= await _initDb();
    return _db!;
  }

  static _initDb() async {
    final path = p.join(await getDatabasesPath(), 'qandang.db');
    return await openDatabase(
      path,
      version: 2,
      onUpgrade: (db, oldVersion, newVersion) async {
        if (oldVersion < 2) {
          await db.execute('ALTER TABLE goats ADD COLUMN dam_id INTEGER');
          await db.execute('ALTER TABLE goats ADD COLUMN sire_id INTEGER');
          await db.execute('ALTER TABLE health_records ADD COLUMN next_scheduled_date TEXT');
        }
      },
      onCreate: (db, version) async {
        await db.execute('''
          CREATE TABLE goats (
            id INTEGER PRIMARY KEY,
            name TEXT,
            qr_code TEXT,
            breed TEXT,
            gender TEXT,
            weight REAL,
            status TEXT,
            note TEXT,
            date_of_birth TEXT,
            weight_logs TEXT,
            health_records TEXT,
            dam_id INTEGER,
            sire_id INTEGER
          )
        ''');
        await db.execute('''
          CREATE TABLE health_records (
            id INTEGER PRIMARY KEY,
            goat_id INTEGER,
            action_type TEXT,
            note TEXT,
            date_recorded TEXT,
            next_scheduled_date TEXT,
            status TEXT
          )
        ''');
        await db.execute('''
          CREATE TABLE sync_queue (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            endpoint TEXT,
            method TEXT,
            body TEXT,
            created_at TEXT
          )
        ''');
      },
    );
  }

  static Future<void> saveGoats(List goats) async {
    final database = await db;
    final batch = database.batch();
    for (var goat in goats) {
      batch.insert('goats', {
        'id': goat['id'],
        'name': goat['name'],
        'qr_code': goat['qr_code'],
        'breed': goat['breed'],
        'gender': goat['gender'],
        'weight': goat['weight'],
        'status': goat['status'],
        'note': goat['note'],
        'date_of_birth': goat['date_of_birth'],
        'weight_logs': jsonEncode(goat['weight_logs'] ?? []),
        'health_records': jsonEncode(goat['health_records'] ?? []),
        'dam_id': goat['dam_id'],
        'sire_id': goat['sire_id'],
      }, conflictAlgorithm: ConflictAlgorithm.replace);
    }
    await batch.commit(noResult: true);
  }

  static Future<List<Map<String, dynamic>>> getGoats() async {
    final database = await db;
    return await database.query('goats');
  }

  static Future<Map<String, dynamic>?> getGoat(String idOrQr) async {
    final database = await db;
    final res = await database.query('goats', where: 'id = ? OR qr_code = ?', whereArgs: [idOrQr, idOrQr]);
    if (res.isEmpty) return null;
    final goat = Map<String, dynamic>.from(res.first);
    return {
      ...goat,
      'weight_logs': jsonDecode(goat['weight_logs']),
      'health_records': jsonDecode(goat['health_records']),
    };
  }

  static Future<void> addToQueue(String endpoint, String method, Map<String, dynamic> body) async {
    final database = await db;
    await database.insert('sync_queue', {
      'endpoint': endpoint,
      'method': method,
      'body': jsonEncode(body),
      'created_at': DateTime.now().toIso8601String(),
    });
  }

  static Future<void> processQueue() async {
    final database = await db;
    final queue = await database.query('sync_queue', orderBy: 'created_at ASC');
    if (queue.isEmpty) return;

    for (var item in queue) {
      try {
        final res = await ApiService.post(item['endpoint'] as String, jsonDecode(item['body'] as String));
        if (res.statusCode == 200 || res.statusCode == 201) {
          await database.delete('sync_queue', where: 'id = ?', whereArgs: [item['id']]);
        }
      } catch (_) {
        break; // Stop if still offline
      }
    }
  }
}

class QandangApp extends StatelessWidget {
  const QandangApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Qandang',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        colorScheme: ColorScheme.fromSeed(seedColor: Colors.green, primary: const Color(0xFF4A6741)),
        useMaterial3: true,
        fontFamily: 'sans-serif',
      ),
      home: const AuthWrapper(),
    );
  }
}

// --- API Service ---
class ApiService {
  static const String baseUrl = 'https://qandang.duckdns.org/api';

  static Future<String?> getToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString('token');
  }

  static Future<Map<String, String>> getHeaders() async {
    final token = await getToken();
    return {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      if (token != null) 'Authorization': 'Bearer $token',
    };
  }

  static Future<http.Response> post(String endpoint, Map<String, dynamic> body) async {
    return await http.post(
      Uri.parse('$baseUrl$endpoint'),
      headers: await getHeaders(),
      body: jsonEncode(body),
    );
  }

  static Future<http.Response> get(String endpoint) async {
    return await http.get(
      Uri.parse('$baseUrl$endpoint'),
      headers: await getHeaders(),
    );
  }
}

// --- Auth Wrapper ---
class AuthWrapper extends StatefulWidget {
  const AuthWrapper({super.key});

  @override
  State<AuthWrapper> createState() => _AuthWrapperState();
}

class _AuthWrapperState extends State<AuthWrapper> {
  bool _isLoading = true;
  bool _isLoggedIn = false;

  @override
  void initState() {
    super.initState();
    _checkLogin();
  }

  _checkLogin() async {
    final token = await ApiService.getToken();
    setState(() {
      _isLoggedIn = token != null;
      _isLoading = false;
    });
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) return const Scaffold(body: Center(child: CircularProgressIndicator()));
    return _isLoggedIn ? const MainNavigation() : const LoginPage();
  }
}

// --- Login Page ---
class LoginPage extends StatefulWidget {
  const LoginPage({super.key});

  @override
  State<LoginPage> createState() => _LoginPageState();
}

class _LoginPageState extends State<LoginPage> {
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  bool _loading = false;

  _login() async {
    setState(() => _loading = true);
    try {
      final res = await ApiService.post('/login', {
        'email': _emailController.text,
        'password': _passwordController.text,
        'device_name': 'mobile_app',
      });

      if (res.statusCode == 200) {
        final data = jsonDecode(res.body);
        final prefs = await SharedPreferences.getInstance();
        await prefs.setString('token', data['token']);
        if (mounted) {
          Navigator.pushReplacement(context, MaterialPageRoute(builder: (_) => const MainNavigation()));
        }
      } else {
        if (mounted) ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Login Gagal')));
      }
    } catch (e) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error: $e')));
    } finally {
      setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Padding(
        padding: const EdgeInsets.all(24.0),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(Icons.grass, size: 80, color: Color(0xFF4A6741)),
            const Text('QANDANG', style: TextStyle(fontSize: 32, fontWeight: FontWeight.bold, color: Color(0xFF4A6741))),
            const SizedBox(height: 40),
            TextField(controller: _emailController, decoration: const InputDecoration(labelText: 'Email')),
            TextField(controller: _passwordController, decoration: const InputDecoration(labelText: 'Password'), obscureText: true),
            const SizedBox(height: 40),
            _loading 
              ? const CircularProgressIndicator()
              : ElevatedButton(
                  onPressed: _login, 
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF4A6741),
                    foregroundColor: Colors.white,
                    minimumSize: const Size(double.infinity, 50),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                  child: const Text('MASUK'),
                ),
          ],
        ),
      ),
    );
  }
}

// --- Main Navigation ---
class MainNavigation extends StatefulWidget {
  const MainNavigation({super.key});

  @override
  State<MainNavigation> createState() => _MainNavigationState();
}

class _MainNavigationState extends State<MainNavigation> {
  int _currentIndex = 0;
  final _pages = [const DashboardPage(), const GoatListPage(), const ProfilePage()];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: _pages[_currentIndex],
      bottomNavigationBar: BottomNavigationBar(
        currentIndex: _currentIndex,
        onTap: (i) => setState(() => _currentIndex = i),
        selectedItemColor: const Color(0xFF4A6741),
        items: const [
          BottomNavigationBarItem(icon: Icon(Icons.home_outlined), activeIcon: Icon(Icons.home), label: 'Beranda'),
          BottomNavigationBarItem(icon: Icon(Icons.pets_outlined), activeIcon: Icon(Icons.pets), label: 'Ternak'),
          BottomNavigationBarItem(icon: Icon(Icons.person_outline), activeIcon: Icon(Icons.person), label: 'Profil'),
        ],
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const QRScannerPage())),
        backgroundColor: const Color(0xFF4A6741),
        child: const Icon(Icons.qr_code_scanner, color: Colors.white),
      ),
      floatingActionButtonLocation: FloatingActionButtonLocation.endFloat,
    );
  }
}

// --- Dashboard Page ---
class DashboardPage extends StatefulWidget {
  const DashboardPage({super.key});

  @override
  State<DashboardPage> createState() => _DashboardPageState();
}

class _DashboardPageState extends State<DashboardPage> {
  List<dynamic> _upcomingVaksins = [];

  @override
  void initState() {
    super.initState();
    _loadDashboardData();
  }

  _loadDashboardData() async {
    await DbHelper.processQueue();
    final goats = await DbHelper.getGoats();
    List<dynamic> upcoming = [];

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
    }

    upcoming.sort((a, b) => a['date'].compareTo(b['date']));
    setState(() => _upcomingVaksins = upcoming);
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

              // Progress Section              Container(
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(20),
                  border: Border.all(color: Colors.grey.shade200),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text('Target Penimbangan', style: TextStyle(fontWeight: FontWeight.bold)),
                        Text('20%', style: TextStyle(color: Color(0xFF4A6741), fontWeight: FontWeight.bold)),
                      ],
                    ),
                    const SizedBox(height: 12),
                    LinearProgressIndicator(
                      value: 0.2,
                      backgroundColor: Colors.grey.shade100,
                      color: const Color(0xFF4A6741),
                      borderRadius: BorderRadius.circular(10),
                      minHeight: 10,
                    ),
                    const SizedBox(height: 8),
                    const Text('8 dari 40 kambing sudah ditimbang minggu ini.', style: TextStyle(fontSize: 12, color: Colors.grey)),
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
                  _buildStatCard('Total Ternak', '42', Icons.pets, Colors.blue),
                  _buildStatCard('Kondisi Kandang', 'Optimal', Icons.wb_sunny, Colors.orange),
                ],
              ),              
              const SizedBox(height: 24),
              _buildActionCard(context, 'Mulai Scan QR', 'Arahkan kamera ke tag telinga kambing', Icons.qr_code_scanner, const Color(0xFF4A6741), () {
                Navigator.push(context, MaterialPageRoute(builder: (_) => const QRScannerPage()));
              }),
              
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

  Widget _buildActionCard(BuildContext context, String title, String subtitle, IconData icon, Color color, VoidCallback onTap) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(20),
      child: Container(
        width: double.infinity,
        padding: const EdgeInsets.all(24),
        decoration: BoxDecoration(
          gradient: LinearGradient(colors: [color, color.withOpacity(0.8)]),
          borderRadius: BorderRadius.circular(20),
          boxShadow: [BoxShadow(color: color.withOpacity(0.3), blurRadius: 10, offset: const Offset(0, 5))],
        ),
        child: Row(
          children: [
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(title, style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 20)),
                  Text(subtitle, style: TextStyle(color: Colors.white.withOpacity(0.8), fontSize: 14)),
                ],
              ),
            ),
            Icon(icon, size: 48, color: Colors.white),
          ],
        ),
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

// --- QR Scanner Page ---
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
          // Custom Overlay
          Center(
            child: Container(
              width: 250,
              height: 250,
              decoration: BoxDecoration(
                border: Border.all(color: Colors.white, width: 2),
                borderRadius: BorderRadius.circular(24),
              ),
              child: Stack(
                children: [
                  Positioned(
                    top: 0, left: 0,
                    child: Container(width: 40, height: 40, decoration: const BoxDecoration(border: Border(top: BorderSide(color: Colors.green, width: 4), left: BorderSide(color: Colors.green, width: 4)), borderRadius: BorderRadius.only(topLeft: Radius.circular(24)))),
                  ),
                  // ... other corners
                ],
              ),
            ),
          ),
          const Positioned(
            bottom: 100,
            left: 0, right: 0,
            child: Center(child: Text('Arahkan kamera ke QR Code', style: TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold))),
          ),
        ],
      ),
    );
  }

  void _onQRCodeScanned(String code) async {
    // Feedback getar
    if (await Vibration.hasVibrator() ?? false) {
      Vibration.vibrate(duration: 100);
    }

    // Show loading dialog
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) => _QuickInfoBottomSheet(qrCode: code),
    ).then((_) => setState(() => _isScanning = true));
  }
}

// --- Quick Info Bottom Sheet ---
class _QuickInfoBottomSheet extends StatefulWidget {
  final String qrCode;
  const _QuickInfoBottomSheet({required this.qrCode});

  @override
  State<_QuickInfoBottomSheet> createState() => _QuickInfoBottomSheetState();
}

class _QuickInfoBottomSheetState extends State<_QuickInfoBottomSheet> {
  bool _loading = true;
  Map<String, dynamic>? _goatData;

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
        setState(() {
          _goatData = goat;
          _loading = false;
        });
        return;
      }
    } catch (_) {}

    // Fallback to local
    final localGoat = await DbHelper.getGoat(widget.qrCode);
    setState(() {
      _goatData = localGoat;
      _loading = false;
    });
  }

  final _weightController = TextEditingController();
  bool _saving = false;

  _saveWeight() async {
    if (_weightController.text.isEmpty) return;
    setState(() => _saving = true);
    final body = {
      'weight': double.parse(_weightController.text),
      'date_recorded': DateFormat('yyyy-MM-dd').format(DateTime.now()),
      'note': 'Input via Mobile Quick Scan',
    };

    try {
      final res = await ApiService.post('/goats/${_goatData!['id']}/weight', body);
      if (res.statusCode == 201) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Berat berhasil dicatat! 🐐⚖️')));
          Navigator.pop(context);
        }
        return;
      }
    } catch (_) {}

    // Offline mode
    await DbHelper.addToQueue('/goats/${_goatData!['id']}/weight', 'POST', body);
    if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
        content: Text('Offline: Berat disimpan di antrean sync. 📡'),
        backgroundColor: Colors.orange,
      ));
      Navigator.pop(context);
    }
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
                  value: selectedType,
                  items: ['Vaksinasi', 'Pemberian Vitamin', 'Obat Cacing', 'Pengobatan Sakit', 'Cek Rutin']
                      .map((e) => DropdownMenuItem(value: e, child: Text(e)))
                      .toList(),
                  onChanged: (v) => selectedType = v!,
                  decoration: const InputDecoration(labelText: 'Jenis Tindakan'),
                ),
                const SizedBox(height: 16),
                TextField(
                  controller: noteController,
                  decoration: const InputDecoration(labelText: 'Catatan / Nama Obat', hintText: 'Contoh: Vaksin PMK Dosis 1'),
                  maxLines: 2,
                ),
                const SizedBox(height: 16),
                const Text('Jadwal Berikutnya (Opsional)', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12, color: Colors.grey)),
                const SizedBox(height: 8),
                OutlinedButton.icon(
                  onPressed: () async {
                    final picked = await showDatePicker(
                      context: context,
                      initialDate: DateTime.now().add(const Duration(days: 30)),
                      firstDate: DateTime.now(),
                      lastDate: DateTime.now().add(const Duration(days: 365)),
                    );
                    if (picked != null) setDialogState(() => nextSchedule = picked);
                  },
                  icon: const Icon(Icons.calendar_today),
                  label: Text(nextSchedule == null ? 'Pilih Tanggal' : DateFormat('dd MMM yyyy').format(nextSchedule!)),
                ),
                const SizedBox(height: 16),
                const Text('Dokumentasi Foto', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12, color: Colors.grey)),
                const SizedBox(height: 8),
                if (imageFile != null)
                  Stack(
                    children: [
                      ClipRRect(
                        borderRadius: BorderRadius.circular(12),
                        child: Image.file(imageFile!, height: 150, width: double.infinity, fit: BoxFit.cover),
                      ),
                      Positioned(
                        right: 8, top: 8,
                        child: CircleAvatar(
                          backgroundColor: Colors.black54,
                          child: IconButton(
                            icon: const Icon(Icons.close, color: Colors.white),
                            onPressed: () => setDialogState(() => imageFile = null),
                          ),
                        ),
                      ),
                    ],
                  )
                else
                  OutlinedButton.icon(
                    onPressed: () async {
                      final picker = ImagePicker();
                      final photo = await picker.pickImage(
                        source: ImageSource.camera,
                        imageQuality: 50,
                        maxWidth: 1024,
                      );
                      if (photo != null) {
                        setDialogState(() => imageFile = File(photo.path));
                      }
                    },
                    icon: const Icon(Icons.camera_alt),
                    label: const Text('AMBIL FOTO'),
                    style: OutlinedButton.styleFrom(
                      minimumSize: const Size(double.infinity, 50),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    ),
                  ),
              ],
            ),
          ),
          actions: [
            TextButton(onPressed: () => Navigator.pop(context), child: const Text('BATAL')),
            ElevatedButton(
              onPressed: () async {
                String? base64Image;
                if (imageFile != null) {
                  final bytes = await imageFile!.readAsBytes();
                  base64Image = base64Encode(bytes);
                }

                final body = {
                  'type': selectedType,
                  'title': selectedType, // Same as type for simplicity
                  'note': noteController.text,
                  'date_recorded': DateFormat('yyyy-MM-dd').format(DateTime.now()),
                  'status': 'completed',
                  'next_scheduled_date': nextSchedule != null ? DateFormat('yyyy-MM-dd').format(nextSchedule!) : null,
                  'image': base64Image,
                };
                
                if (context.mounted) Navigator.pop(context);
                setState(() => _saving = true);
                
                try {
                  final res = await ApiService.post('/goats/${_goatData!['id']}/health', body);
                  if (res.statusCode == 201) {
                    if (mounted) ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Catatan kesehatan & foto disimpan! 💉📸')));
                  }
                } catch (_) {
                  await DbHelper.addToQueue('/goats/${_goatData!['id']}/health', 'POST', body);
                  if (mounted) ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Offline: Antrean foto & kesehatan disimpan. 📡'), backgroundColor: Colors.orange));
                } finally {
                  setState(() => _saving = false);
                }
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
      padding: EdgeInsets.only(
        left: 24, right: 24, top: 24,
        bottom: MediaQuery.of(context).viewInsets.bottom + 24
      ),
      decoration: const BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.vertical(top: Radius.circular(32)),
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Container(width: 40, height: 4, decoration: BoxDecoration(color: Colors.grey.shade300, borderRadius: BorderRadius.circular(2))),
          const SizedBox(height: 24),
          if (_loading)
            const Column(
              children: [
                CircularProgressIndicator(),
                SizedBox(height: 16),
                Text('Mencari data kambing...'),
              ],
            )
          else if (_goatData == null)
            Column(
              children: [
                const Icon(Icons.error_outline, size: 48, color: Colors.red),
                const SizedBox(height: 16),
                const Text('Data Kambing Tidak Ditemukan', style: TextStyle(fontWeight: FontWeight.bold)),
                const SizedBox(height: 8),
                Text('QR: ${widget.qrCode}', style: const TextStyle(color: Colors.grey)),
                const SizedBox(height: 24),
                ElevatedButton(onPressed: () => Navigator.pop(context), child: const Text('TUTUP')),
              ],
            )
          else ...[
            Row(
              children: [
                CircleAvatar(
                  radius: 35, 
                  backgroundColor: const Color(0xFF4A6741).withOpacity(0.1),
                  child: const Icon(Icons.pets, size: 30, color: Color(0xFF4A6741))
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(_goatData!['name'], style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold)),
                      Text('QR: ${widget.qrCode}', style: const TextStyle(color: Colors.grey, fontSize: 12)),
                    ],
                  ),
                ),
              ],
            ),
            const Divider(height: 32),
            const Text('CATAT BERAT BARU', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12, color: Colors.grey, letterSpacing: 1)),
            const SizedBox(height: 16),
            Row(
              children: [
                Expanded(
                  child: TextField(
                    controller: _weightController,
                    keyboardType: TextInputType.number,
                    decoration: InputDecoration(
                      hintText: 'Masukkan berat (kg)',
                      filled: true,
                      fillColor: Colors.grey.shade100,
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide.none),
                      prefixIcon: const Icon(Icons.monitor_weight_outlined),
                      suffixText: 'kg',
                    ),
                  ),
                ),
                const SizedBox(width: 12),
                _saving 
                  ? const CircularProgressIndicator()
                  : ElevatedButton(
                      onPressed: _saveWeight,
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF4A6741),
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 15),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      ),
                      child: const Text('SIMPAN'),
                    ),
              ],
            ),
            const SizedBox(height: 12),
            SizedBox(
              width: double.infinity,
              child: OutlinedButton.icon(
                onPressed: () => _showHealthEntry(context),
                icon: const Icon(Icons.medical_services_outlined),
                label: const Text('CATAT KESEHATAN / VAKSIN'),
                style: OutlinedButton.styleFrom(
                  foregroundColor: Colors.red.shade700,
                  side: BorderSide(color: Colors.red.shade200),
                  padding: const EdgeInsets.symmetric(vertical: 12),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                ),
              ),
            ),
            const SizedBox(height: 24),
            _buildGrowthChart(),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceAround,
              children: [
                _buildMiniStat('Terakhir', '${_goatData!['weight'] ?? (_goatData!['weight_logs']?.isNotEmpty == true ? _goatData!['weight_logs'].last['weight'] : '-')} kg', Icons.history),
                _buildMiniStat('Jenis', _goatData!['breed'] ?? '-', Icons.category),
                _buildMiniStat('Kelamin', _goatData!['gender'] == 'male' ? 'Jantan' : 'Betina', Icons.wc),
              ],
            ),
            const SizedBox(height: 24),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: () {
                  Navigator.pop(context);
                  Navigator.push(context, MaterialPageRoute(builder: (_) => GoatDetailPage(id: _goatData!['id'].toString())));
                },
                style: ElevatedButton.styleFrom(
                  backgroundColor: Colors.grey.shade100,
                  foregroundColor: const Color(0xFF4A6741),
                  elevation: 0,
                  padding: const EdgeInsets.symmetric(vertical: 15),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                ),
                child: const Text('LIHAT DETAIL LENGKAP'),
              ),
            ),
            const SizedBox(height: 12),
            SizedBox(
              width: double.infinity,
              child: TextButton(
                onPressed: () => Navigator.pop(context),
                child: const Text('TUTUP', style: TextStyle(color: Colors.grey)),
              ),
            ),
          ],
          const SizedBox(height: 10),
        ],
      ),
    );
  }

  Widget _buildGrowthChart() {
    final logs = _goatData!['weight_logs'] as List<dynamic>? ?? [];
    if (logs.isEmpty) return const SizedBox.shrink();

    final sortedLogs = List.from(logs);
    sortedLogs.sort((a, b) => a['date_recorded'].compareTo(b['date_recorded']));
    final recentLogs = sortedLogs.length > 7 ? sortedLogs.sublist(sortedLogs.length - 7) : sortedLogs;

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text('TREN PERTUMBUHAN', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12, color: Colors.grey, letterSpacing: 1)),
        const SizedBox(height: 16),
        SizedBox(
          height: 120,
          child: LineChart(
            LineChartData(
              gridData: const FlGridData(show: false),
              titlesData: const FlTitlesData(show: false),
              borderData: FlBorderData(show: false),
              lineBarsData: [
                LineChartBarData(
                  spots: recentLogs.asMap().entries.map((e) {
                    return FlSpot(e.key.toDouble(), double.parse(e.value['weight'].toString()));
                  }).toList(),
                  isCurved: true,
                  color: const Color(0xFF4A6741),
                  barWidth: 4,
                  isStrokeCapRound: true,
                  dotData: const FlDotData(show: true),
                  belowBarData: BarAreaData(show: true, color: const Color(0xFF4A6741).withOpacity(0.1)),
                ),
              ],
            ),
          ),
        ),
        const SizedBox(height: 24),
      ],
    );
  }

  Widget _buildMiniStat(String label, String value, IconData icon) {
    return Column(
      children: [
        Icon(icon, size: 20, color: Colors.grey),
        const SizedBox(height: 4),
        Text(value, style: const TextStyle(fontWeight: FontWeight.bold)),
        Text(label, style: const TextStyle(fontSize: 10, color: Colors.grey)),
      ],
    );
  }
}

// --- Goat List Page ---
class GoatListPage extends StatefulWidget {
  const GoatListPage({super.key});

  @override
  State<GoatListPage> createState() => _GoatListPageState();
}

class _GoatListPageState extends State<GoatListPage> {
  late Future<List<dynamic>> _goatsFuture;
  List<dynamic> _allGoats = [];
  List<dynamic> _filteredGoats = [];
  String _searchQuery = '';
  String _filterBreed = 'Semua';

  @override
  void initState() {
    super.initState();
    _refreshData();
  }

  void _refreshData() {
    setState(() {
      _goatsFuture = _fetchGoats().then((value) {
        setState(() {
          _allGoats = value;
          _applyFilter();
        });
        return value;
      });
    });
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
      // Fallback to local DB
    }
    final localGoats = await DbHelper.getGoats();
    return localGoats.map((g) => {
      ...g,
      'weight_logs': jsonDecode(g['weight_logs'] as String),
      'health_records': jsonDecode(g['health_records'] as String),
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
                  final body = {
                    'name': nameController.text,
                    'breed': breedController.text,
                    'qr_code': qrController.text,
                    'gender': gender,
                    'dam_id': selectedDamId,
                    'sire_id': selectedSireId,
                  };
                  
                  Navigator.pop(context);
                  try {
                    final res = await ApiService.post('/goats', body);
                    if (res.statusCode == 201) {
                      if (mounted) ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Ternak berhasil didaftarkan! 🐐')));
                      _refreshData();
                    }
                  } catch (_) {
                    await DbHelper.addToQueue('/goats', 'POST', body);
                    if (mounted) ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Offline: Antrean pendaftaran disimpan. 📡'), backgroundColor: Colors.orange));
                  }
                },
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF4A6741),
                  foregroundColor: Colors.white,
                  minimumSize: const Size(double.infinity, 50),
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
      body: FutureBuilder<List<dynamic>>(
        future: _goatsFuture,
        builder: (context, snapshot) {
          if (snapshot.connectionState == ConnectionState.waiting) return const Center(child: CircularProgressIndicator());
          if (snapshot.hasError) return Center(child: Text('Error: ${snapshot.error}'));
          
          return ListView.builder(
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
                  subtitle: Text('${goat['breed']} • ${goat['gender']}'),
                  trailing: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      const Icon(Icons.chevron_right),
                      const SizedBox(height: 4),
                      Text(goat['status'] ?? 'Sehat', style: TextStyle(fontSize: 10, color: statusColor, fontWeight: FontWeight.bold)),
                    ],
                  ),
                  onTap: () {
                    Navigator.push(context, MaterialPageRoute(builder: (_) => GoatDetailPage(id: goat['id'].toString())));
                  },
                ),
              );
            },
          );
        },
      ),
    );
  }
}

// --- Goat Detail Page ---
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
    return Container(
      padding: const EdgeInsets.all(24),
      color: const Color(0xFF4A6741).withOpacity(0.05),
      child: Row(
        children: [
          CircleAvatar(
            radius: 40,
            backgroundColor: const Color(0xFF4A6741).withOpacity(0.1),
            child: const Icon(Icons.pets, size: 40, color: Color(0xFF4A6741)),
          ),
          const SizedBox(width: 20),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(goat['name'], style: const TextStyle(fontSize: 24, fontWeight: FontWeight.bold)),
                Text('Tag: ${goat['qr_code'] ?? '-'}', style: const TextStyle(color: Colors.grey)),
                const SizedBox(height: 8),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(color: const Color(0xFF4A6741), borderRadius: BorderRadius.circular(20)),
                  child: Text(
                    goat['status'] ?? 'Sehat',
                    style: const TextStyle(color: Colors.white, fontSize: 12),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildInfoTab(Map<String, dynamic> goat) {
    return ListView(
      padding: const EdgeInsets.all(20),
      children: [
        // AI Prediction Card
        Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            gradient: LinearGradient(colors: [Colors.purple.shade700, Colors.purple.shade400]),
            borderRadius: BorderRadius.circular(16),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Row(
                children: [
                  Icon(Icons.auto_awesome, color: Colors.white, size: 20),
                  SizedBox(width: 8),
                  Text('Prediksi Pertumbuhan AI', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
                ],
              ),
              const SizedBox(height: 12),
              FutureBuilder(
                future: ApiService.get('/goats/${goat['id']}/predict'),
                builder: (context, snapshot) {
                  if (snapshot.connectionState == ConnectionState.waiting) return const Text('Menganalisis...', style: TextStyle(color: Colors.white70));
                  if (snapshot.hasError || !snapshot.hasData) return const Text('Prediksi tidak tersedia', style: TextStyle(color: Colors.white70));
                  
                  final data = jsonDecode((snapshot.data as http.Response).body);
                  return Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Estimasi berat bulan depan: ${data['predicted_weight_next_month']} kg',
                        style: const TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold)
                      ),
                      Text(
                        'Tingkat kepercayaan: ${(data['confidence_score'] * 100).toInt()}%',
                        style: const TextStyle(color: Colors.white70, fontSize: 12)
                      ),
                    ],
                  );
                },
              ),
            ],
          ),
        ),
        const SizedBox(height: 20),
        _buildInfoRow('Jenis', goat['breed'] ?? '-'),
        _buildInfoRow('Jenis Kelamin', goat['gender'] == 'male' ? 'Jantan' : 'Betina'),
        _buildInfoRow('Tanggal Lahir', goat['date_of_birth'] ?? '-'),
        _buildInfoRow('Berat Terakhir', '${goat['weight'] ?? '-'} kg'),
        const Divider(height: 32),
        const Text('Silsilah (Pedigree)', style: TextStyle(fontWeight: FontWeight.bold, color: Colors.grey)),
        const SizedBox(height: 12),
        _buildInfoRow('Induk (Dam)', goat['dam']?['name'] ?? '-'),
        _buildInfoRow('Bapak (Sire)', goat['sire']?['name'] ?? '-'),
        const SizedBox(height: 20),
        const Text('Catatan Tambahan', style: TextStyle(fontWeight: FontWeight.bold, color: Colors.grey)),
        const SizedBox(height: 8),
        Text(goat['note'] ?? 'Tidak ada catatan.'),
      ],
    );
  }

  Widget _buildInfoRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 12),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: const TextStyle(color: Colors.grey)),
          Text(value, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
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
          child: ListTile(
            leading: const Icon(Icons.monitor_weight_outlined, color: Colors.blue),
            title: Text('${log['weight']} kg', style: const TextStyle(fontWeight: FontWeight.bold)),
            subtitle: Text(log['date_recorded']),
            trailing: log['note'] != null ? const Icon(Icons.info_outline, size: 16) : null,
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
          child: ListTile(
            leading: const Icon(Icons.medical_services_outlined, color: Colors.red),
            title: Text(record['action_type'], style: const TextStyle(fontWeight: FontWeight.bold)),
            subtitle: Text('${record['date_recorded']}\n${record['note'] ?? ''}'),
            isThreeLine: true,
          ),
        );
      },
    );
  }
}

// --- Profile Page ---
class ProfilePage extends StatelessWidget {
  const ProfilePage({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Profil')),
      body: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          children: [
            const CircleAvatar(radius: 50, child: Icon(Icons.person, size: 50)),
            const SizedBox(height: 16),
            const Text('Peternak Qandang', style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold)),
            const Text('peternak@qandang.com', style: TextStyle(color: Colors.grey)),
            const SizedBox(height: 40),
            
            _buildProfileMenu(
              icon: Icons.file_download_outlined,
              label: 'Ekspor Data Ternak (CSV)',
              onTap: () async {
                final url = Uri.parse('${ApiService.baseUrl}/export/goats');
                if (await canLaunchUrl(url)) {
                  await launchUrl(url, mode: LaunchMode.externalApplication);
                }
              },
            ),
            _buildProfileMenu(
              icon: Icons.settings_outlined,
              label: 'Pengaturan Akun',
              onTap: () {},
            ),
            _buildProfileMenu(
              icon: Icons.help_outline,
              label: 'Bantuan & Dukungan',
              onTap: () {},
            ),

            const Spacer(),
            ElevatedButton(
              onPressed: () async {
                final prefs = await SharedPreferences.getInstance();
                await prefs.clear();
                if (context.mounted) Navigator.pushReplacement(context, MaterialPageRoute(builder: (_) => const LoginPage()));
              },
              style: ElevatedButton.styleFrom(
                backgroundColor: Colors.red.shade50,
                foregroundColor: Colors.red,
                elevation: 0,
                minimumSize: const Size(double.infinity, 50),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              ),
              child: const Text('LOGOUT'),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildProfileMenu({required IconData icon, required String label, required VoidCallback onTap}) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(12),
        child: Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            border: Border.all(color: Colors.grey.shade100),
            borderRadius: BorderRadius.circular(12),
          ),
          child: Row(
            children: [
              Icon(icon, color: const Color(0xFF4A6741)),
              const SizedBox(width: 16),
              Text(label, style: const TextStyle(fontWeight: FontWeight.w600)),
              const Spacer(),
              const Icon(Icons.chevron_right, size: 16, color: Colors.grey),
            ],
          ),
        ),
      ),
    );
  }
}
