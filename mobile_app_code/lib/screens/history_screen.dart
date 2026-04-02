import 'package:flutter/material.dart';
import '../services/api_service.dart';
import 'package:intl/intl.dart';
import 'package:file_picker/file_picker.dart';

class HistoryScreen extends StatefulWidget {
  const HistoryScreen({super.key});

  @override
  _HistoryScreenState createState() => _HistoryScreenState();
}

class _HistoryScreenState extends State<HistoryScreen> {
  final ApiService _apiService = ApiService();
  bool _isLoading = true;
  List<dynamic> _history = [];

  @override
  void initState() {
    super.initState();
    _loadHistory();
  }

  Future<void> _loadHistory() async {
    setState(() => _isLoading = true);
    try {
      final history = await _apiService.getLeaveHistory();
      setState(() {
        _history = history;
        _isLoading = false;
      });
    } catch (e) {
      print(e);
      setState(() => _isLoading = false);
    }
  }

  Future<void> _pickAndUpload(int id) async {
    try {
      FilePickerResult? result = await FilePicker.platform.pickFiles(
        type: FileType.custom,
        allowedExtensions: ['pdf', 'jpg', 'jpeg', 'png'],
      );

      if (result != null) {
        String? filePath = result.files.single.path;
        if (filePath != null) {
          setState(() => _isLoading = true);
          final response = await _apiService.uploadSignedForm(id, filePath);
          
          if (response['status'] == 200) {
            ScaffoldMessenger.of(context).showSnackBar(
              const SnackBar(content: Text('Form tanda tangan berhasil diunggah')),
            );
            _loadHistory();
          } else {
            ScaffoldMessenger.of(context).showSnackBar(
              SnackBar(content: Text(response['message'] ?? 'Gagal mengunggah form')),
            );
            setState(() => _isLoading = false);
          }
        }
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error: $e')),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey.shade50,
      appBar: AppBar(
        title: const Text('Riwayat Cuti',
            style: TextStyle(fontWeight: FontWeight.bold)),
        backgroundColor: Colors.white,
        foregroundColor: Colors.indigo.shade900,
        elevation: 0.5,
      ),
      body: RefreshIndicator(
        onRefresh: _loadHistory,
        child: _isLoading
            ? const Center(child: CircularProgressIndicator())
            : _history.isEmpty
                ? Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(Icons.history_rounded,
                            size: 64, color: Colors.grey.shade300),
                        const SizedBox(height: 16),
                        Text('Belum ada riwayat cuti',
                            style: TextStyle(color: Colors.grey.shade600)),
                      ],
                    ),
                  )
                : ListView.builder(
                    padding: const EdgeInsets.all(16),
                    itemCount: _history.length,
                    itemBuilder: (context, index) {
                      final item = _history[index];
                      return _buildHistoryCard(item);
                    },
                  ),
      ),
    );
  }

  Widget _buildHistoryCard(dynamic item) {
    final bool isApprovedByAdmin = item['status'].toString().toLowerCase() == 'approved';
    
    return Card(
      elevation: 0,
      margin: const EdgeInsets.only(bottom: 12),
      color: Colors.white,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(16),
        side: BorderSide(color: Colors.grey.shade200, width: 1),
      ),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Container(
                  padding:
                      const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                  decoration: BoxDecoration(
                    color: Colors.indigo.shade50,
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text(
                    item['leave_type_name'] ?? 'Cuti',
                    style: TextStyle(
                      color: Colors.indigo.shade700,
                      fontWeight: FontWeight.bold,
                      fontSize: 12,
                    ),
                  ),
                ),
                _buildStatusBadge(item['status']),
              ],
            ),
            const SizedBox(height: 16),
            Row(
              children: [
                Icon(Icons.calendar_today_rounded,
                    size: 16, color: Colors.grey.shade600),
                const SizedBox(width: 8),
                Text(
                  '${item['start_date']} - ${item['end_date']}',
                  style: const TextStyle(fontWeight: FontWeight.w600),
                ),
              ],
            ),
            if (item['reason'] != null &&
                item['reason'].toString().isNotEmpty) ...[
              const SizedBox(height: 8),
              Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Icon(Icons.notes_rounded,
                      size: 16, color: Colors.grey.shade600),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      item['reason'],
                      style:
                          TextStyle(color: Colors.grey.shade700, fontSize: 13),
                    ),
                  ),
                ],
              ),
            ],
            const Divider(height: 24),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Expanded(
                  child: Text(
                    'Diajukan: ${item['created_at'] != null ? DateFormat('dd MMM yyyy').format(DateTime.parse(item['created_at'])) : '-'}',
                    style: TextStyle(color: Colors.grey.shade500, fontSize: 10),
                  ),
                ),
                if (isApprovedByAdmin)
                  _buildSignedFormSection(item),
              ],
            ),
            if (isApprovedByAdmin) ...[
              const SizedBox(height: 12),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton.icon(
                  onPressed: () async {
                    final token = await _apiService.getToken();
                    final url = Uri.parse(
                        '${ApiService.baseUrl}/leave/print/${item['id']}?token=$token');
                    _apiService.launchURL(url.toString());
                  },
                  icon: const Icon(Icons.picture_as_pdf_rounded, size: 14),
                  label: const Text('Cetak & Tanda Tangan', style: TextStyle(fontSize: 12)),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Colors.indigo.shade600,
                    foregroundColor: Colors.white,
                    elevation: 0,
                    padding: const EdgeInsets.symmetric(vertical: 8),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                  ),
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }

  Widget _buildSignedFormSection(dynamic item) {
    final status = item['signed_form_status'] ?? 'pending_upload';
    final isBypassed = (item['is_bypassed'] ?? 0).toString() == '1';

    if (isBypassed) {
      return Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(Icons.verified_user_rounded, color: Colors.blue.shade600, size: 14),
          const SizedBox(width: 4),
          Text('Bypassed', style: TextStyle(color: Colors.blue.shade700, fontSize: 10, fontWeight: FontWeight.bold)),
        ],
      );
    }

    if (status == 'pending_upload') {
      return TextButton.icon(
        onPressed: () => _pickAndUpload(int.parse(item['id'].toString())),
        icon: const Icon(Icons.upload_file_rounded, size: 14),
        label: const Text('Unggah Form', style: TextStyle(fontSize: 11)),
        style: TextButton.styleFrom(
          foregroundColor: Colors.orange.shade800,
          padding: const EdgeInsets.symmetric(horizontal: 8),
          minimumSize: const Size(0, 32),
        ),
      );
    } else if (status == 'pending_approval') {
      return Container(
        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
        decoration: BoxDecoration(color: Colors.blue.shade50, borderRadius: BorderRadius.circular(8), border: Border.all(color: Colors.blue.shade100)),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            SizedBox(width: 10, height: 10, child: CircularProgressIndicator(strokeWidth: 2, valueColor: AlwaysStoppedAnimation(Colors.blue.shade700))),
            const SizedBox(width: 6),
            Text('Verifikasi', style: TextStyle(color: Colors.blue.shade700, fontSize: 10, fontWeight: FontWeight.bold)),
          ],
        ),
      );
    } else if (status == 'rejected') {
      return Column(
        crossAxisAlignment: CrossAxisAlignment.end,
        children: [
          Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(Icons.warning_amber_rounded, color: Colors.red.shade700, size: 14),
              const SizedBox(width: 4),
              Text('Ditolak!', style: TextStyle(color: Colors.red.shade700, fontSize: 10, fontWeight: FontWeight.bold)),
            ],
          ),
          if (item['signed_form_note'] != null)
            Padding(
              padding: const EdgeInsets.only(top: 4),
              child: Text(
                item['signed_form_note'] ?? 'Ditolak Admin. Silakan unggah ulang form yang benar.',
                style: TextStyle(color: Colors.red.shade900, fontSize: 9, fontStyle: FontStyle.italic),
                textAlign: TextAlign.right,
              ),
            ),
          const SizedBox(height: 8),
          ElevatedButton.icon(
            onPressed: () => _pickAndUpload(int.parse(item['id'].toString())),
            icon: const Icon(Icons.refresh_rounded, size: 12),
            label: const Text('Unggah Ulang', style: TextStyle(fontSize: 10)),
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.red.shade50,
              foregroundColor: Colors.red.shade700,
              elevation: 0,
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
              minimumSize: const Size(0, 32),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8), side: BorderSide(color: Colors.red.shade200)),
            ),
          ),
        ],
      );
    } else if (status == 'approved') {
      return Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          const Icon(Icons.verified_rounded, color: Colors.green, size: 14),
          const SizedBox(width: 4),
          Text('Terverifikasi', style: TextStyle(color: Colors.green.shade700, fontSize: 10, fontWeight: FontWeight.bold)),
        ],
      );
    }
    
    return const SizedBox.shrink();
  }

  Widget _buildStatusBadge(String status) {
    Color bgColor;
    Color textColor;
    String label;

    switch (status.toLowerCase()) {
      case 'approved':
        bgColor = Colors.green.shade50;
        textColor = Colors.green.shade700;
        label = 'Disetujui';
        break;
      case 'rejected':
        bgColor = Colors.red.shade50;
        textColor = Colors.red.shade700;
        label = 'Ditolak';
        break;
      default:
        bgColor = Colors.orange.shade50;
        textColor = Colors.orange.shade700;
        label = 'Menunggu';
    }

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: bgColor,
        borderRadius: BorderRadius.circular(12),
      ),
      child: Text(
        label,
        style: TextStyle(
          color: textColor,
          fontSize: 11,
          fontWeight: FontWeight.bold,
        ),
      ),
    );
  }
}
