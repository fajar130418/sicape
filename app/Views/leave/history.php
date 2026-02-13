<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>Riwayat Cuti<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header">
    <h1 class="page-title">Riwayat Pengajuan Cuti</h1>
</div>

<div class="row" style="margin-bottom: 2rem;">
    <div class="col-span-4">
        <div class="card" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white;">
            <div style="font-size: 0.875rem; opacity: 0.9;">Sisa Cuti Tahunan</div>
            <div style="font-size: 2.25rem; font-weight: 700; margin-top: 0.5rem;"><?= $remainingLeave ?> Hari</div>
            <div style="font-size: 0.75rem; opacity: 0.8; margin-top: 0.5rem;">Tahun <?= date('Y') ?></div>
        </div>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tipe Cuti</th>
                    <th>Tanggal Pengajuan</th>
                    <th>Periode Cuti</th>
                    <th>Durasi</th>
                    <th>Lampiran</th>
                    <th>Status Atasan</th>
                    <th>Catatan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($history)): ?>
                    <tr>
                        <td colspan="9" style="text-align:center; color:#9ca3af; padding: 2rem;">Belum ada riwayat pengajuan cuti.</td>
                    </tr>
                <?php else: ?>
                    <?php $i = 1; foreach ($history as $row): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><?= $row['type_name'] ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($row['created_at'])) ?></td>
                            <td>
                                <?= date('d M Y', strtotime($row['start_date'])) ?> - 
                                <?= date('d M Y', strtotime($row['end_date'])) ?>
                            </td>
                            <td>
                                <?php 
                                    $start = new DateTime($row['start_date']);
                                    $end = new DateTime($row['end_date']);
                                    echo $start->diff($end)->days + 1 . ' Hari'; 
                                ?>
                            </td>
                            <td>
                                <?php if ($row['attachment']): ?>
                                    <a href="<?= base_url('uploads/' . $row['attachment']) ?>" target="_blank" class="btn btn-sm btn-primary">Lihat</a>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($row['status'] == 'approved'): ?>
                                    <span class="badge badge-success">Disetujui</span>
                                <?php elseif ($row['status'] == 'rejected'): ?>
                                    <span class="badge badge-danger">Ditolak</span>
                                <?php else: ?>
                                    <span class="badge badge-warning">Menunggu</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <!-- Check status OR if user contains is_head_of_agency flag -->
                                <?php 
                                    $isHead = isset($row['is_head_of_agency']) && $row['is_head_of_agency'] == 1;
                                    // Also check if status is NOT rejected (unless approved)
                                    $canPrintHead = $isHead && $row['status'] != 'rejected';
                                ?>

                                <?php if ($row['status'] == 'approved' || $canPrintHead): ?>
                                    <?php if ($canPrintHead): ?>
                                        <button onclick="openSignatureModal('<?= base_url('leave/print/' . $row['id']) ?>')" class="btn btn-sm btn-primary" style="background-color: #ef4444; border-color: #ef4444;">
                                            <i class="fas fa-file-pdf"></i> PDF
                                        </button>
                                    <?php else: ?>
                                        <a href="<?= base_url('leave/print/' . $row['id']) ?>" class="btn btn-sm btn-primary" target="_blank" style="background-color: #ef4444; border-color: #ef4444;">
                                            <i class="fas fa-file-pdf"></i> PDF
                                        </a>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span style="color: #9ca3af;">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<!-- Signature Modal -->
<div id="signatureModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; padding: 2rem; border-radius: 8px; width: 400px; max-width: 90%;">
        <h3 style="margin-top: 0; margin-bottom: 1rem;">Data Penanda Tangan</h3>
        <p style="margin-bottom: 1rem; color: #666; font-size: 0.9rem;">Khusus untuk Kepala Dinas, silakan isi pejabat yang menandatangani (Misal: Bupati / Sekda).</p>
        
        <form id="signatureForm" action="" method="post" target="_blank">
            <?= csrf_field() ?>
            <div style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem;">Nama Pejabat</label>
                <input type="text" name="sign_name" required style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;">
            </div>
            <div style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem;">NIP (Opsional)</label>
                <input type="text" name="sign_nip" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;">
            </div>
            <div style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem;">Jabatan (Cth: Bupati Seruyan)</label>
                <input type="text" name="sign_position" required style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;">
            </div>
            
            <div style="display: flex; justify-content: flex-end; gap: 0.5rem;">
                <button type="button" onclick="closeModal()" style="padding: 0.5rem 1rem; background: #ccc; border: none; border-radius: 4px; cursor: pointer;">Batal</button>
                <button type="submit" style="padding: 0.5rem 1rem; background: #2563eb; color: white; border: none; border-radius: 4px; cursor: pointer;">Cetak PDF</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openSignatureModal(url) {
        document.getElementById('signatureForm').action = url;
        document.getElementById('signatureModal').style.display = 'flex';
    }

    function closeModal() {
        document.getElementById('signatureModal').style.display = 'none';
    }

    // Handle form submit
    document.getElementById('signatureForm').onsubmit = function() {
        setTimeout(function() {
            closeModal();
        }, 500);
        return true;
    };

    // Close on click outside
    window.onclick = function(event) {
        var modal = document.getElementById('signatureModal');
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
</script>
<?= $this->endSection() ?>
