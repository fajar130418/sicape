import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'dart:convert';
import '../services/api_service.dart';
import 'login_screen.dart';
import 'leave_form_screen.dart';
import 'approval_screen.dart';

class DashboardScreen extends StatefulWidget {
  const DashboardScreen({super.key});

  @override
  _DashboardScreenState createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  final ApiService _apiService = ApiService();
  Map<String, dynamic>? _dashboardData;
  Map<String, dynamic>? _user;
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    final prefs = await SharedPreferences.getInstance();
    final userStr = prefs.getString('user');
    if (userStr != null) {
      setState(() {
        _user = jsonDecode(userStr);
      });
    }

    try {
      final data = await _apiService.getDashboard();
      if (data['status'] == 200) {
        setState(() {
          _dashboardData = data['data'];
          _isLoading = false;
        });
      }
    } catch (e) {
      // Handle error (e.g., token expired)
      print(e);
      setState(() => _isLoading = false);
    }
  }

  void _logout() async {
    await _apiService.logout();
    Navigator.pushReplacement(
      context,
      MaterialPageRoute(builder: (context) => LoginScreen()),
    );
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return const Scaffold(body: Center(child: CircularProgressIndicator()));
    }

    final leaveBalance = _dashboardData?['leave_balance'];
    final n = leaveBalance?['n'] ?? 0;
    final n1 = leaveBalance?['n1'] ?? 0;
    final n2 = leaveBalance?['n2'] ?? 0;
    final totalBalance = n + n1 + n2;
    final recentLeaves =
        _dashboardData?['recent_leaves'] as List<dynamic>? ?? [];

    // Check if Supervisor (Role 3 usually, or check is_supervisor if avail)
    // For simplicity, assuming validation handles access, we just show button if user has role
    bool isSupervisor = _user?['role'] == 'supervisor' ||
        _user?['role'] == 'admin' ||
        _user?['role'] == 'head';

    return Scaffold(
      appBar: AppBar(
        title: const Text('Dashboard'),
        actions: [
          IconButton(onPressed: _logout, icon: const Icon(Icons.logout)),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: _loadData,
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(16),
          physics: const AlwaysScrollableScrollPhysics(),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // User Card
              Card(
                elevation: 4,
                shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12)),
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Row(
                    children: [
                      CircleAvatar(
                        radius: 30,
                        backgroundColor: Colors.blue.shade100,
                        backgroundImage: _user?['photo'] != null
                            ? NetworkImage(_user!['photo'])
                            : null,
                        child: _user?['photo'] == null
                            ? const Icon(Icons.person, size: 30)
                            : null,
                      ),
                      const SizedBox(width: 16),
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(_user?['name'] ?? 'User',
                              style: const TextStyle(
                                  fontSize: 18, fontWeight: FontWeight.bold)),
                          Text('NIP: ${_user?['nip'] ?? '-'}',
                              style: TextStyle(color: Colors.grey[600])),
                          Text(
                              _user?['role']?.toString().toUpperCase() ??
                                  'STAFF',
                              style: const TextStyle(
                                  color: Colors.blue,
                                  fontWeight: FontWeight.bold)),
                        ],
                      ),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 20),

              // Menu Grid
              GridView.count(
                shrinkWrap: true,
                crossAxisCount: 2,
                crossAxisSpacing: 10,
                mainAxisSpacing: 10,
                physics: const NeverScrollableScrollPhysics(),
                children: [
                  _buildMenuCard(
                    icon: Icons.add_circle,
                    label: 'Ajukan Cuti',
                    color: Colors.blue,
                    onTap: () => Navigator.push(
                            context,
                            MaterialPageRoute(
                                builder: (_) => LeaveFormScreen()))
                        .then((_) => _loadData()),
                  ),
                  if (isSupervisor)
                    _buildMenuCard(
                      icon: Icons.approval,
                      label: 'Persetujuan',
                      color: Colors.orange,
                      onTap: () => Navigator.push(context,
                          MaterialPageRoute(builder: (_) => ApprovalScreen())),
                    ),
                  _buildMenuCard(
                    icon: Icons.history,
                    label: 'Riwayat Cuti',
                    color: Colors.green,
                    onTap: () {/* TODO: History Screen */},
                  ),
                  _buildMenuCard(
                    icon: Icons.settings,
                    label: 'Pengaturan',
                    color: Colors.grey,
                    onTap: () {},
                  ),
                ],
              ),

              const SizedBox(height: 20),

              // Leave Balance
              const Text('Sisa Cuti Tahunan',
                  style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
              const SizedBox(height: 10),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  _buildStatCard('Total', '$totalBalance', Colors.blue),
                  _buildStatCard('Tahun N', '$n', Colors.green),
                  _buildStatCard('Tahun N-1', '$n1', Colors.orange),
                  _buildStatCard('Tahun N-2', '$n2', Colors.red),
                ],
              ),

              const SizedBox(height: 20),

              // Recent Activity
              const Text('Riwayat Terakhir',
                  style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
              const SizedBox(height: 10),
              ListView.builder(
                shrinkWrap: true,
                physics: const NeverScrollableScrollPhysics(),
                itemCount: recentLeaves.length,
                itemBuilder: (context, index) {
                  final item = recentLeaves[index];
                  return Card(
                    margin: const EdgeInsets.only(bottom: 8),
                    child: ListTile(
                      leading: const Icon(Icons.date_range, color: Colors.blue),
                      title: Text(item['leave_type_name']),
                      subtitle: Text(
                          '${item['start_date']} slessai ${item['end_date']}'),
                      trailing: _buildStatusChip(item['status']),
                    ),
                  );
                },
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildMenuCard(
      {required IconData icon,
      required String label,
      required Color color,
      required VoidCallback onTap}) {
    return InkWell(
      onTap: onTap,
      child: Card(
        color: color.withOpacity(0.1),
        shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(12),
            side: BorderSide(color: color.withOpacity(0.3))),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(icon, size: 40, color: color),
            const SizedBox(height: 8),
            Text(label,
                style: TextStyle(fontWeight: FontWeight.bold, color: color)),
          ],
        ),
      ),
    );
  }

  Widget _buildStatCard(String label, String value, Color color) {
    return Expanded(
      child: Card(
        color: color,
        child: Padding(
          padding: const EdgeInsets.symmetric(vertical: 12),
          child: Column(
            children: [
              Text(value,
                  style: const TextStyle(
                      fontSize: 20,
                      fontWeight: FontWeight.bold,
                      color: Colors.white)),
              Text(label,
                  style: const TextStyle(fontSize: 12, color: Colors.white70)),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildStatusChip(String status) {
    Color color;
    switch (status) {
      case 'approved':
        color = Colors.green;
        break;
      case 'rejected':
        color = Colors.red;
        break;
      default:
        color = Colors.orange;
    }
    return Chip(
      label: Text(status.toUpperCase(),
          style: const TextStyle(fontSize: 10, color: Colors.white)),
      backgroundColor: color,
      padding: EdgeInsets.zero,
      visualDensity: VisualDensity.compact,
    );
  }
}
