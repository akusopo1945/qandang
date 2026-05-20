import 'dart:convert';
import 'package:sqflite/sqflite.dart';
import 'package:path/path.dart' as p;
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

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
