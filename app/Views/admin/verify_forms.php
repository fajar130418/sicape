<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>Verifikasi Form Cuti<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div>
        <h1 class="page-title">Verifikasi Form Cuti</h1>
        <p style="color: #64748b; margin-top: 0.25rem;">Tinjau dan setujui formulir cuti yang telah ditandatangani pegawai.</p>
    </div>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div style="background: #f0fdf4; color: #166534; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; border: 1px solid #bbf7d0; display: flex; align-items: center; gap: 10px;">
        <i class="fas fa-check-circle"></i>
        <?= session()->getFlashdata('success') ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Pegawai</th>
                    <th>Tipe Cuti</th>
                    <th>Periode</th>
                    <th>Status</th>
                    <th>Berkas</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pendingForms)): ?>
                    <tr>
                        <td colspan="5" style="text-align:center; color:#9ca3af; padding: 3rem;">
                            <div style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"><i class="fas fa-file-invoice"></i></div>
                            Tidak ada form yang menunggu verifikasi saat ini.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($pendingForms as $row): ?>
                        <tr>
                            <td>
                                <div style="font-weight: 600; color: #1e293b;"><?= $row['user_name'] ?></div>
                                <div style="font-size: 0.8rem; color: #64748b;">NIP: <?= $row['nip'] ?></div>
                            </td>
                            <td><?= $row['type_name'] ?></td>
                            <td>
                                <div style="font-size: 0.9rem;">
                                    <?= date('d M Y', strtotime($row['start_date'])) ?>
                                    <span style="color: #94a3b8;">&rarr;</span>
                                    <?= date('d M Y', strtotime($row['end_date'])) ?>
                                </div>
                                <div style="font-size: 0.75rem; color: #94a3b8;"><?= $row['duration'] ?> Hari</div>
                            </td>
                            <td>
                                <?php if ($row['signed_form_status'] == 'pending_approval'): ?>
                                    <span style="background: #eff6ff; color: #1d4ed8; padding: 4px 8px; border-radius: 6px; font-size: 0.75rem; font-weight: 600; border: 1px solid #dbeafe;">Menunggu Verifikasi</span>
                                <?php elseif ($row['signed_form_status'] == 'rejected'): ?>
                                    <span style="background: #fff1f2; color: #be123c; padding: 4px 8px; border-radius: 6px; font-size: 0.75rem; font-weight: 600; border: 1px solid #ffe4e6;">Ditolak</span>
                                <?php else: ?>
                                    <span style="background: #f8fafc; color: #64748b; padding: 4px 8px; border-radius: 6px; font-size: 0.75rem; font-weight: 600; border: 1px solid #e2e8f0;">Belum Unggah</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($row['signed_form_status'] == 'pending_approval'): ?>
                                    <a href="<?= base_url('uploads/' . $row['signed_form']) ?>" target="_blank" class="btn btn-sm" style="background: #eef2ff; color: #4f46e5; border: 1px solid #e0e7ff; padding: 4px 8px;">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                <?php else: ?>
                                    <span style="color: #cbd5e1; font-size: 0.8rem;">&mdash;</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display: flex; gap: 8px;">
                                    <?php if ($row['signed_form_status'] == 'pending_approval'): ?>
                                        <form action="<?= base_url('admin/approve-signed-form/' . $row['id']) ?>" method="post" onsubmit="return confirm('Setujui form ini? Penguncian cuti pegawai akan dibuka.')">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-sm btn-primary" style="background: #10b981;">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                        
                                        <button onclick="openRejectModal(<?= $row['id'] ?>)" class="btn btn-sm btn-danger">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    <?php endif; ?>
                                    
                                    <form action="<?= base_url('admin/bypass-lock/' . $row['id']) ?>" method="post" onsubmit="return confirm('Bypass penguncian tanpa form? Lakukan ini hanya dalam keadaan darurat.')">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm" style="background: #f8fafc; color: #64748b; border: 1px solid #e2e8f0;" title="Bypass Kunci">
                                            Bypass
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Reject Modal -->
<div id="rejectModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; padding: 2rem; border-radius: 12px; width: 400px; max-width: 90%; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);">
        <h3 style="margin-top: 0; margin-bottom: 0.5rem; font-weight: 700;">Tolak Form</h3>
        <p style="margin-bottom: 1.5rem; color: #64748b; font-size: 0.9rem;">Berikan alasan penolakan agar pegawai dapat memperbaikinya.</p>
        
        <form id="rejectForm" action="" method="post">
            <?= csrf_field() ?>
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label class="form-label">Alasan Penolakan</label>
                <textarea name="note" class="form-control" rows="3" placeholder="Contoh: Tanda tangan tidak jelas atau salah form..." required></textarea>
            </div>
            
            <div style="display: flex; justify-content: flex-end; gap: 0.75rem;">
                <button type="button" onclick="closeRejectModal()" class="btn" style="background: #f1f5f9; color: #475569;">Batal</button>
                <button type="submit" class="btn btn-danger">Tolak & Kirim Masukan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openRejectModal(id) {
        document.getElementById('rejectForm').action = '<?= base_url('admin/reject-signed-form/') ?>/' + id;
        document.getElementById('rejectModal').style.display = 'flex';
    }

    function closeRejectModal() {
        document.getElementById('rejectModal').style.display = 'none';
    }

    // Close on click outside
    window.onclick = function(event) {
        var modal = document.getElementById('rejectModal');
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
</script>
<?= $this->endSection() ?>
