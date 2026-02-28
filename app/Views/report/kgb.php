<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>Laporan KGB
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-file-invoice" style="margin-right:8px;"></i> Laporan Kenaikan Gaji
            Berkala</h1>
        <p style="margin: 0.5rem 0 0; color: #6b7280;">Laporan status dan jadwal KGB seluruh pegawai PNS & PPPK.</p>
    </div>
    <div style="display: flex; gap: 0.75rem;">
        <button onclick="window.print()" class="btn btn-primary">
            <i class="fas fa-print" style="margin-right: 8px;"></i> Cetak Laporan
        </button>
        <a href="<?= base_url('report') ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left" style="margin-right: 8px;"></i> Kembali
        </a>
    </div>
</div>

<div class="card no-print">
    <form action="<?= base_url('report/kgb') ?>" method="get"
        style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 200px;">
            <label class="form-label">Filter Status KGB</label>
            <select name="status" class="form-control">
                <option value="">Semua Status</option>
                <option value="overdue" <?= $status_filter == 'overdue' ? 'selected' : '' ?>>🔴 Terlambat</option>
                <option value="warning" <?= $status_filter == 'warning' ? 'selected' : '' ?>>🟡 Segera</option>
                <option value="contract_expired" <?= $status_filter == 'contract_expired' ? 'selected' : '' ?>>⚪ Hak
                    Berhenti</option>
                <option value="outside_contract" <?= $status_filter == 'outside_contract' ? 'selected' : '' ?>>🔵 Di Luar
                    Kontrak</option>
                <option value="ok" <?= $status_filter == 'ok' ? 'selected' : '' ?>>🟢 Aman</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary" style="height: 42px;">
            <i class="fas fa-filter" style="margin-right: 8px;"></i> Filter
        </button>
    </form>
</div>

<div class="card">
    <div style="text-align: center; margin-bottom: 2rem; display: none;" class="print-only">
        <h2 style="margin: 0; color: #111827;">DINAS PERPUSTAKAAN DAN KEARSIPAN</h2>
        <h3 style="margin: 0.25rem 0; color: #111827;">KABUPATEN SERUYAN</h3>
        <p style="margin: 0; font-size: 0.875rem; color: #4b5563;">Laporan Kenaikan Gaji Berkala (KGB) Pegawai</p>
        <hr style="margin: 1rem 0; border: 1px solid #000;">
    </div>

    <div class="table-responsive">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="border-bottom: 1px solid #e5e7eb; text-align: left;">
                    <th style="padding: 1rem; font-weight: 600;">No</th>
                    <th style="padding: 1rem; font-weight: 600;">Nama / NIP</th>
                    <th style="padding: 1rem; font-weight: 600;">Status / Golongan</th>
                    <th style="padding: 1rem; font-weight: 600;">TMT KGB Terakhir</th>
                    <th style="padding: 1rem; font-weight: 600;">Jadwal KGB Berikutnya</th>
                    <th style="padding: 1rem; font-weight: 600;">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($kgbList)): ?>
                    <tr>
                        <td colspan="6" style="padding: 2rem; text-align: center; color: #6b7280;">Data tidak ditemukan.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php $no = 1;
                    foreach ($kgbList as $emp): ?>
                        <tr style="border-bottom: 1px solid #f3f4f6;">
                            <td style="padding: 1rem;">
                                <?= $no++ ?>
                            </td>
                            <td style="padding: 1rem;">
                                <div style="font-weight: 600;">
                                    <?= esc($emp['name']) ?>
                                </div>
                                <div style="font-size: 0.8rem; color: #6b7280;">
                                    <?= esc($emp['nip']) ?>
                                </div>
                            </td>
                            <td style="padding: 1rem;">
                                <div>
                                    <?= esc($emp['user_type']) ?>
                                </div>
                                <div style="font-size: 0.8rem; color: #6b7280;">
                                    <?= esc($emp['rank']) ?>
                                </div>
                            </td>
                            <td style="padding: 1rem;">
                                <?= $emp['kgb_basis_date'] ? date('d M Y', strtotime($emp['kgb_basis_date'])) : '-' ?>
                            </td>
                            <td style="padding: 1rem;">
                                <strong style="color: #111827;">
                                    <?= $emp['kgb_next_date'] ? date('d M Y', strtotime($emp['kgb_next_date'])) : '-' ?>
                                </strong>
                            </td>
                            <td style="padding: 1rem;">
                                <?php
                                $label = match ($emp['kgb_status']) {
                                    'overdue' => '🔴 Terlambat',
                                    'warning' => '🟡 Segera',
                                    'contract_expired' => '⚪ Berhenti',
                                    'outside_contract' => '🔵 Di Luar Kontrak',
                                    'ok' => '🟢 Aman',
                                    default => '❓ Unknown'
                                };
                                ?>
                                <span style="font-size: 0.875rem;">
                                    <?= $label ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div style="margin-top: 2rem; display: none;" class="print-only">
        <div style="float: right; text-align: center; width: 250px;">
            <p>Kuala Pembuang,
                <?= date('d F Y') ?>
            </p>
            <p>Admin Sistem,</p>
            <br><br><br>
            <p><strong>( ____________________ )</strong></p>
        </div>
        <div style="clear: both;"></div>
    </div>
</div>

<style>
    @media print {

        .no-print,
        .mobile-navbar,
        .sidebar,
        .btn,
        .page-header {
            display: none !important;
        }

        .main-content {
            margin-left: 0 !important;
            padding: 0 !important;
            width: 100% !important;
        }

        .card {
            box-shadow: none !important;
            border: none !important;
            padding: 0 !important;
        }

        .print-only {
            display: block !important;
        }

        body {
            background-color: white !important;
        }
    }
</style>
<?= $this->endSection() ?>