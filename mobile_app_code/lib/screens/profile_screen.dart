import 'package:flutter/material.dart';
import '../services/api_service.dart';
import 'package:google_fonts/google_fonts.dart';

class ProfileScreen extends StatefulWidget {
  const ProfileScreen({super.key});

  @override
  _ProfileScreenState createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
  final ApiService _apiService = ApiService();
  final _formKey = GlobalKey<FormState>();

  bool _isLoading = true;
  bool _isSaving = false;

  final TextEditingController _nameController = TextEditingController();
  final TextEditingController _emailController = TextEditingController();
  final TextEditingController _phoneController = TextEditingController();
  final TextEditingController _addressController = TextEditingController();
  final TextEditingController _pobController = TextEditingController();
  final TextEditingController _dobController = TextEditingController();
  final TextEditingController _passwordController = TextEditingController();
  final TextEditingController _positionController = TextEditingController();
  final TextEditingController _unitController = TextEditingController();
  final TextEditingController _joinDateController = TextEditingController();

  final TextEditingController _frontTitleController = TextEditingController();
  final TextEditingController _backTitleController = TextEditingController();
  final TextEditingController _nipController = TextEditingController();
  final TextEditingController _nikController = TextEditingController();
  final TextEditingController _contractEndDateController =
      TextEditingController();

  String? _selectedUserType;
  String? _selectedRank;
  String? _selectedEducation;
  String? _selectedGender;

  static const _genders = [
    {"value": "L", "label": "Laki-laki"},
    {"value": "P", "label": "Perempuan"},
  ];

  static const _educations = [
    "SD / Sederajat",
    "SMP / Sederajat",
    "SMA / Sederajat",
    "DI",
    "DII",
    "DIII",
    "DIV / S1",
    "S2",
    "S3"
  ];
  static const _userTypes = ["PNS", "PPPK", "PPPK Paruh Waktu"];
  static const _pnsRanks = [
    "Juru Muda (I/a)",
    "Juru Muda Tingkat I (I/b)",
    "Juru (I/c)",
    "Juru Tingkat I (I/d)",
    "Pengatur Muda (II/a)",
    "Pengatur Muda Tingkat I (II/b)",
    "Pengatur (II/c)",
    "Pengatur Tingkat I (II/d)",
    "Penata Muda (III/a)",
    "Penata Muda Tingkat I (III/b)",
    "Penata (III/c)",
    "Penata Tingkat I (III/d)",
    "Pembina (IV/a)",
    "Pembina Tingkat I (IV/b)",
    "Pembina Utama Muda (IV/c)",
    "Pembina Utama Madya (IV/d)",
    "Pembina Utama (IV/e)",
  ];
  static const _pppkRanks = [
    "Golongan I",
    "Golongan II",
    "Golongan III",
    "Golongan IV",
    "Golongan V",
    "Golongan VI",
    "Golongan VII",
    "Golongan VIII",
    "Golongan IX",
    "Golongan X",
    "Golongan XI",
    "Golongan XII",
    "Golongan XIII",
    "Golongan XIV",
    "Golongan XV",
    "Golongan XVI",
    "Golongan XVII",
  ];

  List<String> get _currentRanks =>
      (_selectedUserType == 'PNS') ? _pnsRanks : _pppkRanks;

  Map<String, dynamic>? _userProfile;

  @override
  void initState() {
    super.initState();
    _loadProfile();
  }

  Future<void> _loadProfile() async {
    try {
      final response = await _apiService.getProfile();
      if (response['status'] == 200 && response['data'] != null) {
        final data = response['data'];
        setState(() {
          _userProfile = data is Map<String, dynamic> ? data : null;
          if (_userProfile != null) {
            _nameController.text = _userProfile!['name']?.toString() ?? '';
            _nipController.text = _userProfile!['nip']?.toString() ?? '';
            _frontTitleController.text =
                _userProfile!['front_title']?.toString() ?? '';
            _backTitleController.text =
                _userProfile!['back_title']?.toString() ?? '';
            _nikController.text = _userProfile!['nik']?.toString() ?? '';
            _emailController.text = _userProfile!['email']?.toString() ?? '';
            _phoneController.text = _userProfile!['phone']?.toString() ?? '';
            _addressController.text =
                _userProfile!['address']?.toString() ?? '';
            _pobController.text = _userProfile!['pob']?.toString() ?? '';
            _dobController.text = _userProfile!['dob']?.toString() ?? '';
            _positionController.text =
                _userProfile!['position']?.toString() ?? '';
            _unitController.text = _userProfile!['unit']?.toString() ?? '';
            _joinDateController.text =
                _userProfile!['join_date']?.toString() ?? '';
            _contractEndDateController.text =
                _userProfile!['contract_end_date']?.toString() ?? '';

            // Dropdown state vars with safety
            final edu = _userProfile!['education']?.toString();
            _selectedEducation = _educations.contains(edu) ? edu : null;

            final ut = _userProfile!['user_type']?.toString();
            _selectedUserType = _userTypes.contains(ut) ? ut : null;

            final rank = _userProfile!['rank']?.toString();
            _selectedRank = (rank != null && rank.isNotEmpty) ? rank : null;

            final g = _userProfile!['gender']?.toString();
            _selectedGender = (g == 'L' || g == 'P') ? g : null;
          }
          _isLoading = false;
        });
      } else {
        setState(() => _isLoading = false);
        _showError('Gagal memuat profil: ${response['message'] ?? 'Error'}');
      }
    } catch (e) {
      setState(() => _isLoading = false);
      _showError('Terjadi kesalahan: $e');
    }
  }

  void _showError(String msg) {
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(msg), backgroundColor: Colors.red),
    );
  }

  Future<void> _saveProfile() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() => _isSaving = true);

    Map<String, dynamic> updateData = {
      'name': _nameController.text,
      'front_title': _frontTitleController.text,
      'back_title': _backTitleController.text,
      'nik': _nikController.text,
      'gender': _selectedGender ?? '',
      'email': _emailController.text,
      'phone': _phoneController.text,
      'address': _addressController.text,
      'pob': _pobController.text,
      'dob': _dobController.text,
      'position': _positionController.text,
      'unit': _unitController.text,
      'rank': _selectedRank ?? '',
      'user_type': _selectedUserType ?? '',
      'education': _selectedEducation ?? '',
      'contract_end_date': _contractEndDateController.text,
    };

    if (_passwordController.text.isNotEmpty) {
      updateData['password'] = _passwordController.text;
    }

    final response = await _apiService.updateProfile(updateData);
    setState(() => _isSaving = false);

    if (response['status'] == 200) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
              content: Text('Profil berhasil diperbarui',
                  style: TextStyle(color: Colors.white)),
              backgroundColor: Colors.green),
        );
        _passwordController.clear();
      }
    } else {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Gagal memperbarui: ${response['message']}')),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: Text('Profil Saya',
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
      body: RefreshIndicator(
        onRefresh: _loadProfile,
        color: Colors.indigo,
        child: _isLoading && _userProfile == null
            ? const Center(child: CircularProgressIndicator())
            : SingleChildScrollView(
                physics: const AlwaysScrollableScrollPhysics(),
                padding:
                    const EdgeInsets.symmetric(horizontal: 20, vertical: 24),
                child: Form(
                  key: _formKey,
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      if (_isLoading)
                        const LinearProgressIndicator(minHeight: 2),
                      // Header Card (Photo & NIP)
                      _buildHeaderCard(),
                      const SizedBox(height: 24),

                      // Section: Informasi Jabatan
                      _buildSectionHeader(
                          'Informasi Jabatan',
                          Icons.shield_rounded,
                          'Hanya dapat diubah oleh Admin'),
                      _buildInfoJabatanCard(),
                      const SizedBox(height: 32),

                      // Section: Data Pribadi
                      _buildSectionHeader('Data Pribadi', Icons.person_rounded,
                          'Dapat diubah oleh Anda'),
                      _buildDataPribadiCard(),
                      const SizedBox(height: 32),

                      // Section: Data Kepegawaian
                      _buildSectionHeader(
                          'Data Kepegawaian', Icons.badge_rounded),
                      _buildDataKepegawaianCard(),
                      const SizedBox(height: 32),

                      // Section: Kontak & Keamanan
                      _buildSectionHeader(
                          'Kontak & Keamanan', Icons.security_rounded),
                      _buildKeamananCard(),

                      const SizedBox(height: 48),
                      _buildSaveButton(),
                      const SizedBox(height: 60),
                    ],
                  ),
                ),
              ),
      ),
    );
  }

  Widget _buildSectionHeader(String title, IconData icon, [String? hint]) {
    return Padding(
      padding: const EdgeInsets.only(left: 4, bottom: 16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(icon, size: 20, color: Colors.indigo.shade700),
              const SizedBox(width: 8),
              Text(
                title.toUpperCase(),
                style: GoogleFonts.outfit(
                  fontSize: 14,
                  fontWeight: FontWeight.w900,
                  color: Colors.indigo.shade900,
                  letterSpacing: 1,
                ),
              ),
            ],
          ),
          if (hint != null) ...[
            const SizedBox(height: 4),
            Text(
              hint,
              style: GoogleFonts.outfit(
                fontSize: 12,
                color: Colors.grey.shade500,
                fontWeight: FontWeight.w500,
              ),
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildHeaderCard() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(24),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(24),
        border: Border.all(color: Colors.grey.shade100),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.03),
            blurRadius: 20,
            offset: const Offset(0, 10),
          ),
        ],
      ),
      child: Column(
        children: [
          Stack(
            children: [
              Container(
                padding: const EdgeInsets.all(4),
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  border: Border.all(color: Colors.indigo.shade500, width: 3),
                ),
                child: CircleAvatar(
                  radius: 50,
                  backgroundColor: Colors.indigo.shade50,
                  backgroundImage: _userProfile?['photo'] != null
                      ? NetworkImage(_userProfile!['photo'])
                      : null,
                  child: _userProfile?['photo'] == null
                      ? Icon(Icons.person_rounded,
                          size: 50, color: Colors.indigo.shade400)
                      : null,
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),
          Text(
            _userProfile?['name'] ?? 'User Name',
            style: GoogleFonts.outfit(
              fontSize: 20,
              fontWeight: FontWeight.bold,
              color: Colors.indigo.shade900,
            ),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 4),
          Text(
            _userProfile?['nip'] ?? '-',
            style: GoogleFonts.outfit(
              fontSize: 14,
              color: Colors.grey.shade600,
              fontWeight: FontWeight.w500,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildInfoJabatanCard() {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: const Color(0xFFF1F5F9), // Slate 100
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFFE2E8F0)),
      ),
      child: Column(
        children: [
          _buildReadOnlyRow(
              'Hak Akses (Role)', _userProfile?['role']?.toUpperCase() ?? '-'),
          const Divider(height: 24),
          _buildReadOnlyRow('Saldo Cuti N (Tahun Ini)',
              '${_userProfile?['leave_balance_n'] ?? 0} Hari',
              isBadge: true),
          const Divider(height: 24),
          _buildReadOnlyRow(
              'Tanggal Masuk (TMT)', _userProfile?['join_date'] ?? '-'),
        ],
      ),
    );
  }

  Widget _buildDataPribadiCard() {
    return _buildFormCard([
      _buildTextField(_nameController, 'Nama Lengkap', Icons.person_outline),
      const SizedBox(height: 20),
      Row(
        children: [
          Expanded(
            child: _buildTextField(
                _frontTitleController, 'Gelar Depan', Icons.title_rounded,
                hint: 'Misal: Drs., Ir.'),
          ),
          const SizedBox(width: 16),
          Expanded(
            child: _buildTextField(
                _backTitleController, 'Gelar Belakang', Icons.school_outlined,
                hint: 'Misal: S.Kom'),
          ),
        ],
      ),
      const SizedBox(height: 20),
      _buildTextField(
          _nikController, 'NIK (No. Induk Kependudukan)', Icons.badge_outlined,
          keyboardType: TextInputType.number),
      const SizedBox(height: 20),
      _buildGenderDropdown(),
      const SizedBox(height: 20),
      Row(
        children: [
          Expanded(
            child: _buildTextField(
                _pobController, 'Tempat Lahir', Icons.location_city_outlined),
          ),
          const SizedBox(width: 16),
          Expanded(
            child: _buildTextField(
                _dobController, 'Tgl Lahir', Icons.calendar_today_outlined,
                hint: 'YYYY-MM-DD'),
          ),
        ],
      ),
      const SizedBox(height: 20),
      _buildDropdown(
        'Pendidikan Terakhir',
        Icons.school_outlined,
        _educations,
        _selectedEducation,
        (val) => setState(() => _selectedEducation = val),
      ),
      const SizedBox(height: 20),
      _buildTextField(_addressController, 'Alamat Lengkap', Icons.home_outlined,
          maxLines: 3),
    ]);
  }

  Widget _buildDataKepegawaianCard() {
    return _buildFormCard([
      _buildTextField(_positionController, 'Jabatan', Icons.work_outline),
      const SizedBox(height: 20),
      _buildTextField(_unitController, 'Unit Kerja', Icons.business_outlined),
      const SizedBox(height: 20),
      _buildDropdown(
        'Status Pegawai',
        Icons.badge_outlined,
        _userTypes,
        _selectedUserType,
        (val) => setState(() {
          _selectedUserType = val;
          if (!_currentRanks.contains(_selectedRank)) {
            _selectedRank = null;
          }
        }),
      ),
      const SizedBox(height: 20),
      _buildDropdown(
        'Pangkat/Golongan',
        Icons.military_tech_outlined,
        _currentRanks,
        _currentRanks.contains(_selectedRank) ? _selectedRank : null,
        (val) => setState(() => _selectedRank = val),
      ),
      if (_selectedUserType?.contains('PPPK') ?? false) ...[
        const SizedBox(height: 20),
        _buildTextField(_contractEndDateController, 'Tgl Berakhir Kontrak',
            Icons.event_busy_outlined,
            hint: 'YYYY-MM-DD'),
      ],
    ]);
  }

  Widget _buildKeamananCard() {
    return _buildFormCard([
      _buildTextField(_phoneController, 'Nomor WhatsApp', Icons.phone_outlined),
      const SizedBox(height: 20),
      _buildTextField(_emailController, 'Email Aktif', Icons.email_outlined),
      const SizedBox(height: 20),
      _buildTextField(_passwordController, 'Password Baru (Opsional)',
          Icons.lock_open_rounded,
          isPassword: true, hint: 'Minimal 6 karakter'),
    ]);
  }

  Widget _buildFormCard(List<Widget> children) {
    return Container(
      padding: const EdgeInsets.all(24),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(24),
        border: Border.all(color: Colors.grey.shade100),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.02),
            blurRadius: 15,
            offset: const Offset(0, 5),
          ),
        ],
      ),
      child: Column(children: children),
    );
  }

  Widget _buildReadOnlyRow(String label, String value, {bool isBadge = false}) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(
          label,
          style: GoogleFonts.outfit(
            fontSize: 13,
            color: Colors.grey.shade600,
            fontWeight: FontWeight.w500,
          ),
        ),
        if (isBadge)
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
            decoration: BoxDecoration(
              color: Colors.indigo.shade600,
              borderRadius: BorderRadius.circular(12),
            ),
            child: Text(
              value,
              style: GoogleFonts.outfit(
                fontSize: 12,
                color: Colors.white,
                fontWeight: FontWeight.bold,
              ),
            ),
          )
        else
          Text(
            value,
            style: GoogleFonts.outfit(
              fontSize: 14,
              color: Colors.indigo.shade900,
              fontWeight: FontWeight.bold,
            ),
          ),
      ],
    );
  }

  Widget _buildGenderDropdown() {
    return DropdownButtonFormField<String>(
      initialValue: _selectedGender,
      onChanged: (val) => setState(() => _selectedGender = val),
      decoration: _getInputDecoration('Jenis Kelamin', Icons.wc_rounded),
      items: _genders
          .map((e) => DropdownMenuItem(
                value: e['value'],
                child:
                    Text(e['label']!, style: GoogleFonts.outfit(fontSize: 15)),
              ))
          .toList(),
    );
  }

  Widget _buildSaveButton() {
    return SizedBox(
      width: double.infinity,
      height: 60,
      child: ElevatedButton(
        onPressed: _isSaving ? null : _saveProfile,
        style: ElevatedButton.styleFrom(
          backgroundColor: Colors.indigo.shade600,
          foregroundColor: Colors.white,
          shape:
              RoundedRectangleBorder(borderRadius: BorderRadius.circular(18)),
          elevation: 4,
          shadowColor: Colors.indigo.shade200,
        ),
        child: _isSaving
            ? const CircularProgressIndicator(color: Colors.white)
            : Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Icon(Icons.save_rounded, size: 22),
                  const SizedBox(width: 12),
                  Text(
                    'SIMPAN PERUBAHAN',
                    style: GoogleFonts.outfit(
                      fontSize: 16,
                      fontWeight: FontWeight.w800,
                      letterSpacing: 1,
                    ),
                  ),
                ],
              ),
      ),
    );
  }

  InputDecoration _getInputDecoration(String label, IconData icon,
      {String? hint}) {
    return InputDecoration(
      labelText: label,
      hintText: hint,
      prefixIcon: Icon(icon, color: Colors.indigo.shade400, size: 22),
      labelStyle: GoogleFonts.outfit(color: Colors.grey.shade600, fontSize: 14),
      hintStyle: GoogleFonts.outfit(color: Colors.grey.shade400, fontSize: 13),
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

  Widget _buildTextField(
    TextEditingController controller,
    String label,
    IconData icon, {
    bool isPassword = false,
    int maxLines = 1,
    String? hint,
    TextInputType? keyboardType,
  }) {
    return TextFormField(
      controller: controller,
      obscureText: isPassword,
      maxLines: maxLines,
      keyboardType: keyboardType,
      style: GoogleFonts.outfit(fontSize: 15),
      decoration: _getInputDecoration(label, icon, hint: hint),
    );
  }

  Widget _buildDropdown(
    String label,
    IconData icon,
    List<String> items,
    String? value,
    void Function(String?) onChanged,
  ) {
    return DropdownButtonFormField<String>(
      initialValue: items.contains(value) ? value : null,
      onChanged: onChanged,
      isExpanded: true,
      decoration: _getInputDecoration(label, icon),
      items: items
          .map((e) => DropdownMenuItem(
                value: e,
                child: Text(e,
                    overflow: TextOverflow.ellipsis,
                    style: GoogleFonts.outfit(fontSize: 15)),
              ))
          .toList(),
    );
  }
}
