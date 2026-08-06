import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:local_auth/local_auth.dart';
import '../services/app_services.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'main_navigation.dart';

import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../services/providers.dart';

class LoginPage extends ConsumerStatefulWidget {
  const LoginPage({super.key});

  @override
  ConsumerState<LoginPage> createState() => _LoginPageState();
}

class _LoginPageState extends ConsumerState<LoginPage> {
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  bool _biometricConfigured = false;
  bool _loading = false;
  bool _obscurePassword = true;

  @override
  void initState() {
    super.initState();
    _checkBiometricPreference();
  }

  final _secureStorage = const FlutterSecureStorage();

  _checkBiometricPreference() async {
    final prefs = await SharedPreferences.getInstance();
    final isEnabled = prefs.getBool('biometric_enabled') ?? false;
    final savedEmail = await _secureStorage.read(key: 'user_email');
    final savedPassword = await _secureStorage.read(key: 'user_password');
    
    // Aktifkan prompt biometrik jika toggle ON dan ada kredensial tersimpan
    if (isEnabled && savedEmail != null && savedPassword != null) {
      setState(() {
        _biometricConfigured = true;
      });
      // Pemicu otomatis prompt login sidik jari
      WidgetsBinding.instance.addPostFrameCallback((_) {
        _biometricLogin();
      });
    }
  }

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
        
        // Simpan ke secure storage untuk keperluan biometrik
        await _secureStorage.write(key: 'user_email', value: _emailController.text);
        await _secureStorage.write(key: 'user_password', value: _passwordController.text);

        if (data['user'] != null) {
          await prefs.setString('user_name', data['user']['name'] ?? '');
          await prefs.setString('user_email', data['user']['email'] ?? '');
          await prefs.setString('user_avatar', data['user']['avatar_url'] ?? '');
        }
        ref.read(authStateProvider.notifier).setLoggedIn(true);
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

  _biometricLogin() async {
    final savedEmail = await _secureStorage.read(key: 'user_email');
    final savedPassword = await _secureStorage.read(key: 'user_password');
    
    if (savedEmail == null || savedPassword == null) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Anda harus login dengan password terlebih dahulu untuk mengaktifkan biometrik.')));
      return;
    }

    try {
      final auth = LocalAuthentication();
      final bool canAuthenticateWithBiometrics = await auth.canCheckBiometrics;
      final bool canAuthenticate = canAuthenticateWithBiometrics || await auth.isDeviceSupported();

      if (!canAuthenticate) {
        if (mounted) ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Perangkat ini tidak mendukung login biometrik.')));
        return;
      }

      final bool didAuthenticate = await auth.authenticate(
        localizedReason: 'Pindai sidik jari / wajah Anda untuk masuk ke Qandang',
        options: const AuthenticationOptions(stickyAuth: true, biometricOnly: true),
      );

      if (didAuthenticate && mounted) {
        setState(() => _loading = true);
        
        // Lakukan request login di belakang layar
        final res = await ApiService.post('/login', {
          'email': savedEmail,
          'password': savedPassword,
          'device_name': 'mobile_app',
        });

        if (res.statusCode == 200) {
          final data = jsonDecode(res.body);
          final prefs = await SharedPreferences.getInstance();
          await prefs.setString('token', data['token']);
          if (data['user'] != null) {
            await prefs.setString('user_name', data['user']['name'] ?? '');
            await prefs.setString('user_email', data['user']['email'] ?? '');
            await prefs.setString('user_avatar', data['user']['avatar_url'] ?? '');
          }
          ref.read(authStateProvider.notifier).setLoggedIn(true);
          if (mounted) {
            Navigator.pushReplacement(context, MaterialPageRoute(builder: (_) => const MainNavigation()));
          }
        } else {
          if (mounted) ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Login biometrik gagal (Sesi kadaluarsa)')));
        }
      }
    } catch (e) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Biometrik error: $e')));
    } finally {
      if (mounted) setState(() => _loading = false);
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
            TextField(
              controller: _passwordController, 
              decoration: InputDecoration(
                labelText: 'Password',
                suffixIcon: IconButton(
                  icon: Icon(_obscurePassword ? Icons.visibility_off : Icons.visibility),
                  onPressed: () => setState(() => _obscurePassword = !_obscurePassword),
                ),
              ), 
              obscureText: _obscurePassword,
            ),
            const SizedBox(height: 40),
            _loading 
              ? const CircularProgressIndicator()
              : Column(
                  children: [
                    ElevatedButton(
                      onPressed: _login, 
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF4A6741),
                        foregroundColor: Colors.white,
                        minimumSize: const Size(double.infinity, 50),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      ),
                      child: const Text('MASUK'),
                    ),
                    const SizedBox(height: 16),
                    OutlinedButton.icon(
                      onPressed: _biometricLogin,
                      icon: const Icon(Icons.fingerprint, color: Color(0xFF4A6741)),
                      label: const Text('Login dengan Biometrik', style: TextStyle(color: Color(0xFF4A6741))),
                      style: OutlinedButton.styleFrom(
                        minimumSize: const Size(double.infinity, 50),
                        side: const BorderSide(color: Color(0xFF4A6741)),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      ),
                    ),
                  ],
                ),
          ],
        ),
      ),
    );
  }
}
