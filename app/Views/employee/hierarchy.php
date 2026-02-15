<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>Manajemen Hirarki Pegawai
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header">
    <h1 class="page-title">Hirarki Organisasi</h1>
    <div style="display: flex; gap: 0.5rem;">
        <button type="button" id="btnSetSupervisor" class="btn btn-primary"
            style="display: none; background-color: #4f46e5;">
            <i class="fas fa-user-tie"></i> Set Atasan
        </button>
        <a href="<?= base_url('employee') ?>" class="btn btn-secondary" style="background-color: #6b7280;">
            <i class="fas fa-arrow-left"></i> Kembali ke Daftar
        </a>
    </div>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div
        style="background-color: #d1fae5; border: 1px solid #10b981; color: #065f46; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
        <?= session()->getFlashdata('success') ?>
    </div>
<?php endif; ?>

<form id="formMassAssign" action="<?= base_url('employee/mass-assign-supervisor') ?>" method="POST">
    <?= csrf_field() ?>
    <!-- Pegawai Tanpa Atasan -->
    <?php if (!empty($noSupervisor)): ?>
        <div class="card" style="border-left: 4px solid #ef4444; background-color: #fef2f2; margin-bottom: 2rem;">
            <div class="card-header" style="border-bottom: 1px solid #fee2e2;">
                <h3 class="card-title" style="color: #991b1b;"><i class="fas fa-user-slash"></i> Pegawai Belum Memiliki
                    Atasan (<?= count($noSupervisor) ?>)</h3>
            </div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 40px;"><input type="checkbox" class="select-section"
                                    data-target="no-supervisor-check"></th>
                            <th>NIP</th>
                            <th>Nama Pegawai</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($noSupervisor as $row): ?>
                            <tr>
                                <td><input type="checkbox" name="employee_ids[]" value="<?= $row['id'] ?>"
                                        class="employee-check no-supervisor-check"></td>
                                <td><?= $row['nip'] ?></td>
                                <td><?= $row['name'] ?></td>
                                <td><span class="badge badge-warning"><?= $row['user_type'] ?></span></td>
                                <td>
                                    <a href="<?= base_url('employee/edit/' . $row['id']) ?>"
                                        class="btn btn-sm btn-primary">Atur</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <!-- Kelompok Per Atasan -->
    <?php if (empty($hierarchy)): ?>
        <?php if (empty($noSupervisor)): ?>
            <div class="card" style="text-align: center; padding: 3rem;">
                <i class="fas fa-sitemap" style="font-size: 3rem; color: #e5e7eb; margin-bottom: 1rem;"></i>
                <p style="color: #9ca3af;">Belum ada hirarki yang terbentuk.</p>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <?php foreach ($hierarchy as $group): ?>
            <div class="card" style="margin-bottom: 2rem;">
                <div class="card-header"
                    style="background-color: #f8fafb; display: flex; justify-content: space-between; align-items: center;">
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <div
                            style="background-color: #4f46e5; color: white; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <div>
                            <h3 class="card-title" style="margin: 0;"><?= $group['supervisor']['name'] ?></h3>
                            <p style="margin: 0; font-size: 0.8rem; color: #6b7280;">
                                <?= strtoupper($group['supervisor']['role']) ?> - Koordinator / Atasan</p>
                        </div>
                    </div>
                    <div style="font-size: 0.8rem; color: #6b7280;">
                        <input type="checkbox" class="select-section" data-target="sub-<?= $group['supervisor']['id'] ?>"> Pilih
                        Semua Bawahan
                    </div>
                </div>
                <div class="table-responsive">
                    <table style="background-color: #fff;">
                        <thead style="background-color: #f9fafb;">
                            <tr>
                                <th style="width: 40px; padding-left: 1.5rem;"></th>
                                <th style="padding-left: 2rem;">Nama Bawahan</th>
                                <th>NIP</th>
                                <th>Jabatan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($group['subordinates'] as $sub): ?>
                                <tr>
                                    <td style="padding-left: 1.5rem;"><input type="checkbox" name="employee_ids[]"
                                            value="<?= $sub['id'] ?>" class="employee-check sub-<?= $group['supervisor']['id'] ?>">
                                    </td>
                                    <td style="padding-left: 2rem; position: relative;">
                                        <div
                                            style="position: absolute; left: 0; top: 0; bottom: 50%; width: 15px; border-left: 2px solid #e5e7eb; border-bottom: 2px solid #e5e7eb;">
                                        </div>
                                        <strong><?= $sub['name'] ?></strong>
                                    </td>
                                    <td><?= $sub['nip'] ?></td>
                                    <td><?= $sub['position'] ?></td>
                                    <td>
                                        <a href="<?= base_url('employee/edit/' . $sub['id']) ?>" class="btn btn-sm btn-secondary"
                                            style="background-color: #f3f4f6; color: #4b5563;">Pindah</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
    <input type="hidden" name="supervisor_id" id="target_supervisor_id">
</form>

<!-- Modal Set Atasan -->
<div id="supervisorModal"
    style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5);">
    <div
        style="background: white; width: 450px; margin: 100px auto; padding: 2rem; border-radius: 16px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);">
        <h3 style="margin-top: 0; margin-bottom: 1rem; color: #111827;">Pindahkan ke Atasan Lain</h3>
        <p style="color: #6b7280; font-size: 0.9rem; margin-bottom: 1.5rem;">Pilih atasan baru untuk pegawai yang telah
            dipilih.</p>

        <div class="form-group" style="margin-bottom: 1.5rem;">
            <label class="form-label">Nama Atasan Baru</label>
            <select id="selectSupervisor" class="form-control" required style="width: 100%;">
                <option value="">-- Pilih Atasan Barru --</option>
                <?php foreach ($potential_supervisors as $s): ?>
                    <option value="<?= $s['id'] ?>"><?= $s['name'] ?> (<?= strtoupper($s['role']) ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 0.75rem;">
            <button type="button" id="btnCancel" class="btn" style="background: #f3f4f6; color: #4b5563;">Batal</button>
            <button type="button" id="btnConfirm" class="btn btn-primary">Simpan Perpindahan</button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('supervisorModal');
        const btnSetSupervisor = document.getElementById('btnSetSupervisor');
        const selectSupervisor = document.getElementById('selectSupervisor');
        const btnCancel = document.getElementById('btnCancel');
        const btnConfirm = document.getElementById('btnConfirm');
        const form = document.getElementById('formMassAssign');
        const targetSupervisorInput = document.getElementById('target_supervisor_id');
        const checks = document.querySelectorAll('.employee-check');
        const sectionChecks = document.querySelectorAll('.select-section');

        // Toggle mass button
        const toggleBtn = () => {
            const checkedCount = document.querySelectorAll('.employee-check:checked').length;
            btnSetSupervisor.style.display = checkedCount > 0 ? 'inline-flex' : 'none';

            if (checkedCount > 0) {
                btnSetSupervisor.innerHTML = `<i class="fas fa-user-tie"></i> Pindahkan (${checkedCount}) Pegawai`;
            }
        };

        sectionChecks.forEach(sc => {
            sc.addEventListener('change', function () {
                const targetClass = this.getAttribute('data-target');
                document.querySelectorAll('.' + targetClass).forEach(c => c.checked = this.checked);
                toggleBtn();
            });
        });

        checks.forEach(c => c.addEventListener('change', toggleBtn));

        // Modal behavior
        btnSetSupervisor.addEventListener('click', () => modal.style.display = 'block');
        btnCancel.addEventListener('click', () => modal.style.display = 'none');

        btnConfirm.addEventListener('click', function () {
            if (!selectSupervisor.value) {
                alert('Silakan pilih atasan tujuan!');
                return;
            }
            targetSupervisorInput.value = selectSupervisor.value;
            form.submit();
        });
    });
</script>

<?= $this->endSection() ?>