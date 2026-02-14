<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>
<?= $title ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div>
        <h1 class="page-title">
            <?= $title ?>
        </h1>
        <p style="margin: 0.5rem 0 0; color: #6b7280;">Menampilkan data penggunaan cuti tahun
            <?= $year ?>
        </p>
    </div>
    <div style="display: flex; gap: 0.75rem;">
        <a href="<?= base_url('report') ?>" class="btn btn-danger" style="background: #4b5563;">
            <i class="fas fa-arrow-left" style="margin-right: 8px;"></i> Kembali
        </a>
        <form action="<?= base_url('report/recap') ?>" method="get" style="display: flex; gap: 0.5rem;">
            <select name="year" class="form-control" style="width: 120px;" onchange="this.form.submit()">
                <?php for ($y = date('Y'); $y >= 2023; $y--): ?>
                    <option value="<?= $y ?>" <?= $year == $y ? 'selected' : '' ?>>
                        <?= $y ?>
                    </option>
                <?php endfor; ?>
            </select>
        </form>
        <button onclick="window.print()" class="btn btn-primary">
            <i class="fas fa-print" style="margin-right: 8px;"></i> Cetak Laporan
        </button>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table style="font-size: 0.85rem;">
            <thead>
                <tr>
                    <th style="width: 50px;">No</th>
                    <th>Nama Pegawai / NIP</th>
                    <?php foreach ($leave_types as $type): ?>
                        <th style="text-align: center;">
                            <?= $type['name'] ?>
                        </th>
                    <?php endforeach; ?>
                    <th style="text-align: center; background: #f3f4f6; font-weight: 700;">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $no = 1;
                foreach ($users as $user):
                    $rowTotal = 0;
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
                        <?php foreach ($leave_types as $type): ?>
                            <?php
                            $days = 0;
                            foreach ($user['recap'] as $item) {
                                if ($item['name'] == $type['name']) {
                                    $days = $item['total_days'];
                                    break;
                                }
                            }
                            $rowTotal += $days;
                            ?>
                            <td style="text-align: center;">
                                <?= $days > 0 ? $days . ' hari' : '-' ?>
                            </td>
                        <?php endforeach; ?>
                        <td style="text-align: center; background: #f9fafb; font-weight: 700; color: var(--primary-color);">
                            <?= $rowTotal ?> hari
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
        .page-header form,
        .sidebar-footer,
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

        .page-header h1 {
            font-size: 1.5rem !important;
        }

        table {
            border: 1px solid #e5e7eb !important;
        }

        th,
        td {
            border: 1px solid #e5e7eb !important;
            padding: 0.5rem !important;
        }
    }
</style>
<?= $this->endSection() ?>