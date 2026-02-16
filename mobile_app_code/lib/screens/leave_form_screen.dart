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
      appBar: AppBar(title: const Text('Ajukan Cuti')),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              DropdownButtonFormField(
                decoration: const InputDecoration(
                    labelText: 'Jenis Cuti', border: OutlineInputBorder()),
                items: _leaveTypes.map((type) {
                  return DropdownMenuItem(
                    value: type,
                    child: Text(type['name']),
                  );
                }).toList(),
                onChanged: (val) => setState(() => _selectedType = val),
                validator: (val) => val == null ? 'Pilih jenis cuti' : null,
              ),
              const SizedBox(height: 16),
              Row(
                children: [
                  Expanded(
                    child: InkWell(
                      onTap: () => _selectDate(context, true),
                      child: InputDecorator(
                        decoration: const InputDecoration(
                            labelText: 'Tanggal Mulai',
                            border: OutlineInputBorder(),
                            prefixIcon: Icon(Icons.calendar_today)),
                        child: Text(_startDate != null
                            ? DateFormat('dd/MM/yyyy').format(_startDate!)
                            : 'Pilih'),
                      ),
                    ),
                  ),
                  const SizedBox(width: 16),
                  Expanded(
                    child: InkWell(
                      onTap: () => _selectDate(context, false),
                      child: InputDecorator(
                        decoration: const InputDecoration(
                            labelText: 'Tanggal Selesai',
                            border: OutlineInputBorder(),
                            prefixIcon: Icon(Icons.calendar_today)),
                        child: Text(_endDate != null
                            ? DateFormat('dd/MM/yyyy').format(_endDate!)
                            : 'Pilih'),
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 16),
              TextFormField(
                controller: _reasonController,
                decoration: const InputDecoration(
                    labelText: 'Alasan Cuti', border: OutlineInputBorder()),
                maxLines: 3,
                validator: (val) => val!.isEmpty ? 'Isi alasan cuti' : null,
              ),
              const SizedBox(height: 16),
              TextFormField(
                controller: _addressController,
                decoration: const InputDecoration(
                    labelText: 'Alamat Selama Cuti',
                    border: OutlineInputBorder()),
                validator: (val) =>
                    val!.isEmpty ? 'Isi alamat selama cuti' : null,
              ),
              const SizedBox(height: 24),
              SizedBox(
                width: double.infinity,
                height: 50,
                child: ElevatedButton(
                  onPressed: _isLoading ? null : _submit,
                  style: ElevatedButton.styleFrom(backgroundColor: Colors.blue),
                  child: _isLoading
                      ? CircularProgressIndicator(color: Colors.white)
                      : Text('Kirim Pengajuan'),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
