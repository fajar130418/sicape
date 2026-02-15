<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>Manajemen Kontrak PPPK
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header">
    <h1 class="page-title">Monitoring Kontrak PPPK</h1>
    <div style="display: flex; gap: 0.5rem;">
        <button type="button" id="btnMassRenew" class="btn btn-primary" style="display: none;">
            <i class="fas fa-calendar-plus"></i> Perpanjang Terpilih
        </button>
    </div>
</div>

<!-- Panel Reguasi -->
<div style="background-color: #eff6ff; border: 1px solid #3b82f6; border-radius: 12px; padding: 1.25rem; margin-bottom: 2rem;">
    <div style="display: flex; gap: 1rem; align-items: flex-start;">
        <div style="background-color: #3b82f6; color: white; width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i class="fas fa-info-circle"></i>
        </div>
        <div>
            <h4 style="margin: 0 0 0.5rem 0; color: #1e40af; font-size: 1rem;">Pedoman Perpanjangan Kontrak (PP No. 49 Tahun 2018)</h4>
            <ul style="margin: 0; padding-left: 1.25rem; color: #1e3a8a; font-size: 0.85rem; line-height: 1.5;">
                <li><strong>Evaluasi Kinerja</strong>: Perpanjangan harus didasarkan pada hasil penilaian kinerja tahunan yang menunjukkan kualifikasi minimal "Baik".</li>
                <li><strong>Batas Waktu</strong>: Proses administrasi perpanjangan disarankan dilakukan paling lambat <strong>3 bulan</strong> sebelum masa kontrak berakhir.</li>
                <li><strong>Durasi Kontrak</strong>: Perpanjangan dapat dilakukan untuk jangka waktu minimal 1 tahun dan maksimal 5 tahun sesuai kebutuhan organisasi.</li>
            </ul>
        </div>
    </div>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div
        style="background-color: #d1fae5; border: 1px solid #10b981; color: #065f46; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
        <?= session()->getFlashdata('success') ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="table-responsive">
        <form id="formMassRenew" action="<?= base_url('employee/mass-renew') ?>" method="POST">
            <table id="contractTable">
                <thead>
                    <tr>
                        <th style="width: 40px;"><input type="checkbox" id="selectAll"></th>
                        <th>NIP</th>
                        <th>Nama Pegawai</th>
                        <th>Status</th>
                        <th>Tgl Berakhir Kontrak</th>
                        <th>Sisa Waktu</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pppk)): ?>
                        <tr>
                            <td colspan="7" style="text-align:center; color:#9ca3af; padding: 2rem;">Tidak ada data pegawai
                                PPPK.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($pppk as $row): ?>
                            <?php
                            $endDate = new DateTime($row['contract_end_date']);
                            $today = new DateTime();
                            $diff = $today->diff($endDate);

                            $statusClass = 'badge-success';
                            $rowStyle = '';

                            if ($endDate < $today) {
                                $statusClass = 'badge-danger';
                                $timeLabel = "Sudah Berakhir";
                                $rowStyle = 'background-color: #fff1f2;';
                            } else {
                                $daysLeft = $today->diff($endDate)->days;
                                if ($daysLeft <= 90) { // < 3 bulan
                                    $statusClass = 'badge-warning';
                                    $rowStyle = 'background-color: #fffbeb;';
                                }

                                $totalMonths = ($diff->y * 12) + $diff->m;
                                if ($totalMonths > 0) {
                                    $timeLabel = $totalMonths . " Bulan " . $diff->d . " Hari";
                                } else {
                                    $timeLabel = $diff->d . " Hari Lagi";
                                }
                            }
                            ?>
                            <tr style="<?= $rowStyle ?>">
                                <td><input type="checkbox" name="employee_ids[]" value="<?= $row['id'] ?>"
                                        class="employee-check"></td>
                                <td>
                                    <?= $row['nip'] ?>
                                </td>
                                <td>
                                    <strong>
                                        <?= $row['name'] ?>
                                    </strong><br>
                                    <small style="color: #6b7280;">
                                        <?= $row['user_type'] ?>
                                    </small>
                                </td>
                                <td><span class="badge <?= $statusClass ?>">
                                        <?= $row['user_type'] ?>
                                    </span></td>
                                <td>
                                    <?= date('d M Y', strtotime($row['contract_end_date'])) ?>
                                </td>
                                <td><strong>
                                        <?= $timeLabel ?>
                                    </strong></td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary btn-renew" data-id="<?= $row['id'] ?>"
                                        data-name="<?= $row['name'] ?>" data-date="<?= $row['contract_end_date'] ?>"
                                        style="background-color: #4f46e5;">
                                        Perpanjang
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Modal logic will be handled via JS and a single modal structure -->
            <input type="hidden" name="new_contract_end_date" id="mass_new_date">
        </form>
    </div>
</div>

<!-- Modal Perpanjangan -->
<div id="renewModal"
    style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5);">
    <div
        style="background: white; width: 400px; margin: 100px auto; padding: 2rem; border-radius: 16px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);">
        <h3 id="modalTitle" style="margin-top: 0; margin-bottom: 1rem; color: #111827;">Perpanjang Kontrak</h3>
        <p id="modalInfo" style="color: #6b7280; font-size: 0.9rem; margin-bottom: 1.5rem;">Silakan masukkan tanggal
            berakhir kontrak yang baru.</p>

        <div class="form-group" style="margin-bottom: 1.5rem;">
            <label class="form-label">Tanggal Berakhir Kontrak Baru</label>
            <input type="date" id="inputNewDate" class="form-control" required>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 0.75rem;">
            <button type="button" id="btnCancel" class="btn" style="background: #f3f4f6; color: #4b5563;">Batal</button>
            <button type="button" id="btnConfirm" class="btn btn-primary">Simpan Perubahan</button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const renewModal = document.getElementById('renewModal');
        const inputNewDate = document.getElementById('inputNewDate');
        const btnCancel = document.getElementById('btnCancel');
        const btnConfirm = document.getElementById('btnConfirm');
        const btnMassRenew = document.getElementById('btnMassRenew');
        const selectAll = document.getElementById('selectAll');
        const checks = document.querySelectorAll('.employee-check');
        const form = document.getElementById('formMassRenew');
        const massNewDateInput = document.getElementById('mass_new_date');

        let currentMode = 'single'; // or 'mass'
        let currentId = null;

        // Open single modal
        document.querySelectorAll('.btn-renew').forEach(btn => {
            btn.addEventListener('click', function () {
                currentMode = 'single';
                currentId = this.dataset.id;
                document.getElementById('modalTitle').innerText = 'Perpanjang: ' + this.dataset.name;
                inputNewDate.value = '';
                renewModal.style.display = 'block';
            });
        });

        // Open mass modal
        btnMassRenew.addEventListener('click', function () {
            currentMode = 'mass';
            document.getElementById('modalTitle').innerText = 'Perpanjang Massal';
            inputNewDate.value = '';
            renewModal.style.display = 'block';
        });

        btnCancel.addEventListener('click', () => renewModal.style.display = 'none');

        btnConfirm.addEventListener('click', function () {
            if (!inputNewDate.value) {
                alert('Silakan pilih tanggal!');
                return;
            }

            if (currentMode === 'single') {
                // If single, we can reuse mass-renew logic by just sending one ID
                const tempForm = document.createElement('form');
                tempForm.method = 'POST';
                tempForm.action = '<?= base_url('employee/mass-renew') ?>';

                const idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'employee_ids[]';
                idInput.value = currentId;

                const dateInput = document.createElement('input');
                dateInput.type = 'hidden';
                dateInput.name = 'new_contract_end_date';
                dateInput.value = inputNewDate.value;

                tempForm.appendChild(idInput);
                tempForm.appendChild(dateInput);
                document.body.appendChild(tempForm);
                tempForm.submit();
            } else {
                massNewDateInput.value = inputNewDate.value;
                form.submit();
            }
        });

        // Select All Logic
        selectAll.addEventListener('change', function () {
            checks.forEach(c => c.checked = this.checked);
            toggleMassBtn();
        });

        checks.forEach(c => c.addEventListener('change', toggleMassBtn));

        function toggleMassBtn() {
            const checkedCount = document.querySelectorAll('.employee-check:checked').length;
            btnMassRenew.style.display = checkedCount > 0 ? 'inline-flex' : 'none';
        }
    });
</script>
<?= $this->endSection() ?>