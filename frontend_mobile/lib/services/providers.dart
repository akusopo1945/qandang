import 'dart:convert';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'app_services.dart';

// Provider untuk memantau status login
final authStateProvider = StateNotifierProvider<AuthNotifier, AsyncValue<bool>>((ref) {
  return AuthNotifier();
});

class AuthNotifier extends StateNotifier<AsyncValue<bool>> {
  AuthNotifier() : super(const AsyncValue.loading()) {
    checkLoginStatus();
  }

  Future<void> checkLoginStatus() async {
    state = const AsyncValue.loading();
    try {
      final token = await ApiService.getToken();
      state = AsyncValue.data(token != null);
    } catch (e, stack) {
      state = AsyncValue.error(e, stack);
    }
  }

  Future<void> logout() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('token');
    await prefs.remove('user_name');
    await prefs.remove('user_email');
    await prefs.remove('user_avatar');
    state = const AsyncValue.data(false);
  }

  void setLoggedIn(bool isLoggedIn) {
    state = AsyncValue.data(isLoggedIn);
  }
}

// Provider untuk data kambing (Goat List)
final goatListProvider = StateNotifierProvider<GoatListNotifier, AsyncValue<List<Map<String, dynamic>>>>((ref) {
  return GoatListNotifier();
});

class GoatListNotifier extends StateNotifier<AsyncValue<List<Map<String, dynamic>>>> {
  GoatListNotifier() : super(const AsyncValue.loading()) {
    loadGoats();
  }

  Future<void> loadGoats() async {
    state = const AsyncValue.loading();
    try {
      // 1. Load data lokal terlebih dahulu untuk kecepatan akses (offline-first)
      final localGoats = await DbHelper.getGoats();
      state = AsyncValue.data(localGoats);

      // 2. Coba fetch dari remote server jika ada internet
      await syncWithRemote();
    } catch (e, stack) {
      // Jika error, pastikan fallback ke data lokal tetap ada
      final localGoats = await DbHelper.getGoats();
      if (localGoats.isNotEmpty) {
        state = AsyncValue.data(localGoats);
      } else {
        state = AsyncValue.error(e, stack);
      }
    }
  }

  Future<void> syncWithRemote() async {
    try {
      // Proses antrean sync lokal sebelum ambil data baru
      await DbHelper.processQueue();

      final res = await ApiService.get('/goats');
      if (res.statusCode == 200) {
        final List remoteGoats = jsonDecode(res.body);
        await DbHelper.saveGoats(remoteGoats);
        
        final updatedLocalGoats = await DbHelper.getGoats();
        state = AsyncValue.data(updatedLocalGoats);
      }
    } catch (_) {
      // Offline / network failure, biarkan data lokal yang tampil
    }
  }

  Future<void> addGoat(Map<String, dynamic> body, String? base64Image) async {
    if (base64Image != null) {
      body['image'] = base64Image;
    }

    try {
      final res = await ApiService.post('/goats', body);
      if (res.statusCode == 200 || res.statusCode == 201) {
        final newGoat = jsonDecode(res.body);
        await DbHelper.saveGoats([newGoat]);
      } else {
        throw Exception(jsonDecode(res.body)['message'] ?? 'Gagal menyimpan ke server');
      }
    } catch (e) {
      // Mode Offline: Simpan ke database lokal dengan ID sementara, dan masukkan antrean sync
      final tempId = DateTime.now().millisecondsSinceEpoch;
      final localGoat = {
        'id': tempId,
        'name': body['name'],
        'qr_code': body['qr_code'],
        'breed': body['breed'],
        'gender': body['gender'],
        'weight': body['weight'] ?? body['initial_weight'] ?? 0.0,
        'status': body['status'] ?? 'Sehat',
        'note': body['description'] ?? body['note'],
        'date_of_birth': body['birth_date'],
        'weight_logs': jsonEncode([]),
        'health_records': jsonEncode([]),
        'dam_id': body['dam_id'],
        'sire_id': body['sire_id'],
      };
      await DbHelper.saveGoats([localGoat]);
      await DbHelper.addToQueue('/goats', 'POST', body);
    }
    
    // Refresh list setelah perubahan
    await loadGoats();
  }

  Future<void> deleteGoat(Map<String, dynamic> goat) async {
    try {
      await DbHelper.deleteGoatLocally(goat['id']);
      final res = await ApiService.delete('/goats/${goat['id']}');
      if (res.statusCode != 200 && res.statusCode != 204) {
        // Jika server menolak, tambahkan ke queue sync untuk di-delete nanti
        await DbHelper.addToQueue('/goats/${goat['id']}', 'DELETE', null);
      }
    } catch (_) {
      await DbHelper.addToQueue('/goats/${goat['id']}', 'DELETE', null);
    }
    await loadGoats();
  }
}
