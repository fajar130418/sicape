import 'package:flutter/material.dart';
import '../services/api_service.dart';
import 'package:url_launcher/url_launcher.dart';

class AdminApprovalScreen extends StatefulWidget {
  const AdminApprovalScreen({super.key});

  @override
  _AdminApprovalScreenState createState() => _AdminApprovalScreenState();
}

class _AdminApprovalScreenState extends State<AdminApprovalScreen> {
  final ApiService _apiService = ApiService();
  List<dynamic> _pendingForms = [];
  bool _isLoading = true;

  // Premium design tokens
  static const Color _primary = Color(0xFF6366F1);
  static const Color _success = Color(0xFF10B981);
  static const Color _danger = Color(0xFFEF4444);
  static const Color _warning = Color(0xFFF59E0B);
  static const Color _bgColor = Color(0xFFF8FAFC);
  static const Color _textDark = Color(0xFF1E293B);
  static const Color _textMid = Color(0xFF64748B);

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    setState(() => _isLoading = true);
    try {
      final data = await _apiService.getPendingSignedForms();
      setState(() {
        _pendingForms = data;
        _isLoading = false;
      });
    } catch (e) {
      setState(() => _isLoading = false);
      _showSnackBar('Gagal memuat data: $e', isError: true);
    }
  }

  void _showSnackBar(String message, {bool isError = false}) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: isError ? _danger : _success,
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
      ),
    );
  }

  Future<void> _handleAction(int id, String action, {String? note}) async {
    setState(() => _isLoading = true);
    try {
      Map<String, dynamic> response;
      if (action == 'approve') {
        response = await _apiService.approveSignedForm(id);
      } else if (action == 'reject') {
        response = await _apiService.rejectSignedForm(id, note ?? '');
      } else {
        response = await _apiService.bypassLeaveLock(id);
      }

      if (response['status'] == 200) {
        _showSnackBar(response['message'] ?? 'Berhasil diproses');
        _loadData();
      } else {
        _showSnackBar(response['message'] ?? 'Terjadi kesalahan', isError: true);
        setState(() => _isLoading = false);
      }
    } catch (e) {
      _showSnackBar('Error: $e', isError: true);
      setState(() => _isLoading = false);
    }
  }

  void _showRejectDialog(int id) {
    final controller = TextEditingController();
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Tolak Form'),
        content: TextField(
          controller: controller,
          decoration: const InputDecoration(
            hintText: 'Masukkan alasan penolakan...',
            border: OutlineInputBorder(),
          ),
          maxLines: 3,
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context), child: const Text('Batal')),
          ElevatedButton(
            style: ElevatedButton.styleFrom(backgroundColor: _danger, foregroundColor: Colors.white),
            onPressed: () {
              Navigator.pop(context);
              _handleAction(id, 'reject', note: controller.text);
            },
            child: const Text('Tolak'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: _bgColor,
      appBar: AppBar(
        title: const Text('Verifikasi Form Cuti', style: TextStyle(fontWeight: FontWeight.bold, color: Colors.white)),
        backgroundColor: _primary,
        elevation: 0,
        actions: [
          IconButton(onPressed: _loadData, icon: const Icon(Icons.refresh, color: Colors.white)),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator(valueColor: AlwaysStoppedAnimation(_primary)))
          : _pendingForms.isEmpty
              ? _buildEmptyState()
              : ListView.builder(
                  padding: const EdgeInsets.all(16),
                  itemCount: _pendingForms.length,
                  itemBuilder: (context, index) => _buildRequestCard(_pendingForms[index]),
                ),
    );
  }

  Widget _buildEmptyState() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.assignment_turned_in_outlined, size: 80, color: _textMid.withOpacity(0.3)),
          const SizedBox(height: 16),
          const Text('Tidak ada form menunggu verifikasi', style: TextStyle(color: _textMid, fontSize: 16)),
        ],
      ),
    );
  }

  Widget _buildRequestCard(dynamic item) {
    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.05), blurRadius: 10, offset: const Offset(0, 4))],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Padding(
            padding: const EdgeInsets.all(16),
            child: Row(
              children: [
                CircleAvatar(
                  backgroundColor: _primary.withOpacity(0.1),
                  child: Text(item['user_name'][0].toUpperCase(), style: const TextStyle(color: _primary, fontWeight: FontWeight.bold)),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(item['user_name'], style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: _textDark)),
                      Text('NIP: ${item['nip']}', style: const TextStyle(color: _textMid, fontSize: 12)),
                    ],
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(color: _warning.withOpacity(0.1), borderRadius: BorderRadius.circular(8)),
                  child: const Text('Menunggu', style: TextStyle(color: _warning, fontSize: 10, fontWeight: FontWeight.bold)),
                ),
              ],
            ),
          ),
          const Divider(height: 1),
          Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                _buildInfoRow(Icons.category_outlined, 'Jenis Cuti', item['type_name']),
                const SizedBox(height: 8),
                _buildInfoRow(Icons.calendar_today_outlined, 'Periode', '${item['start_date']} s/d ${item['end_date']}'),
              ],
            ),
          ),
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
            child: Row(
              children: [
                Expanded(
                  child: OutlinedButton.icon(
                    onPressed: () => _openFile(item['signed_form']),
                    icon: const Icon(Icons.picture_as_pdf_outlined, size: 18),
                    label: const Text('Lihat Form'),
                    style: OutlinedButton.styleFrom(
                      foregroundColor: _primary,
                      side: const BorderSide(color: _primary),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                    ),
                  ),
                ),
              ],
            ),
          ),
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: Colors.grey[50],
              borderRadius: const BorderRadius.vertical(bottom: Radius.circular(16)),
            ),
            child: Row(
              children: [
                Expanded(
                  child: TextButton(
                    onPressed: () => _handleAction(int.parse(item['id'].toString()), 'bypass'),
                    child: const Text('Bypass Kunci', style: TextStyle(color: _textMid, fontSize: 13)),
                  ),
                ),
                const SizedBox(width: 8),
                ElevatedButton(
                  onPressed: () => _showRejectDialog(int.parse(item['id'].toString())),
                  style: ElevatedButton.styleFrom(backgroundColor: _danger.withOpacity(0.1), foregroundColor: _danger, elevation: 0),
                  child: const Text('Tolak'),
                ),
                const SizedBox(width: 8),
                ElevatedButton(
                  onPressed: () => _handleAction(int.parse(item['id'].toString()), 'approve'),
                  style: ElevatedButton.styleFrom(backgroundColor: _success, foregroundColor: Colors.white, elevation: 0),
                  child: const Text('Setujui'),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildInfoRow(IconData icon, String label, String value) {
    return Row(
      children: [
        Icon(icon, size: 16, color: _textMid),
        const SizedBox(width: 8),
        Text('$label: ', style: const TextStyle(color: _textMid, fontSize: 13)),
        Expanded(child: Text(value, style: const TextStyle(color: _textDark, fontWeight: FontWeight.w500, fontSize: 13))),
      ],
    );
  }

  Future<void> _openFile(String? path) async {
    if (path == null) return;
    final url = Uri.parse('${ApiService.siteUrl}/uploads/$path');
    if (await canLaunchUrl(url)) {
      await launchUrl(url, mode: LaunchMode.externalApplication);
    } else {
      _showSnackBar('Tidak dapat membuka file', isError: true);
    }
  }
}
