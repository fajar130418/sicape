<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>Data Administrator<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header">
    <h1 class="page-title">Data Administrator Sistem</h1>
    <a href="<?= base_url('employee/create') ?>" class="btn btn-primary">
        <i class="fas fa-plus" style="margin-right: 8px;"></i> Tambah Admin Baru
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
                    <th>Unit Kerja</th>
                    <th>Kontak</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($admins)): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 2rem; color: #6b7280;">
                            Belum ada data administrator.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach($admins as $a): ?>
                    <tr>
                        <td>
                            <div style="font-weight: 600; color: #111827;"><?= $a['name'] ?></div>
                            <div style="font-size: 0.85rem; color: #6b7280;"><?= $a['nip'] ?></div>
                            <?php if($a['id'] == session()->get('id')): ?>
                                <span class="badge badge-success" style="margin-top: 0.25rem; display: inline-block;">Anda</span>
                            <?php endif; ?>
                        </td>
                        <td><?= $a['position'] ?: '-' ?></td>
                        <td><?= $a['unit'] ?: '-' ?></td>
                        <td><?= $a['phone'] ?: '-' ?></td>
                        <td>
                            <div style="display: flex; gap: 0.5rem;">
                                <a href="<?= base_url('employee/edit/' . $a['id']) ?>" class="btn btn-sm btn-primary" style="background: #fbbf24; color: #78350f;">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php if($a['id'] != session()->get('id')): ?>
                                <a href="<?= base_url('employee/delete/' . $a['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus admin ini?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                                <?php endif; ?>
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
