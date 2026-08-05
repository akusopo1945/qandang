import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'services/notification_service.dart';
import 'services/app_services.dart';
import 'screens/login_page.dart';
import 'screens/main_navigation.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  await NotificationService.init();
  runApp(
    const ProviderScope(
      child: QandangApp(),
    ),
  );
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

class AuthWrapper extends ConsumerWidget {
  const AuthWrapper({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final authState = ref.watch(authStateProvider);

    return authState.when(
      data: (isLoggedIn) => isLoggedIn ? const MainNavigation() : const LoginPage(),
      loading: () => const Scaffold(body: Center(child: CircularProgressIndicator())),
      error: (err, stack) => Scaffold(
        body: Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Text('Error memuat status auth: $err'),
              const SizedBox(height: 16),
              ElevatedButton(
                onPressed: () => ref.refresh(authStateProvider),
                child: const Text('Coba Lagi'),
              )
            ],
          ),
        ),
      ),
    );
  }
}
