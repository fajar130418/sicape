<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>Data Atasan<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header">
    <h1 class="page-title">Data Atasan / Pejabat</h1>
    <a href="<?= base_url('employee/create') ?>" class="btn btn-primary">
        <i class="fas fa-plus" style="margin-right: 8px;"></i> Tambah Pejabat
    </a>
</div>

<?php if(session()->getFlashdata('success')): ?>
    <div style="background: #d1fae5; color: #065f46; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; border: 1px solid #a7f3d0;">
        <?= session()->getFlashdata('success') ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Nama / NIP</th>
                    <th>Jabatan</th>
                    <th>Golongan</th>
                    <th>Kontak</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($supervisors)): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 2rem; color: #6b7280;">
                            Belum ada data atasan yang ditambahkan. <br>
                            Silakan edit pegawai dan centang "Tandai sebagai Atasan" atau tambah baru.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach($supervisors as $s): ?>
                    <tr>
                        <td>
                            <div style="font-weight: 600; color: #111827;"><?= $s['name'] ?></div>
                            <div style="font-size: 0.85rem; color: #6b7280;"><?= $s['nip'] ?></div>
                        </td>
                        <td>
                            <div style="font-weight: 500;"><?= $s['position'] ?: '-' ?></div>
                            <div style="font-size: 0.85rem; color: #6b7280;"><?= $s['unit'] ?: '-' ?></div>
                        </td>
                        <td><?= $s['rank'] ?: '-' ?></td>
                        <td><?= $s['phone'] ?: '-' ?></td>
                        <td>
                            <div style="display: flex; gap: 0.5rem;">
                                <a href="<?= base_url('employee/edit/' . $s['id']) ?>" class="btn btn-sm btn-primary" style="background: #fbbf24; color: #78350f;">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="<?= base_url('employee/delete/' . $s['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus atasan ini?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
