import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:url_launcher/url_launcher.dart';
import '../services/app_services.dart';
import 'login_page.dart';

class ProfilePage extends StatefulWidget {
  const ProfilePage({super.key});

  @override
  State<ProfilePage> createState() => _ProfilePageState();
}

class _ProfilePageState extends State<ProfilePage> {
  String _name = 'Peternak Qandang';
  String _email = 'peternak@qandang.com';

  @override
  void initState() {
    super.initState();
    _loadLocalProfile();
    _fetchRemoteProfile();
  }

  Future<void> _loadLocalProfile() async {
    final prefs = await SharedPreferences.getInstance();
    setState(() {
      _name = prefs.getString('user_name') ?? 'Peternak Qandang';
      _email = prefs.getString('user_email') ?? 'peternak@qandang.com';
    });
  }

  Future<void> _fetchRemoteProfile() async {
    try {
      final res = await ApiService.get('/user');
      if (res.statusCode == 200) {
        final data = jsonDecode(res.body);
        final prefs = await SharedPreferences.getInstance();
        await prefs.setString('user_name', data['name'] ?? '');
        await prefs.setString('user_email', data['email'] ?? '');
        if (mounted) {
          setState(() {
            _name = data['name'] ?? '';
            _email = data['email'] ?? '';
          });
        }
      }
    } catch (_) {
      // Fallback to local SharedPreferences
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Profil')),
      body: RefreshIndicator(
        onRefresh: _fetchRemoteProfile,
        child: ListView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.all(24),
          children: [
            CircleAvatar(
              radius: 50,
              backgroundColor: const Color(0xFF4A6741).withOpacity(0.1),
              child: const Icon(Icons.person, size: 50, color: Color(0xFF4A6741)),
            ),
            const SizedBox(height: 16),
            Center(
              child: Text(
                _name,
                style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
              ),
            ),
            Center(
              child: Text(
                _email,
                style: const TextStyle(color: Colors.grey),
              ),
            ),
            const SizedBox(height: 40),
            
            _buildProfileMenu(
              context,
              icon: Icons.file_download_outlined,
              label: 'Ekspor Data Ternak (CSV)',
              onTap: () async {
                final url = Uri.parse('${ApiService.baseUrl}/export/goats');
                if (await canLaunchUrl(url)) await launchUrl(url, mode: LaunchMode.externalApplication);
              },
            ),
            _buildProfileMenu(
              context,
              icon: Icons.settings_outlined,
              label: 'Pengaturan Akun',
              onTap: () async {
                final result = await Navigator.push(
                  context,
                  MaterialPageRoute(
                    builder: (_) => AccountSettingsPage(initialName: _name, initialEmail: _email),
                  ),
                );
                if (result == true) {
                  _loadLocalProfile();
                }
              },
            ),
            _buildProfileMenu(context, icon: Icons.help_outline, label: 'Bantuan & Dukungan', onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const HelpSupportPage()))),
            _buildProfileMenu(context, icon: Icons.description_outlined, label: 'Panduan Pengguna', onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const UserManualPage()))),
            _buildProfileMenu(context, icon: Icons.info_outline, label: 'Tentang Aplikasi', onTap: () => showAboutDialog(context: context, applicationName: 'Qandang', applicationVersion: '1.0.0', applicationIcon: const Icon(Icons.grass, color: Color(0xFF4A6741), size: 40), children: [const Text('Qandang adalah solusi cerdas untuk manajemen peternakan kambing modern.')])),

            const SizedBox(height: 40),
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

  Widget _buildProfileMenu(BuildContext context, {required IconData icon, required String label, required VoidCallback onTap}) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(12),
        child: Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(border: Border.all(color: Colors.grey.shade100), borderRadius: BorderRadius.circular(12)),
          child: Row(children: [Icon(icon, color: const Color(0xFF4A6741)), const SizedBox(width: 16), Text(label, style: const TextStyle(fontWeight: FontWeight.w600)), const Spacer(), const Icon(Icons.chevron_right, size: 16, color: Colors.grey)]),
        ),
      ),
    );
  }
}

class AccountSettingsPage extends StatefulWidget {
  final String initialName;
  final String initialEmail;
  const AccountSettingsPage({super.key, required this.initialName, required this.initialEmail});

  @override
  State<AccountSettingsPage> createState() => _AccountSettingsPageState();
}

class _AccountSettingsPageState extends State<AccountSettingsPage> {
  late TextEditingController _nameController;
  late TextEditingController _emailController;
  final _passwordController = TextEditingController();
  final _passwordConfirmController = TextEditingController();
  bool _loading = false;

  @override
  void initState() {
    super.initState();
    _nameController = TextEditingController(text: widget.initialName);
    _emailController = TextEditingController(text: widget.initialEmail);
  }

  @override
  void dispose() {
    _nameController.dispose();
    _emailController.dispose();
    _passwordController.dispose();
    _passwordConfirmController.dispose();
    super.dispose();
  }

  Future<void> _saveChanges() async {
    if (_nameController.text.trim().isEmpty || _emailController.text.trim().isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Nama dan Email tidak boleh kosong')));
      return;
    }

    if (_passwordController.text.isNotEmpty && _passwordController.text != _passwordConfirmController.text) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Konfirmasi password tidak cocok')));
      return;
    }

    setState(() => _loading = true);

    try {
      final body = {
        '_method': 'PUT',
        'name': _nameController.text.trim(),
        'email': _emailController.text.trim(),
      };

      if (_passwordController.text.isNotEmpty) {
        body['password'] = _passwordController.text;
        body['password_confirmation'] = _passwordConfirmController.text;
      }

      final res = await ApiService.post('/user', body);
      if (res.statusCode == 200) {
        final data = jsonDecode(res.body);
        final prefs = await SharedPreferences.getInstance();
        await prefs.setString('user_name', data['user']['name'] ?? '');
        await prefs.setString('user_email', data['user']['email'] ?? '');
        
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Profil diperbarui')));
          Navigator.pop(context, true);
        }
      } else {
        final err = jsonDecode(res.body);
        final msg = err['message'] ?? 'Gagal memperbarui profil';
        if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(msg)));
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
      appBar: AppBar(title: const Text('Pengaturan Akun')),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : ListView(
              padding: const EdgeInsets.all(24),
              children: [
                Center(
                  child: Stack(
                    children: [
                      CircleAvatar(
                        radius: 50,
                        backgroundColor: const Color(0xFF4A6741).withOpacity(0.1),
                        child: const Icon(Icons.person, size: 50, color: Color(0xFF4A6741)),
                      ),
                      Positioned(
                        bottom: 0,
                        right: 0,
                        child: CircleAvatar(
                          backgroundColor: const Color(0xFF4A6741),
                          radius: 18,
                          child: IconButton(
                            icon: const Icon(Icons.camera_alt, color: Colors.white, size: 16),
                            onPressed: () {},
                          ),
                        ),
                      )
                    ],
                  ),
                ),
                const SizedBox(height: 32),
                TextField(
                  controller: _nameController,
                  decoration: const InputDecoration(labelText: 'Nama Lengkap', prefixIcon: Icon(Icons.person_outline)),
                ),
                const SizedBox(height: 16),
                TextField(
                  controller: _emailController,
                  keyboardType: TextInputType.emailAddress,
                  decoration: const InputDecoration(labelText: 'Email', prefixIcon: Icon(Icons.email_outlined)),
                ),
                const SizedBox(height: 16),
                TextField(
                  controller: _passwordController,
                  obscureText: true,
                  decoration: const InputDecoration(labelText: 'Password Baru (Opsional)', prefixIcon: Icon(Icons.lock_outline)),
                ),
                const SizedBox(height: 16),
                TextField(
                  controller: _passwordConfirmController,
                  obscureText: true,
                  decoration: const InputDecoration(labelText: 'Konfirmasi Password Baru', prefixIcon: Icon(Icons.lock_clock_outlined)),
                ),
                const SizedBox(height: 32),
                ElevatedButton(
                  onPressed: _saveChanges,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF4A6741),
                    foregroundColor: Colors.white,
                    minimumSize: const Size(double.infinity, 50),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                  child: const Text('SIMPAN PERUBAHAN'),
                ),
              ],
            ),
    );
  }
}

class HelpSupportPage extends StatelessWidget {
  const HelpSupportPage({super.key});
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Bantuan & Dukungan')),
      body: ListView(
        padding: const EdgeInsets.all(20),
        children: [
          ListTile(leading: const Icon(Icons.help_center_outlined), title: const Text('Pusat Bantuan'), onTap: () {}),
          ListTile(leading: const Icon(Icons.contact_support_outlined), title: const Text('Hubungi Kami'), onTap: () {}),
          const Divider(),
          const ExpansionTile(
            title: Text('Bagaimana cara scan QR?'),
            children: [
              Padding(
                padding: EdgeInsets.all(16),
                child: Text('Tekan tombol QR di pojok kanan bawah beranda untuk memindai kode QR ternak.'),
              )
            ],
          )
        ],
      ),
    );
  }
}

class UserManualPage extends StatelessWidget {
  const UserManualPage({super.key});
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Panduan Pengguna')),
      body: ListView(
        padding: const EdgeInsets.all(20),
        children: [
          const Text('1. Registrasi Ternak', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
          const SizedBox(height: 8),
          const Text('Buka menu Ternak, klik tombol Tambah Ternak, isi data silsilah dan data diri ternak, lalu simpan.'),
          const SizedBox(height: 20),
          const Text('2. Analisis AI', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
          const SizedBox(height: 8),
          const Text('Buka detail kambing dari daftar ternak, lalu klik tombol JALANKAN ANALISIS & FORECAST.'),
        ],
      ),
    );
  }
}
