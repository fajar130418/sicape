<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>Impor Data Pegawai
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header">
    <h1 class="page-title">Impor Data Pegawai dari Excel</h1>
    <a href="<?= base_url('employee') ?>" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>
</div>

<?php if (session()->getFlashdata('error')): ?>
    <div
        style="background-color: #fee2e2; border: 1px solid #ef4444; color: #991b1b; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
        <?= session()->getFlashdata('error') ?>
    </div>
<?php endif; ?>

<div class="card">
    <div style="padding: 1.5rem;">
        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1.5rem;">
            <div>
                <h3 style="margin-top: 0; margin-bottom: 0.5rem;">Petunjuk Impor</h3>
                <p style="color: #6b7280; margin: 0;">Gunakan file dengan ekstensi <strong>.xlsx</strong> atau
                    <strong>.xls</strong>.
                </p>
            </div>
            <a href="<?= base_url('employee/import/template') ?>" class="btn btn-primary"
                style="background-color: #10b981;">
                <i class="fas fa-download"></i> Unduh Template Contoh (.xlsx)
            </a>
        </div>

        <ul style="margin-bottom: 1.5rem; color: #4b5563; line-height: 1.6;">
            <li>Pastikan baris pertama adalah <strong>Header</strong> (akan dilewati oleh sistem).</li>
            <li>Urutan kolom dalam Excel harus sebagai berikut:
                <div
                    style="margin-top: 0.5rem; font-family: monospace; background: #f3f4f6; padding: 1rem; border-radius: 8px; font-size: 0.85rem; display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.5rem;">
                    <div>1. NIP (Wajib)</div>
                    <div>2. NIK</div>
                    <div>3. Nama Lengkap (Wajib)</div>
                    <div>4. Email Aktif</div>
                    <div>5. Pangkat / Golongan</div>
                    <div>6. Pendidikan</div>
                    <div>7. Jabatan</div>
                    <div>8. Unit Kerja</div>
                    <div>9. TMT (YYYY-MM-DD)</div>
                    <div>10. Jenis Kelamin (L/P)</div>
                    <div>11. No. HP</div>
                    <div>12. Alamat</div>
                    <div>13. Status (PNS/PPPK/PPPK Paruh Waktu)</div>
                    <div>14. Akses (admin/pegawai)</div>
                    <div>15. Tgl Berakhir Kontrak</div>
                </div>
            </li>
            <li style="margin-top: 1rem;"><strong>Khusus PPPK:</strong> Pastikan kolom nomor 15 (Tgl Berakhir Kontrak)
                diisi.</li>
            <li>Password default untuk pegawai yang baru diimpor adalah: <strong>123456</strong>.</li>
        </ul>

        <form action="<?= base_url('employee/import/process') ?>" method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label class="form-label">Pilih File Excel</label>
                <input type="file" name="excel_file" class="form-control" accept=".xlsx, .xls" required>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-upload"></i> Mulai Impor Data
            </button>
        </form>
    </div>
</div>
<?= $this->endSection() ?>