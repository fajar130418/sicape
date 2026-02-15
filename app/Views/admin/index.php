<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>Admin Panel<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header">
    <h1 class="page-title">Admin Panel - Persetujuan Cuti</h1>
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
                    <th>Pemohon</th>
                    <th>Tipe Cuti</th>
                    <th>Periode</th>
                    <th>Durasi</th>
                    <th>Alasan</th>
                    <th>Lampiran</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($requests)): ?>
                    <tr>
                        <td colspan="8" style="text-align:center; color:#9ca3af; padding: 2rem;">Tidak ada permintaan cuti
                            yang perlu diproses.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($requests as $row): ?>
                        <tr>
                            <td>
                                <strong><?= $row['user_name'] ?></strong><br>
                                <small style="color: #6b7280;"><?= $row['nip'] ?></small>
                            </td>
                            <td><?= $row['type_name'] ?></td>
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
                            <td style="max-width: 200px;"><?= $row['reason'] ?></td>
                            <td>
                                <?php if ($row['attachment']): ?>
                                    <a href="<?= base_url('uploads/' . $row['attachment']) ?>" target="_blank"
                                        class="btn btn-sm btn-primary">Lihat</a>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
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
                            <td>
                                <?php if ($row['status'] == 'pending'): ?>
                                    <div style="display: flex; gap: 0.5rem;">
                                        <form action="<?= base_url('admin/process/' . $row['id'] . '/approved') ?>" method="post">
                                            <?= csrf_field() ?>
                                            <div style="margin-bottom: 5px;">
                                                <select name="admin_sign_as"
                                                    style="font-size: 0.75rem; padding: 2px; border: 1px solid #ccc; border-radius: 4px;">
                                                    <option value="Definitif">Definitif</option>
                                                    <option value="Plt.">Plt.</option>
                                                    <option value="Plh.">Plh.</option>
                                                </select>
                                            </div>
                                            <button type="submit" class="btn btn-sm btn-primary"
                                                onclick="confirmApproveAdmin(event, this)">Setuju</button>
                                        </form>
                                        <button type="button" class="btn btn-sm btn-danger"
                                            onclick="reject(<?= $row['id'] ?>)">Tolak</button>
                                    </div>

                                    <!-- Modal/Form Hidden for Rejection Note -->
                                    <div id="reject-form-<?= $row['id'] ?>" style="display:none; margin-top: 5px;">
                                        <form action="<?= base_url('admin/process/' . $row['id'] . '/rejected') ?>" method="post">
                                            <?= csrf_field() ?>
                                            <input type="text" name="admin_note" placeholder="Alasan penolakan..." required
                                                style="padding: 4px; border: 1px solid #ccc; border-radius: 4px; font-size: 0.8rem;">
                                            <button type="submit" class="btn btn-sm btn-danger">Kirim</button>
                                        </form>
                                    </div>

                                <?php else: ?>
                                    <span style="color: #6b7280; font-size: 0.9rem;">Selesai</span>
                                    <?php if ($row['status'] == 'approved'): ?>
                                        <a href="<?= base_url('leave/print/' . $row['id']) ?>" class="btn btn-sm btn-primary"
                                            target="_blank" style="margin-left:5px;">
                                            <i class="fas fa-print"></i> PDF
                                        </a>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <?php
                                // Debug: Check if is_head_of_agency is being passed correctly
                                // echo "<!-- Debug: Head=" . $row['is_head_of_agency'] . " Status=" . $row['status'] . " -->";
                                ?>

                                <?php if ($row['is_head_of_agency'] == 1): ?>
                                    <button onclick="openSignatureModal('<?= base_url('leave/print/' . $row['id']) ?>')"
                                        class="btn btn-sm btn-primary"
                                        style="margin-top: 5px; background-color: #ef4444; border-color: #ef4444; display: block;">
                                        <i class="fas fa-file-pdf"></i> PDF (Kepala)
                                    </button>
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

<!-- Signature Modal -->
<div id="signatureModal"
    style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; overflow-y: auto;">
    <div
        style="background: white; padding: 2rem; border-radius: 8px; width: 500px; max-width: 90%; max-height: 90vh; overflow-y: auto;">
        <h3 style="margin-top: 0; margin-bottom: 1rem;">Data Penanda Tangan (Khusus Kepala Dinas)</h3>
        <p style="margin-bottom: 1.5rem; color: #666; font-size: 0.9rem;">Silakan isi data untuk Atasan Langsung (Sekda)
            dan Pejabat Berwenang (Bupati).</p>

        <form id="signatureForm" action="" method="post" target="_blank">
            <?= csrf_field() ?>

            <!-- 1. PERTIMBANGAN ATASAN LANGSUNG -->
            <div
                style="background: #f9fafb; padding: 1rem; border-radius: 6px; margin-bottom: 1rem; border: 1px solid #e5e7eb;">
                <h4 style="margin-top: 0; margin-bottom: 0.5rem; color: #374151; font-size: 1rem;">1. Pertimbangan
                    Atasan Langsung (Sekda)</h4>

                <div style="margin-bottom: 0.5rem;">
                    <label style="display: block; margin-bottom: 0.25rem; font-size: 0.85rem;">Nama Lengkap</label>
                    <input type="text" name="supervisor_sign_name" placeholder="Atasan Langsung" required
                        style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;">
                </div>
                <div style="margin-bottom: 0.5rem;">
                    <label style="display: block; margin-bottom: 0.25rem; font-size: 0.85rem;">NIP</label>
                    <input type="text" name="supervisor_sign_nip" placeholder="Cth: 1970..." required
                        style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;">
                </div>
                <div style="margin-bottom: 0.5rem;">
                    <label style="display: block; margin-bottom: 0.25rem; font-size: 0.85rem;">Jabatan</label>
                    <input type="text" name="supervisor_sign_position" value="Sekretaris Daerah" required
                        style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;">
                </div>
            </div>

            <!-- 2. KEPUTUSAN PEJABAT BERWENANG -->
            <div
                style="background: #f9fafb; padding: 1rem; border-radius: 6px; margin-bottom: 1rem; border: 1px solid #e5e7eb;">
                <h4 style="margin-top: 0; margin-bottom: 0.5rem; color: #374151; font-size: 1rem;">2. Pejabat Berwenang
                    (Bupati)</h4>

                <div style="margin-bottom: 0.5rem;">
                    <label style="display: block; margin-bottom: 0.25rem; font-size: 0.85rem;">Nama Lengkap</label>
                    <input type="text" name="official_sign_name" placeholder="Nama Bupati Saat Ini" required
                        style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;">
                </div>
                <div style="margin-bottom: 0.5rem;">
                    <label style="display: block; margin-bottom: 0.25rem; font-size: 0.85rem;">NIP (Opsional)</label>
                    <input type="text" name="official_sign_nip" placeholder="Kosongkan jika Bupati"
                        style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;">
                </div>
                <div style="margin-bottom: 0.5rem;">
                    <label style="display: block; margin-bottom: 0.25rem; font-size: 0.85rem;">Jabatan</label>
                    <input type="text" name="official_sign_position" value="Bupati Seruyan" required
                        style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;">
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.5rem; margin-top: 1rem;">
                <button type="button" onclick="closeModal()"
                    style="padding: 0.5rem 1rem; background: #ccc; border: none; border-radius: 4px; cursor: pointer;">Batal</button>
                <button type="submit"
                    style="padding: 0.5rem 1rem; background: #2563eb; color: white; border: none; border-radius: 4px; cursor: pointer;">Cetak
                    PDF</button>
            </div>
        </form>
    </div>
</div>

<script>
    function confirmApproveAdmin(event, btn) {
        event.preventDefault();
        const form = btn.closest('form');
        Swal.fire({
            title: 'Setujui Pengajuan?',
            text: "Pastikan Anda telah memilih status tanda tangan yang benar (Definitif/Plt/Plh).",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#ef4444',
            confirmButtonText: 'Ya, Setujui',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    }

    function reject(id) {
        const form = document.getElementById('reject-form-' + id);
        if (form.style.display === 'none') {
            form.style.display = 'block';
        } else {
            form.style.display = 'none';
        }
    }

    function openSignatureModal(url) {
        document.getElementById('signatureForm').action = url;
        document.getElementById('signatureModal').style.display = 'flex';
    }

    function closeModal() {
        document.getElementById('signatureModal').style.display = 'none';
    }

    // Handle form submit to close modal after short delay allows new tab to open
    document.getElementById('signatureForm').onsubmit = function () {
        setTimeout(function () {
            closeModal();
        }, 500);
        return true;
    };

    // Close on click outside
    window.onclick = function (event) {
        var modal = document.getElementById('signatureModal');
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
</script>
<?= $this->endSection() ?>