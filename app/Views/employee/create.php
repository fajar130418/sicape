<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>Tambah Pegawai<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header">
    <h1 class="page-title">Tambah Pegawai Baru</h1>
    <a href="<?= base_url('employee') ?>" class="btn btn-secondary">
        <i class="fas fa-arrow-left" style="margin-right: 8px;"></i> Kembali
    </a>
</div>

<div class="card">
    <form action="<?= base_url('employee/store') ?>" method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <div class="form-grid">
            <!-- HEADER: DATA PRIBADI -->
            <div class="section-separator">
                <i class="fas fa-user"></i>
                <h3>Data Pribadi</h3>
            </div>

            <!-- ROW 1: NIP & NIK -->
            <div class="form-group col-span-6">
                <label class="form-label">NIP <span class="text-danger">*</span></label>
                <input type="text" name="nip" class="form-control" value="<?= old('nip') ?>" required
                    placeholder="Masukkan NIP (18 digit)">
            </div>

            <div class="form-group col-span-6">
                <label class="form-label">NIK <span class="text-danger">*</span></label>
                <input type="text" name="nik" class="form-control" value="<?= old('nik') ?>" required
                    placeholder="Masukkan NIK (16 digit)">
            </div>

            <!-- ROW 2: NAMA LENGKAP -->
            <div class="form-group col-span-12">
                <label class="form-label">Nama Lengkap (Tanpa Gelar) <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" value="<?= old('name') ?>" required
                    placeholder="Contoh: Budi Santoso">
            </div>

            <!-- ROW 3: GELAR DEPAN & BELAKANG -->
            <div class="form-group col-span-6">
                <label class="form-label">Gelar Depan</label>
                <input type="text" name="front_title" class="form-control" value="<?= old('front_title') ?>"
                    placeholder="Contoh: Dr., Ir., H.">
            </div>

            <div class="form-group col-span-6">
                <label class="form-label">Gelar Belakang</label>
                <input type="text" name="back_title" class="form-control" value="<?= old('back_title') ?>"
                    placeholder="Contoh: S.Kom, M.M.">
            </div>

            <!-- ROW 4: TEMPAT & TANGGAL LAHIR -->
            <div class="form-group col-span-6">
                <label class="form-label">Tempat Lahir <span class="text-danger">*</span></label>
                <input type="text" name="pob" class="form-control" value="<?= old('pob') ?>" required
                    placeholder="Kota Kelahiran">
            </div>

            <div class="form-group col-span-6">
                <label class="form-label">Tanggal Lahir <span class="text-danger">*</span></label>
                <input type="date" name="dob" class="form-control" value="<?= old('dob') ?>" required>
            </div>

            <!-- ROW 5: JENIS KELAMIN & PENDIDIKAN -->
            <div class="form-group col-span-6">
                <label class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                <select name="gender" class="form-control" required>
                    <option value="">-- Pilih Jenis Kelamin --</option>
                    <option value="L" <?= old('gender') == 'L' ? 'selected' : '' ?>>Laki-laki</option>
                    <option value="P" <?= old('gender') == 'P' ? 'selected' : '' ?>>Perempuan</option>
                </select>
            </div>

            <div class="form-group col-span-6">
                <label class="form-label">Pendidikan Terakhir <span class="text-danger">*</span></label>
                <select name="education" class="form-control" required>
                    <option value="">-- Pilih Pendidikan --</option>
                    <?php
                    $educations = ["SD / Sederajat", "SMP / Sederajat", "SMA / Sederajat", "DI", "DII", "DIII", "DIV / S1", "S2", "S3"];
                    foreach ($educations as $edu): ?>
                        <option value="<?= $edu ?>" <?= old('education') == $edu ? 'selected' : '' ?>><?= $edu ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- ROW 6: ALAMAT -->
            <div class="form-group col-span-12">
                <label class="form-label">Alamat Lengkap</label>
                <textarea name="address" class="form-control" rows="3"
                    placeholder="Masukkan alamat lengkap domisili saat ini"><?= old('address') ?></textarea>
            </div>

            <!-- ROW 7: KONTAK -->
            <div class="form-group col-span-6">
                <label class="form-label">No. Telepon/HP (WhatsApp)</label>
                <input type="text" name="phone" class="form-control" value="<?= old('phone') ?>"
                    placeholder="08xxxxxxxxxx">
            </div>

            <div class="form-group col-span-6">
                <label class="form-label">Foto Profil</label>
                <input type="file" name="photo" class="form-control" accept="image/*">
                <div class="form-hint">Format: JPG, JPEG, PNG. Maksimal 2MB.</div>
            </div>

            <!-- ROW 8: PASSWORD -->
            <div class="form-group col-span-6">
                <label class="form-label">Password Login <span class="text-danger">*</span></label>
                <input type="password" name="password" class="form-control" required placeholder="Minimal 6 karakter">
            </div>


            <!-- HEADER: DATA KEPEGAWAIAN -->
            <div class="section-separator" style="margin-top: 1.5rem;">
                <i class="fas fa-briefcase"></i>
                <h3>Data Kepegawaian</h3>
            </div>

            <!-- ROW 9: PANGKAT & JABATAN -->
            <div class="form-group col-span-6">
                <label class="form-label">Pangkat / Golongan <span class="text-danger">*</span></label>
                <select name="rank" class="form-control" required>
                    <option value="">-- Pilih Pangkat/Golongan --</option>
                    <?php
                    $ranks = [
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
                        "Pembina Utama (IV/e)"
                    ];
                    foreach ($ranks as $r): ?>
                        <option value="<?= $r ?>" <?= old('rank') == $r ? 'selected' : '' ?>><?= $r ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group col-span-6">
                <label class="form-label">Jabatan</label>
                <input type="text" name="position" class="form-control" value="<?= old('position') ?>"
                    placeholder="Contoh: Staf Teknis / Kepala Bidang">
            </div>

            <!-- ROW 10: UNIT & TMT -->
            <div class="form-group col-span-6">
                <label class="form-label">Unit Kerja</label>
                <input type="text" name="unit" class="form-control" value="<?= old('unit') ?>"
                    placeholder="Contoh: Bidang Pelayanan">
            </div>

            <div class="form-group col-span-6">
                <label class="form-label">Tanggal Masuk (TMT)</label>
                <input type="date" name="join_date" class="form-control" value="<?= old('join_date') ?>">
            </div>

            <!-- ROW 11: ROLE & STATUS -->
            <div class="form-group col-span-6">
                <label class="form-label">Hak Akses (Role) <span class="text-danger">*</span></label>
                <select name="role" class="form-control" required>
                    <option value="pegawai" <?= old('role') == 'pegawai' ? 'selected' : '' ?>>Pegawai Biasa</option>
                    <option value="admin" <?= old('role') == 'admin' ? 'selected' : '' ?>>Administrator</option>
                </select>
                <div class="form-hint">Admin memiliki akses penuh ke sistem.</div>
            </div>

            <div class="form-group col-span-6">
                <label class="form-label">Status Pegawai <span class="text-danger">*</span></label>
                <select name="user_type" id="user_type" class="form-control" required>
                    <option value="PNS" <?= old('user_type') == 'PNS' ? 'selected' : '' ?>>PNS</option>
                    <option value="PPPK" <?= old('user_type') == 'PPPK' ? 'selected' : '' ?>>PPPK</option>
                    <option value="PPPK Paruh Waktu" <?= old('user_type') == 'PPPK Paruh Waktu' ? 'selected' : '' ?>>PPPK
                        Paruh Waktu</option>
                </select>
                <div class="form-hint">Menentukan hak jenis cuti (Cuti Besar hanya untuk PNS).</div>
            </div>

            <div class="form-group col-span-12" id="contract_field" style="display: none;">
                <label class="form-label">Tanggal Berakhir Kontrak (Hanya untuk PPPK)</label>
                <input type="date" name="contract_end_date" class="form-control"
                    value="<?= old('contract_end_date') ?>">
                <div class="form-hint">Diperlukan untuk validasi durasi Cuti Sakit PPPK.</div>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const userTypeSelect = document.getElementById('user_type');
                    const contractField = document.getElementById('contract_field');

                    function toggleContractField() {
                        if (userTypeSelect.value && userTypeSelect.value.includes('PPPK')) {
                            contractField.style.display = 'block';
                        } else {
                            contractField.style.display = 'none';
                        }
                    }

                    userTypeSelect.addEventListener('change', toggleContractField);
                    toggleContractField(); // Initial check
                });
            </script>

            <!-- ROW 12: ATASAN -->
            <div class="form-group col-span-12">
                <label class="form-label">Atasan Langsung (Untuk Persetujuan Cuti)</label>
                <select name="supervisor_id" class="form-control">
                    <option value="">-- Tidak Ada / Pilih Nanti --</option>
                    <?php foreach ($potential_supervisors as $user): ?>
                        <?php if ($user['role'] == 'admin' || $user['id'] != session()->get('id')): ?>
                            <option value="<?= $user['id'] ?>" <?= old('supervisor_id') == $user['id'] ? 'selected' : '' ?>>
                                <?= $user['name'] ?> (<?= $user['nip'] ?>) - <?= $user['position'] ?>
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
                <div class="form-hint">Pilih pegawai yang berwenang menyetujui cuti pegawai ini.</div>
            </div>

            <div class="form-group col-span-12">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <input type="checkbox" name="is_supervisor" value="1" <?= old('is_supervisor') ? 'checked' : '' ?>
                        style="width: 1.25rem; height: 1.25rem;">
                    <span>Tandai sebagai Atasan / Pejabat (Bisa menyetujui cuti bawahan)</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.5rem; margin-top: 0.5rem;">
                    <input type="checkbox" name="is_head_of_agency" value="1" <?= old('is_head_of_agency') ? 'checked' : '' ?> style="width: 1.25rem; height: 1.25rem;">
                    <span>Tandai sebagai Kepala Dinas (Penanda Tangan Akhir)</span>
                </div>
            </div>

            <!-- HEADER: SALDO CUTI AWAL (MIGRASI) -->
            <div class="section-separator" style="margin-top: 1.5rem;">
                <i class="fas fa-history"></i>
                <h3>Saldo Cuti Awal (Migrasi)</h3>
            </div>

            <div class="form-group col-span-4">
                <label class="form-label">Sisa Cuti N (<?= date('Y') ?>)</label>
                <input type="number" name="leave_balance_n" class="form-control"
                    value="<?= old('leave_balance_n', 12) ?>" min="0" max="12">
                <div class="form-hint">Jatah awal tahun berjalan (Max 12).</div>
            </div>

            <div class="form-group col-span-4">
                <label class="form-label">Sisa Cuti N-1 (<?= date('Y') - 1 ?>)</label>
                <input type="number" name="leave_balance_n1" class="form-control"
                    value="<?= old('leave_balance_n1', 0) ?>" min="0" max="6">
                <div class="form-hint">Maksimal 6 hari (Aturan ASN).</div>
            </div>

            <div class="form-group col-span-4">
                <label class="form-label">Sisa Cuti N-2 (<?= date('Y') - 2 ?>)</label>
                <input type="number" name="leave_balance_n2" class="form-control"
                    value="<?= old('leave_balance_n2', 0) ?>" min="0" max="6">
                <div class="form-hint">Maksimal 6 hari (Aturan ASN).</div>
            </div>

            <!-- HEADER: PENYESUAIAN MASA KERJA -->
            <div class="section-separator" style="margin-top: 1.5rem;">
                <i class="fas fa-business-time"></i>
                <h3>Penyesuaian Masa Kerja (MKG)</h3>
            </div>

            <div class="form-group col-span-6">
                <label class="form-label">Masa Kerja Tambahan (PMK) - Tahun</label>
                <input type="number" name="mkg_additional_years" class="form-control"
                    value="<?= old('mkg_additional_years', 0) ?>" min="0">
                <div class="form-hint">Pengakuan masa kerja sebelumnya (Honorer/Swasta).</div>
            </div>

            <div class="form-group col-span-6">
                <label class="form-label">Masa Kerja Tambahan (PMK) - Bulan</label>
                <input type="number" name="mkg_additional_months" class="form-control"
                    value="<?= old('mkg_additional_months', 0) ?>" min="0" max="11">
            </div>

            <div class="form-group col-span-6">
                <label class="form-label">Penyesuaian Gelar/Ijazah - Tahun</label>
                <input type="number" name="mkg_adjustment_years" class="form-control"
                    value="<?= old('mkg_adjustment_years', 0) ?>">
                <div class="form-hint">Gunakan angka negatif (misal: -2) untuk pengurangan.</div>
            </div>

            <div class="form-group col-span-6">
                <label class="form-label">Penyesuaian Gelar/Ijazah - Bulan</label>
                <input type="number" name="mkg_adjustment_months" class="form-control"
                    value="<?= old('mkg_adjustment_months', 0) ?>">
            </div>

            <!-- SUBMIT BUTTON -->
            <div class="col-span-12"
                style="margin-top: 1rem; padding-top: 1.5rem; border-top: 1px solid #f3f4f6; text-align: right;">
                <button type="submit" class="btn btn-primary" style="padding: 0.75rem 2rem; font-size: 1rem;">
                    <i class="fas fa-save" style="margin-right: 8px;"></i> Simpan Data Pegawai
                </button>
            </div>

        </div> <!-- End Grid -->
    </form>
</div>
<?= $this->endSection() ?>