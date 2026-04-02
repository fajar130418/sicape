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
                    <th>Lampiran Pendukung</th>
                    <th>Status Persetujuan</th>
                    <th>Form Tanda Tangan</th>
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
                                <?= $row['duration'] ?> Hari
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
                                <?php if ($row['status'] == 'approved'): ?>
                                    <?php 
                                        $sf_status = $row['signed_form_status'] ?? 'pending_upload';
                                        $isBypassed = ($row['is_bypassed'] ?? 0) == 1;
                                    ?>
                                    
                                    <?php if ($isBypassed): ?>
                                        <span class="badge badge-success" style="background: #e0f2fe; color: #0369a1;">Bypassed</span>
                                    <?php elseif ($sf_status == 'pending_upload'): ?>
                                        <button onclick="openUploadModal(<?= $row['id'] ?>)" class="btn btn-sm btn-warning" style="background: #fff7ed; color: #9a3412; border: 1px solid #fed7aa;">
                                            <i class="fas fa-upload"></i> Unggah
                                        </button>
                                    <?php elseif ($sf_status == 'pending_approval'): ?>
                                        <span class="badge badge-warning" style="background: #fef9c3; color: #854d0e;">Verifikasi</span>
                                    <?php elseif ($sf_status == 'approved'): ?>
                                        <span class="badge badge-success">Terverifikasi</span>
                                    <?php elseif ($sf_status == 'rejected'): ?>
                                        <div style="display: flex; flex-direction: column; gap: 4px; border: 1px solid #fee2e2; padding: 6px; border-radius: 8px; background: #fffafb;">
                                            <span class="badge badge-danger">Ditolak</span>
                                                <div style="font-size: 0.7rem; color: #991b1b; line-height: 1.2;">
                                                    <strong>Ket:</strong> <?= $row['signed_form_note'] ?: 'Ditolak Admin. Silakan unggah ulang form yang benar.' ?>
                                                </div>
                                            <button onclick="openUploadModal(<?= $row['id'] ?>)" class="btn btn-sm btn-danger" style="font-size: 0.7rem; padding: 4px; width: 100%; margin-top: 4px;">
                                                <i class="fas fa-redo"></i> Unggah Ulang
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span style="color: #9ca3af;">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                    $isHead = isset($row['is_head_of_agency']) && $row['is_head_of_agency'] == 1;
                                    $canPrintHead = $isHead && $row['status'] != 'rejected';
                                ?>

                                <?php if ($row['status'] == 'approved' || $canPrintHead): ?>
                                    <div style="display: flex; gap: 4px;">
                                        <?php if ($canPrintHead): ?>
                                            <button onclick="openSignatureModal('<?= base_url('leave/print/' . $row['id']) ?>')" class="btn btn-sm btn-primary" style="background-color: #ef4444; border-color: #ef4444;">
                                                <i class="fas fa-file-pdf"></i> PDF
                                            </button>
                                        <?php else: ?>
                                            <a href="<?= base_url('leave/print/' . $row['id']) ?>" class="btn btn-sm btn-primary" target="_blank" style="background-color: #ef4444; border-color: #ef4444;">
                                                <i class="fas fa-file-pdf"></i> PDF
                                            </a>
                                        <?php endif; ?>
                                    </div>
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
</div>

<!-- Upload Form Modal -->
<div id="uploadModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; padding: 2rem; border-radius: 12px; width: 450px; max-width: 90%; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);">
        <h3 style="margin-top: 0; margin-bottom: 0.5rem; font-weight: 700;">Unggah Form Tanda Tangan</h3>
        <p style="margin-bottom: 1.5rem; color: #64748b; font-size: 0.9rem;">Silakan unggah pindai/foto formulir cuti yang telah ditandatangani oleh atasan dalam format PDF atau Gambar (JPG/PNG).</p>
        
        <form id="uploadForm" action="" method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label class="form-label">Pilih Berkas</label>
                <input type="file" name="signed_form" class="form-control" accept=".pdf,image/*" required>
                <p style="font-size: 0.75rem; color: #94a3b8; margin-top: 0.5rem;">Ukuran maksimal: 2MB</p>
            </div>
            
            <div style="display: flex; justify-content: flex-end; gap: 0.75rem;">
                <button type="button" onclick="closeUploadModal()" class="btn" style="background: #f1f5f9; color: #475569;">Batal</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-cloud-upload-alt" style="margin-right: 8px;"></i> Unggah Sekarang
                </button>
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

    function openUploadModal(id) {
        document.getElementById('uploadForm').action = '<?= base_url('leave/upload-signed-form-web/') ?>/' + id;
        document.getElementById('uploadModal').style.display = 'flex';
    }

    function closeUploadModal() {
        document.getElementById('uploadModal').style.display = 'none';
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
        var modal1 = document.getElementById('signatureModal');
        var modal2 = document.getElementById('uploadModal');
        if (event.target == modal1) modal1.style.display = "none";
        if (event.target == modal2) modal2.style.display = "none";
    }
</script>
<?= $this->endSection() ?>
