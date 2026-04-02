import 'package:flutter/material.dart';
import '../services/api_service.dart';
import 'package:intl/intl.dart';
import 'package:file_picker/file_picker.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:image_picker/image_picker.dart';

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
  String? _selectedCategory;
  String? _attachmentPath;
  String? _attachmentName;
  DateTime? _startDate;
  DateTime? _endDate;
  bool _isLoading = false;

  // Dynamic state
  bool _showCategory = false;
  bool _showAttachment = false;
  bool _attachmentRequired = false;
  List<String> _categories = [];
  String _categoryLabel = 'Kategori';
  String _reasonHint = '';

  // Categories Mapping (Match Web)
  final Map<String, List<String>> _typeCategories = {
    'Cuti Besar': [
      'Ibadah Keagamaan (Haji Pertama)',
      'Ibadah Keagamaan (Umrah/Haji Lanjutan)',
      'Keperluan Keluarga (Sakit Keras/Pemulihan)',
      'Persalinan Anak ke-4 dan seterusnya',
      'Keperluan Pribadi Mendesak'
    ],
    'Cuti Sakit': ['Sakit Biasa', 'Gugur Kandungan', 'Kecelakaan Kerja'],
    'Cuti Melahirkan': [
      'Anak ke-1',
      'Anak ke-2',
      'Anak ke-3',
      'Anak ke-4 atau lebih'
    ],
    'Cuti di Luar Tanggungan Negara': [
      'Mengikuti Suami/Istri Tugas Negara/Belajar',
      'Mendampingi Anak Butuh Perhatian Khusus',
      'Mendampingi Suami/Istri/Orang Tua Sakit Parah/Menua',
      'Persalinan Anak ke-4 dan Seterusnya (Tanpa Jatah Cuti Besar)',
      'Alasan Pribadi yang Sangat Penting & Mendesak'
    ],
    'Cuti Alasan Penting': [
      'Keluarga Inti Sakit Keras',
      'Keluarga Inti Meninggal Dunia',
      'Mengurus Hak Keluarga',
      'Melangsungkan Pernikahan',
      'Istri Melahirkan/Operasi Caesar',
      'Musibah Bencana',
      'Faktor Kejiwaan'
    ],
    'Cuti Karena Alasan Penting': [
      'Keluarga Inti Sakit Keras',
      'Keluarga Inti Meninggal Dunia',
      'Mengurus Hak Keluarga',
      'Melangsungkan Pernikahan',
      'Istri Melahirkan/Operasi Caesar',
      'Musibah Bencana',
      'Faktor Kejiwaan'
    ]
  };

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
      debugPrint('Error loading types: $e');
    }
  }

  void _onTypeChanged(dynamic type) {
    setState(() {
      _selectedType = type;
      _selectedCategory = null;
      _attachmentPath = null;
      _attachmentName = null;
      _reasonHint = '';

      String typeName = type['name'];
      _showCategory = _typeCategories.containsKey(typeName);
      _categories = _typeCategories[typeName] ?? [];

      // Initial file visibility from DB config
      _showAttachment = type['requires_file'] == 1;
      _attachmentRequired = _showAttachment;

      if (typeName == 'Cuti Sakit') {
        _categoryLabel = 'Kategori Sakit';
      } else if (typeName == 'Cuti Melahirkan') {
        _categoryLabel = 'Urutan Kelahiran';
      } else if (typeName == 'Cuti Alasan Penting' ||
          typeName == 'Cuti Karena Alasan Penting') {
        _categoryLabel = 'Alasan Penting';
      } else {
        _categoryLabel = 'Kategori';
      }
      _updateLeaveDetails();
    });
  }

  void _onCategoryChanged(String? val) {
    setState(() {
      _selectedCategory = val;
      _updateLeaveDetails();
    });
  }

  void _updateLeaveDetails() {
    if (_selectedType == null) return;

    String typeName = _selectedType['name'];
    String? category = _selectedCategory;

    setState(() {
      // CAP: Specific attachment rules
      if (typeName == 'Cuti Alasan Penting' ||
          typeName == 'Cuti Karena Alasan Penting') {
        List<String> reqFiles = [
          'Keluarga Inti Sakit Keras',
          'Musibah Bencana',
          'Faktor Kejiwaan'
        ];
        _showAttachment = true;
        _attachmentRequired = reqFiles.contains(category);
        _reasonHint =
            'Keluarga Inti: Ibu, Bapak, Istri/Suami, Anak, Adik, Kakak, Mertua, atau Menantu.';
      }
      // Cuti Sakit: logic based on duration and category
      else if (typeName == 'Cuti Sakit') {
        _showAttachment = true;
        _attachmentRequired = true;

        if (category == 'Gugur Kandungan') {
          _reasonHint = 'GUGUR KANDUNGAN: Maksimal jatah 45 hari (1,5 bulan).';
        } else if (category == 'Kecelakaan Kerja') {
          _reasonHint =
              'KECELAKAAN KERJA: Diberikan sampai sembuh total tanpa batas waktu kaku.';
        } else if (_startDate != null && _endDate != null) {
          final diff = _endDate!.difference(_startDate!).inDays + 1;
          if (diff > 14) {
            _reasonHint =
                'DURASI > 14 HARI: Wajib melampirkan Surat Keterangan dari Dokter Pemerintah.';
          } else {
            _reasonHint =
                'DURASI 1-14 HARI: Wajib melampirkan Surat Keterangan Dokter (Puskesmas/Klinik/RS).';
          }
        }
      }
      // Cuti Besar: forfeiting annual leave & Haji exception
      else if (typeName == 'Cuti Besar') {
        String hint =
            'PENTING: Mengambil Cuti Besar akan menghapus jatah Cuti Tahunan Anda di tahun berjalan.';
        if (category == 'Ibadah Keagamaan (Haji Pertama)') {
          hint +=
              '\n* Pengecualian masa kerja 5 tahun berlaku untuk Haji Pertama.';
        } else if (category == 'Persalinan Anak ke-4 dan seterusnya') {
          hint +=
              '\nTIPS: Jika masa kerja < 5 tahun, pengajuan persalinan anak ke-4+ diarahkan menggunakan CLTN.';
        }
        _reasonHint = hint;
      }
      // Cuti Melahirkan: 3-month check & Child limit
      else if (typeName == 'Cuti Melahirkan') {
        if (category == 'Anak ke-4 atau lebih') {
          _reasonHint =
              'PERHATIAN: Cuti Melahirkan hanya s.d anak ke-3. Untuk anak ke-4+, silakan ajukan Cuti Besar atau CLTN.';
        } else {
          String hint =
              'INFO: Cuti diambil selama 3 bulan kalender (termasuk hari libur).';
          if (_startDate != null && _endDate != null) {
            // Check 3 months
            DateTime maxEnd = DateTime(
                _startDate!.year, _startDate!.month + 3, _startDate!.day);
            if (_endDate!.isAfter(maxEnd)) {
              hint +=
                  '\nPERINGATAN: Durasi melebihi 3 bulan kalender (Batas: ${DateFormat('dd/MM/yyyy').format(maxEnd)}).';
            }
          }
          _reasonHint = hint;
        }
      }
      // CLTN: Consequences
      else if (typeName == 'Cuti di Luar Tanggungan Negara') {
        _reasonHint =
            'KONSEKUENSI PENTING:\n1. Tidak menerima gaji/tunjangan.\n2. Masa CLTN tidak dihitung masa kerja.\n3. Diberhentikan dari jabatan.\n4. Syarat minimal PNS 5 tahun.';
      }
    });
  }

  Future<void> _pickFile() async {
    showModalBottomSheet(
      context: context,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (context) => Container(
        padding: const EdgeInsets.symmetric(vertical: 32, horizontal: 24),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Pilih Sumber Lampiran',
              style: GoogleFonts.outfit(
                fontSize: 20,
                fontWeight: FontWeight.bold,
                color: Colors.indigo.shade900,
              ),
            ),
            const SizedBox(height: 24),
            _buildSourceItem(
              icon: Icons.camera_alt_rounded,
              color: Colors.blue.shade600,
              label: 'Kamera',
              onTap: () {
                Navigator.pop(context);
                _pickFromImageSource(ImageSource.camera);
              },
            ),
            _buildSourceItem(
              icon: Icons.photo_library_rounded,
              color: Colors.purple.shade600,
              label: 'Galeri Foto',
              onTap: () {
                Navigator.pop(context);
                _pickFromImageSource(ImageSource.gallery);
              },
            ),
            _buildSourceItem(
              icon: Icons.description_rounded,
              color: Colors.orange.shade600,
              label: 'Dokumen / PDF',
              onTap: () {
                Navigator.pop(context);
                _pickDocument();
              },
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildSourceItem({
    required IconData icon,
    required Color color,
    required String label,
    required VoidCallback onTap,
  }) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(16),
      child: Padding(
        padding: const EdgeInsets.symmetric(vertical: 12),
        child: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: color.withOpacity(0.1),
                borderRadius: BorderRadius.circular(12),
              ),
              child: Icon(icon, color: color, size: 24),
            ),
            const SizedBox(width: 20),
            Text(
              label,
              style: GoogleFonts.outfit(
                fontSize: 16,
                fontWeight: FontWeight.w600,
                color: Colors.grey.shade800,
              ),
            ),
            const Spacer(),
            Icon(Icons.chevron_right_rounded, color: Colors.grey.shade400),
          ],
        ),
      ),
    );
  }

  Future<void> _pickFromImageSource(ImageSource source) async {
    final ImagePicker picker = ImagePicker();
    final XFile? image = await picker.pickImage(
      source: source,
      imageQuality: 70, // Compressing for easier upload
    );

    if (image != null) {
      setState(() {
        _attachmentPath = image.path;
        _attachmentName = image.name;
      });
    }
  }

  Future<void> _pickDocument() async {
    FilePickerResult? result = await FilePicker.platform.pickFiles(
      type: FileType.custom,
      allowedExtensions: ['pdf', 'jpg', 'jpeg', 'png'],
    );

    if (result != null) {
      setState(() {
        _attachmentPath = result.files.single.path;
        _attachmentName = result.files.single.name;
      });
    }
  }

  void _submit() async {
    if (!_formKey.currentState!.validate()) return;
    if (_selectedType == null) {
      _showError('Pilih jenis cuti');
      return;
    }
    String typeName = _selectedType['name'];
    if (_showCategory && _selectedCategory == null) {
      _showError('Pilih $_categoryLabel');
      return;
    }
    if (_startDate == null || _endDate == null) {
      _showError('Pilih rentang tanggal');
      return;
    }
    if (_attachmentRequired && _attachmentPath == null) {
      _showError('Lampiran wajib diunggah untuk jenis cuti ini');
      return;
    }

    String reasonValue = _reasonController.text;
    if (_showCategory) {
      if (typeName == 'Cuti Besar' ||
          typeName == 'Cuti Sakit' ||
          typeName == 'Cuti Alasan Penting' ||
          typeName == 'Cuti Karena Alasan Penting') {
        reasonValue = _selectedCategory ?? '';
      } else if (typeName == 'Cuti Melahirkan') {
        reasonValue = 'Cuti Melahirkan - ${_selectedCategory ?? ''}';
      } else if (typeName == 'Cuti di Luar Tanggungan Negara') {
        reasonValue = 'CLTN - ${_selectedCategory ?? ''}';
      }
    }

    if (reasonValue.isEmpty) {
      _showError('Isi keterangan/alasan secara detail');
      return;
    }

    setState(() => _isLoading = true);

    final data = {
      'leave_type_id': _selectedType['id'],
      'start_date': DateFormat('yyyy-MM-dd').format(_startDate!),
      'end_date': DateFormat('yyyy-MM-dd').format(_endDate!),
      'address_during_leave': _addressController.text,
      'reason': reasonValue,
      'category': _selectedCategory ?? '',
    };

    try {
      final result = await _apiService.submitLeaveRequest(data,
          attachmentPath: _attachmentPath);
      setState(() => _isLoading = false);

      if (result['status'] == 201 ||
          result['status'] == 200 ||
          result['status'] == 'success') {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
            content: Text('Pengajuan Cuti Berhasil'),
            backgroundColor: Colors.green));
        Navigator.pop(context);
      } else {
        _showError(result['message'] ??
            result['messages']?['error'] ??
            'Gagal Mengajukan');
      }
    } catch (e) {
      setState(() => _isLoading = false);
      _showError('Terjadi kesalahan koneksi');
    }
  }

  void _showError(String msg) {
    ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(msg), backgroundColor: Colors.red));
  }

  Future<void> _selectDate(BuildContext context, bool isStart) async {
    final picked = await showDatePicker(
      context: context,
      initialDate: isStart ? DateTime.now() : (_startDate ?? DateTime.now()),
      firstDate: DateTime.now().subtract(const Duration(days: 365)),
      lastDate: DateTime.now().add(const Duration(days: 365)),
      builder: (context, child) {
        return Theme(
          data: Theme.of(context).copyWith(
            colorScheme: ColorScheme.light(primary: Colors.indigo.shade600),
          ),
          child: child!,
        );
      },
    );
    if (picked != null) {
      setState(() {
        if (isStart) {
          _startDate = picked;
          if (_endDate != null && _endDate!.isBefore(_startDate!)) {
            _endDate = null;
          }
        } else {
          if (_startDate != null && picked.isBefore(_startDate!)) {
            _showError('Tanggal selesai tidak boleh sebelum tanggal mulai');
          } else {
            _endDate = picked;
          }
        }
        _updateLeaveDetails();
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    String typeName = _selectedType?['name'] ?? '';
    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: Text('Ajukan Cuti',
            style:
                GoogleFonts.outfit(fontWeight: FontWeight.bold, fontSize: 18)),
        centerTitle: true,
        backgroundColor: Colors.white,
        foregroundColor: Colors.indigo.shade900,
        elevation: 0,
        bottom: PreferredSize(
          preferredSize: const Size.fromHeight(1),
          child: Divider(color: Colors.grey.shade200, height: 1),
        ),
      ),
      body: _leaveTypes.isEmpty
          ? const Center(child: CircularProgressIndicator())
          : SingleChildScrollView(
              child: Column(
                children: [
                  _buildHeader(),
                  Padding(
                    padding: const EdgeInsets.all(20),
                    child: Form(
                      key: _formKey,
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          _buildCard([
                            _buildDropdownField(),
                            if (_showCategory) ...[
                              const SizedBox(height: 20),
                              _buildCategoryDropdown(),
                            ],
                          ]),
                          const SizedBox(height: 20),
                          _buildCard([
                            Row(
                              children: [
                                Expanded(child: _buildDateField(true)),
                                const SizedBox(width: 16),
                                Expanded(child: _buildDateField(false)),
                              ],
                            ),
                          ]),
                          const SizedBox(height: 20),
                          if (_reasonHint.isNotEmpty) _buildHint(_reasonHint),
                          const SizedBox(height: 20),
                          _buildCard([
                            if (!_showCategory) ...[
                              _buildTextField(
                                controller: _reasonController,
                                label: 'Keterangan / Alasan',
                                icon: Icons.edit_note_rounded,
                                maxLines: 3,
                              ),
                              const SizedBox(height: 20),
                            ],
                            _buildTextField(
                              controller: _addressController,
                              label: 'Alamat Selama Cuti',
                              icon: Icons.location_on_rounded,
                            ),
                          ]),
                          if (_showAttachment) ...[
                            const SizedBox(height: 20),
                            _buildAttachmentSection(),
                          ],
                          const SizedBox(height: 32),
                          _buildSubmitButton(),
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

  Widget _buildHeader() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.symmetric(vertical: 24, horizontal: 24),
      decoration: const BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.only(
          bottomLeft: Radius.circular(32),
          bottomRight: Radius.circular(32),
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Formulir Pengajuan',
            style: GoogleFonts.outfit(
              fontSize: 22,
              fontWeight: FontWeight.w800,
              color: Colors.indigo.shade900,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            'Lengkapi data di bawah ini untuk mengajukan cuti Anda.',
            style:
                GoogleFonts.outfit(color: Colors.grey.shade600, fontSize: 14),
          ),
        ],
      ),
    );
  }

  Widget _buildCard(List<Widget> children) {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(24),
        border: Border.all(color: Colors.grey.shade100),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.02),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(children: children),
    );
  }

  Widget _buildHint(String text) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.amber.shade50,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: Colors.amber.shade200),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(Icons.info_outline_rounded,
              color: Colors.amber.shade700, size: 20),
          const SizedBox(width: 12),
          Expanded(
            child: Text(
              text,
              style: GoogleFonts.outfit(
                fontSize: 13,
                color: Colors.amber.shade900,
                fontWeight: FontWeight.w500,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildDropdownField() {
    return DropdownButtonFormField<dynamic>(
      decoration: _getInputDecoration('Jenis Cuti', Icons.category_rounded),
      initialValue: _selectedType,
      items: _leaveTypes.map((type) {
        return DropdownMenuItem(
          value: type,
          child: Text(type['name'], style: GoogleFonts.outfit(fontSize: 15)),
        );
      }).toList(),
      onChanged: _onTypeChanged,
      validator: (val) => val == null ? 'Pilih jenis cuti' : null,
      icon: Icon(Icons.keyboard_arrow_down_rounded,
          color: Colors.indigo.shade400),
      dropdownColor: Colors.white,
      borderRadius: BorderRadius.circular(16),
    );
  }

  String _getCategoryDisplay(String category) {
    if (_selectedType != null &&
        (_selectedType['name'] == 'Cuti Alasan Penting' ||
            _selectedType['name'] == 'Cuti Karena Alasan Penting')) {
      if (['Keluarga Inti Sakit Keras', 'Musibah Bencana', 'Faktor Kejiwaan']
          .contains(category)) {
        return '$category (Wajib Lampiran)';
      } else {
        return '$category (Opsional Lampiran)';
      }
    }
    return category;
  }

  Widget _buildCategoryDropdown() {
    return DropdownButtonFormField<String>(
      decoration: _getInputDecoration(_categoryLabel, Icons.list_rounded),
      initialValue: _selectedCategory,
      items: _categories.map((cat) {
        return DropdownMenuItem(
          value: cat,
          child: Text(_getCategoryDisplay(cat),
              overflow: TextOverflow.ellipsis,
              style: GoogleFonts.outfit(fontSize: 14)),
        );
      }).toList(),
      onChanged: _onCategoryChanged,
      validator: (val) => val == null ? 'Bagian ini wajib dipilih' : null,
      isExpanded: true,
      icon: Icon(Icons.keyboard_arrow_down_rounded,
          color: Colors.indigo.shade400),
    );
  }

  Widget _buildDateField(bool isStart) {
    return InkWell(
      onTap: () => _selectDate(context, isStart),
      child: IgnorePointer(
        child: TextFormField(
          decoration: _getInputDecoration(
              isStart ? 'Mulai' : 'Selesai', Icons.calendar_month_rounded),
          controller: TextEditingController(
            text: isStart
                ? (_startDate != null
                    ? DateFormat('dd/MM/yyyy').format(_startDate!)
                    : '')
                : (_endDate != null
                    ? DateFormat('dd/MM/yyyy').format(_endDate!)
                    : ''),
          ),
          validator: (val) => val!.isEmpty ? 'Wajib' : null,
        ),
      ),
    );
  }

  Widget _buildAttachmentSection() {
    return _buildCard([
      Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text('Lampiran Pendukung',
                    style: GoogleFonts.outfit(
                        fontWeight: FontWeight.bold,
                        color: Colors.indigo.shade900)),
                Text(
                    _attachmentRequired
                        ? '* Wajib dilampirkan (Surat Dokter/Kematian/Bukti RT)'
                        : 'Lampiran bersifat opsional (jika ada)',
                    style: GoogleFonts.outfit(
                        fontSize: 12,
                        color: _attachmentRequired ? Colors.red : Colors.grey)),
              ],
            ),
          ),
          ElevatedButton.icon(
            onPressed: _pickFile,
            icon: const Icon(Icons.upload_file_rounded, size: 18),
            label: const Text('PILIH'),
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.indigo.shade50,
              foregroundColor: Colors.indigo.shade700,
              elevation: 0,
              shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12)),
            ),
          ),
        ],
      ),
      if (_attachmentName != null) ...[
        const SizedBox(height: 12),
        Container(
          padding: const EdgeInsets.all(12),
          decoration: BoxDecoration(
            color: Colors.green.shade50,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: Colors.green.shade100),
          ),
          child: Row(
            children: [
              const Icon(Icons.check_circle_rounded,
                  color: Colors.green, size: 20),
              const SizedBox(width: 8),
              Expanded(
                child: Text(_attachmentName!,
                    style: GoogleFonts.outfit(
                        fontSize: 13, color: Colors.green.shade900)),
              ),
              IconButton(
                icon: const Icon(Icons.close, size: 18, color: Colors.red),
                onPressed: () => setState(() {
                  _attachmentPath = null;
                  _attachmentName = null;
                }),
              ),
            ],
          ),
        ),
      ],
    ]);
  }

  Widget _buildSubmitButton() {
    return SizedBox(
      width: double.infinity,
      height: 60,
      child: ElevatedButton(
        onPressed: _isLoading ? null : _submit,
        style: ElevatedButton.styleFrom(
          backgroundColor: Colors.indigo.shade600,
          foregroundColor: Colors.white,
          shape:
              RoundedRectangleBorder(borderRadius: BorderRadius.circular(18)),
          elevation: 4,
          shadowColor: Colors.indigo.shade200,
        ),
        child: _isLoading
            ? const CircularProgressIndicator(color: Colors.white)
            : Text(
                'KIRIM PENGAJUAN',
                style: GoogleFonts.outfit(
                  fontSize: 16,
                  fontWeight: FontWeight.w800,
                  letterSpacing: 1.2,
                ),
              ),
      ),
    );
  }

  InputDecoration _getInputDecoration(String label, IconData icon) {
    return InputDecoration(
      labelText: label,
      prefixIcon: Icon(icon, color: Colors.indigo.shade400, size: 22),
      labelStyle: GoogleFonts.outfit(color: Colors.grey.shade600, fontSize: 14),
      filled: true,
      fillColor: Colors.white,
      contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
      border: OutlineInputBorder(
        borderRadius: BorderRadius.circular(16),
        borderSide: BorderSide(color: Colors.grey.shade200),
      ),
      enabledBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(16),
        borderSide: BorderSide(color: Colors.grey.shade200),
      ),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(16),
        borderSide: BorderSide(color: Colors.indigo.shade400, width: 2),
      ),
      errorBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(16),
        borderSide: BorderSide(color: Colors.red.shade200),
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
      style: GoogleFonts.outfit(fontSize: 15),
      decoration: _getInputDecoration(label, icon),
      validator: (val) => val!.isEmpty ? 'Wajib diisi' : null,
    );
  }
}
