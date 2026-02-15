<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>Persetujuan Cuti<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header">
    <h1 class="page-title">Persetujuan Cuti (Sebagai Atasan)</h1>
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
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($requests)): ?>
                    <tr>
                        <td colspan="7" style="text-align:center; color:#9ca3af; padding: 2rem;">Tidak ada permintaan cuti
                            yang perlu disetujui.</td>
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
                                <?= date('d/m/Y', strtotime($row['start_date'])) ?> -
                                <?= date('d/m/Y', strtotime($row['end_date'])) ?>
                            </td>
                            <td>
                                <?php
                                $start = new DateTime($row['start_date']);
                                $end = new DateTime($row['end_date']);
                                echo $start->diff($end)->days + 1 . ' Hari';
                                ?>
                            </td>
                            <td><?= $row['reason'] ?></td>
                            <td>
                                <?php if ($row['attachment']): ?>
                                    <a href="<?= base_url('uploads/' . $row['attachment']) ?>" target="_blank"
                                        class="btn btn-sm btn-primary">Lihat</a>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                    <form action="<?= base_url('approval/process/' . $row['id'] . '/approved') ?>" method="post"
                                        style="display:inline;" id="form-approve-<?= $row['id'] ?>">
                                        <?= csrf_field() ?>
                                        <div style="margin-bottom: 5px;">
                                            <select name="supervisor_sign_as" class="form-control"
                                                style="font-size: 0.8rem; padding: 2px;">
                                                <option value="Definitif">Definitif</option>
                                                <option value="Plt.">Plt.</option>
                                                <option value="Plh.">Plh.</option>
                                            </select>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-primary"
                                            onclick="confirmApprove(<?= $row['id'] ?>)">Setuju</button>
                                    </form>

                                    <button type="button" class="btn btn-sm btn-warning"
                                        onclick="toggleNote(<?= $row['id'] ?>, 'deferred')"
                                        style="background-color: #f59e0b; color: white;">Tangguhkan</button>
                                    <button type="button" class="btn btn-sm btn-info"
                                        onclick="toggleNote(<?= $row['id'] ?>, 'changed')"
                                        style="background-color: #3b82f6; color: white;">Perubahan</button>
                                    <button type="button" class="btn btn-sm btn-danger"
                                        onclick="toggleNote(<?= $row['id'] ?>, 'rejected')">Tolak</button>
                                </div>

                                <div id="note-form-<?= $row['id'] ?>" style="display:none; margin-top: 10px;">
                                    <form action="" method="post" id="form-note-<?= $row['id'] ?>">
                                        <?= csrf_field() ?>
                                        <textarea name="note" placeholder="Catatan/Alasan..." required
                                            style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 4px; margin-bottom: 0.5rem; font-family: sans-serif;"></textarea>
                                        <button type="submit" class="btn btn-sm btn-primary">Kirim</button>
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

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    function confirmApprove(id) {
        Swal.fire({
            title: 'Setujui Pengajuan?',
            text: "Apakah Anda yakin ingin menyetujui permohonan cuti ini?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10b981', // Emerald green matching the theme
            cancelButtonColor: '#ef4444',
            confirmButtonText: 'Ya, Setujui',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('form-approve-' + id).submit();
            }
        });
    }

    function toggleNote(id, action) {
        const formDiv = document.getElementById('note-form-' + id);
        const form = document.getElementById('form-note-' + id);
        const btn = document.querySelector(`button[onclick='toggleNote(${id}, "${action}")']`);

        // Hide other note forms if any (optional improvement, keeping simple for now)

        if (formDiv.style.display === 'none' || formDiv.dataset.currentAction !== action) {
            formDiv.style.display = 'block';
            form.action = '<?= base_url('approval/process/') ?>' + id + '/' + action;
            formDiv.dataset.currentAction = action;

            // Dynamic placeholder based on action
            const textarea = form.querySelector('textarea');
            if (action === 'rejected') textarea.placeholder = 'Alasan penolakan...';
            else if (action === 'deferred') textarea.placeholder = 'Alasan penangguhan...';
            else if (action === 'changed') textarea.placeholder = 'Detail perubahan yang diminta...';

        } else {
            // If clicking same action again, toggle off
            formDiv.style.display = 'none';
        }
    }
</script>
<?= $this->endSection() ?>