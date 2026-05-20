import 'package:flutter/material.dart';
import 'services/notification_service.dart';
import 'services/app_services.dart';
import 'screens/login_page.dart';
import 'screens/main_navigation.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  await NotificationService.init();
  runApp(const QandangApp());
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
