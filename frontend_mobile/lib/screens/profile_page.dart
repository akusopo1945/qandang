import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:url_launcher/url_launcher.dart';
import '../services/app_services.dart';
import 'login_page.dart';

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
              context,
              icon: Icons.file_download_outlined,
              label: 'Ekspor Data Ternak (CSV)',
              onTap: () async {
                final url = Uri.parse('${ApiService.baseUrl}/export/goats');
                if (await canLaunchUrl(url)) await launchUrl(url, mode: LaunchMode.externalApplication);
              },
            ),
            _buildProfileMenu(context, icon: Icons.settings_outlined, label: 'Pengaturan Akun', onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const AccountSettingsPage()))),
            _buildProfileMenu(context, icon: Icons.help_outline, label: 'Bantuan & Dukungan', onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const HelpSupportPage()))),
            _buildProfileMenu(context, icon: Icons.description_outlined, label: 'Panduan Pengguna', onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const UserManualPage()))),
            _buildProfileMenu(context, icon: Icons.info_outline, label: 'Tentang Aplikasi', onTap: () => showAboutDialog(context: context, applicationName: 'Qandang', applicationVersion: '1.0.0', applicationIcon: const Icon(Icons.grass, color: Color(0xFF4A6741), size: 40), children: [const Text('Qandang adalah solusi cerdas untuk manajemen peternakan kambing modern.')])),

            const Spacer(),
            ElevatedButton(
              onPressed: () async {
                final prefs = await SharedPreferences.getInstance();
                await prefs.clear();
                if (context.mounted) Navigator.pushReplacement(context, MaterialPageRoute(builder: (_) => const LoginPage()));
              },
              style: ElevatedButton.styleFrom(backgroundColor: Colors.red.shade50, foregroundColor: Colors.red, elevation: 0, minimumSize: const Size(double.infinity, 50), shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12))),
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
  const AccountSettingsPage({super.key});
  @override State<AccountSettingsPage> createState() => _AccountSettingsPageState();
}
class _AccountSettingsPageState extends State<AccountSettingsPage> {
  @override Widget build(BuildContext context) {
    return Scaffold(appBar: AppBar(title: const Text('Pengaturan Akun')), body: ListView(padding: const EdgeInsets.all(24), children: [const Center(child: Stack(children: [CircleAvatar(radius: 50, child: Icon(Icons.person, size: 50)), Positioned(bottom: 0, right: 0, child: CircleAvatar(backgroundColor: Color(0xFF4A6741), radius: 18, child: Icon(Icons.camera_alt, color: Colors.white, size: 18)))])), const SizedBox(height: 32), const TextField(decoration: InputDecoration(labelText: 'Nama Lengkap', prefixIcon: Icon(Icons.person_outline))), const SizedBox(height: 16), const TextField(decoration: InputDecoration(labelText: 'Email', prefixIcon: Icon(Icons.email_outlined))), const SizedBox(height: 32), ElevatedButton(onPressed: () => ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Profil diperbarui'))), style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFF4A6741), foregroundColor: Colors.white, minimumSize: const Size(double.infinity, 50)), child: const Text('SIMPAN PERUBAHAN'))]));
  }
}

class HelpSupportPage extends StatelessWidget {
  const HelpSupportPage({super.key});
  @override Widget build(BuildContext context) {
    return Scaffold(appBar: AppBar(title: const Text('Bantuan & Dukungan')), body: ListView(padding: const EdgeInsets.all(20), children: [ListTile(leading: const Icon(Icons.help_center_outlined), title: const Text('Pusat Bantuan'), onTap: () {}), ListTile(leading: const Icon(Icons.contact_support_outlined), title: const Text('Hubungi Kami'), onTap: () {}), const Divider(), const ExpansionTile(title: Text('Bagaimana cara scan QR?'), children: [Padding(padding: EdgeInsets.all(16), child: Text('Tekan tombol QR di pojok kanan bawah.'))])]));
  }
}

class UserManualPage extends StatelessWidget {
  const UserManualPage({super.key});
  @override Widget build(BuildContext context) {
    return Scaffold(appBar: AppBar(title: const Text('Panduan Pengguna')), body: ListView(padding: const EdgeInsets.all(20), children: [const Text('1. Registrasi Ternak', style: TextStyle(fontWeight: FontWeight.bold)), const Text('Buka menu Ternak, klik Tambah Ternak.'), const SizedBox(height: 20), const Text('2. Analisis AI', style: TextStyle(fontWeight: FontWeight.bold)), const Text('Buka detail ternak, klik JALANKAN ANALISIS.')]));
  }
}
