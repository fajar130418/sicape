import 'package:flutter/material.dart';
import '../services/api_service.dart';

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

  String? _selectedUserType;
  String? _selectedRank;
  String? _selectedEducation;

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
    final response = await _apiService.getProfile();
    if (response['status'] == 200 && response['data'] != null) {
      setState(() {
        _userProfile = response['data'];
        _nameController.text = _userProfile!['name'] ?? '';
        _emailController.text = _userProfile!['email'] ?? '';
        _phoneController.text = _userProfile!['phone'] ?? '';
        _addressController.text = _userProfile!['address'] ?? '';
        _pobController.text = _userProfile!['pob'] ?? '';
        _dobController.text = _userProfile!['dob'] ?? '';
        _positionController.text = _userProfile!['position'] ?? '';
        _unitController.text = _userProfile!['unit'] ?? '';
        _joinDateController.text = _userProfile!['join_date'] ?? '';
        // Dropdown state vars
        final edu = _userProfile!['education'];
        _selectedEducation = _educations.contains(edu) ? edu : null;
        final ut = _userProfile!['user_type'];
        _selectedUserType = _userTypes.contains(ut) ? ut : null;
        final rank = _userProfile!['rank'];
        // Rank list may not be loaded yet; defer validation
        _selectedRank = rank?.isNotEmpty == true ? rank : null;
        _isLoading = false;
      });
    } else {
      setState(() => _isLoading = false);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
              content: Text('Gagal memuat profil: ${response['message']}')),
        );
      }
    }
  }

  Future<void> _saveProfile() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() => _isSaving = true);

    Map<String, dynamic> updateData = {
      'name': _nameController.text,
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
      backgroundColor: Colors.grey.shade50,
      appBar: AppBar(
        title: const Text('Profil Saya',
            style: TextStyle(fontWeight: FontWeight.bold)),
        backgroundColor: Colors.white,
        foregroundColor: Colors.indigo.shade900,
        elevation: 0.5,
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : SingleChildScrollView(
              padding: const EdgeInsets.all(20),
              child: Form(
                key: _formKey,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Center(
                      child: Stack(
                        children: [
                          CircleAvatar(
                            radius: 50,
                            backgroundColor: Colors.indigo.shade100,
                            backgroundImage: _userProfile?['photo'] != null
                                ? NetworkImage(ApiService.baseUrl.replaceAll(
                                    '/api',
                                    '/uploads/photos/${_userProfile!['photo']}'))
                                : null,
                            child: _userProfile?['photo'] == null
                                ? Icon(Icons.person,
                                    size: 50, color: Colors.indigo.shade400)
                                : null,
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 30),
                    Container(
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(
                        color: Colors.indigo.shade50,
                        borderRadius: BorderRadius.circular(16),
                        border: Border.all(color: Colors.indigo.shade100),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              Icon(Icons.info_outline,
                                  color: Colors.indigo.shade700, size: 20),
                              const SizedBox(width: 8),
                              Text('Informasi Jabatan (Hanya Admin)',
                                  style: TextStyle(
                                      fontWeight: FontWeight.bold,
                                      color: Colors.indigo.shade900)),
                            ],
                          ),
                          const Divider(height: 24),
                          _buildReadOnlyField(
                              'NIP', _userProfile?['nip'] ?? '-'),
                          const SizedBox(height: 12),
                          _buildReadOnlyField(
                              'Hak Akses (Role)', _userProfile?['role'] ?? '-'),
                          const SizedBox(height: 12),
                          _buildReadOnlyField('Saldo Cuti N',
                              '${_userProfile?['leave_balance_n'] ?? 0} Hari'),
                          const SizedBox(height: 12),
                          _buildReadOnlyField('Tanggal Masuk (TMT)',
                              _userProfile?['join_date'] ?? '-'),
                        ],
                      ),
                    ),
                    const SizedBox(height: 30),
                    Text('Data Pribadi (Dapat Diubah)',
                        style: TextStyle(
                            fontSize: 18,
                            fontWeight: FontWeight.bold,
                            color: Colors.indigo.shade900)),
                    const SizedBox(height: 16),
                    _buildTextField(
                        _nameController, 'Nama Lengkap', Icons.person_outline),
                    const SizedBox(height: 16),
                    _buildTextField(
                        _emailController, 'Email', Icons.email_outlined),
                    const SizedBox(height: 16),
                    _buildTextField(
                        _phoneController, 'Nomor HP', Icons.phone_outlined),
                    const SizedBox(height: 16),
                    Row(
                      children: [
                        Expanded(
                            child: _buildTextField(_pobController,
                                'Tempat Lahir', Icons.location_city_outlined)),
                        const SizedBox(width: 16),
                        Expanded(
                            child: _buildTextField(
                                _dobController,
                                'Tgl Lahir (YYYY-MM-DD)',
                                Icons.calendar_today_outlined)),
                      ],
                    ),
                    const SizedBox(height: 16),
                    _buildTextField(_addressController, 'Alamat Lengkap',
                        Icons.home_outlined,
                        maxLines: 3),
                    const SizedBox(height: 16),
                    _buildDropdown(
                      'Pendidikan Terakhir',
                      Icons.school_outlined,
                      _educations,
                      _selectedEducation,
                      (val) => setState(() => _selectedEducation = val),
                    ),
                    const SizedBox(height: 30),
                    Text('Data Kepegawaian',
                        style: TextStyle(
                            fontSize: 18,
                            fontWeight: FontWeight.bold,
                            color: Colors.indigo.shade900)),
                    const SizedBox(height: 16),
                    _buildTextField(
                        _positionController, 'Jabatan', Icons.work_outline),
                    const SizedBox(height: 16),
                    Row(
                      children: [
                        Expanded(
                            child: _buildTextField(_unitController,
                                'Unit Kerja', Icons.business_outlined)),
                        const SizedBox(width: 16),
                        Expanded(
                          child: _buildDropdown(
                            'Status Pegawai',
                            Icons.badge_outlined,
                            _userTypes,
                            _selectedUserType,
                            (val) => setState(() {
                              _selectedUserType = val;
                              // reset rank when type changes
                              if (!_currentRanks.contains(_selectedRank)) {
                                _selectedRank = null;
                              }
                            }),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 16),
                    _buildDropdown(
                      'Pangkat/Golongan',
                      Icons.star_border,
                      _currentRanks,
                      _currentRanks.contains(_selectedRank)
                          ? _selectedRank
                          : null,
                      (val) => setState(() => _selectedRank = val),
                    ),
                    const SizedBox(height: 30),
                    Text('Keamanan',
                        style: TextStyle(
                            fontSize: 18,
                            fontWeight: FontWeight.bold,
                            color: Colors.indigo.shade900)),
                    const SizedBox(height: 16),
                    _buildTextField(_passwordController,
                        'Password Baru (Opsional)', Icons.lock_outline,
                        isPassword: true),
                    const SizedBox(height: 40),
                    SizedBox(
                      width: double.infinity,
                      height: 56,
                      child: ElevatedButton(
                        onPressed: _isSaving ? null : _saveProfile,
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Colors.indigo.shade600,
                          foregroundColor: Colors.white,
                          shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(16)),
                          elevation: 2,
                        ),
                        child: _isSaving
                            ? const CircularProgressIndicator(
                                color: Colors.white)
                            : const Text('SIMPAN PROFIL',
                                style: TextStyle(
                                    fontSize: 16,
                                    fontWeight: FontWeight.bold,
                                    letterSpacing: 1.2)),
                      ),
                    ),
                    const SizedBox(height: 40),
                  ],
                ),
              ),
            ),
    );
  }

  Widget _buildReadOnlyField(String label, String value) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(label,
            style: TextStyle(
                fontSize: 12,
                color: Colors.indigo.shade400,
                fontWeight: FontWeight.w600)),
        const SizedBox(height: 4),
        Text(value,
            style: TextStyle(
                fontSize: 15,
                color: Colors.indigo.shade900,
                fontWeight: FontWeight.bold)),
      ],
    );
  }

  Widget _buildTextField(
      TextEditingController controller, String label, IconData icon,
      {bool isPassword = false, int maxLines = 1}) {
    return TextFormField(
      controller: controller,
      obscureText: isPassword,
      maxLines: maxLines,
      decoration: InputDecoration(
        labelText: label,
        prefixIcon: Padding(
          padding:
              EdgeInsets.only(bottom: maxLines > 1 ? (maxLines * 10.0) : 0),
          child: Icon(icon, color: Colors.indigo.shade400),
        ),
        filled: true,
        fillColor: Colors.white,
        border: OutlineInputBorder(
            borderRadius: BorderRadius.circular(16),
            borderSide: BorderSide.none),
        enabledBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(16),
            borderSide: BorderSide(color: Colors.grey.shade200)),
        focusedBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(16),
            borderSide: BorderSide(color: Colors.indigo.shade400, width: 2)),
      ),
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
      decoration: InputDecoration(
        labelText: label,
        prefixIcon: Icon(icon, color: Colors.indigo.shade400),
        filled: true,
        fillColor: Colors.white,
        border: OutlineInputBorder(
            borderRadius: BorderRadius.circular(16),
            borderSide: BorderSide.none),
        enabledBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(16),
            borderSide: BorderSide(color: Colors.grey.shade200)),
        focusedBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(16),
            borderSide: BorderSide(color: Colors.indigo.shade400, width: 2)),
      ),
      items: items
          .map((e) => DropdownMenuItem(
              value: e, child: Text(e, overflow: TextOverflow.ellipsis)))
          .toList(),
    );
  }
}
