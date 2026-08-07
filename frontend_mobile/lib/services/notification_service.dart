import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'package:timezone/timezone.dart' as tz;
import 'package:timezone/data/latest.dart' as tz;
import 'dart:convert';
import 'app_services.dart';

class NotificationService {
  static final FlutterLocalNotificationsPlugin _notifications = FlutterLocalNotificationsPlugin();

  static Future<void> init() async {
    tz.initializeTimeZones();
    
    const AndroidInitializationSettings initializationSettingsAndroid =
        AndroidInitializationSettings('@mipmap/ic_launcher');
    
    const InitializationSettings initializationSettings = InitializationSettings(
      android: initializationSettingsAndroid,
    );

    await _notifications.initialize(initializationSettings);
    
    // Meminta izin untuk Android 13+
    await _notifications.resolvePlatformSpecificImplementation<AndroidFlutterLocalNotificationsPlugin>()?.requestNotificationsPermission();
    await _notifications.resolvePlatformSpecificImplementation<AndroidFlutterLocalNotificationsPlugin>()?.requestExactAlarmsPermission();
  }

  static Future<void> showNotification(int id, String title, String body) async {
    const AndroidNotificationDetails androidDetails = AndroidNotificationDetails(
      'qandang_main',
      'Notifikasi Utama Qandang',
      importance: Importance.max,
      priority: Priority.high,
    );

    const NotificationDetails details = NotificationDetails(android: androidDetails);
    await _notifications.show(id, title, body, details);
  }

  static Future<void> scheduleHealthReminder(int id, String name, String action, DateTime scheduledDate) async {
    // Schedule for 08:00 AM on the scheduled day
    final scheduledAt = tz.TZDateTime(
      tz.local,
      scheduledDate.year,
      scheduledDate.month,
      scheduledDate.day,
      8, 0,
    );

    if (scheduledAt.isBefore(tz.TZDateTime.now(tz.local))) return;

    await _notifications.zonedSchedule(
      id,
      'Jadwal Kesehatan: $name',
      'Hari ini waktunya $action untuk kambing $name. Jangan lupa ya! 🐐',
      scheduledAt,
      const NotificationDetails(
        android: AndroidNotificationDetails(
          'qandang_reminders',
          'Pengingat Kesehatan Ternak',
          channelDescription: 'Pengingat otomatis untuk vaksinasi dan obat cacing',
          importance: Importance.max,
          priority: Priority.high,
        ),
      ),
      androidScheduleMode: AndroidScheduleMode.exactAllowWhileIdle,
      uiLocalNotificationDateInterpretation: UILocalNotificationDateInterpretation.absoluteTime,
    );
  }

  static Future<void> syncAllReminders() async {
    final goats = await DbHelper.getGoats();
    int notificationIdCounter = 100;

    // Clear old scheduled notifications
    await _notifications.cancelAll();

    for (var goat in goats) {
      final records = jsonDecode(goat['health_records'] as String) as List;
      for (var record in records) {
        if (record['next_scheduled_date'] != null) {
          final nextDate = DateTime.parse(record['next_scheduled_date']);
          if (nextDate.isAfter(DateTime.now())) {
            await scheduleHealthReminder(
              notificationIdCounter++,
              goat['name'],
              record['action_type'] ?? record['type'] ?? 'Tindakan',
              nextDate,
            );
          }
        }
      }
    }
  }
}
