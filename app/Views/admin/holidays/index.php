<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>Manajemen Hari Libur<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header">
    <h1 class="page-title">Manajemen Hari Libur</h1>
</div>

<?php if(session()->getFlashdata('success')):?>
    <div style="background-color: #d1fae5; border: 1px solid #6ee7b7; color: #065f46; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
        <?= session()->getFlashdata('success') ?>
    </div>
<?php endif;?>

<?php if(session()->getFlashdata('errors')):?>
    <div style="background-color: #fee2e2; border: 1px solid #fca5a5; color: #b91c1c; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
        <ul>
        <?php foreach (session()->getFlashdata('errors') as $error) : ?>
            <li><?= esc($error) ?></li>
        <?php endforeach ?>
        </ul>
    </div>
<?php endif;?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Tambah Hari Libur Baru</h3>
    </div>
    <form action="<?= base_url('admin/holidays/store') ?>" method="post">
        <?= csrf_field() ?>
        <div class="form-grid">
            <div class="form-group col-span-2">
                <label class="form-label">Tanggal</label>
                <input type="text" name="date" id="date" class="form-control" placeholder="dd/mm/yyyy" required style="background-color: #fff;">
            </div>
            <div class="form-group col-span-2">
                <label class="form-label">Tipe</label>
                <select name="type" class="form-control" required style="background-color: #fff;">
                    <option value="Libur Nasional">Libur Nasional</option>
                    <option value="Cuti Bersama">Cuti Bersama</option>
                </select>
            </div>
            <div class="form-group col-span-6">
                <label class="form-label">Keterangan</label>
                <input type="text" name="description" class="form-control" placeholder="Contoh: Hari Raya Idul Fitri" required>
            </div>
            <div class="form-group col-span-2">
                <label class="form-label" style="opacity: 0;">Tambah</label>
                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                    <i class="fas fa-plus" style="margin-right: 8px;"></i> Tambah
                </button>
            </div>
        </div>
    </form>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Daftar Hari Libur</h3>
    </div>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 15%;">Tanggal</th>
                    <th style="width: 10%;">Hari</th>
                    <th style="width: 15%;">Tipe</th>
                    <th>Keterangan</th>
                    <th style="width: 10%;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($holidays)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; color: #6b7280; padding: 2rem;">Belum ada data hari libur.</td>
                    </tr>
                <?php else: ?>
                    <?php $i = 1; foreach($holidays as $holiday): ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= date('d/m/Y', strtotime($holiday['date'])) ?></td>
                        <td>
                            <?php 
                                $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                                echo $days[date('w', strtotime($holiday['date']))];
                            ?>
                        </td>
                        <td>
                            <?php if ($holiday['type'] == 'Cuti Bersama'): ?>
                                <span class="badge badge-warning" style="background-color: #fef3c7; color: #92400e; border: 1px solid #fcd34d;">Cuti Bersama</span>
                            <?php else: ?>
                                <span class="badge badge-success" style="background-color: #d1fae5; color: #065f46; border: 1px solid #6ee7b7;">Libur Nasional</span>
                            <?php endif; ?>
                        </td>
                        <td><?= esc($holiday['description']) ?></td>
                        <td>
                            <a href="<?= base_url('admin/holidays/delete/' . $holiday['id']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus hari libur ini?');">
                                <i class="fas fa-trash"></i> Hapus
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Flatpickr CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<!-- Flatpickr JS -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/id.js"></script>

<script>
    const dateInput = document.getElementById('date');

    flatpickr(dateInput, {
        dateFormat: "Y-m-d",
        altInput: true,
        altFormat: "d/m/Y",
        allowInput: true,
        locale: "id",
        disableMobile: "true"
    });
</script>
<?= $this->endSection() ?>
