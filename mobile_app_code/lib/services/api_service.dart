import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

class ApiService {
  // Replace with your actual local IP address (e.g., 192.168.1.x) if testing on real device
  // Or 10.0.2.2 if testing on Android Emulator
  static String get baseUrl {
    if (kIsWeb) {
      return 'http://localhost/sicape/public/index.php/api';
    }
    // Android Emulator 10.0.2.2
    return 'http://10.0.2.2/sicape/public/index.php/api';
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
        return {
          'success': false,
          'message': jsonDecode(response.body)['messages']['error'] ??
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

  Future<Map<String, dynamic>> submitLeaveRequest(
      Map<String, dynamic> data) async {
    final response = await http.post(
      Uri.parse('$baseUrl/leave/store'),
      headers: await _headers(),
      body: jsonEncode(data),
    );
    return jsonDecode(response.body);
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
    final response = await http.post(
      Uri.parse('$baseUrl/approval/process/$id'),
      headers: await _headers(),
      body: jsonEncode({'action': action, 'role': role, 'note': note}),
    );
    return jsonDecode(response.body);
  }
}
