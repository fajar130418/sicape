<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>Manajemen KGB
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header">
    <h1 class="page-title"><i class="fas fa-chart-line" style="margin-right:8px;"></i> Manajemen KGB</h1>
    <span class="date-today">
        <?= date('d F Y') ?>
    </span>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert badge-success" style="padding:1rem;margin-bottom:1.5rem;border-radius:8px;border:1px solid #a7f3d0;">
        <i class="fas fa-check-circle" style="margin-right:8px;"></i>
        <?= session()->getFlashdata('success') ?>
    </div>
<?php endif; ?>

<!-- Legend / Info -->
<div class="card" style="margin-bottom:1.5rem; padding: 1.25rem 1.5rem;">
    <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1rem;">
        <div>
            <h3 style="margin:0 0 0.25rem; font-size:1rem;">Kenaikan Gaji Berkala (KGB)</h3>
            <p style="margin:0; color:#6b7280; font-size:0.875rem;">
                PNS & PPPK berhak atas KGB setiap <strong>2 tahun</strong> sekali
                (PP No.15/2019 untuk PNS · PP No.49/2018 untuk PPPK).
                Pengingat aktif <strong>2 bulan sebelum</strong> jatuh tempo.
            </p>
        </div>
        <div style="display:flex;gap:1rem;flex-wrap:wrap;font-size:0.8rem;">
            <span><span style="background:#fee2e2;color:#b91c1c;padding:2px 10px;border-radius:12px;">🔴
                    Terlambat</span></span>
            <span><span style="background:#fef3c7;color:#92400e;padding:2px 10px;border-radius:12px;">🟡 Segera (&le;2
                    bln)</span></span>
            <span><span style="background:#f3f4f6;color:#6b7280;padding:2px 10px;border-radius:12px;">⚪ Hak Berhenti
                    (Kontrak Berakhir)</span></span>
            <span><span style="background:#e0e7ff;color:#4338ca;padding:2px 10px;border-radius:12px;">🔵 Di Luar Kontrak
                    (Tunggu Perpanjangan)</span></span>
            <span><span style="background:#d1fae5;color:#065f46;padding:2px 10px;border-radius:12px;">🟢
                    Aman</span></span>
        </div>
    </div>
    <?php if ($dueCount > 0): ?>
        <div
            style="margin-top:1rem;background:#fff7ed;border:1px solid #fed7aa;border-radius:8px;padding:0.75rem 1rem;color:#9a3412;font-size:0.875rem;">
            <i class="fas fa-bell" style="margin-right:6px;"></i>
            Ada <strong>
                <?= $dueCount ?> pegawai
            </strong> yang KGB-nya perlu segera ditindaklanjuti.
        </div>
    <?php endif; ?>
</div>

<!-- Table -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Daftar KGB Pegawai</h3>
        <span style="font-size:0.875rem;color:#6b7280;">
            <?= count($kgbList) ?> pegawai PNS/PPPK
        </span>
    </div>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Nama Pegawai</th>
                    <th>Status / Masa Kontrak</th>
                    <th>Dasar Perhitungan</th>
                    <th>KGB Berikutnya</th>
                    <th>Sisa Hari</th>
                    <th>Status KGB</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($kgbList)): ?>
                    <tr>
                        <td colspan="7" style="text-align:center;color:#9ca3af;padding:2rem;">Belum ada pegawai PNS/PPPK
                            yang terdaftar.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($kgbList as $emp): ?>
                        <?php
                        $statusStyle = match ($emp['kgb_status']) {
                            'overdue' => 'background:#fee2e2;color:#b91c1c;',
                            'warning' => 'background:#fef3c7;color:#92400e;',
                            'contract_expired' => 'background:#f3f4f6;color:#6b7280;',
                            'outside_contract' => 'background:#e0e7ff;color:#4338ca;',
                            'ok' => 'background:#d1fae5;color:#065f46;',
                            default => 'background:#f3f4f6;color:#6b7280;',
                        };
                        $statusIcon = match ($emp['kgb_status']) {
                            'overdue' => '🔴 Terlambat',
                            'warning' => '🟡 Segera',
                            'contract_expired' => '⚪ Kontrak Berakhir',
                            'outside_contract' => '🔵 Di Luar Kontrak',
                            'ok' => '🟢 Aman',
                            default => '❓ Tidak Diketahui',
                        };
                        $daysDisplay = $emp['kgb_days_left'] !== null
                            ? ($emp['kgb_days_left'] < 0
                                ? abs($emp['kgb_days_left']) . ' hari lewat'
                                : $emp['kgb_days_left'] . ' hari lagi')
                            : '-';

                        if ($emp['kgb_status'] === 'contract_expired')
                            $daysDisplay = 'Berhenti';
                        if ($emp['kgb_status'] === 'outside_contract')
                            $daysDisplay = 'Tunggu Perpanjangan';
                        ?>
                        <tr>
                            <td>
                                <div style="font-weight:600;">
                                    <?= esc($emp['name']) ?>
                                </div>
                                <div style="font-size:0.8rem;color:#6b7280;">
                                    <?= esc($emp['nip']) ?>
                                </div>
                            </td>
                            <td>
                                <div style="margin-bottom:4px;">
                                    <span style="<?= $statusStyle ?> padding:2px 8px;border-radius:12px;font-size:0.8rem;">
                                        <?= esc($emp['user_type']) ?>
                                    </span>
                                </div>
                                <?php if (!$emp['is_pns'] && $emp['contract_end']): ?>
                                    <div
                                        style="font-size:0.75rem;color:<?= (strtotime($emp['contract_end']) < time()) ? '#ef4444' : '#6b7280' ?>;">
                                        TMT Kontrak:
                                        <?= date('d M Y', strtotime($emp['contract_end'])) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($emp['kgb_basis_date']): ?>
                                    <div style="font-size:0.875rem;">
                                        <?= date('d M Y', strtotime($emp['kgb_basis_date'])) ?>
                                    </div>
                                    <div style="font-size:0.75rem;color:#6b7280;">
                                        <?= $emp['kgb_basis_label'] ?>
                                    </div>
                                <?php else: ?>
                                    <span style="color:#9ca3af;">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($emp['kgb_next_date']): ?>
                                    <strong>
                                        <?= date('d M Y', strtotime($emp['kgb_next_date'])) ?>
                                    </strong>
                                <?php else: ?>
                                    <span style="color:#9ca3af;">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span style="<?= $statusStyle ?> padding:2px 10px;border-radius:12px;font-size:0.8rem;">
                                    <?= $daysDisplay ?>
                                </span>
                            </td>
                            <td>
                                <span style="<?= $statusStyle ?> padding:2px 10px;border-radius:12px;font-size:0.8rem;">
                                    <?= $statusIcon ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($emp['kgb_basis_date'] && $emp['kgb_status'] !== 'contract_expired'): ?>
                                    <button
                                        onclick="openKgbModal(<?= $emp['id'] ?>, '<?= esc($emp['name']) ?>', '<?= $emp['kgb_next_date'] ?>')"
                                        class="btn btn-primary btn-sm" style="font-size:0.8rem;padding:0.4rem 0.8rem;">
                                        <i class="fas fa-check" style="margin-right:4px;"></i>Tandai Diproses
                                    </button>
                                <?php elseif ($emp['kgb_status'] === 'contract_expired'): ?>
                                    <span style="color:#ef4444;font-size:0.8rem;"><i class="fas fa-times-circle"></i> Hak
                                        Berhenti</span>
                                <?php else: ?>
                                    <span style="color:#9ca3af;font-size:0.8rem;">TMT tidak ada</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Tandai Diproses -->
<div id="kgbModal"
    style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,0.5);align-items:center;justify-content:center;">
    <div
        style="background:#fff;border-radius:16px;padding:2rem;max-width:440px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,0.2);">
        <h3 style="margin-top:0;"><i class="fas fa-check-circle" style="color:#059669;margin-right:8px;"></i>Tandai KGB
            Diproses</h3>
        <p id="kgbModalDesc" style="color:#6b7280;font-size:0.9rem;"></p>
        <form id="kgbForm" method="post">
            <?= csrf_field() ?>
            <div class="form-group">
                <label class="form-label">Tanggal TMT KGB Yang Diproses</label>
                <input type="date" name="processed_date" id="processed_date" class="form-control" required>
                <div class="form-hint">Umumnya adalah tanggal jatuh tempo KGB. Ini menjadi basis perhitungan KGB
                    berikutnya (+2 tahun).</div>
            </div>
            <div style="display:flex;gap:1rem;justify-content:flex-end;margin-top:1.5rem;">
                <button type="button" onclick="closeKgbModal()" class="btn btn-secondary">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"
                        style="margin-right:6px;"></i>Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openKgbModal(id, name, nextDate) {
        document.getElementById('kgbForm').action = '<?= base_url('admin/kgb/update/') ?>' + id;
        document.getElementById('kgbModalDesc').textContent = 'Tandai KGB untuk: ' + name + '. Masukkan tanggal TMT KGB yang sudah diproses.';
        document.getElementById('processed_date').value = nextDate || '';
        document.getElementById('kgbModal').style.display = 'flex';
    }
    function closeKgbModal() {
        document.getElementById('kgbModal').style.display = 'none';
    }
    // Close on backdrop click
    document.getElementById('kgbModal').addEventListener('click', function (e) {
        if (e.target === this) closeKgbModal();
    });
</script>

<?= $this->endSection() ?>