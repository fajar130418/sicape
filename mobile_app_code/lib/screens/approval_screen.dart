import 'package:flutter/material.dart';
import '../services/api_service.dart';

class ApprovalScreen extends StatefulWidget {
  const ApprovalScreen({super.key});

  @override
  _ApprovalScreenState createState() => _ApprovalScreenState();
}

class _ApprovalScreenState extends State<ApprovalScreen> {
  final ApiService _apiService = ApiService();
  List<dynamic> _supervisorApprovals = [];
  List<dynamic> _headApprovals = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  void _loadData() async {
    try {
      final data = await _apiService.getApprovals();
      if (data['status'] == 200) {
        setState(() {
          _supervisorApprovals = data['data']['supervisor_approvals'];
          _headApprovals = data['data']['head_approvals'];
          _isLoading = false;
        });
      }
    } catch (e) {
      print(e);
      setState(() => _isLoading = false);
    }
  }

  void _process(int id, String action, String role) async {
    final noteController = TextEditingController();
    await showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Text('${action == 'approve' ? 'Setujui' : 'Tolak'} Pengajuan?'),
        content: TextField(
          controller: noteController,
          decoration: const InputDecoration(labelText: 'Catatan (Opsional)'),
        ),
        actions: [
          TextButton(
              onPressed: () => Navigator.pop(context),
              child: const Text('Batal')),
          ElevatedButton(
            onPressed: () {
              Navigator.pop(context);
              _submitProcess(id, action, role, noteController.text);
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: action == 'approve' ? Colors.green : Colors.red,
            ),
            child: Text('Konfirmasi'),
          ),
        ],
      ),
    );
  }

  void _submitProcess(int id, String action, String role, String note) async {
    setState(() => _isLoading = true);
    try {
      final result = await _apiService.processApproval(id, action, role, note);
      if (result['status'] == 200) {
        ScaffoldMessenger.of(context)
            .showSnackBar(const SnackBar(content: Text('Berhasil diproses')));
        _loadData();
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(content: Text('Gagal: ${result['message']}')));
        setState(() => _isLoading = false);
      }
    } catch (e) {
      ScaffoldMessenger.of(context)
          .showSnackBar(SnackBar(content: Text('Error: $e')));
      setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return DefaultTabController(
      length: 2,
      child: Scaffold(
        appBar: AppBar(
          title: const Text('Persetujuan Cuti'),
          bottom: const TabBar(
            tabs: [
              Tab(text: 'Sebagai Atasan'),
              Tab(text: 'Sebagai Kapus'),
            ],
          ),
        ),
        body: _isLoading
            ? const Center(child: CircularProgressIndicator())
            : TabBarView(
                children: [
                  _buildList(_supervisorApprovals, 'supervisor'),
                  _buildList(_headApprovals, 'head'),
                ],
              ),
      ),
    );
  }

  Widget _buildList(List<dynamic> list, String role) {
    if (list.isEmpty)
      return const Center(child: Text('Tidak ada permintaan tertunda'));

    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: list.length,
      itemBuilder: (context, index) {
        final item = list[index];
        return Card(
          elevation: 3,
          margin: const EdgeInsets.only(bottom: 12),
          child: Padding(
            padding: const EdgeInsets.all(12),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(item['user_name'] ?? 'User',
                    style: const TextStyle(
                        fontWeight: FontWeight.bold, fontSize: 16)),
                Text(item['leave_type_name'],
                    style: const TextStyle(color: Colors.blue)),
                const SizedBox(height: 8),
                Text('Durasi: ${item['duration']} Hari'),
                Text('Tanggal: ${item['start_date']} s/d ${item['end_date']}'),
                Text('Alasan: ${item['reason']}',
                    style: const TextStyle(fontStyle: FontStyle.italic)),
                const SizedBox(height: 12),
                Row(
                  mainAxisAlignment: MainAxisAlignment.end,
                  children: [
                    OutlinedButton.icon(
                      onPressed: () =>
                          _process(int.parse(item['id']), 'reject', role),
                      icon: const Icon(Icons.close, color: Colors.red),
                      label: const Text('Tolak',
                          style: TextStyle(color: Colors.red)),
                    ),
                    const SizedBox(width: 12),
                    ElevatedButton.icon(
                      onPressed: () =>
                          _process(int.parse(item['id']), 'approve', role),
                      icon: const Icon(Icons.check),
                      label: const Text('Setujui'),
                      style: ElevatedButton.styleFrom(
                          backgroundColor: Colors.green),
                    ),
                  ],
                ),
              ],
            ),
          ),
        );
      },
    );
  }
}
