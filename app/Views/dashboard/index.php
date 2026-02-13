<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>Dashboard<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header">
    <h1 class="page-title">Dashboard</h1>
    <span class="date-today"><?= date('d F Y') ?></span>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-content">
            <h3><?= $yearsOfService ?> Tahun</h3>
            <p>Masa Kerja</p>
        </div>
        <div class="stat-icon" style="background: #e0e7ff; color: #4f46e5;">
            <i class="fas fa-briefcase"></i>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-content">
            <h3><?= $annualLeaveQuota ?> Hari</h3>
            <p>Kuota Cuti Tahunan</p>
        </div>
        <div class="stat-icon" style="background: #d1fae5; color: #059669;">
            <i class="fas fa-calendar-check"></i>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-content">
            <h3><?= $usedAnnualLeave ?> Hari</h3>
            <p>Cuti Terpakai</p>
        </div>
        <div class="stat-icon" style="background: #fee2e2; color: #b91c1c;">
            <i class="fas fa-calendar-minus"></i>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-content">
            <h3><?= $remainingAnnualLeave ?> Hari</h3>
            <p>Sisa Cuti Tahunan</p>
            <div style="font-size: 0.75rem; color: #92400e; margin-top: 0.5rem; line-height: 1.2;">
                N: <?= $leaveBreakdown['n'] ?> | 
                N-1: <?= $leaveBreakdown['n1'] ?> | 
                N-2: <?= $leaveBreakdown['n2'] ?>
            </div>
        </div>
        <div class="stat-icon" style="background: #fef3c7; color: #d97706;">
            <i class="fas fa-hourglass-half"></i>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Riwayat Pengajuan Terakhir</h3>
        <a href="<?= base_url('leave/history') ?>" class="btn btn-primary btn-sm">Lihat Semua</a>
    </div>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Tipe Cuti</th>
                    <th>Tanggal Mulai</th>
                    <th>Tanggal Selesai</th>
                    <th>Durasi (Hari)</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($history)): ?>
                    <tr>
                        <td colspan="5" style="text-align:center; color:#9ca3af; padding: 2rem;">Belum ada riwayat pengajuan cuti.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($history as $row): ?>
                        <tr>
                            <td><?= $row['type_name'] ?></td>
                            <td><?= date('d M Y', strtotime($row['start_date'])) ?></td>
                            <td><?= date('d M Y', strtotime($row['end_date'])) ?></td>
                            <td>
                                <?php 
                                    $start = new DateTime($row['start_date']);
                                    $end = new DateTime($row['end_date']);
                                    echo $start->diff($end)->days + 1; 
                                ?>
                            </td>
                            <td>
                                <?php if ($row['status'] == 'approved'): ?>
                                    <span class="badge badge-success">Disetujui</span>
                                <?php elseif ($row['status'] == 'pending'): ?>
                                    <span class="badge badge-warning">Menunggu</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Ditolak</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
