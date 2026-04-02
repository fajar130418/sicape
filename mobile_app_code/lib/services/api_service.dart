import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import 'package:url_launcher/url_launcher.dart' as url_launcher;

class ApiService {
  // Replace with your actual local IP address (e.g., 192.168.1.x) if testing on real device
  // Or 10.0.2.2 if testing on Android Emulator
  // Set this to your ngrok or production URL (e.g., 'https://your-id.ngrok-free.app/api')
  static String? productionUrl = 'https://sicape.dispursipseruyan.my.id/api';

  static String get siteUrl {
    if (productionUrl != null) {
      // Strip /api if it exists to get the base domain
      return productionUrl!.replaceAll('/api', '');
    }
    if (kIsWeb) {
      return 'http://localhost:8081';
    }
    return 'http://10.0.2.2:8081';
  }

  static String get baseUrl {
    if (productionUrl != null) {
      return productionUrl!;
    }
    if (kIsWeb) {
      return 'http://localhost:8081/api';
    }
    // Android Emulator 10.0.2.2
    return 'http://10.0.2.2:8081/api';
  }

  Future<String?> getToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString('token');
  }

  Future<Map<String, String>> _headers() async {
    final token = await getToken();
    return {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      if (token != null) 'Authorization': 'Bearer $token',
    };
  }

  Future<Map<String, dynamic>> login(String nip, String password) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/login'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({'nip': nip, 'password': password}),
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        final prefs = await SharedPreferences.getInstance();
        await prefs.setString('token', data['token']);
        await prefs.setString('user', jsonEncode(data['user']));
        return {'success': true, 'data': data};
      } else {
        final Map<String, dynamic> errorData = jsonDecode(response.body);
        return {
          'success': false,
          'message': (errorData['messages'] is Map
                  ? errorData['messages']['error']
                  : null) ??
              'Login failed: ${response.statusCode}'
        };
      }
    } catch (e) {
      return {'success': false, 'message': 'Connection error: $e'};
    }
  }

  Future<void> logout() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('token');
    await prefs.remove('user');
  }

  Future<Map<String, dynamic>> getDashboard() async {
    final response = await http.get(
      Uri.parse('$baseUrl/dashboard'),
      headers: await _headers(),
    );
    return jsonDecode(response.body);
  }

  Future<List<dynamic>> getLeaveHistory() async {
    final response = await http.get(
      Uri.parse('$baseUrl/leave/history'),
      headers: await _headers(),
    );
    if (response.statusCode == 200) {
      final json = jsonDecode(response.body);
      return json['data'];
    }
    return [];
  }

  Future<List<dynamic>> getLeaveTypes() async {
    final response = await http.get(
      Uri.parse('$baseUrl/leave/types'),
      headers: await _headers(),
    );
    if (response.statusCode == 200) {
      final json = jsonDecode(response.body);
      return json['data'];
    }
    return [];
  }

  Future<Map<String, dynamic>> uploadSignedForm(int id, String filePath) async {
    try {
      final token = await getToken();
      var request = http.MultipartRequest(
          'POST', Uri.parse('$baseUrl/leave/upload-signed-form/$id'));

      request.headers.addAll({
        'Accept': 'application/json',
        if (token != null) 'Authorization': 'Bearer $token',
      });

      request.files.add(
          await http.MultipartFile.fromPath('signed_form', filePath));

      final response = await request.send();
      final responseData = await response.stream.bytesToString();
      return jsonDecode(responseData);
    } catch (e) {
      return {'status': 500, 'message': 'Connection error: $e'};
    }
  }

  Future<List<dynamic>> getPendingSignedForms() async {
    final response = await http.get(
      Uri.parse('$baseUrl/leave/pending-uploads'),
      headers: await _headers(),
    );
    if (response.statusCode == 200) {
      final json = jsonDecode(response.body);
      return json['data'];
    }
    return [];
  }

  Future<Map<String, dynamic>> approveSignedForm(int id) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/leave/approve-signed-form/$id'),
        headers: await _headers(),
      );
      return jsonDecode(response.body);
    } catch (e) {
      return {'status': 500, 'message': 'Connection error: $e'};
    }
  }

  Future<Map<String, dynamic>> rejectSignedForm(int id, String note) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/leave/reject-signed-form/$id'),
        headers: await _headers(),
        body: jsonEncode({'note': note}),
      );
      return jsonDecode(response.body);
    } catch (e) {
      return {'status': 500, 'message': 'Connection error: $e'};
    }
  }

  Future<Map<String, dynamic>> bypassLeaveLock(int id) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/leave/bypass-lock/$id'),
        headers: await _headers(),
      );
      return jsonDecode(response.body);
    } catch (e) {
      return {'status': 500, 'message': 'Connection error: $e'};
    }
  }

  Future<Map<String, dynamic>> submitLeaveRequest(Map<String, dynamic> data,
      {String? attachmentPath}) async {
    try {
      final token = await getToken();
      var request =
          http.MultipartRequest('POST', Uri.parse('$baseUrl/leave/store'));

      // Headers
      request.headers.addAll({
        'Accept': 'application/json',
        if (token != null) 'Authorization': 'Bearer $token',
      });

      // Fields
      data.forEach((key, value) {
        request.fields[key] = value.toString();
      });

      // File
      if (attachmentPath != null) {
        request.files.add(
            await http.MultipartFile.fromPath('attachment', attachmentPath));
      }

      final response = await request.send();
      final responseData = await response.stream.bytesToString();
      return jsonDecode(responseData);
    } catch (e) {
      return {'status': 500, 'message': 'Connection error: $e'};
    }
  }

  Future<Map<String, dynamic>> getApprovals() async {
    final response = await http.get(
      Uri.parse('$baseUrl/approval'),
      headers: await _headers(),
    );
    return jsonDecode(response.body);
  }

  Future<Map<String, dynamic>> processApproval(
      int id, String action, String role, String note) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/approval/process/$id'),
        headers: await _headers(),
        body: jsonEncode({'action': action, 'role': role, 'note': note}),
      );

      final data = jsonDecode(response.body);
      if (response.statusCode == 200) {
        return {'success': true, 'status': 200, 'data': data};
      } else {
        return {
          'success': false,
          'status': response.statusCode,
          'message': (data['messages'] is Map ? data['messages']['error'] : data['message']) ??
              'Gagal memproses: ${response.statusCode}'
        };
      }
    } catch (e) {
      return {'success': false, 'status': 500, 'message': 'Connection error: $e'};
    }
  }

  Future<Map<String, dynamic>> getProfile() async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/profile'),
        headers: await _headers(),
      );
      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      }
      return {
        'status': response.statusCode,
        'message': 'Failed to load profile'
      };
    } catch (e) {
      return {'status': 500, 'message': 'Connection error: $e'};
    }
  }

  Future<Map<String, dynamic>> updateProfile(Map<String, dynamic> data) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/profile/update'),
        headers: await _headers(),
        body: jsonEncode(data),
      );
      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      }
      return {
        'status': response.statusCode,
        'message': 'Failed to update profile'
      };
    } catch (e) {
      return {'status': 500, 'message': 'Connection error: $e'};
    }
  }

  Future<Map<String, dynamic>> sendFcmToken(String fcmToken) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/users/fcm-token'),
        headers: await _headers(),
        body: jsonEncode({'fcm_token': fcmToken}),
      );
      return jsonDecode(response.body);
    } catch (e) {
      return {'status': 500, 'message': 'Connection error: $e'};
    }
  }

  Future<void> launchURL(String urlString) async {
    try {
      final Uri url = Uri.parse(urlString);
      if (!await url_launcher.launchUrl(url,
          mode: url_launcher.LaunchMode.externalApplication)) {
        throw Exception('Could not launch $url');
      }
    } catch (e) {
      debugPrint('Error launching URL: $e');
    }
  }
}
