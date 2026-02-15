<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>Manajemen Pegawai<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header">
    <h1 class="page-title">Daftar Pegawai</h1>
    <div style="display: flex; gap: 0.5rem;">
        <a href="<?= base_url('employee/export') ?>" class="btn btn-secondary" style="background-color: #10b981;">
            <i class="fas fa-file-export"></i> Ekspor Excel
        </a>
        <a href="<?= base_url('employee/import') ?>" class="btn btn-secondary" style="background-color: #6b7280;">
            <i class="fas fa-file-excel"></i> Impor Excel
        </a>
        <a href="<?= base_url('employee/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Pegawai
        </a>
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
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>NIP</th>
                    <th>Nama</th>
                    <th>Atasan saat ini</th>
                    <th>Sisa Cuti</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($employees)): ?>
                    <tr>
                        <td colspan="6" style="text-align:center; color:#9ca3af; padding: 2rem;">Belum ada data pegawai.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php $i = 1;
                    foreach ($employees as $row): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><?= $row['nip'] ?></td>
                            <td><?= $row['name'] ?></td>
                            <td>
                                <?php
                                if ($row['supervisor_id']) {
                                    $userModel = new \App\Models\UserModel();
                                    $supervisor = $userModel->find($row['supervisor_id']);
                                    echo $supervisor ? $supervisor['name'] : '-';
                                } else {
                                    echo '<span style="color: #9ca3af; font-style: italic;">Belum ada</span>';
                                }
                                ?>
                            </td>
                            <td>
                                <span class="badge badge-success"
                                    style="background-color: #10b981; color: white; padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.875rem;">
                                    <?= $row['remaining_leave'] ?? 0 ?> Hari
                                </span>
                            </td>
                            <td>
                                <a href="<?= base_url('employee/edit/' . $row['id']) ?>" class="btn btn-sm btn-primary"
                                    style="background-color: #f59e0b; margin-right: 5px;">Edit</a>
                                <a href="<?= base_url('employee/delete/' . $row['id']) ?>" class="btn btn-sm btn-danger"
                                    onclick="return confirm('Yakin ingin menghapus pegawai ini?')">Hapus</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>