import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import 'package:intl/intl.dart';
// Note: mobile_scanner used for QR scanning logic

void main() {
  runApp(const QandangApp());
}

class QandangApp extends StatelessWidget {
  const QandangApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Qandang',
      theme: ThemeData(
        colorScheme: ColorScheme.fromSeed(seedColor: Colors.green),
        useMaterial3: true,
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
            const Icon(Icons.grass, size: 80, color: Colors.green),
            const Text('QANDANG', style: TextStyle(fontSize: 32, fontWeight: FontWeight.bold, color: Colors.green)),
            const SizedBox(height: 40),
            TextField(controller: _emailController, decoration: const InputDecoration(labelText: 'Email')),
            TextField(controller: _passwordController, decoration: const InputDecoration(labelText: 'Password'), obscureText: true),
            const SizedBox(height: 40),
            _loading 
              ? const CircularProgressIndicator()
              : ElevatedButton(onPressed: _login, child: const Text('MASUK')),
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
        items: const [
          BottomNavigationBarItem(icon: Icon(Icons.home), label: 'Beranda'),
          BottomNavigationBarItem(icon: Icon(Icons.list), label: 'Ternak'),
          BottomNavigationBarItem(icon: Icon(Icons.person), label: 'Profil'),
        ],
      ),
    );
  }
}

// --- Dashboard Page ---
class DashboardPage extends StatelessWidget {
  const DashboardPage({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Qandang Dashboard')),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            _buildActionCard(context, 'SCAN QR KAMBING', Icons.qr_code_scanner, Colors.green, () {
              // Placeholder for scanner trigger
              ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Membuka Kamera...')));
            }),
            const SizedBox(height: 16),
            const Card(
              child: ListTile(
                leading: Icon(Icons.info),
                title: Text('Tips Hari Ini'),
                subtitle: Text('Pastikan sanitasi kandang terjaga setelah hujan.'),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildActionCard(BuildContext context, String title, IconData icon, Color color, VoidCallback onTap) {
    return InkWell(
      onTap: onTap,
      child: Container(
        width: double.infinity,
        padding: const EdgeInsets.all(24),
        decoration: BoxDecoration(color: color, borderRadius: BorderRadius.circular(12)),
        child: Column(
          children: [
            Icon(icon, size: 48, color: Colors.white),
            const SizedBox(height: 12),
            Text(title, style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 18)),
          ],
        ),
      ),
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

  @override
  void initState() {
    super.initState();
    _goatsFuture = _fetchGoats();
  }

  Future<List<dynamic>> _fetchGoats() async {
    final res = await ApiService.get('/goats');
    if (res.statusCode == 200) return jsonDecode(res.body);
    throw Exception('Gagal mengambil data');
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Data Ternak')),
      body: FutureBuilder<List<dynamic>>(
        future: _goatsFuture,
        builder: (context, snapshot) {
          if (snapshot.connectionState == ConnectionState.waiting) return const Center(child: CircularProgressIndicator());
          if (snapshot.hasError) return Center(child: Text('Error: ${snapshot.error}'));
          final goats = snapshot.data ?? [];
          return ListView.builder(
            itemCount: goats.length,
            itemBuilder: (context, i) {
              final goat = goats[i];
              return ListTile(
                leading: const CircleAvatar(child: Icon(Icons.pets)),
                title: Text(goat['name']),
                subtitle: Text('${goat['breed']} - ${goat['gender']}'),
                trailing: const Icon(Icons.chevron_right),
                onTap: () {},
              );
            },
          );
        },
      ),
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
      body: Center(
        child: ElevatedButton(
          onPressed: () async {
            final prefs = await SharedPreferences.getInstance();
            await prefs.clear();
            if (context.mounted) Navigator.pushReplacement(context, MaterialPageRoute(builder: (_) => const LoginPage()));
          },
          style: ElevatedButton.styleFrom(backgroundColor: Colors.red, foregroundColor: Colors.white),
          child: const Text('LOGOUT'),
        ),
      ),
    );
  }
}
