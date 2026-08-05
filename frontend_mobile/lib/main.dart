import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:workmanager/workmanager.dart';
import 'services/notification_service.dart';
import 'services/app_services.dart';
import 'services/providers.dart';
import 'screens/login_page.dart';
import 'screens/main_navigation.dart';

const String syncTaskName = "com.qandang.app.syncTask";

@pragma('vm:entry-point')
void callbackDispatcher() {
  Workmanager().executeTask((task, inputData) async {
    try {
      // Menjalankan sinkronisasi data tertunda dari antrean lokal ke API
      await DbHelper.processQueue();
      return Future.value(true);
    } catch (_) {
      return Future.value(false);
    }
  });
}

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  await NotificationService.init();

  // Inisialisasi Workmanager untuk background task
  await Workmanager().initialize(
    callbackDispatcher,
    isInDebugMode: false,
  );

  // Daftarkan tugas periodic sync (berjalan setiap 15 menit jika terhubung ke internet)
  await Workmanager().registerPeriodicTask(
    "1",
    syncTaskName,
    frequency: const Duration(minutes: 15),
    constraints: Constraints(
      networkType: NetworkType.connected, // Hanya berjalan saat terhubung internet
    ),
  );

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
