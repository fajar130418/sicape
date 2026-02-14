<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>
<?= $title ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div>
        <h1 class="page-title">Pusat Laporan SICAPE</h1>
        <p style="margin: 0.5rem 0 0; color: #6b7280;">Silakan pilih jenis laporan yang ingin Anda lihat atau cetak.</p>
    </div>
    <div style="display: flex; gap: 0.75rem;">
        <a href="<?= base_url('admin') ?>" class="btn btn-danger" style="background: #4b5563;">
            <i class="fas fa-arrow-left" style="margin-right: 8px;"></i> Kembali ke Admin Panel
        </a>
    </div>
</div>

<div class="stats-grid">
    <a href="<?= base_url('report/recap') ?>" style="text-decoration: none; color: inherit;">
        <div class="stat-card" style="transition: transform 0.2s; cursor: pointer;"
            onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
            <div class="stat-icon" style="background: #e0e7ff; color: #4338ca;">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="stat-content">
                <h3 style="font-size: 1.25rem; margin-bottom: 0.5rem;">Rekap Tahunan</h3>
                <p>Rekapitulasi penggunaan cuti per pegawai dalam tabel tahunan.</p>
            </div>
            <i class="fas fa-chevron-right" style="color: #9ca3af; align-self: center;"></i>
        </div>
    </a>

    <a href="<?= base_url('report/details') ?>" style="text-decoration: none; color: inherit;">
        <div class="stat-card" style="transition: transform 0.2s; cursor: pointer;"
            onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
            <div class="stat-icon" style="background: #fef3c7; color: #d97706;">
                <i class="fas fa-list-alt"></i>
            </div>
            <div class="stat-content">
                <h3 style="font-size: 1.25rem; margin-bottom: 0.5rem;">Detail Riwayat</h3>
                <p>Riwayat detail pengajuan dengan filter tipe, tanggal, dan status.</p>
            </div>
            <i class="fas fa-chevron-right" style="color: #9ca3af; align-self: center;"></i>
        </div>
    </a>

    <a href="<?= base_url('report/quota') ?>" style="text-decoration: none; color: inherit;">
        <div class="stat-card" style="transition: transform 0.2s; cursor: pointer;"
            onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
            <div class="stat-icon" style="background: #d1fae5; color: #059669;">
                <i class="fas fa-chart-pie"></i>
            </div>
            <div class="stat-content">
                <h3 style="font-size: 1.25rem; margin-bottom: 0.5rem;">Pantauan Quota</h3>
                <p>Status real-time sisa jatah cuti (N, N-1, N-2) seluruh pegawai.</p>
            </div>
            <i class="fas fa-chevron-right" style="color: #9ca3af; align-self: center;"></i>
        </div>
    </a>
</div>

<div class="card" style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); border: none; color: white;">
    <div style="display: flex; align-items: center; gap: 1.5rem;">
        <div style="font-size: 3rem; opacity: 0.8;">
            <i class="fas fa-info-circle"></i>
        </div>
        <div>
            <h3 style="margin: 0; font-size: 1.25rem;">Tips Laporan</h3>
            <p style="margin: 0.5rem 0 0; opacity: 0.9; font-size: 0.9rem;">
                Gunakan fitur filter pada laporan detail untuk mencari data spesifik berdasarkan rentang waktu atau
                status pengajuan.
                Anda dapat mencetak laporan langsung menggunakan tombol cetak yang disediakan di setiap halaman.
            </p>
        </div>
    </div>
</div>
<?= $this->endSection() ?>