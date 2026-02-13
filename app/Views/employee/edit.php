<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>Edit Pegawai<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header">
    <h1 class="page-title">Edit Data Pegawai</h1>
    <a href="<?= base_url('employee') ?>" class="btn btn-secondary">
        <i class="fas fa-arrow-left" style="margin-right: 8px;"></i> Kembali
    </a>
</div>

<div class="card">
    <form action="<?= base_url('employee/update/' . $employee['id']) ?>" method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        
        <div class="form-grid">
            <!-- HEADER: DATA PRIBADI -->
            <div class="section-separator">
                <i class="fas fa-user-edit"></i>
                <h3>Data Pribadi</h3>
            </div>

            <!-- ROW 1: NIP & NIK -->
            <div class="form-group col-span-6">
                <label class="form-label">NIP <span class="text-danger">*</span></label>
                <input type="text" name="nip" class="form-control" value="<?= old('nip', $employee['nip']) ?>" required>
            </div>
            
            <div class="form-group col-span-6">
                <label class="form-label">NIK <span class="text-danger">*</span></label>
                <input type="text" name="nik" class="form-control" value="<?= old('nik', $employee['nik'] ?? '') ?>" required>
            </div>

            <!-- ROW 2: NAMA LENGKAP -->
            <div class="form-group col-span-12">
                <label class="form-label">Nama Lengkap (Tanpa Gelar) <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" value="<?= old('name', $employee['name']) ?>" required>
            </div>

            <!-- ROW 3: GELAR DEPAN & BELAKANG -->
            <div class="form-group col-span-6">
                <label class="form-label">Gelar Depan</label>
                <input type="text" name="front_title" class="form-control" value="<?= old('front_title', $employee['front_title']) ?>" placeholder="Cth: Dr., Ir.">
            </div>

            <div class="form-group col-span-6">
                <label class="form-label">Gelar Belakang</label>
                <input type="text" name="back_title" class="form-control" value="<?= old('back_title', $employee['back_title']) ?>" placeholder="Cth: S.Kom, M.M.">
            </div>

            <!-- ROW 4: TEMPAT & TANGGAL LAHIR -->
            <div class="form-group col-span-6">
                <label class="form-label">Tempat Lahir <span class="text-danger">*</span></label>
                <input type="text" name="pob" class="form-control" value="<?= old('pob', $employee['pob']) ?>" required>
            </div>

            <div class="form-group col-span-6">
                <label class="form-label">Tanggal Lahir <span class="text-danger">*</span></label>
                <input type="date" name="dob" class="form-control" value="<?= old('dob', $employee['dob']) ?>" required>
            </div>

            <!-- ROW 5: JENIS KELAMIN & PENDIDIKAN -->
            <div class="form-group col-span-6">
                <label class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                <select name="gender" class="form-control" required>
                    <option value="">-- Pilih Jenis Kelamin --</option>
                    <option value="L" <?= old('gender', $employee['gender']) == 'L' ? 'selected' : '' ?>>Laki-laki</option>
                    <option value="P" <?= old('gender', $employee['gender']) == 'P' ? 'selected' : '' ?>>Perempuan</option>
                </select>
            </div>

            <div class="form-group col-span-6">
                <label class="form-label">Pendidikan Terakhir <span class="text-danger">*</span></label>
                <input type="text" name="education" class="form-control" value="<?= old('education', $employee['education']) ?>" required>
            </div>

            <!-- ROW 6: ALAMAT -->
            <div class="form-group col-span-12">
                <label class="form-label">Alamat Lengkap</label>
                <textarea name="address" class="form-control" rows="3"><?= old('address', $employee['address']) ?></textarea>
            </div>

            <!-- ROW 7: KONTAK -->
            <div class="form-group col-span-6">
                <label class="form-label">No. Telepon/HP</label>
                <input type="text" name="phone" class="form-control" value="<?= old('phone', $employee['phone']) ?>">
            </div>

            <div class="form-group col-span-6">
                <label class="form-label">Password Baru</label>
                <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak ingin mengubah">
            </div>

             <!-- ROW 8: FOTO PROFIL (Detailed) -->
            <div class="col-span-12 form-group">
                <label class="form-label">Foto Profil</label>
                <div style="display: flex; align-items: flex-start; gap: 1.5rem; background: #f9fafb; padding: 1rem; border-radius: 8px; border: 1px dashed #d1d5db;">
                    <?php if(!empty($employee['photo'])): ?>
                        <div style="flex-shrink: 0; text-align: center;">
                            <img src="<?= base_url('uploads/photos/' . $employee['photo']) ?>" alt="Foto Profil" style="width: 100px; height: 100px; object-fit: cover; border-radius: 50%; border: 3px solid white; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                            <div style="margin-top: 0.5rem; font-size: 0.75rem; color: #6b7280;">Foto Saat Ini</div>
                        </div>
                    <?php else: ?>
                        <div style="width: 100px; height: 100px; background: #e5e7eb; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #9ca3af; font-size: 2rem;">
                            <i class="fas fa-user"></i>
                        </div>
                    <?php endif; ?>
                    
                    <div style="flex-grow: 1;">
                        <input type="file" name="photo" class="form-control" accept="image/*">
                        <div class="form-hint" style="margin-top: 0.5rem;">
                            Upload foto baru untuk mengganti. Format JPG/PNG, Maksimal 2MB.<br>
                            Disarankan foto formal dengan rasio 1:1 (Persegi).
                        </div>
                    </div>
                </div>
            </div>


            <!-- HEADER: DATA KEPEGAWAIAN -->
            <div class="section-separator" style="margin-top: 1.5rem;">
                <i class="fas fa-id-card-alt"></i>
                <h3>Data Kepegawaian</h3>
            </div>

            <!-- ROW 9: PANGKAT & JABATAN -->
            <div class="form-group col-span-6">
                <label class="form-label">Pangkat / Golongan <span class="text-danger">*</span></label>
                <input type="text" name="rank" class="form-control" value="<?= old('rank', $employee['rank']) ?>" required>
            </div>

            <div class="form-group col-span-6">
                <label class="form-label">Jabatan</label>
                <input type="text" name="position" class="form-control" value="<?= old('position', $employee['position']) ?>">
            </div>

            <!-- ROW 10: UNIT & TMT -->
            <div class="form-group col-span-6">
                <label class="form-label">Unit Kerja</label>
                <input type="text" name="unit" class="form-control" value="<?= old('unit', $employee['unit']) ?>">
            </div>

            <div class="form-group col-span-6">
                <label class="form-label">Tanggal Masuk (TMT)</label>
                <input type="date" name="join_date" class="form-control" value="<?= old('join_date', $employee['join_date']) ?>">
            </div>

            <!-- ROW 11: ROLE & ATASAN -->
            <div class="form-group col-span-6">
                <label class="form-label">Hak Akses (Role) <span class="text-danger">*</span></label>
                <select name="role" class="form-control" required>
                    <option value="pegawai" <?= old('role', $employee['role']) == 'pegawai' ? 'selected' : '' ?>>Pegawai Biasa</option>
                    <option value="admin" <?= old('role', $employee['role']) == 'admin' ? 'selected' : '' ?>>Administrator</option>
                </select>
                <div class="form-hint">Admin memiliki akses penuh ke sistem.</div>
            </div>

            <div class="form-group col-span-6">
                <label class="form-label">Atasan Langsung</label>
                <select name="supervisor_id" class="form-control">
                    <option value="">-- Tidak Ada / Pilih Nanti --</option>
                    <?php foreach($potential_supervisors as $user): ?>
                         <?php if($user['id'] != $employee['id']): ?>
                            <option value="<?= $user['id'] ?>" <?= old('supervisor_id', $employee['supervisor_id']) == $user['id'] ? 'selected' : '' ?>>
                                <?= $user['name'] ?> (<?= $user['nip'] ?>) - <?= $user['position'] ?>
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
                <div class="form-hint">Pilih pegawai yang berwenang menyetujui cuti pegawai ini.</div>
            </div>

            <div class="form-group col-span-12">
                <label style="display: flex; align-items: center; gap: 0.5rem;">
                    <input type="checkbox" name="is_supervisor" value="1" <?= old('is_supervisor', $employee['is_supervisor']) ? 'checked' : '' ?> style="width: 1.25rem; height: 1.25rem;">
                    <span>Tandai sebagai Atasan / Pejabat (Bisa menyetujui cuti bawahan)</span>
                </label>
                <label style="display: flex; align-items: center; gap: 0.5rem; margin-top: 0.5rem;">
                    <input type="checkbox" name="is_head_of_agency" value="1" <?= old('is_head_of_agency', $employee['is_head_of_agency']) ? 'checked' : '' ?> style="width: 1.25rem; height: 1.25rem;">
                    <span>Tandai sebagai Kepala Dinas (Penanda Tangan Akhir)</span>
                </label>
            </div>

            <!-- HEADER: SALDO CUTI AWAL (MIGRASI) -->
            <div class="section-separator" style="margin-top: 1.5rem;">
                <i class="fas fa-history"></i>
                <h3>Saldo Cuti Awal (Migrasi)</h3>
            </div>

            <div class="form-group col-span-4">
                <label class="form-label">Sisa Cuti N (<?= date('Y') ?>)</label>
                <input type="number" name="leave_balance_n" class="form-control" value="<?= old('leave_balance_n', $employee['leave_balance_n']) ?>" min="0" max="12">
                <div class="form-hint">Jatah awal tahun berjalan (Max 12).</div>
            </div>

            <div class="form-group col-span-4">
                <label class="form-label">Sisa Cuti N-1 (<?= date('Y')-1 ?>)</label>
                <input type="number" name="leave_balance_n1" class="form-control" value="<?= old('leave_balance_n1', $employee['leave_balance_n1']) ?>" min="0" max="6">
                <div class="form-hint">Maksimal 6 hari (Aturan ASN).</div>
            </div>

            <div class="form-group col-span-4">
                <label class="form-label">Sisa Cuti N-2 (<?= date('Y')-2 ?>)</label>
                <input type="number" name="leave_balance_n2" class="form-control" value="<?= old('leave_balance_n2', $employee['leave_balance_n2']) ?>" min="0" max="6">
                <div class="form-hint">Maksimal 6 hari (Aturan ASN).</div>
            </div>

            <!-- SUBMIT BUTTON -->
            <div class="col-span-12" style="margin-top: 1rem; padding-top: 1.5rem; border-top: 1px solid #f3f4f6; text-align: right;">
                <button type="submit" class="btn btn-primary" style="padding: 0.75rem 2rem; font-size: 1rem;">
                    <i class="fas fa-save" style="margin-right: 8px;"></i> Simpan Perubahan
                </button>
            </div>

        </div> <!-- End Grid -->
    </form>
</div>
<?= $this->endSection() ?>
