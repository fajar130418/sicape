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

<div class="card no-print">
    <form action="<?= base_url('report/details') ?>" method="get" class="form-grid">
        <div class="form-group col-span-3">
            <label class="form-label">Mulai Tanggal</label>
            <input type="date" name="start_date" class="form-control" value="<?= $filters['start_date'] ?>">
        </div>
        <div class="form-group col-span-3">
            <label class="form-label">Sampai Tanggal</label>
            <input type="date" name="end_date" class="form-control" value="<?= $filters['end_date'] ?>">
        </div>
        <div class="form-group col-span-2">
            <label class="form-label">Jenis Cuti</label>
            <select name="type" class="form-control">
                <option value="">Semua</option>
                <?php foreach ($leave_types as $type): ?>
                    <option value="<?= $type['id'] ?>" <?= $filters['type'] == $type['id'] ? 'selected' : '' ?>>
                        <?= $type['name'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group col-span-2">
            <label class="form-label">Pegawai</label>
            <select name="user_id" class="form-control">
                <option value="">Semua</option>
                <?php foreach ($users as $user): ?>
                    <option value="<?= $user['id'] ?>" <?= $filters['user_id'] == $user['id'] ? 'selected' : '' ?>>
                        <?= $user['name'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group col-span-2">
            <label class="form-label">Status</label>
            <select name="status" class="form-control">
                <option value="">Semua</option>
                <option value="pending" <?= $filters['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="approved" <?= $filters['status'] == 'approved' ? 'selected' : '' ?>>Disetujui</option>
                <option value="rejected" <?= $filters['status'] == 'rejected' ? 'selected' : '' ?>>Ditolak</option>
            </select>
        </div>
        <div class="form-group col-span-2" style="justify-content: flex-end;">
            <button type="submit" class="btn btn-primary" style="width: 100%;">
                <i class="fas fa-filter" style="margin-right: 8px;"></i> Filter
            </button>
        </div>
    </form>
</div>

<div class="card">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Tgl Pengajuan</th>
                    <th>Pegawai</th>
                    <th>Jenis Cuti</th>
                    <th>Periode</th>
                    <th>Durasi</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($requests)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 2rem; color: #6b7280;">Data tidak ditemukan.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($requests as $req): ?>
                        <tr>
                            <td style="font-size: 0.8rem;">
                                <?= date('d/m/Y H:i', strtotime($req['created_at'])) ?>
                            </td>
                            <td>
                                <div style="font-weight: 600;">
                                    <?= $req['user_name'] ?>
                                </div>
                                <div style="color: #6b7280; font-size: 0.75rem;">
                                    <?= $req['nip'] ?>
                                </div>
                            </td>
                            <td>
                                <?= $req['type_name'] ?>
                            </td>
                            <td>
                                <div style="font-size: 0.85rem;">
                                    <?= date('d/m/Y', strtotime($req['start_date'])) ?> -
                                    <?= date('d/m/Y', strtotime($req['end_date'])) ?>
                                </div>
                            </td>
                            <td><strong>
                                    <?= $req['duration'] ?>
                                </strong> hari</td>
                            <td>
                                <span
                                    class="badge badge-<?= $req['status'] == 'approved' ? 'success' : ($req['status'] == 'rejected' ? 'danger' : 'warning') ?>">
                                    <?= ucfirst($req['status']) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
    @media print {

        .sidebar,
        .no-print,
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