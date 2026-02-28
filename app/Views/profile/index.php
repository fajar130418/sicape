<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>Profil Saya
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header">
    <h1 class="page-title">Profil Saya</h1>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert badge-success"
        style="padding: 1rem; margin-bottom: 1.5rem; border-radius: 8px; border: 1px solid #a7f3d0;">
        <i class="fas fa-check-circle" style="margin-right: 8px;"></i>
        <?= session()->getFlashdata('success') ?>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert badge-danger"
        style="padding: 1rem; margin-bottom: 1.5rem; border-radius: 8px; border: 1px solid #fecaca;">
        <i class="fas fa-exclamation-circle" style="margin-right: 8px;"></i>
        <?= session()->getFlashdata('error') ?>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('errors')): ?>
    <div class="alert badge-danger"
        style="padding: 1rem; margin-bottom: 1.5rem; border-radius: 8px; border: 1px solid #fecaca;">
        <ul style="margin: 0; padding-left: 20px;">
            <?php foreach (session()->getFlashdata('errors') as $error): ?>
                <li>
                    <?= esc($error) ?>
                </li>
            <?php endforeach ?>
        </ul>
    </div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem; align-items: start;">

    <!-- Bagian Kiri: Info Read-Only & Foto -->
    <div class="card" style="position: sticky; top: 100px;">
        <div style="text-align: center; margin-bottom: 1.5rem;">
            <?php if (!empty($user['photo'])): ?>
                <img src="<?= base_url('uploads/photos/' . $user['photo']) ?>" alt="User Photo"
                    style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 4px solid var(--primary-color); padding: 2px;">
            <?php else: ?>
                <div
                    style="width: 120px; height: 120px; border-radius: 50%; background: var(--primary-color); color: white; display: flex; align-items: center; justify-content: center; font-size: 3rem; font-weight: bold; margin: 0 auto; border: 4px solid rgba(79, 70, 229, 0.2);">
                    <?= substr($user['name'], 0, 1) ?>
                </div>
            <?php endif; ?>
            <h3 style="margin-top: 1rem; margin-bottom: 0.2rem; font-size: 1.25rem;">
                <?= esc($user['name']) ?>
            </h3>
            <p style="color: #6b7280; font-size: 0.9rem; margin: 0;">
                <?= esc($user['nip']) ?>
            </p>
        </div>

        <hr style="border: 0; border-top: 1px solid #e5e7eb; margin: 1.5rem 0;">

        <div style="background: #f8fafc; padding: 1rem; border-radius: 8px; border: 1px solid #e2e8f0;">
            <p
                style="font-size: 0.8rem; font-weight: 700; color: var(--primary-color); text-transform: uppercase; margin-top: 0; margin-bottom: 1rem;">
                Informasi Jabatan <br><span style="font-size: 0.7rem; color: #64748b; font-weight: normal;">(Hanya dapat
                    diubah oleh Admin)</span>
            </p>

            <div style="margin-bottom: 1rem;">
                <label style="font-size: 0.8rem; color: #64748b;">Hak Akses Role</label>
                <div style="font-weight: 600; color: #334155; text-transform: capitalize;">
                    <?= esc($user['role']) ?>
                </div>
            </div>



            <div style="margin-bottom: 0;">
                <label style="font-size: 0.8rem; color: #64748b;">Saldo Cuti N (Tahun Ini)</label>
                <div style="font-weight: 600; color: #334155;">
                    <span
                        style="background: var(--primary-color); color: white; padding: 2px 8px; border-radius: 12px; font-size: 0.85rem;">
                        <?= esc($user['leave_balance_n'] ?? 0) ?> Hari
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Bagian Kanan: Form Edit -->
    <div class="card">
        <form action="<?= base_url('profile/update') ?>" method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>

            <div class="form-grid">
                <div class="section-separator" style="margin-top: 0;">
                    <i class="fas fa-user"></i>
                    <h3>Data Pribadi (Dapat Diubah)</h3>
                </div>

                <div class="col-span-12 form-group">
                    <label class="form-label">Nama Lengkap</label>
                    <input type="text" class="form-control" name="name" value="<?= old('name', $user['name']) ?>"
                        required>
                </div>

                <div class="col-span-6 form-group">
                    <label class="form-label">Gelar Depan</label>
                    <input type="text" class="form-control" name="front_title"
                        value="<?= old('front_title', $user['front_title']) ?>" placeholder="Misal: Drs., Ir., dsb">
                </div>

                <div class="col-span-6 form-group">
                    <label class="form-label">Gelar Belakang</label>
                    <input type="text" class="form-control" name="back_title"
                        value="<?= old('back_title', $user['back_title']) ?>" placeholder="Misal: S.Kom, M.Ti, dsb">
                </div>

                <div class="col-span-6 form-group">
                    <label class="form-label">NIK (Nomor Induk Kependudukan)</label>
                    <input type="text" class="form-control" name="nik" value="<?= old('nik', $user['nik']) ?>">
                </div>

                <div class="col-span-6 form-group">
                    <label class="form-label">Jenis Kelamin</label>
                    <select class="form-control" name="gender">
                        <option value="">Pilih Gender</option>
                        <option value="L" <?= old('gender', $user['gender']) == 'L' ? 'selected' : '' ?>>Laki-laki</option>
                        <option value="P" <?= old('gender', $user['gender']) == 'P' ? 'selected' : '' ?>>Perempuan</option>
                    </select>
                </div>

                <div class="col-span-6 form-group">
                    <label class="form-label">Tempat Lahir</label>
                    <input type="text" class="form-control" name="pob" value="<?= old('pob', $user['pob']) ?>">
                </div>

                <div class="col-span-6 form-group">
                    <label class="form-label">Tanggal Lahir</label>
                    <input type="date" class="form-control" name="dob" value="<?= old('dob', $user['dob']) ?>">
                </div>

                <div class="col-span-12 form-group">
                    <label class="form-label">Pendidikan Terakhir</label>
                    <select class="form-control" name="education">
                        <option value="">-- Pilih Pendidikan --</option>
                        <?php
                        $educations = ["SD / Sederajat", "SMP / Sederajat", "SMA / Sederajat", "DI", "DII", "DIII", "DIV / S1", "S2", "S3"];
                        foreach ($educations as $edu): ?>
                            <option value="<?= $edu ?>" <?= old('education', $user['education']) == $edu ? 'selected' : '' ?>>
                                <?= $edu ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-span-12 form-group">
                    <label class="form-label">Alamat Lengkap</label>
                    <textarea class="form-control" name="address"
                        rows="3"><?= old('address', $user['address']) ?></textarea>
                </div>

                <div class="section-separator" style="margin-top: 1rem;">
                    <i class="fas fa-briefcase"></i>
                    <h3>Data Kepegawaian</h3>
                </div>

                <div class="col-span-12 form-group">
                    <label class="form-label">Jabatan</label>
                    <input type="text" class="form-control" name="position"
                        value="<?= old('position', $user['position']) ?>">
                </div>

                <div class="col-span-12 form-group">
                    <label class="form-label">Unit Kerja</label>
                    <input type="text" class="form-control" name="unit" value="<?= old('unit', $user['unit']) ?>">
                </div>

                <div class="col-span-6 form-group">
                    <label class="form-label">Status Pegawai</label>
                    <select class="form-control" name="user_type" id="user_type">
                        <option value="">-- Pilih Status --</option>
                        <option value="PNS" <?= old('user_type', $user['user_type']) == 'PNS' ? 'selected' : '' ?>>PNS
                        </option>
                        <option value="PPPK" <?= old('user_type', $user['user_type']) == 'PPPK' ? 'selected' : '' ?>>PPPK
                        </option>
                        <option value="PPPK Paruh Waktu" <?= old('user_type', $user['user_type']) == 'PPPK Paruh Waktu' ? 'selected' : '' ?>>PPPK Paruh Waktu</option>
                    </select>
                    <div class="form-hint">Menentukan hak jenis cuti (Cuti Besar hanya untuk PNS).</div>
                </div>

                <div class="col-span-6 form-group">
                    <label class="form-label">Pangkat/Golongan</label>
                    <select class="form-control" name="rank" id="rank">
                        <option value="">-- Pilih Pangkat/Golongan --</option>
                    </select>
                </div>

                <div class="col-span-12 form-group" id="contract_field" style="display:none;">
                    <label class="form-label">Tanggal Berakhir Kontrak (Hanya untuk PPPK)</label>
                    <input type="date" class="form-control" name="contract_end_date"
                        value="<?= old('contract_end_date', $user['contract_end_date'] ?? '') ?>">
                    <div class="form-hint">Diperlukan untuk validasi durasi Cuti Sakit PPPK.</div>
                </div>

                <div class="col-span-6 form-group">
                    <label class="form-label">Tanggal Masuk (TMT)</label>
                    <input type="date" class="form-control" name="join_date"
                        value="<?= old('join_date', isset($user['join_date']) ? date('Y-m-d', strtotime($user['join_date'])) : '') ?>"
                        readonly style="background-color: #f3f4f6; cursor: not-allowed;">
                    <div class="form-hint">Hanya dapat diubah oleh Administrator melalui Manajemen Pegawai.</div>
                </div>


                <div class="section-separator" style="margin-top: 1rem;">
                    <i class="fas fa-envelope"></i>
                    <h3>Kontak & Keamanan</h3>
                </div>

                <div class="col-span-6 form-group">
                    <label class="form-label">Nomor WhatsApp / HP</label>
                    <input type="text" class="form-control" name="phone" value="<?= old('phone', $user['phone']) ?>">
                </div>

                <div class="col-span-6 form-group">
                    <label class="form-label">Email Aktif</label>
                    <input type="email" class="form-control" name="email" value="<?= old('email', $user['email']) ?>">
                </div>

                <div class="col-span-12 form-group">
                    <label class="form-label">Ubah Password Baru</label>
                    <input type="password" class="form-control" name="password"
                        placeholder="Biarkan kosong jika tidak ingin mengubah password">
                    <div class="form-hint">Minimal 6 karakter, isi hanya bila Anda ingin mengubah password saat ini.
                    </div>
                </div>

                <div class="col-span-12 form-group">
                    <label class="form-label">Perbarui Foto Profil</label>
                    <input type="file" class="form-control" name="photo" accept="image/jpeg,image/png,image/jpg">
                    <div class="form-hint">Format yang diizinkan: JPG, PNG maksimal 2MB. Biarkan kosong jika tidak ingin
                        mengubah foto.</div>
                </div>

                <div class="col-span-12" style="margin-top: 1.5rem; text-align: right;">
                    <button type="submit" class="btn btn-primary"
                        style="padding: 0.75rem 2rem; font-size: 1rem; border-radius: 8px;">
                        <i class="fas fa-save" style="margin-right: 8px;"></i> Simpan Perubahan Profil
                    </button>
                </div>
            </div>
        </form>
    </div>

</div>

<!-- JS: Dynamic Rank Dropdown (matches Admin Employee Form) -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const userTypeSelect = document.getElementById('user_type');
        const rankSelect = document.getElementById('rank');
        const contractField = document.getElementById('contract_field');
        const currentRank = "<?= htmlspecialchars(old('rank', $user['rank'] ?? '')) ?>";

        const pnsRanks = [
            "Juru Muda (I/a)", "Juru Muda Tingkat I (I/b)", "Juru (I/c)", "Juru Tingkat I (I/d)",
            "Pengatur Muda (II/a)", "Pengatur Muda Tingkat I (II/b)", "Pengatur (II/c)", "Pengatur Tingkat I (II/d)",
            "Penata Muda (III/a)", "Penata Muda Tingkat I (III/b)", "Penata (III/c)", "Penata Tingkat I (III/d)",
            "Pembina (IV/a)", "Pembina Tingkat I (IV/b)", "Pembina Utama Muda (IV/c)", "Pembina Utama Madya (IV/d)", "Pembina Utama (IV/e)"
        ];
        const pppkRanks = [
            "Golongan I", "Golongan II", "Golongan III", "Golongan IV", "Golongan V",
            "Golongan VI", "Golongan VII", "Golongan VIII", "Golongan IX", "Golongan X",
            "Golongan XI", "Golongan XII", "Golongan XIII", "Golongan XIV", "Golongan XV",
            "Golongan XVI", "Golongan XVII"
        ];

        function updateRanks() {
            const type = userTypeSelect.value;
            let list = (type === 'PNS') ? pnsRanks : pppkRanks;
            let options = '<option value="">-- Pilih Pangkat/Golongan --</option>';
            list.forEach(rank => {
                const selected = (rank === currentRank) ? 'selected' : '';
                options += `<option value="${rank}" ${selected}>${rank}</option>`;
            });
            rankSelect.innerHTML = options;
            // Toggle contract field
            if (contractField) {
                contractField.style.display = (type && type.includes('PPPK')) ? 'block' : 'none';
            }
        }

        userTypeSelect.addEventListener('change', updateRanks);
        updateRanks(); // Initial load
    });
</script>

<!-- Media Queries for Mobile Responsiveness -->
<style>
    @media (max-width: 900px) {
        div[style*="display: grid; grid-template-columns: 1fr 2fr;"] {
            grid-template-columns: 1fr !important;
            gap: 1rem !important;
        }

        .card[style*="position: sticky;"] {
            position: relative !important;
            top: 0 !important;
        }
    }
</style>
<?= $this->endSection() ?>