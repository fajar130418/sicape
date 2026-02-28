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
        backgroundColor: Colors.grey.shade50,
        appBar: AppBar(
          title: const Text('Persetujuan Cuti', style: TextStyle(fontWeight: FontWeight.bold)),
          backgroundColor: Colors.white,
          foregroundColor: Colors.indigo.shade900,
          elevation: 0,
          bottom: PreferredSize(
            preferredSize: const Size.fromHeight(60),
            child: Container(
              margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
              decoration: BoxDecoration(
                color: Colors.grey.shade100,
                borderRadius: BorderRadius.circular(25),
              ),
              child: TabBar(
                indicatorSize: TabBarIndicatorSize.tab,
                dividerColor: Colors.transparent,
                indicator: BoxDecoration(
                  borderRadius: BorderRadius.circular(25),
                  color: Colors.indigo.shade600,
                  boxShadow: [
                    BoxShadow(
                      color: Colors.indigo.withOpacity(0.3),
                      blurRadius: 8,
                      offset: const Offset(0, 4),
                    ),
                  ],
                ),
                labelColor: Colors.white,
                unselectedLabelColor: Colors.grey.shade600,
                labelStyle: const TextStyle(fontWeight: FontWeight.bold),
                tabs: const [
                  Tab(text: 'Sbg Atasan'),
                  Tab(text: 'Sbg Kapus'),
                ],
              ),
            ),
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
    if (list.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.inbox_rounded, size: 80, color: Colors.grey.shade300),
            const SizedBox(height: 16),
            Text(
              'Tidak ada permintaan tertunda',
              style: TextStyle(color: Colors.grey.shade600, fontSize: 16),
            ),
          ],
        ),
      );
    }

    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: list.length,
      itemBuilder: (context, index) {
        final item = list[index];
        return Card(
          elevation: 2,
          shadowColor: Colors.black12,
          margin: const EdgeInsets.only(bottom: 16),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(20),
            side: BorderSide(color: Colors.grey.shade200),
          ),
          child: Padding(
            padding: const EdgeInsets.all(20),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    CircleAvatar(
                      backgroundColor: Colors.indigo.shade50,
                      child: Icon(Icons.person_rounded, color: Colors.indigo.shade400),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            item['user_name'] ?? 'User',
                            style: const TextStyle(
                              fontWeight: FontWeight.bold,
                              fontSize: 16,
                            ),
                          ),
                          Text(
                            item['leave_type_name'],
                            style: TextStyle(
                              color: Colors.indigo.shade600,
                              fontWeight: FontWeight.w600,
                              fontSize: 13,
                            ),
                          ),
                        ],
                      ),
                    ),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                      decoration: BoxDecoration(
                        color: Colors.blue.shade50,
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Text(
                        '${item['duration']} Hari',
                        style: TextStyle(
                          color: Colors.blue.shade700,
                          fontWeight: FontWeight.bold,
                          fontSize: 12,
                        ),
                      ),
                    ),
                  ],
                ),
                const Divider(height: 32),
                Row(
                  children: [
                    Icon(Icons.calendar_month_rounded, size: 16, color: Colors.grey.shade500),
                    const SizedBox(width: 8),
                    Text(
                      '${item['start_date']} s/d ${item['end_date']}',
                      style: TextStyle(color: Colors.grey.shade800),
                    ),
                  ],
                ),
                const SizedBox(height: 8),
                Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Icon(Icons.notes_rounded, size: 16, color: Colors.grey.shade500),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        item['reason'] ?? 'Tanpa alasan',
                        style: TextStyle(
                          color: Colors.grey.shade600,
                          fontStyle: FontStyle.italic,
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 20),
                Row(
                  children: [
                    Expanded(
                      child: OutlinedButton.icon(
                        onPressed: () => _process(int.parse(item['id']), 'reject', role),
                        icon: const Icon(Icons.close_rounded, size: 18),
                        label: const Text('Tolak'),
                        style: OutlinedButton.styleFrom(
                          foregroundColor: Colors.red.shade600,
                          side: BorderSide(color: Colors.red.shade200),
                          padding: const EdgeInsets.symmetric(vertical: 12),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(12),
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: ElevatedButton.icon(
                        onPressed: () => _process(int.parse(item['id']), 'approve', role),
                        icon: const Icon(Icons.check_rounded, size: 18),
                        label: const Text('Setujui'),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Colors.green.shade600,
                          foregroundColor: Colors.white,
                          elevation: 0,
                          padding: const EdgeInsets.symmetric(vertical: 12),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(12),
                          ),
                        ),
                      ),
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
