<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>
<?= $title ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header">
    <h1 class="page-title"><?= $title ?></h1>
    <div style="display: flex; gap: 0.75rem;">
        <a href="<?= base_url('report') ?>" class="btn btn-danger" style="background: #4b5563;">
            <i class="fas fa-arrow-left" style="margin-right: 8px;"></i> Kembali
        </a>
        <button onclick="window.print()" class="btn btn-primary">
            <i class="fas fa-print" style="margin-right: 8px;"></i> Cetak
        </button>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th style="width: 50px;">No</th>
                    <th>Nama Pegawai / NIP</th>
                    <th style="text-align: center;">Jatah N-2</th>
                    <th style="text-align: center;">Jatah N-1</th>
                    <th style="text-align: center;">Jatah N</th>
                    <th style="text-align: center; background: #f3f4f6; font-weight: 700;">TOTAL SISA</th>
                    <th style="text-align: center;">Status CLTN</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $no = 1;
                foreach ($users as $user):
                    $totalQuota = $user['leave_balance_n'] + $user['leave_balance_n1'] + $user['leave_balance_n2'];
                    ?>
                    <tr>
                        <td>
                            <?= $no++ ?>
                        </td>
                        <td>
                            <div style="font-weight: 600;">
                                <?= $user['name'] ?>
                            </div>
                            <div style="color: #6b7280; font-size: 0.75rem;">
                                <?= $user['nip'] ?>
                            </div>
                        </td>
                        <td
                            style="text-align: center; color: <?= $user['leave_balance_n2'] > 0 ? '#b91c1c' : '#9ca3af' ?>;">
                            <?= $user['leave_balance_n2'] ?>
                        </td>
                        <td
                            style="text-align: center; color: <?= $user['leave_balance_n1'] > 0 ? '#92400e' : '#9ca3af' ?>;">
                            <?= $user['leave_balance_n1'] ?>
                        </td>
                        <td style="text-align: center; color: <?= $user['leave_balance_n'] > 0 ? '#065f46' : '#9ca3af' ?>;">
                            <?= $user['leave_balance_n'] ?>
                        </td>
                        <td style="text-align: center; background: #f9fafb; font-weight: 700; color: var(--primary-color);">
                            <?= $totalQuota ?> hari
                        </td>
                        <td style="text-align: center;">
                            <?php
                            $joinDate = $user['join_date'] ?? date('Y-m-d');
                            $workingYears = date_diff(date_create($joinDate), date_create('today'))->y;
                            if ($user['user_type'] == 'PNS' && $workingYears >= 5): ?>
                                <span class="badge badge-success">Berhak</span>
                            <?php else: ?>
                                <span class="badge badge-danger" title="Masa kerja atau tipe tidak sesuai">Belum Berhak</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
    @media print {

        .sidebar,
        .btn-primary {
            display: none !important;
        }

        .main-content {
            margin-left: 0 !important;
            padding: 0 !important;
        }

        .card {
            box-shadow: none !important;
            border: none !important;
            padding: 0 !important;
        }

        th,
        td {
            padding: 0.5rem !important;
            border-bottom: 1px solid #e5e7eb !important;
        }
    }
</style>
<?= $this->endSection() ?>