import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import '../services/api_service.dart';

@pragma('vm:entry-point')
Future<void> handleBackgroundMessage(RemoteMessage message) async {
  print('Title: ${message.notification?.title}');
  print('Body: ${message.notification?.body}');
  print('Payload: ${message.data}');
}

class FirebaseApi {
  final _firebaseMessaging = FirebaseMessaging.instance;
  final ApiService _apiService = ApiService();

  Future<void> initNotifications() async {
    try {
      // Request permission
      await _firebaseMessaging.requestPermission();

      // Fetch FCM token
      final fcmToken = await _firebaseMessaging.getToken();
      print('FCM Token: $fcmToken');

      if (fcmToken != null) {
        // Send token to backend safely
        _apiService.sendFcmToken(fcmToken).catchError((err) {
          print('Error sending FCM token to backend: $err');
          return {'status': 500, 'message': err.toString()};
        });
      }

      // Handle token refresh
      _firebaseMessaging.onTokenRefresh.listen((fcmToken) {
        _apiService.sendFcmToken(fcmToken);
      }).onError((err) {
        print('Error getting token refresh: $err');
      });

      // Initialize background settings
      FirebaseMessaging.onBackgroundMessage(handleBackgroundMessage);

      // Initialize local notifications for foreground messages
      const AndroidInitializationSettings initializationSettingsAndroid =
          AndroidInitializationSettings('@mipmap/ic_launcher');
      const InitializationSettings initSettings =
          InitializationSettings(android: initializationSettingsAndroid);
      final FlutterLocalNotificationsPlugin localNotifications =
          FlutterLocalNotificationsPlugin();

      // Fix: Use named parameters according to v20.x structure (parameter name is 'settings')
      await localNotifications.initialize(
        settings: initSettings,
        onDidReceiveNotificationResponse: (NotificationResponse details) {
          print('Notification tapped: ${details.payload}');
        },
      );

      FirebaseMessaging.onMessage.listen((RemoteMessage message) {
        RemoteNotification? notification = message.notification;
        AndroidNotification? android = message.notification?.android;

        if (notification != null && android != null) {
          localNotifications.show(
            id: notification.hashCode,
            title: notification.title,
            body: notification.body,
            notificationDetails: const NotificationDetails(
              android: AndroidNotificationDetails(
                'high_importance_channel',
                'High Importance Notifications',
                importance: Importance.max,
                priority: Priority.high,
              ),
            ),
          );
        }
      });

      // Handle when app is opened from notification
      FirebaseMessaging.onMessageOpenedApp.listen((RemoteMessage message) {
        print('A new onMessageOpenedApp event was published!');
      });
    } catch (e) {
      print('Error initializing notifications: $e');
    }
  }
}
