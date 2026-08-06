import 'dart:convert';
import 'dart:io';
import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:url_launcher/url_launcher.dart';
import 'package:image_picker/image_picker.dart';
import 'package:share_plus/share_plus.dart';
import 'package:path_provider/path_provider.dart';
import '../services/app_services.dart';
import '../widgets/premium_image.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:local_auth/local_auth.dart';
import '../services/providers.dart';
import 'login_page.dart';

class ProfilePage extends ConsumerStatefulWidget {
  const ProfilePage({super.key});

  @override
  ConsumerState<ProfilePage> createState() => _ProfilePageState();
}

class _ProfilePageState extends ConsumerState<ProfilePage> {
  String _name = 'Peternak Qandang';
  String _email = 'peternak@qandang.com';
  String? _avatarUrl;
  int _goatCount = 0;
  int _syncQueueCount = 0;
  bool _syncing = false;
  bool _biometricEnabled = false;

  @override
  void initState() {
    super.initState();
    _loadLocalProfile();
    _fetchRemoteProfile();
    _loadLocalStats();
  }

  Future<void> _loadLocalProfile() async {
    final prefs = await SharedPreferences.getInstance();
    setState(() {
      _name = prefs.getString('user_name') ?? 'Peternak Qandang';
      _email = prefs.getString('user_email') ?? 'peternak@qandang.com';
      _avatarUrl = prefs.getString('user_avatar');
      if (_avatarUrl?.isEmpty == true) _avatarUrl = null;
      _biometricEnabled = prefs.getBool('biometric_enabled') ?? false;
    });
  }

  Future<void> _loadLocalStats() async {
    try {
      final goats = await DbHelper.getGoats();
      final database = await DbHelper.db;
      final queue = await database.query('sync_queue');
      setState(() {
        _goatCount = goats.length;
        _syncQueueCount = queue.length;
      });
    } catch (_) {}
  }

  Future<void> _fetchRemoteProfile() async {
    try {
      final res = await ApiService.get('/user');
      if (res.statusCode == 200) {
        final data = jsonDecode(res.body);
        final prefs = await SharedPreferences.getInstance();
        await prefs.setString('user_name', data['name'] ?? '');
        await prefs.setString('user_email', data['email'] ?? '');
        await prefs.setString('user_avatar', data['avatar_url'] ?? '');
        if (mounted) {
          setState(() {
            _name = data['name'] ?? '';
            _email = data['email'] ?? '';
            _avatarUrl = data['avatar_url'];
            if (_avatarUrl?.isEmpty == true) _avatarUrl = null;
          });
        }
      }
    } catch (_) {
      // Fallback to local
    }
  }

  Future<void> _syncQueueNow() async {
    if (_syncQueueCount == 0) return;
    setState(() => _syncing = true);
    ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Menyingkronkan data tertunda... 📡')));
    await DbHelper.processQueue();
    await _loadLocalStats();
    setState(() => _syncing = false);
    if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(
        content: Text(_syncQueueCount == 0 ? 'Semua data berhasil disinkronkan!' : 'Sinkronisasi gagal, beberapa data masih antre.'),
        backgroundColor: _syncQueueCount == 0 ? Colors.green : Colors.orange,
      ));
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Profil')),
      body: RefreshIndicator(
        onRefresh: () async {
          await _fetchRemoteProfile();
          await _loadLocalStats();
        },
        child: ListView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.all(24),
          children: [
            // Profile Header Card
            Card(
              elevation: 0,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(20),
                side: BorderSide(color: Colors.grey.shade200),
              ),
              child: Padding(
                padding: const EdgeInsets.all(20),
                child: Column(
                  children: [
                    PremiumAvatar(
                      radius: 45,
                      imageUrl: _avatarUrl,
                    ),
                    const SizedBox(height: 16),
                    Text(
                      _name,
                      style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
                    ),
                    Text(
                      _email,
                      style: const TextStyle(color: Colors.grey, fontSize: 14),
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 16),

            // Statistics Grid Card
            Row(
              children: [
                Expanded(
                  child: Card(
                    elevation: 0,
                    color: const Color(0xFF4A6741).withOpacity(0.05),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(16),
                      side: BorderSide(color: const Color(0xFF4A6741).withOpacity(0.15)),
                    ),
                    child: Padding(
                      padding: const EdgeInsets.all(16),
                      child: Column(
                        children: [
                          const Icon(Icons.pets, color: Color(0xFF4A6741)),
                          const SizedBox(height: 8),
                          Text('$_goatCount', style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: Color(0xFF4A6741))),
                          const Text('Total Ternak', style: TextStyle(fontSize: 12, color: Colors.grey)),
                        ],
                      ),
                    ),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: GestureDetector(
                    onTap: _syncing ? null : _syncQueueNow,
                    child: Card(
                      elevation: 0,
                      color: _syncQueueCount > 0 ? Colors.orange.shade50 : Colors.blue.shade50,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(16),
                        side: BorderSide(color: _syncQueueCount > 0 ? Colors.orange.shade200 : Colors.blue.shade200),
                      ),
                      child: Padding(
                        padding: const EdgeInsets.all(16),
                        child: Column(
                          children: [
                            _syncing
                                ? const SizedBox(height: 24, width: 24, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.orange))
                                : Icon(Icons.cloud_sync_outlined, color: _syncQueueCount > 0 ? Colors.orange : Colors.blue),
                            const SizedBox(height: 8),
                            Text('$_syncQueueCount', style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: _syncQueueCount > 0 ? Colors.orange : Colors.blue)),
                            Text(_syncQueueCount > 0 ? 'Ketuk untuk Sync' : 'Antrean Sync', style: const TextStyle(fontSize: 12, color: Colors.grey)),
                          ],
                        ),
                      ),
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 24),

            // Profile Menus
            _buildProfileMenu(
              context,
              icon: Icons.settings_outlined,
              label: 'Pengaturan Akun',
              onTap: () async {
                final result = await Navigator.push(
                  context,
                  MaterialPageRoute(
                    builder: (_) => AccountSettingsPage(initialName: _name, initialEmail: _email, initialAvatarUrl: _avatarUrl),
                  ),
                );
                if (result == true) {
                  _loadLocalProfile();
                  _fetchRemoteProfile();
                }
              },
            ),
            
            // Switch Keamanan Biometrik
            Card(
              elevation: 0,
              margin: const EdgeInsets.only(bottom: 12),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(16),
                side: BorderSide(color: Colors.grey.shade200),
              ),
              child: SwitchListTile(
                secondary: const Icon(Icons.fingerprint, color: Color(0xFF4A6741)),
                title: const Text('Keamanan Biometrik', style: TextStyle(fontWeight: FontWeight.bold)),
                subtitle: const Text('Sidik jari / Face ID untuk masuk cepat', style: TextStyle(fontSize: 11)),
                value: _biometricEnabled,
                activeColor: const Color(0xFF4A6741),
                onChanged: (bool value) async {
                  final prefs = await SharedPreferences.getInstance();
                  if (value) {
                    // Minta verifikasi sekali sebelum mengaktifkan
                    try {
                      final auth = LocalAuthentication();
                      final bool canAuthenticate = await auth.canCheckBiometrics || await auth.isDeviceSupported();
                      if (!canAuthenticate) {
                        if (mounted) ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Perangkat tidak mendukung biometrik')));
                        return;
                      }
                      
                      final bool didAuthenticate = await auth.authenticate(
                        localizedReason: 'Konfirmasi biometrik untuk mengaktifkan fitur ini',
                        options: const AuthenticationOptions(stickyAuth: true, biometricOnly: true),
                      );

                      if (didAuthenticate) {
                        await prefs.setBool('biometric_enabled', true);
                        setState(() {
                          _biometricEnabled = true;
                        });
                        if (mounted) ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Biometrik berhasil diaktifkan!')));
                      }
                    } catch (e) {
                      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error: $e')));
                    }
                  } else {
                    // Menonaktifkan biometrik
                    await prefs.setBool('biometric_enabled', false);
                    setState(() {
                      _biometricEnabled = false;
                    });
                    if (mounted) ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Biometrik dinonaktifkan')));
                  }
                },
              ),
            ),
            _buildProfileMenu(
              context,
              icon: Icons.home_work_outlined,
              label: 'Profil Kandang',
              onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const BarnProfilePage())),
            ),
            _buildProfileMenu(
              context,
              icon: Icons.file_download_outlined,
              label: 'Ekspor Data Ternak (CSV)',
              onTap: _exportCSVLocally,
            ),
            _buildProfileMenu(context, icon: Icons.help_outline, label: 'Bantuan & Dukungan', onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const HelpSupportPage()))),
            _buildProfileMenu(context, icon: Icons.description_outlined, label: 'Panduan Pengguna', onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const UserManualPage()))),
            _buildProfileMenu(context, icon: Icons.info_outline, label: 'Tentang Aplikasi', onTap: () => showAboutDialog(context: context, applicationName: 'Qandang', applicationVersion: '1.0.0', applicationIcon: const Icon(Icons.grass, color: Color(0xFF4A6741), size: 40), children: [const Text('Qandang adalah solusi cerdas untuk manajemen peternakan kambing modern.')])),

            const SizedBox(height: 40),
            ElevatedButton(
              onPressed: () async {
                await ref.read(authStateProvider.notifier).logout();
                if (context.mounted) {
                  Navigator.pushAndRemoveUntil(
                    context,
                    MaterialPageRoute(builder: (_) => const LoginPage()),
                    (route) => false,
                  );
                }
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

  Future<void> _exportCSVLocally() async {
    final goats = ref.read(goatListProvider).value;
    if (goats == null || goats.isEmpty) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Tidak ada data ternak untuk diekspor')));
      return;
    }

    try {
      final List<String> csvRows = [];
      csvRows.add('ID,Nama,QR Code,Jenis,Kelamin,Bobot(kg),Status,Tanggal Lahir');
      for (var goat in goats) {
        final id = goat['id'];
        final name = (goat['name'] ?? '').toString().replaceAll(',', ' ');
        final qr = (goat['qr_code'] ?? '').toString().replaceAll(',', ' ');
        final breed = (goat['breed'] ?? '').toString().replaceAll(',', ' ');
        final gender = goat['gender'] == 'male' ? 'Jantan' : 'Betina';
        final weight = goat['weight'] ?? 0.0;
        final status = (goat['status'] ?? '').toString().replaceAll(',', ' ');
        final dob = goat['date_of_birth'] ?? '';
        csvRows.add('$id,$name,$qr,$breed,$gender,$weight,$status,$dob');
      }
      final csvString = csvRows.join('\n');

      final directory = await getTemporaryDirectory();
      final path = '${directory.path}/data_ternak_qandang.csv';
      final file = File(path);
      await file.writeAsString(csvString);

      if (mounted) {
        final box = context.findRenderObject() as RenderBox?;
        await Share.shareXFiles(
          [XFile(path)],
          text: 'Data Ternak Qandang',
          sharePositionOrigin: box!.localToGlobal(Offset.zero) & box.size,
        );
      }
    } catch (e) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Gagal ekspor: $e')));
    }
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
  final String? initialAvatarUrl;
  const AccountSettingsPage({super.key, required this.initialName, required this.initialEmail, this.initialAvatarUrl});

  @override
  State<AccountSettingsPage> createState() => _AccountSettingsPageState();
}

class _AccountSettingsPageState extends State<AccountSettingsPage> {
  late TextEditingController _nameController;
  late TextEditingController _emailController;
  final _passwordController = TextEditingController();
  final _passwordConfirmController = TextEditingController();
  File? _avatarFile;
  String? _base64Avatar;
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

  Future<void> _pickAvatar() async {
    final photo = await ImagePicker().pickImage(
      source: ImageSource.gallery,
      imageQuality: 70,
      maxWidth: 400,
      maxHeight: 400,
    );
    if (photo != null) {
      final file = File(photo.path);
      final bytes = await file.readAsBytes();
      setState(() {
        _avatarFile = file;
        _base64Avatar = base64Encode(bytes);
      });
    }
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

      if (_base64Avatar != null) {
        body['avatar'] = _base64Avatar!;
      }

      final res = await ApiService.post('/user', body);
      if (res.statusCode == 200) {
        final data = jsonDecode(res.body);
        final prefs = await SharedPreferences.getInstance();
        await prefs.setString('user_name', data['user']['name'] ?? '');
        await prefs.setString('user_email', data['user']['email'] ?? '');
        await prefs.setString('user_avatar', data['user']['avatar_url'] ?? '');
        
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
                      GestureDetector(
                        onTap: _pickAvatar,
                        child: CircleAvatar(
                          radius: 50,
                          backgroundColor: const Color(0xFF4A6741).withOpacity(0.1),
                          backgroundImage: _avatarFile != null
                              ? FileImage(_avatarFile!)
                              : (widget.initialAvatarUrl != null
                                  ? NetworkImage(widget.initialAvatarUrl!)
                                  : null) as ImageProvider?,
                          child: (_avatarFile == null && widget.initialAvatarUrl == null)
                              ? const Icon(Icons.person, size: 50, color: Color(0xFF4A6741))
                              : null,
                        ),
                      ),
                      Positioned(
                        bottom: 0,
                        right: 0,
                        child: GestureDetector(
                          onTap: _pickAvatar,
                          child: const CircleAvatar(
                            backgroundColor: Color(0xFF4A6741),
                            radius: 16,
                            child: Icon(Icons.camera_alt, color: Colors.white, size: 14),
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

class BarnProfilePage extends StatefulWidget {
  const BarnProfilePage({super.key});

  @override
  State<BarnProfilePage> createState() => _BarnProfilePageState();
}

class _BarnProfilePageState extends State<BarnProfilePage> {
  final _nameController = TextEditingController();
  final _ownerController = TextEditingController();
  final _phoneController = TextEditingController();
  final _addressController = TextEditingController();
  final _capacityController = TextEditingController();
  final _descController = TextEditingController();
  bool _loading = true;
  bool _saving = false;

  @override
  void initState() {
    super.initState();
    _fetchBarnProfile();
  }

  @override
  void dispose() {
    _nameController.dispose();
    _ownerController.dispose();
    _phoneController.dispose();
    _addressController.dispose();
    _capacityController.dispose();
    _descController.dispose();
    super.dispose();
  }

  Future<void> _fetchBarnProfile() async {
    try {
      final res = await ApiService.get('/barn-profile');
      if (res.statusCode == 200) {
        final data = jsonDecode(res.body);
        _nameController.text = data['name'] ?? '';
        _ownerController.text = data['owner_name'] ?? '';
        _phoneController.text = data['phone'] ?? '';
        _addressController.text = data['address'] ?? '';
        _capacityController.text = data['capacity']?.toString() ?? '';
        _descController.text = data['description'] ?? '';
      }
    } catch (_) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Offline: Gagal memuat profil kandang')));
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _saveBarnProfile() async {
    if (_nameController.text.trim().isEmpty || _addressController.text.trim().isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Nama Kandang dan Alamat wajib diisi')));
      return;
    }

    setState(() => _saving = true);

    try {
      final body = {
        '_method': 'PUT',
        'name': _nameController.text.trim(),
        'owner_name': _ownerController.text.trim(),
        'phone': _phoneController.text.trim(),
        'address': _addressController.text.trim(),
        'capacity': int.tryParse(_capacityController.text.trim()),
        'description': _descController.text.trim(),
      };

      final res = await ApiService.post('/barn-profile', body);
      if (res.statusCode == 200) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Profil Kandang diperbarui! 🏡')));
          Navigator.pop(context);
        }
      } else {
        if (mounted) ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Gagal memperbarui profil')));
      }
    } catch (e) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error: $e')));
    } finally {
      setState(() => _saving = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Profil Kandang')),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : _saving
              ? const Center(child: CircularProgressIndicator())
              : ListView(
                  padding: const EdgeInsets.all(24),
                  children: [
                    TextField(
                      controller: _nameController,
                      decoration: const InputDecoration(labelText: 'Nama Kandang *', prefixIcon: Icon(Icons.home_work_outlined)),
                    ),
                    const SizedBox(height: 16),
                    TextField(
                      controller: _ownerController,
                      decoration: const InputDecoration(labelText: 'Nama Pemilik', prefixIcon: Icon(Icons.person_outline)),
                    ),
                    const SizedBox(height: 16),
                    TextField(
                      controller: _phoneController,
                      keyboardType: TextInputType.phone,
                      decoration: const InputDecoration(labelText: 'No. Telepon', prefixIcon: Icon(Icons.phone_outlined)),
                    ),
                    const SizedBox(height: 16),
                    TextField(
                      controller: _capacityController,
                      keyboardType: TextInputType.number,
                      decoration: const InputDecoration(labelText: 'Kapasitas (Ekor)', prefixIcon: Icon(Icons.tag)),
                    ),
                    const SizedBox(height: 16),
                    TextField(
                      controller: _addressController,
                      decoration: const InputDecoration(labelText: 'Alamat Lengkap *', prefixIcon: Icon(Icons.location_on_outlined)),
                    ),
                    const SizedBox(height: 16),
                    TextField(
                      controller: _descController,
                      maxLines: 3,
                      decoration: const InputDecoration(labelText: 'Deskripsi / Catatan Kandang', prefixIcon: Icon(Icons.info_outline)),
                    ),
                    const SizedBox(height: 32),
                    ElevatedButton(
                      onPressed: _saveBarnProfile,
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF4A6741),
                        foregroundColor: Colors.white,
                        minimumSize: const Size(double.infinity, 50),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      ),
                      child: const Text('SIMPAN PROFIL KANDANG'),
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
      appBar: AppBar(title: const Text('Bantuan & Dukungan', style: TextStyle(fontWeight: FontWeight.bold))),
      body: ListView(
        padding: const EdgeInsets.all(20),
        children: [
          Container(
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(color: const Color(0xFF4A6741).withOpacity(0.1), borderRadius: BorderRadius.circular(16)),
            child: const Column(
              children: [
                Icon(Icons.support_agent, size: 60, color: Color(0xFF4A6741)),
                SizedBox(height: 12),
                Text('Ada pertanyaan atau kendala?', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: Color(0xFF4A6741))),
                SizedBox(height: 4),
                Text('Tim dukungan kami siap membantu Anda 24/7.', textAlign: TextAlign.center, style: TextStyle(color: Colors.grey)),
              ],
            ),
          ),
          const SizedBox(height: 24),
          Card(
            elevation: 0,
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16), side: BorderSide(color: Colors.grey.shade200)),
            child: Column(
              children: [
                ListTile(
                  leading: const CircleAvatar(backgroundColor: Colors.blue, child: Icon(Icons.chat_bubble_outline, color: Colors.white, size: 20)),
                  title: const Text('Chat WhatsApp'),
                  subtitle: const Text('Respon cepat'),
                  trailing: const Icon(Icons.chevron_right),
                  onTap: () async {
                    final url = Uri.parse('https://wa.me/6282244994491');
                    try {
                      await launchUrl(url, mode: LaunchMode.externalApplication);
                    } catch (e) {
                      if (context.mounted) ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Gagal membuka WhatsApp')));
                    }
                  },
                ),
                const Divider(height: 1),
                ListTile(
                  leading: const CircleAvatar(backgroundColor: Colors.orange, child: Icon(Icons.email_outlined, color: Colors.white, size: 20)),
                  title: const Text('Email Dukungan'),
                  subtitle: const Text('support@qandang.com'),
                  trailing: const Icon(Icons.chevron_right),
                  onTap: () async {
                    final url = Uri.parse('mailto:support@qandang.com');
                    try {
                      await launchUrl(url, mode: LaunchMode.externalApplication);
                    } catch (e) {
                      if (context.mounted) ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Gagal membuka aplikasi Email')));
                    }
                  },
                ),
              ],
            ),
          ),
          const SizedBox(height: 24),
          const Text('FAQ Singkat', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
          const SizedBox(height: 12),
          const ExpansionTile(
            title: Text('Bagaimana cara kerja fitur offline?'),
            children: [Padding(padding: EdgeInsets.all(16), child: Text('Semua perubahan akan disimpan di perangkat Anda saat tidak ada internet. Ketika koneksi tersedia, aplikasi akan menyinkronkannya secara otomatis ke server.', style: TextStyle(color: Colors.grey)))],
          ),
          const ExpansionTile(
            title: Text('Bagaimana cara scan QR?'),
            children: [Padding(padding: EdgeInsets.all(16), child: Text('Buka halaman "Ternak", lalu klik tombol QR di bagian atas untuk memindai kode QR kambing.', style: TextStyle(color: Colors.grey)))],
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
      appBar: AppBar(title: const Text('Panduan Pengguna', style: TextStyle(fontWeight: FontWeight.bold))),
      body: ListView(
        padding: const EdgeInsets.all(20),
        children: [
          _buildManualCard(
            title: '1. Registrasi Ternak Baru',
            icon: Icons.add_circle_outline,
            description: 'Buka menu Ternak, klik ikon ➕. Isi biodata ternak termasuk silsilah indukan, jenis, kelmin, dan bobot awal. Anda juga bisa langsung memindai QR Tag baru.',
          ),
          _buildManualCard(
            title: '2. Pemantauan & Sinkronisasi',
            icon: Icons.sync,
            description: 'Aplikasi bekerja 100% offline. Anda dapat mengedit dan menghapus ternak di lapangan tanpa sinyal. Cek antrean sync di halaman profil.',
          ),
          _buildManualCard(
            title: '3. Prediksi Pertumbuhan AI',
            icon: Icons.auto_graph,
            description: 'Di halaman Detail Ternak, klik "Jalankan Analisis & Forecast" untuk melihat prediksi bobot 30 hari ke depan menggunakan model kecerdasan buatan.',
          ),
          _buildManualCard(
            title: '4. Ekspor Data Ternak',
            icon: Icons.download_outlined,
            description: 'Gunakan fitur Ekspor CSV di profil untuk mengunduh seluruh data ternak Anda dalam format Excel/CSV yang siap dikirim via WhatsApp.',
          ),
        ],
      ),
    );
  }

  Widget _buildManualCard({required String title, required IconData icon, required String description}) {
    return Card(
      elevation: 0,
      margin: const EdgeInsets.only(bottom: 16),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16), side: BorderSide(color: Colors.grey.shade200)),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(color: const Color(0xFF4A6741).withOpacity(0.1), borderRadius: BorderRadius.circular(12)),
              child: Icon(icon, color: const Color(0xFF4A6741)),
            ),
            const SizedBox(width: 16),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(title, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
                  const SizedBox(height: 6),
                  Text(description, style: const TextStyle(color: Colors.grey, fontSize: 13, height: 1.4)),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}
