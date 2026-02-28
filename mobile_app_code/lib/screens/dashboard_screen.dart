import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'dart:convert';
import '../services/api_service.dart';
import 'login_screen.dart';
import 'leave_form_screen.dart';
import 'approval_screen.dart';
import 'profile_screen.dart';

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

    bool isSupervisor = _user?['role'] == 'supervisor' ||
        _user?['role'] == 'admin' ||
        _user?['role'] == 'head';

    return Scaffold(
      backgroundColor: Colors.grey.shade100,
      body: RefreshIndicator(
        onRefresh: _loadData,
        child: CustomScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          slivers: [
            SliverAppBar(
              expandedHeight: 220.0,
              floating: false,
              pinned: true,
              backgroundColor: Colors.indigo.shade600,
              actions: [
                IconButton(
                  onPressed: _logout,
                  icon: const Icon(Icons.logout_rounded, color: Colors.white),
                ),
              ],
              flexibleSpace: FlexibleSpaceBar(
                background: Stack(
                  fit: StackFit.expand,
                  children: [
                    Container(
                      decoration: BoxDecoration(
                        gradient: LinearGradient(
                          begin: Alignment.topLeft,
                          end: Alignment.bottomRight,
                          colors: [
                            Colors.indigo.shade800,
                            Colors.indigo.shade500,
                          ],
                        ),
                      ),
                    ),
                    Positioned(
                      top: -50,
                      right: -50,
                      child: CircleAvatar(
                        radius: 100,
                        backgroundColor: Colors.white.withOpacity(0.1),
                      ),
                    ),
                    Positioned(
                      bottom: -80,
                      left: -50,
                      child: CircleAvatar(
                        radius: 120,
                        backgroundColor: Colors.white.withOpacity(0.05),
                      ),
                    ),
                    Positioned(
                      bottom: 40,
                      left: 20,
                      right: 20,
                      child: GestureDetector(
                        onTap: () {
                          Navigator.push(
                            context,
                            MaterialPageRoute(
                                builder: (_) => const ProfileScreen()),
                          ).then((_) => _loadData());
                        },
                        child: Row(
                          children: [
                            Container(
                              decoration: BoxDecoration(
                                shape: BoxShape.circle,
                                border:
                                    Border.all(color: Colors.white, width: 3),
                                boxShadow: [
                                  BoxShadow(
                                    color: Colors.black26,
                                    blurRadius: 10,
                                    offset: const Offset(0, 4),
                                  ),
                                ],
                              ),
                              child: CircleAvatar(
                                radius: 35,
                                backgroundColor: Colors.indigo.shade100,
                                backgroundImage: _user?['photo'] != null
                                    ? NetworkImage(_user!['photo'])
                                    : null,
                                child: _user?['photo'] == null
                                    ? Icon(Icons.person_rounded,
                                        size: 40, color: Colors.indigo.shade500)
                                    : null,
                              ),
                            ),
                            const SizedBox(width: 16),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    _user?['name'] ?? 'User Name',
                                    style: const TextStyle(
                                      fontSize: 22,
                                      fontWeight: FontWeight.bold,
                                      color: Colors.white,
                                    ),
                                    overflow: TextOverflow.ellipsis,
                                  ),
                                  const SizedBox(height: 4),
                                  Container(
                                    padding: const EdgeInsets.symmetric(
                                        horizontal: 8, vertical: 2),
                                    decoration: BoxDecoration(
                                      color: Colors.white.withOpacity(0.2),
                                      borderRadius: BorderRadius.circular(12),
                                    ),
                                    child: Text(
                                      _user?['role']
                                              ?.toString()
                                              .toUpperCase() ??
                                          'STAFF',
                                      style: const TextStyle(
                                        color: Colors.white,
                                        fontSize: 12,
                                        fontWeight: FontWeight.w600,
                                        letterSpacing: 0.5,
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
            SliverToBoxAdapter(
              child: Padding(
                padding: const EdgeInsets.all(20.0),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Menu Utama',
                      style: TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.w800,
                        color: Colors.indigo.shade900,
                      ),
                    ),
                    const SizedBox(height: 16),
                    GridView.count(
                      shrinkWrap: true,
                      crossAxisCount: 2,
                      crossAxisSpacing: 16,
                      mainAxisSpacing: 16,
                      childAspectRatio: 1.1,
                      physics: const NeverScrollableScrollPhysics(),
                      children: [
                        _buildModernMenuCard(
                          icon: Icons.edit_document,
                          label: 'Ajukan Cuti',
                          color: Colors.blue.shade600,
                          bgColor: Colors.blue.shade50,
                          onTap: () => Navigator.push(
                                  context,
                                  MaterialPageRoute(
                                      builder: (_) => LeaveFormScreen()))
                              .then((_) => _loadData()),
                        ),
                        if (isSupervisor)
                          _buildModernMenuCard(
                            icon: Icons.verified_rounded,
                            label: 'Persetujuan',
                            color: Colors.orange.shade600,
                            bgColor: Colors.orange.shade50,
                            onTap: () => Navigator.push(
                                context,
                                MaterialPageRoute(
                                    builder: (_) => ApprovalScreen())),
                          ),
                        _buildModernMenuCard(
                          icon: Icons.history_rounded,
                          label: 'Riwayat Cuti',
                          color: Colors.teal.shade600,
                          bgColor: Colors.teal.shade50,
                          onTap: () {/* TODO: History Screen */},
                        ),
                        _buildModernMenuCard(
                          icon: Icons.settings_rounded,
                          label: 'Pengaturan',
                          color: Colors.blueGrey.shade600,
                          bgColor: Colors.blueGrey.shade50,
                          onTap: () {},
                        ),
                      ],
                    ),
                    const SizedBox(height: 32),
                    Text(
                      'Info Sisa Cuti',
                      style: TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.w800,
                        color: Colors.indigo.shade900,
                      ),
                    ),
                    const SizedBox(height: 16),
                    Row(
                      children: [
                        _buildModernStatCard('Total Cuti', '$totalBalance',
                            Colors.blue.shade600, Icons.pie_chart_rounded),
                        const SizedBox(width: 12),
                        _buildModernStatCard('Tahun N', '$n',
                            Colors.teal.shade500, Icons.filter_1_rounded),
                      ],
                    ),
                    const SizedBox(height: 12),
                    Row(
                      children: [
                        _buildModernStatCard('Tahun N-1', '$n1',
                            Colors.orange.shade500, Icons.filter_2_rounded),
                        const SizedBox(width: 12),
                        _buildModernStatCard('Tahun N-2', '$n2',
                            Colors.red.shade400, Icons.filter_3_rounded),
                      ],
                    ),
                    const SizedBox(height: 32),
                    Text(
                      'Aktivitas Terakhir',
                      style: TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.w800,
                        color: Colors.indigo.shade900,
                      ),
                    ),
                    const SizedBox(height: 16),
                    recentLeaves.isEmpty
                        ? Center(
                            child: Padding(
                              padding: const EdgeInsets.all(20.0),
                              child: Text('Belum ada riwayat pengajuan cuti',
                                  style:
                                      TextStyle(color: Colors.grey.shade600)),
                            ),
                          )
                        : ListView.builder(
                            shrinkWrap: true,
                            physics: const NeverScrollableScrollPhysics(),
                            itemCount: recentLeaves.length,
                            itemBuilder: (context, index) {
                              final item = recentLeaves[index];
                              return Card(
                                elevation: 0,
                                margin: const EdgeInsets.only(bottom: 12),
                                shape: RoundedRectangleBorder(
                                  borderRadius: BorderRadius.circular(16),
                                  side: BorderSide(
                                      color: Colors.grey.shade200, width: 1),
                                ),
                                child: Padding(
                                  padding: const EdgeInsets.symmetric(
                                      vertical: 8.0, horizontal: 4.0),
                                  child: ListTile(
                                    leading: Container(
                                      padding: const EdgeInsets.all(10),
                                      decoration: BoxDecoration(
                                        color: Colors.indigo.shade50,
                                        borderRadius: BorderRadius.circular(12),
                                      ),
                                      child: Icon(Icons.date_range_rounded,
                                          color: Colors.indigo.shade600),
                                    ),
                                    title: Text(
                                      item['leave_type_name'] ?? 'Cuti',
                                      style: const TextStyle(
                                          fontWeight: FontWeight.w700),
                                    ),
                                    subtitle: Padding(
                                      padding: const EdgeInsets.only(top: 4.0),
                                      child: Text(
                                        '${item['start_date']} s/d ${item['end_date']}',
                                        style: TextStyle(
                                            color: Colors.grey.shade600,
                                            fontSize: 12),
                                      ),
                                    ),
                                    trailing: _buildStatusBadge(item['status']),
                                  ),
                                ),
                              );
                            },
                          ),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildModernMenuCard({
    required IconData icon,
    required String label,
    required Color color,
    required Color bgColor,
    required VoidCallback onTap,
  }) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(20),
      child: Container(
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(20),
          boxShadow: [
            BoxShadow(
              color: Colors.grey.withOpacity(0.1),
              spreadRadius: 2,
              blurRadius: 8,
              offset: const Offset(0, 4),
            ),
          ],
        ),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: bgColor,
                shape: BoxShape.circle,
              ),
              child: Icon(icon, size: 32, color: color),
            ),
            const SizedBox(height: 12),
            Text(
              label,
              style: TextStyle(
                fontWeight: FontWeight.bold,
                color: Colors.grey.shade800,
                fontSize: 14,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildModernStatCard(
      String label, String value, Color color, IconData icon) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: color,
          borderRadius: BorderRadius.circular(16),
          boxShadow: [
            BoxShadow(
              color: color.withOpacity(0.3),
              blurRadius: 8,
              offset: const Offset(0, 4),
            ),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Icon(icon, color: Colors.white.withOpacity(0.8), size: 24),
                Text(
                  value,
                  style: const TextStyle(
                    fontSize: 24,
                    fontWeight: FontWeight.bold,
                    color: Colors.white,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 8),
            Text(
              label,
              style: TextStyle(
                fontSize: 12,
                fontWeight: FontWeight.w500,
                color: Colors.white.withOpacity(0.9),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildStatusBadge(String status) {
    Color bgColor;
    Color textColor;
    String label;

    switch (status.toLowerCase()) {
      case 'approved':
        bgColor = Colors.green.shade100;
        textColor = Colors.green.shade800;
        label = 'Disetujui';
        break;
      case 'rejected':
        bgColor = Colors.red.shade100;
        textColor = Colors.red.shade800;
        label = 'Ditolak';
        break;
      default:
        bgColor = Colors.orange.shade100;
        textColor = Colors.orange.shade800;
        label = 'Menunggu';
    }

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
      decoration: BoxDecoration(
        color: bgColor,
        borderRadius: BorderRadius.circular(20),
      ),
      child: Text(
        label,
        style: TextStyle(
          color: textColor,
          fontSize: 12,
          fontWeight: FontWeight.bold,
        ),
      ),
    );
  }
}
