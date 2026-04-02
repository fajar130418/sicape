import 'package:flutter/material.dart';
import '../services/api_service.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'dart:convert';

class ApprovalScreen extends StatefulWidget {
  const ApprovalScreen({super.key});

  @override
  _ApprovalScreenState createState() => _ApprovalScreenState();
}

class _ApprovalScreenState extends State<ApprovalScreen>
    with TickerProviderStateMixin {
  final ApiService _apiService = ApiService();
  List<dynamic> _supervisorApprovals = [];
  List<dynamic> _headApprovals = [];
  bool _isLoading = true;
  bool _isAdmin = false;
  late TabController _tabController;

  // Modern color palette
  static const Color _primary = Color(0xFF4F46E5);
  static const Color _primaryLight = Color(0xFF818CF8);
  static const Color _secondary = Color(0xFF06B6D4);
  static const Color _bgColor = Color(0xFFF0F4FF);
  static const Color _cardBg = Colors.white;
  static const Color _success = Color(0xFF10B981);
  static const Color _danger = Color(0xFFEF4444);
  static const Color _textDark = Color(0xFF1E1B4B);
  static const Color _textMid = Color(0xFF6B7280);

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this); // Initial
    _checkRole();
    _loadData();
  }

  void _checkRole() async {
    final prefs = await SharedPreferences.getInstance();
    final userStr = prefs.getString('user');
    if (userStr != null) {
      final user = jsonDecode(userStr);
      if (mounted) {
        setState(() {
          _isAdmin = user['role'] == 'admin';
          _tabController = TabController(length: _isAdmin ? 3 : 2, vsync: this);
        });
      }
    }
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  void _loadData() async {
    setState(() => _isLoading = true);
    try {
      final data = await _apiService.getApprovals();
      if (!mounted) return;
      if (data['status'] == 200) {
        setState(() {
          _supervisorApprovals = data['data']?['supervisor_approvals'] ?? [];
          _headApprovals = data['data']?['head_approvals'] ?? [];
          _isLoading = false;
        });
      } else {
        setState(() => _isLoading = false);
        _showError(
            'Gagal memuat data: ${data['message'] ?? 'Status bukan 200'}');
      }
    } catch (e) {
      if (mounted) {
        setState(() => _isLoading = false);
        _showError('Terjadi kesalahan koneksi');
      }
    }
  }

  void _showError(String msg) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Row(
          children: [
            const Icon(Icons.error_outline, color: Colors.white, size: 18),
            const SizedBox(width: 8),
            Expanded(child: Text(msg)),
          ],
        ),
        backgroundColor: _danger,
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
        margin: const EdgeInsets.all(16),
      ),
    );
  }

  void _showSuccess(String msg) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Row(
          children: [
            const Icon(Icons.check_circle_outline,
                color: Colors.white, size: 18),
            const SizedBox(width: 8),
            Text(msg),
          ],
        ),
        backgroundColor: _success,
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
        margin: const EdgeInsets.all(16),
      ),
    );
  }

  void _process(int id, String action, String role) async {
    final noteController = TextEditingController();
    final bool isApprove = action == 'approved';

    await showDialog(
      context: context,
      builder: (context) => Dialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
        child: Container(
          padding: const EdgeInsets.all(24),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(24),
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Container(
                width: 64,
                height: 64,
                decoration: BoxDecoration(
                  color: isApprove
                      ? _success.withOpacity(0.1)
                      : _danger.withOpacity(0.1),
                  shape: BoxShape.circle,
                ),
                child: Icon(
                  isApprove
                      ? Icons.check_circle_outline_rounded
                      : Icons.cancel_outlined,
                  color: isApprove ? _success : _danger,
                  size: 32,
                ),
              ),
              const SizedBox(height: 16),
              Text(
                isApprove ? 'Setujui Pengajuan?' : 'Tolak Pengajuan?',
                style: const TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.bold,
                  color: _textDark,
                ),
              ),
              const SizedBox(height: 8),
              Text(
                isApprove
                    ? 'Pengajuan ini akan disetujui dan pihak terkait akan diberitahu.'
                    : 'Pengajuan ini akan ditolak. Tambahkan catatan jika perlu.',
                textAlign: TextAlign.center,
                style: const TextStyle(color: _textMid, fontSize: 13),
              ),
              const SizedBox(height: 20),
              TextField(
                controller: noteController,
                decoration: InputDecoration(
                  labelText: 'Catatan (Opsional)',
                  labelStyle: const TextStyle(color: _textMid),
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                    borderSide: const BorderSide(color: Color(0xFFE5E7EB)),
                  ),
                  focusedBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                    borderSide: const BorderSide(color: _primary, width: 2),
                  ),
                  filled: true,
                  fillColor: _bgColor,
                ),
                maxLines: 2,
              ),
              const SizedBox(height: 20),
              Row(
                children: [
                  Expanded(
                    child: OutlinedButton(
                      onPressed: () => Navigator.pop(context),
                      style: OutlinedButton.styleFrom(
                        foregroundColor: _textMid,
                        side: const BorderSide(color: Color(0xFFE5E7EB)),
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                      ),
                      child: const Text('Batal'),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: ElevatedButton(
                      onPressed: () {
                        Navigator.pop(context);
                        _submitProcess(id, action, role, noteController.text);
                      },
                      style: ElevatedButton.styleFrom(
                        backgroundColor: isApprove ? _success : _danger,
                        foregroundColor: Colors.white,
                        elevation: 0,
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                      ),
                      child: Text(isApprove ? 'Setujui' : 'Tolak'),
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }

  void _submitProcess(int id, String action, String role, String note) async {
    setState(() => _isLoading = true);
    try {
      final result = await _apiService.processApproval(id, action, role, note);
      if (result['status'] == 200) {
        _showSuccess('Berhasil diproses');
        _loadData();
      } else {
        _showError('Gagal: ${result['message'] ?? result['error'] ?? 'Unknown error'}');
        setState(() => _isLoading = false);
      }
    } catch (e) {
      _showError('Error: $e');
      setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: _bgColor,
      body: NestedScrollView(
        headerSliverBuilder: (context, innerBoxIsScrolled) => [
          SliverAppBar(
            expandedHeight: 160,
            pinned: true,
            elevation: 0,
            backgroundColor: _primary,
            leading: GestureDetector(
              onTap: () => Navigator.pop(context),
              child: Container(
                margin: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: Colors.white.withOpacity(0.2),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: const Icon(Icons.arrow_back_ios_new_rounded,
                    color: Colors.white, size: 18),
              ),
            ),
            flexibleSpace: FlexibleSpaceBar(
              background: Container(
                decoration: const BoxDecoration(
                  gradient: LinearGradient(
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                    colors: [Color(0xFF4F46E5), Color(0xFF7C3AED)],
                  ),
                ),
                child: SafeArea(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      const SizedBox(height: 8),
                      const Icon(Icons.approval_rounded,
                          color: Colors.white70, size: 32),
                      const SizedBox(height: 8),
                      const Text(
                        'Persetujuan Cuti',
                        style: TextStyle(
                          color: Colors.white,
                          fontSize: 20,
                          fontWeight: FontWeight.bold,
                          letterSpacing: 0.5,
                        ),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        'Kelola permintaan cuti',
                        style: TextStyle(
                          color: Colors.white.withOpacity(0.75),
                          fontSize: 12,
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ),
            bottom: PreferredSize(
              preferredSize: const Size.fromHeight(52),
              child: Container(
                color: _primary,
                child: Container(
                  margin: const EdgeInsets.fromLTRB(16, 0, 16, 12),
                  height: 44,
                  decoration: BoxDecoration(
                    color: Colors.white.withOpacity(0.15),
                    borderRadius: BorderRadius.circular(22),
                  ),
                  child: TabBar(
                    controller: _tabController,
                    dividerColor: Colors.transparent,
                    indicatorSize: TabBarIndicatorSize.tab,
                    indicator: BoxDecoration(
                      borderRadius: BorderRadius.circular(22),
                      color: Colors.white,
                      boxShadow: [
                        BoxShadow(
                          color: _primary.withOpacity(0.3),
                          blurRadius: 8,
                          offset: const Offset(0, 2),
                        ),
                      ],
                    ),
                    labelColor: _primary,
                    unselectedLabelColor: Colors.white,
                    labelStyle: const TextStyle(
                      fontWeight: FontWeight.bold,
                      fontSize: 13,
                    ),
                    unselectedLabelStyle: const TextStyle(
                      fontWeight: FontWeight.w500,
                      fontSize: 13,
                    ),
                    tabs: [
                      Tab(
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            const Icon(Icons.supervisor_account_rounded,
                                size: 16),
                            const SizedBox(width: 6),
                            const Text('Atasan'),
                            if (_supervisorApprovals.isNotEmpty) ...[
                              const SizedBox(width: 6),
                              _buildBadge(_supervisorApprovals.length),
                            ],
                          ],
                        ),
                      ),
                      Tab(
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            const Icon(Icons.account_balance_rounded, size: 16),
                            const SizedBox(width: 6),
                            const Text('Kepala Dinas'),
                            if (_headApprovals.isNotEmpty) ...[
                              const SizedBox(width: 6),
                              _buildBadge(_headApprovals.length),
                            ],
                          ],
                        ),
                      ),
                      if (_isAdmin)
                        const Tab(
                          child: Row(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Icon(Icons.verified_user_rounded, size: 16),
                              SizedBox(width: 6),
                              Text('Form Cuti'),
                            ],
                          ),
                        ),
                    ],
                  ),
                ),
              ),
            ),
          ),
        ],
        body: _isLoading
            ? const Center(
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    CircularProgressIndicator(
                      valueColor: AlwaysStoppedAnimation<Color>(_primary),
                      strokeWidth: 3,
                    ),
                    SizedBox(height: 16),
                    Text(
                      'Memuat data...',
                      style: TextStyle(color: _textMid, fontSize: 14),
                    ),
                  ],
                ),
              )
            : TabBarView(
                controller: _tabController,
                children: [
                  _buildList(
                    _supervisorApprovals,
                    'supervisor',
                    icon: Icons.supervisor_account_rounded,
                    label: 'Atasan',
                  ),
                  _buildList(
                    _headApprovals,
                    'head',
                    icon: Icons.account_balance_rounded,
                    label: 'Kepala Dinas',
                  ),
                  if (_isAdmin) const AdminApprovalView(),
                ],
              ),
      ),
    );
  }

  Widget _buildBadge(int count) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 1),
      decoration: BoxDecoration(
        color: _danger,
        borderRadius: BorderRadius.circular(10),
      ),
      child: Text(
        '$count',
        style: const TextStyle(
          color: Colors.white,
          fontSize: 10,
          fontWeight: FontWeight.bold,
        ),
      ),
    );
  }

  Widget _buildList(
    List<dynamic> list,
    String role, {
    required IconData icon,
    required String label,
  }) {
    if (list.isEmpty) {
      return RefreshIndicator(
        onRefresh: () async => _loadData(),
        color: _primary,
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          child: SizedBox(
            height: MediaQuery.of(context).size.height * 0.6,
            child: Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Container(
                    width: 100,
                    height: 100,
                    decoration: BoxDecoration(
                      color: _primary.withOpacity(0.07),
                      shape: BoxShape.circle,
                    ),
                    child:
                        Icon(icon, size: 48, color: _primary.withOpacity(0.4)),
                  ),
                  const SizedBox(height: 20),
                  const Text(
                    'Tidak ada permintaan',
                    style: TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                      color: _textDark,
                    ),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    'Belum ada pengajuan yang perlu\ndisetujui sebagai $label.',
                    textAlign: TextAlign.center,
                    style: const TextStyle(
                      color: _textMid,
                      fontSize: 14,
                      height: 1.5,
                    ),
                  ),
                  const SizedBox(height: 24),
                  OutlinedButton.icon(
                    onPressed: _loadData,
                    icon: const Icon(Icons.refresh_rounded, size: 18),
                    label: const Text('Refresh'),
                    style: OutlinedButton.styleFrom(
                      foregroundColor: _primary,
                      side: const BorderSide(color: _primary),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),
        ),
      );
    }

    return RefreshIndicator(
      onRefresh: () async => _loadData(),
      color: _primary,
      child: ListView.builder(
        padding: const EdgeInsets.fromLTRB(16, 16, 16, 100),
        itemCount: list.length,
        itemBuilder: (context, index) {
          final item = list[index];
          return _buildApprovalCard(item, role, index);
        },
      ),
    );
  }

  Widget _buildApprovalCard(dynamic item, String role, int index) {
    // Leave type color mapping
    final leaveTypeName = (item['leave_type_name'] ?? '').toString();
    final leaveColor = _getLeaveTypeColor(leaveTypeName);

    return TweenAnimationBuilder<double>(
      tween: Tween(begin: 0.0, end: 1.0),
      duration: Duration(milliseconds: 300 + (index * 80)),
      curve: Curves.easeOutCubic,
      builder: (context, value, child) => Transform.translate(
        offset: Offset(0, 20 * (1 - value)),
        child: Opacity(opacity: value, child: child),
      ),
      child: Container(
        margin: const EdgeInsets.only(bottom: 16),
        decoration: BoxDecoration(
          color: _cardBg,
          borderRadius: BorderRadius.circular(20),
          boxShadow: [
            BoxShadow(
              color: _primary.withOpacity(0.08),
              blurRadius: 20,
              offset: const Offset(0, 4),
            ),
          ],
        ),
        child: Column(
          children: [
            // Header strip
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 14),
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  colors: [
                    leaveColor.withOpacity(0.08),
                    leaveColor.withOpacity(0.02),
                  ],
                ),
                borderRadius:
                    const BorderRadius.vertical(top: Radius.circular(20)),
                border: Border(
                  left: BorderSide(color: leaveColor, width: 4),
                ),
              ),
              child: Row(
                children: [
                  // Avatar
                  Container(
                    width: 46,
                    height: 46,
                    decoration: BoxDecoration(
                      gradient: LinearGradient(
                        begin: Alignment.topLeft,
                        end: Alignment.bottomRight,
                        colors: [
                          leaveColor.withOpacity(0.8),
                          leaveColor,
                        ],
                      ),
                      shape: BoxShape.circle,
                    ),
                    child: Center(
                      child: Text(
                        (item['user_name'] ?? '?')[0].toUpperCase(),
                        style: const TextStyle(
                          color: Colors.white,
                          fontWeight: FontWeight.bold,
                          fontSize: 18,
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          item['user_name'] ?? 'Pengguna',
                          style: const TextStyle(
                            fontWeight: FontWeight.bold,
                            fontSize: 15,
                            color: _textDark,
                          ),
                        ),
                        const SizedBox(height: 2),
                        Text(
                          item['nip'] ?? '',
                          style: const TextStyle(
                            color: _textMid,
                            fontSize: 12,
                          ),
                        ),
                      ],
                    ),
                  ),
                  // Duration badge
                  Container(
                    padding:
                        const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                    decoration: BoxDecoration(
                      color: leaveColor.withOpacity(0.12),
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(Icons.calendar_today_rounded,
                            size: 12, color: leaveColor),
                        const SizedBox(width: 4),
                        Text(
                          '${item['duration']} Hari',
                          style: TextStyle(
                            color: leaveColor,
                            fontWeight: FontWeight.bold,
                            fontSize: 12,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
            // Body
            Padding(
              padding: const EdgeInsets.all(20),
              child: Column(
                children: [
                  // Leave type chip
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(
                            horizontal: 12, vertical: 6),
                        decoration: BoxDecoration(
                          color: leaveColor.withOpacity(0.1),
                          borderRadius: BorderRadius.circular(20),
                          border:
                              Border.all(color: leaveColor.withOpacity(0.3)),
                        ),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Icon(Icons.label_rounded,
                                size: 13, color: leaveColor),
                            const SizedBox(width: 5),
                            Text(
                              leaveTypeName,
                              style: TextStyle(
                                color: leaveColor,
                                fontWeight: FontWeight.w600,
                                fontSize: 12,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 14),
                  // Info rows
                  _buildInfoRow(
                    Icons.date_range_rounded,
                    'Tanggal',
                    '${item['start_date']} s/d ${item['end_date']}',
                  ),
                  const SizedBox(height: 10),
                  _buildInfoRow(
                    Icons.notes_rounded,
                    'Alasan',
                    item['reason'] ?? 'Tanpa alasan',
                  ),
                  const SizedBox(height: 18),
                  // Action buttons
                  Row(
                    children: [
                      Expanded(
                        child: _buildActionButton(
                          label: 'Tolak',
                          icon: Icons.close_rounded,
                          color: _danger,
                          onTap: () => _process(
                              int.parse(item['id'].toString()), 'rejected', role),
                          filled: false,
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: _buildActionButton(
                          label: 'Setujui',
                          icon: Icons.check_rounded,
                          color: _success,
                          onTap: () => _process(
                              int.parse(item['id'].toString()),
                              'approved',
                              role),
                          filled: true,
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildInfoRow(IconData icon, String label, String value) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Container(
          width: 32,
          height: 32,
          decoration: BoxDecoration(
            color: _bgColor,
            borderRadius: BorderRadius.circular(8),
          ),
          child: Icon(icon, size: 16, color: _textMid),
        ),
        const SizedBox(width: 10),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                label,
                style: const TextStyle(
                  color: _textMid,
                  fontSize: 11,
                  fontWeight: FontWeight.w500,
                ),
              ),
              const SizedBox(height: 2),
              Text(
                value,
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
                style: const TextStyle(
                  color: _textDark,
                  fontSize: 13,
                  fontWeight: FontWeight.w500,
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }

  Widget _buildActionButton({
    required String label,
    required IconData icon,
    required Color color,
    required VoidCallback onTap,
    required bool filled,
  }) {
    return GestureDetector(
      onTap: onTap,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        padding: const EdgeInsets.symmetric(vertical: 13),
        decoration: BoxDecoration(
          color: filled ? color : Colors.transparent,
          borderRadius: BorderRadius.circular(14),
          border: filled ? null : Border.all(color: color.withOpacity(0.5)),
          boxShadow: filled
              ? [
                  BoxShadow(
                    color: color.withOpacity(0.3),
                    blurRadius: 8,
                    offset: const Offset(0, 3),
                  )
                ]
              : null,
        ),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(icon, size: 18, color: filled ? Colors.white : color),
            const SizedBox(width: 6),
            Text(
              label,
              style: TextStyle(
                color: filled ? Colors.white : color,
                fontWeight: FontWeight.bold,
                fontSize: 14,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Color _getLeaveTypeColor(String name) {
    if (name.contains('Tahunan')) return Colors.blue;
    if (name.contains('Sakit')) return Colors.red;
    if (name.contains('Besar')) return Colors.purple;
    if (name.contains('Melahirkan')) return Colors.pink;
    return _primary;
  }
}

class AdminApprovalView extends StatefulWidget {
  const AdminApprovalView({super.key});

  @override
  _AdminApprovalViewState createState() => _AdminApprovalViewState();
}

class _AdminApprovalViewState extends State<AdminApprovalView> {
  final ApiService _apiService = ApiService();
  List<dynamic> _pendingForms = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    if (!mounted) return;
    setState(() => _isLoading = true);
    try {
      final data = await _apiService.getPendingSignedForms();
      if (!mounted) return;
      setState(() {
        _pendingForms = data;
        _isLoading = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) return const Center(child: CircularProgressIndicator());
    if (_pendingForms.isEmpty) {
      return RefreshIndicator(
        onRefresh: _loadData,
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          child: SizedBox(
            height: 400,
            child: Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(Icons.assignment_turned_in_outlined, size: 64, color: Colors.grey.shade300),
                  const SizedBox(height: 16),
                  const Text('Tidak ada form menunggu verifikasi', style: TextStyle(color: Colors.grey)),
                ],
              ),
            ),
          ),
        ),
      );
    }

    return RefreshIndicator(
      onRefresh: _loadData,
      child: ListView.builder(
        padding: const EdgeInsets.all(16),
        itemCount: _pendingForms.length,
        itemBuilder: (context, index) {
          final item = _pendingForms[index];
          return Card(
            elevation: 0,
            margin: const EdgeInsets.only(bottom: 12),
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(16),
              side: BorderSide(color: Colors.grey.shade200),
            ),
            child: ExpansionTile(
              title: Text(item['user_name'], style: const TextStyle(fontWeight: FontWeight.bold)),
              subtitle: Text('${item['type_name']} | ${item['start_date']}'),
              leading: CircleAvatar(
                backgroundColor: Colors.indigo.shade50,
                child: Text(item['user_name'][0].toUpperCase(), style: const TextStyle(color: Colors.indigo)),
              ),
              children: [
                Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    children: [
                      Row(
                        children: [
                          Expanded(
                            child: OutlinedButton.icon(
                              onPressed: () => _openPreview(item['signed_form']),
                              icon: const Icon(Icons.picture_as_pdf),
                              label: const Text('Lihat Form'),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 12),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                        children: [
                          Expanded(
                            child: ElevatedButton.icon(
                              onPressed: () => _process(int.parse(item['id'].toString()), 'approve'),
                              icon: const Icon(Icons.check),
                              label: const Text('Setujui'),
                              style: ElevatedButton.styleFrom(backgroundColor: Colors.green, foregroundColor: Colors.white),
                            ),
                          ),
                          const SizedBox(width: 8),
                          Expanded(
                            child: ElevatedButton.icon(
                              onPressed: () => _showRejectDialog(int.parse(item['id'].toString())),
                              icon: const Icon(Icons.close),
                              label: const Text('Tolak'),
                              style: ElevatedButton.styleFrom(backgroundColor: Colors.red, foregroundColor: Colors.white),
                            ),
                          ),
                        ],
                      ),
                      TextButton(
                        onPressed: () => _process(int.parse(item['id'].toString()), 'bypass'),
                        child: const Text('Bypass Kunci (Buka Paksa)', style: TextStyle(color: Colors.grey, fontSize: 12)),
                      ),
                    ],
                  ),
                )
              ],
            ),
          );
        },
      ),
    );
  }

  void _openPreview(String? path) async {
    if (path == null) return;
    final url = Uri.parse('${ApiService.siteUrl}/uploads/$path');
    _apiService.launchURL(url.toString());
  }

  void _showRejectDialog(int id) {
    final controller = TextEditingController();
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Tolak Form'),
        content: TextField(
          controller: controller,
          decoration: const InputDecoration(hintText: 'Alasan penolakan'),
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context), child: const Text('Batal')),
          ElevatedButton(
            onPressed: () {
              Navigator.pop(context);
              _process(id, 'reject', note: controller.text);
            },
            child: const Text('Tolak'),
          ),
        ],
      ),
    );
  }

  void _process(int id, String action, {String? note}) async {
    setState(() => _isLoading = true);
    Map<String, dynamic> result;
    if (action == 'approve') {
      result = await _apiService.approveSignedForm(id);
    } else if (action == 'reject') {
      result = await _apiService.rejectSignedForm(id, note ?? 'Ditolak Admin');
    } else {
      result = await _apiService.bypassLeaveLock(id);
    }

    if (result['status'] == 200) {
      _loadData();
    } else {
      setState(() => _isLoading = false);
    }
  }
}
