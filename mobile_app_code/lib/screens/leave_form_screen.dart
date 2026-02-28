import 'package:flutter/material.dart';
import '../services/api_service.dart';
import 'package:intl/intl.dart';

class LeaveFormScreen extends StatefulWidget {
  const LeaveFormScreen({super.key});

  @override
  _LeaveFormScreenState createState() => _LeaveFormScreenState();
}

class _LeaveFormScreenState extends State<LeaveFormScreen> {
  final _formKey = GlobalKey<FormState>();
  final _reasonController = TextEditingController();
  final _addressController = TextEditingController();
  final ApiService _apiService = ApiService();

  List<dynamic> _leaveTypes = [];
  dynamic _selectedType;
  DateTime? _startDate;
  DateTime? _endDate;
  bool _isLoading = false;

  @override
  void initState() {
    super.initState();
    _loadTypes();
  }

  void _loadTypes() async {
    try {
      final types = await _apiService.getLeaveTypes();
      setState(() {
        _leaveTypes = types;
      });
    } catch (e) {
      print(e);
    }
  }

  void _submit() async {
    if (_formKey.currentState!.validate() &&
        _selectedType != null &&
        _startDate != null &&
        _endDate != null) {
      setState(() => _isLoading = true);

      final data = {
        'leave_type_id': _selectedType['id'],
        'start_date': DateFormat('yyyy-MM-dd').format(_startDate!),
        'end_date': DateFormat('yyyy-MM-dd').format(_endDate!),
        'reason': _reasonController.text,
        'work_address':
            _addressController.text, // Assuming API accepts this or maps it
      };

      try {
        final result = await _apiService.submitLeaveRequest(data);
        setState(() => _isLoading = false);

        if (result['status'] == 201 || result['status'] == 200) {
          ScaffoldMessenger.of(context).showSnackBar(
              const SnackBar(content: Text('Pengajuan Cuti Berhasil')));
          Navigator.pop(context);
        } else {
          ScaffoldMessenger.of(context).showSnackBar(SnackBar(
              content: Text(result['messages']?['error'] ??
                  'Gagal Mengajukan: ${result.toString()}')));
        }
      } catch (e) {
        setState(() => _isLoading = false);
        ScaffoldMessenger.of(context)
            .showSnackBar(SnackBar(content: Text('Error: $e')));
      }
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Mohon lengkapi formulir')));
    }
  }

  Future<void> _selectDate(BuildContext context, bool isStart) async {
    final picked = await showDatePicker(
      context: context,
      initialDate: DateTime.now(),
      firstDate: DateTime.now().subtract(const Duration(days: 365)),
      lastDate: DateTime.now().add(const Duration(days: 365)),
    );
    if (picked != null) {
      setState(() {
        if (isStart) {
          _startDate = picked;
        } else {
          _endDate = picked;
        }
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey.shade50,
      appBar: AppBar(
        title: const Text('Ajukan Cuti', style: TextStyle(fontWeight: FontWeight.bold)),
        backgroundColor: Colors.white,
        foregroundColor: Colors.indigo.shade900,
        elevation: 0.5,
      ),
      body: SingleChildScrollView(
        child: Column(
          children: [
            Container(
              width: double.infinity,
              padding: const EdgeInsets.symmetric(vertical: 24, horizontal: 20),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: const BorderRadius.only(
                  bottomLeft: Radius.circular(30),
                  bottomRight: Radius.circular(30),
                ),
                boxShadow: [
                  BoxShadow(
                    color: Colors.grey.withOpacity(0.05),
                    blurRadius: 10,
                    offset: const Offset(0, 5),
                  ),
                ],
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Formulir Pengajuan',
                    style: TextStyle(
                      fontSize: 22,
                      fontWeight: FontWeight.w800,
                      color: Colors.indigo.shade900,
                    ),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    'Lengkapi data di bawah ini untuk mengajukan cuti Anda.',
                    style: TextStyle(color: Colors.grey.shade600, fontSize: 14),
                  ),
                ],
              ),
            ),
            Padding(
              padding: const EdgeInsets.all(20),
              child: Form(
                key: _formKey,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    _buildDropdownField(),
                    const SizedBox(height: 20),
                    Row(
                      children: [
                        Expanded(child: _buildDateField(true)),
                        const SizedBox(width: 16),
                        Expanded(child: _buildDateField(false)),
                      ],
                    ),
                    const SizedBox(height: 20),
                    _buildTextField(
                      controller: _reasonController,
                      label: 'Alasan Cuti',
                      icon: Icons.edit_note_rounded,
                      maxLines: 3,
                    ),
                    const SizedBox(height: 20),
                    _buildTextField(
                      controller: _addressController,
                      label: 'Alamat Selama Cuti',
                      icon: Icons.location_on_rounded,
                    ),
                    const SizedBox(height: 32),
                    SizedBox(
                      width: double.infinity,
                      height: 56,
                      child: ElevatedButton(
                        onPressed: _isLoading ? null : _submit,
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Colors.indigo.shade600,
                          foregroundColor: Colors.white,
                          elevation: 2,
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(16),
                          ),
                        ),
                        child: _isLoading
                            ? const SizedBox(
                                height: 24,
                                width: 24,
                                child: CircularProgressIndicator(
                                  color: Colors.white,
                                  strokeWidth: 2.5,
                                ),
                              )
                            : const Text(
                                'KIRIM PENGAJUAN',
                                style: TextStyle(
                                  fontSize: 16,
                                  fontWeight: FontWeight.bold,
                                  letterSpacing: 1.2,
                                ),
                              ),
                      ),
                    ),
                    const SizedBox(height: 40),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildDropdownField() {
    return DropdownButtonFormField(
      decoration: InputDecoration(
        labelText: 'Jenis Cuti',
        prefixIcon: Icon(Icons.category_rounded, color: Colors.indigo.shade400),
        filled: true,
        fillColor: Colors.white,
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: BorderSide.none,
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: BorderSide(color: Colors.grey.shade200),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: BorderSide(color: Colors.indigo.shade400, width: 2),
        ),
      ),
      items: _leaveTypes.map((type) {
        return DropdownMenuItem(
          value: type,
          child: Text(type['name']),
        );
      }).toList(),
      onChanged: (val) => setState(() => _selectedType = val),
      validator: (val) => val == null ? 'Pilih jenis cuti' : null,
      icon: Icon(Icons.keyboard_arrow_down_rounded, color: Colors.indigo.shade400),
      dropdownColor: Colors.white,
      borderRadius: BorderRadius.circular(16),
    );
  }

  Widget _buildDateField(bool isStart) {
    return InkWell(
      onTap: () => _selectDate(context, isStart),
      borderRadius: BorderRadius.circular(16),
      child: IgnorePointer(
        child: TextFormField(
          decoration: InputDecoration(
            labelText: isStart ? 'Mulai' : 'Selesai',
            prefixIcon: Icon(Icons.calendar_month_rounded, color: Colors.indigo.shade400),
            filled: true,
            fillColor: Colors.white,
            border: OutlineInputBorder(
              borderRadius: BorderRadius.circular(16),
              borderSide: BorderSide.none,
            ),
            enabledBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(16),
              borderSide: BorderSide(color: Colors.grey.shade200),
            ),
          ),
          controller: TextEditingController(
            text: isStart
                ? (_startDate != null ? DateFormat('dd/MM/yyyy').format(_startDate!) : '')
                : (_endDate != null ? DateFormat('dd/MM/yyyy').format(_endDate!) : ''),
          ),
          validator: (val) => val!.isEmpty ? 'Pilih' : null,
        ),
      ),
    );
  }

  Widget _buildTextField({
    required TextEditingController controller,
    required String label,
    required IconData icon,
    int maxLines = 1,
  }) {
    return TextFormField(
      controller: controller,
      maxLines: maxLines,
      decoration: InputDecoration(
        labelText: label,
        alignLabelWithHint: maxLines > 1,
        prefixIcon: Padding(
          padding: EdgeInsets.only(bottom: maxLines > 1 ? (maxLines * 10.0) : 0),
          child: Icon(icon, color: Colors.indigo.shade400),
        ),
        filled: true,
        fillColor: Colors.white,
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: BorderSide.none,
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: BorderSide(color: Colors.grey.shade200),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: BorderSide(color: Colors.indigo.shade400, width: 2),
        ),
      ),
      validator: (val) => val!.isEmpty ? 'Bagian ini wajib diisi' : null,
    );
  }
}
