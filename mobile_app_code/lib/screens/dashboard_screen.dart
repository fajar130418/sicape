import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'dart:convert';
import '../services/api_service.dart';
import 'login_screen.dart';
import 'leave_form_screen.dart';
import 'approval_screen.dart';
import 'profile_screen.dart';
import 'history_screen.dart';
import 'package:google_fonts/google_fonts.dart';

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
  int _selectedIndex = 0;

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

    final recentLeaves =
        _dashboardData?['recent_leaves'] as List<dynamic>? ?? [];

    bool isSupervisor = _user?['role'] == 'supervisor' ||
        _user?['role'] == 'admin' ||
        _user?['role'] == 'head';

    return Scaffold(
      backgroundColor: Colors.grey.shade100,
      bottomNavigationBar: Container(
        decoration: BoxDecoration(
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.1),
              blurRadius: 10,
              offset: const Offset(0, -2),
            ),
          ],
        ),
        child: BottomNavigationBar(
          currentIndex: _selectedIndex,
          onTap: (index) {
            setState(() {
              _selectedIndex = index;
            });
            // Handle navigation for non-home tabs if needed,
            // but for "WhatsApp style" we can just switch content or push screens.
            // Keeping it simple: Home is index 0. Others navigate.
            if (index == 1) {
              Navigator.push(
                      context,
                      MaterialPageRoute(
                          builder: (_) => const LeaveFormScreen()))
                  .then((_) => _loadData());
              setState(() => _selectedIndex = 0);
            } else if (index == 2) {
              Navigator.push(context,
                  MaterialPageRoute(builder: (_) => const HistoryScreen()));
              setState(() => _selectedIndex = 0);
            } else if (index == 3 && isSupervisor) {
              Navigator.push(context,
                  MaterialPageRoute(builder: (_) => const ApprovalScreen()));
              setState(() => _selectedIndex = 0);
            } else if (index == (isSupervisor ? 4 : 3)) {
              Navigator.push(context,
                      MaterialPageRoute(builder: (_) => const ProfileScreen()))
                  .then((_) => _loadData());
              setState(() => _selectedIndex = 0);
            }
          },
          type: BottomNavigationBarType.fixed,
          backgroundColor: Colors.white,
          selectedItemColor: Colors.indigo.shade600,
          unselectedItemColor: Colors.grey.shade400,
          showUnselectedLabels: true,
          selectedLabelStyle:
              GoogleFonts.outfit(fontWeight: FontWeight.bold, fontSize: 12),
          unselectedLabelStyle:
              GoogleFonts.outfit(fontWeight: FontWeight.medium, fontSize: 12),
          elevation: 0,
          items: [
            const BottomNavigationBarItem(
              icon: Icon(Icons.home_rounded),
              label: 'Beranda',
            ),
            const BottomNavigationBarItem(
              icon: Icon(Icons.add_circle_outline_rounded),
              label: 'Ajukan',
            ),
            const BottomNavigationBarItem(
              icon: Icon(Icons.history_rounded),
              label: 'Riwayat',
            ),
            if (isSupervisor)
              const BottomNavigationBarItem(
                icon: Icon(Icons.verified_user_rounded),
                label: 'Persetujuan',
              ),
            const BottomNavigationBarItem(
              icon: Icon(Icons.person_rounded),
              label: 'Profil',
            ),
          ],
        ),
      ),
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
                    if (_dashboardData?['kgb_info'] != null &&
                        (_dashboardData!['kgb_info']['kgb_status'] ==
                                'warning' ||
                            _dashboardData!['kgb_info']['kgb_status'] ==
                                'overdue'))
                      _buildKgbWarning(),
                    Text(
                      'Info Pegawai & Cuti',
                      style: GoogleFonts.outfit(
                        fontSize: 20,
                        fontWeight: FontWeight.bold,
                        color: Colors.indigo.shade900,
                      ),
                    ),
                    const SizedBox(height: 16),
                    _buildHorizontalInfoCards(),
                    const SizedBox(height: 32),
                    Text(
                      'Aktivitas Terakhir',
                      style: GoogleFonts.outfit(
                        fontSize: 20,
                        fontWeight: FontWeight.bold,
                        color: Colors.indigo.shade900,
                      ),
                    ),
                    const SizedBox(height: 16),
                    recentLeaves.isEmpty
                        ? _buildEmptyActivity()
                        : ListView.builder(
                            shrinkWrap: true,
                            physics: const NeverScrollableScrollPhysics(),
                            itemCount: recentLeaves.length,
                            itemBuilder: (context, index) {
                              final item = recentLeaves[index];
                              return _buildRecentActivityCard(item);
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

  Widget _buildHorizontalInfoCards() {
    final leaveBalance = _dashboardData?['leave_balance'];
    final seniority = _dashboardData?['seniority'];
    final kgbInfo = _dashboardData?['kgb_info'];

    final String masaKerja = seniority != null
        ? '${seniority['years']} Thn ${seniority['months']} Bln'
        : '0 Thn 0 Bln';

    final String kuotaCuti = '${leaveBalance?['initial']?['total'] ?? 0} Hari';
    final String cutiTerpakai = '${leaveBalance?['used'] ?? 0} Hari';
    final String sisaCuti = '${leaveBalance?['remaining']?['total'] ?? 0} Hari';
    final String kgbValue = kgbInfo != null
        ? (kgbInfo['kgb_days_left'] != null && kgbInfo['kgb_days_left'] > 0
            ? '${kgbInfo['kgb_days_left']} Hari'
            : 'Jatuh Tempo')
        : '-';

    return SingleChildScrollView(
      scrollDirection: Axis.horizontal,
      physics: const BouncingScrollPhysics(),
      child: Row(
        children: [
          _buildInfoCard(
            value: masaKerja,
            label: 'Masa Kerja',
            icon: Icons.work_history_rounded,
            color: Colors.blue.shade600,
          ),
          _buildInfoCard(
            value: kuotaCuti,
            label: 'Kuota Cuti Tahunan',
            icon: Icons.calendar_month_rounded,
            color: Colors.green.shade600,
          ),
          _buildInfoCard(
            value: cutiTerpakai,
            label: 'Cuti Terpakai',
            icon: Icons.calendar_today_rounded,
            color: Colors.red.shade600,
          ),
          _buildInfoCard(
            value: sisaCuti,
            label: 'Sisa Cuti Tahunan',
            subLabel:
                'N: ${leaveBalance?['remaining']?['n'] ?? 0} | N-1: ${leaveBalance?['remaining']?['n1'] ?? 0} | N-2: ${leaveBalance?['remaining']?['n2'] ?? 0}',
            icon: Icons.hourglass_bottom_rounded,
            color: Colors.orange.shade600,
          ),
          _buildInfoCard(
            value: kgbValue,
            label: 'Sisa Waktu KGB',
            subLabel:
                '(Kenaikan Gaji Berkala)\nTgl: ${kgbInfo?['kgb_next_date'] ?? "-"}',
            icon: Icons.access_time_rounded,
            color: Colors.purple.shade600,
          ),
        ],
      ),
    );
  }

  Widget _buildInfoCard({
    required String value,
    required String label,
    required IconData icon,
    required Color color,
    String? subLabel,
  }) {
    return Container(
      width: 200,
      margin: const EdgeInsets.only(right: 16, bottom: 8),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.04),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Expanded(
                child: Text(
                  value,
                  style: GoogleFonts.outfit(
                    fontSize: 22,
                    fontWeight: FontWeight.w900,
                    color: Colors.grey.shade900,
                  ),
                  overflow: TextOverflow.ellipsis,
                ),
              ),
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: color.withOpacity(0.1),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Icon(icon, color: color, size: 24),
              ),
            ],
          ),
          const SizedBox(height: 4),
          Text(
            label,
            style: GoogleFonts.outfit(
              fontSize: 13,
              color: Colors.grey.shade600,
              fontWeight: FontWeight.w500,
            ),
          ),
          if (subLabel != null) ...[
            const SizedBox(height: 8),
            Text(
              subLabel,
              style: GoogleFonts.outfit(
                fontSize: 11,
                color: color.withOpacity(0.8),
                fontWeight: FontWeight.w600,
              ),
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildRecentActivityCard(dynamic item) {
    return Card(
      elevation: 0,
      margin: const EdgeInsets.only(bottom: 12),
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(16),
        side: BorderSide(color: Colors.grey.shade200, width: 1),
      ),
      child: ListTile(
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
        leading: Container(
          padding: const EdgeInsets.all(10),
          decoration: BoxDecoration(
            color: Colors.indigo.shade50,
            borderRadius: BorderRadius.circular(12),
          ),
          child: Icon(Icons.date_range_rounded, color: Colors.indigo.shade600),
        ),
        title: Text(
          item['leave_type_name'] ?? 'Cuti',
          style: GoogleFonts.outfit(fontWeight: FontWeight.bold),
        ),
        subtitle: Text(
          '${item['start_date']} s/d ${item['end_date']}',
          style: GoogleFonts.outfit(color: Colors.grey.shade600, fontSize: 12),
        ),
        trailing: _buildStatusBadge(item['status']),
      ),
    );
  }

  Widget _buildKgbWarning() {
    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.orange.shade50,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: Colors.orange.shade200, width: 1),
      ),
      child: Row(
        children: [
          Icon(Icons.warning_amber_rounded,
              color: Colors.orange.shade800, size: 32),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Penting: Jatuh Tempo KGB',
                  style: GoogleFonts.outfit(
                    fontWeight: FontWeight.bold,
                    color: Colors.orange.shade900,
                  ),
                ),
                Text(
                  'Kenaikan Gaji Berkala (KGB) Anda hampir tercapai. Silakan segera lapor kepada Admin.',
                  style: GoogleFonts.outfit(
                      color: Colors.orange.shade800, fontSize: 12),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildEmptyActivity() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(40.0),
        child: Column(
          children: [
            Icon(Icons.inbox_rounded, size: 48, color: Colors.grey.shade300),
            const SizedBox(height: 12),
            Text(
              'Belum ada riwayat pengajuan',
              style: GoogleFonts.outfit(color: Colors.grey.shade500),
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
        bgColor = Colors.green.shade50;
        textColor = Colors.green.shade800;
        label = 'Disetujui';
        break;
      case 'rejected':
        bgColor = Colors.red.shade50;
        textColor = Colors.red.shade800;
        label = 'Ditolak';
        break;
      default:
        bgColor = Colors.orange.shade50;
        textColor = Colors.orange.shade800;
        label = 'Menunggu';
    }

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: bgColor,
        borderRadius: BorderRadius.circular(10),
      ),
      child: Text(
        label,
        style: GoogleFonts.outfit(
          color: textColor,
          fontSize: 11,
          fontWeight: FontWeight.bold,
        ),
      ),
    );
  }
}
