<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>Ajukan Cuti<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header">
    <h1 class="page-title">Ajukan Cuti Baru</h1>
</div>

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
    <form action="<?= base_url('leave/store') ?>" method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        
        <?php if(isset($users) && !empty($users)): ?>
            <div style="margin-bottom: 1.5rem; background-color: #f0f9ff; padding: 1rem; border-radius: 8px; border: 1px solid #bae6fd;">
                <label for="target_user_id" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #0369a1;">Buat Pengajuan Untuk Pegawai (Mode Admin)</label>
                <select name="target_user_id" id="target_user_id" style="width: 100%; padding: 0.75rem; border: 1px solid #93c5fd; border-radius: 8px; font-family: 'Outfit', sans-serif;">
                    <option value="">-- Pilih Pegawai --</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?= $user['id'] ?>" <?= (session()->get('id') == $user['id']) ? 'selected' : '' ?>>
                            <?= $user['nip'] ?> - <?= $user['name'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small style="display: block; margin-top: 0.5rem; color: #0ea5e9;">Biarkan default jika untuk diri sendiri.</small>
            </div>
        <?php endif; ?>

        <div style="margin-bottom: 1.5rem;">
            <label for="leave_type_id" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Jenis Cuti</label>
            <select name="leave_type_id" id="leave_type_id" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 8px; font-family: 'Outfit', sans-serif;">
                <option value="">-- Pilih Jenis Cuti --</option>
                <?php foreach ($leave_types as $type): ?>
                    <option value="<?= $type['id'] ?>" data-file="<?= $type['requires_file'] ?>" data-min="<?= $type['min_duration'] ?>" data-max="<?= $type['max_duration'] ?>">
                        <?= $type['name'] ?> (Maks <?= $type['max_duration'] ?> hari)
                    </option>
                <?php endforeach; ?>
            </select>
            <small id="leave_desc" style="display: block; margin-top: 0.5rem; color: #6b7280;"></small>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
            <div>
                <label for="start_date" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Tanggal Mulai</label>
                <input type="text" name="start_date" id="start_date" placeholder="dd/mm/yyyy" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 8px; font-family: 'Outfit', sans-serif; box-sizing: border-box; background-color: #fff;">
            </div>
            <div>
                <label for="end_date" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Tanggal Selesai</label>
                <input type="text" name="end_date" id="end_date" placeholder="dd/mm/yyyy" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 8px; font-family: 'Outfit', sans-serif; box-sizing: border-box; background-color: #fff;">
            </div>
        </div>

        <div style="margin-bottom: 1.5rem;">
            <label for="address_during_leave" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Alamat Selama Menjalankan Cuti</label>
            <textarea name="address_during_leave" id="address_during_leave" rows="3" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 8px; font-family: 'Outfit', sans-serif; box-sizing: border-box;"></textarea>
        </div>

        <div style="margin-bottom: 1.5rem;">
            <label for="reason" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Alasan Cuti</label>
            <textarea name="reason" id="reason" rows="4" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 8px; font-family: 'Outfit', sans-serif; box-sizing: border-box;"></textarea>
        </div>

        <div id="file_upload_container" style="margin-bottom: 1.5rem; display: none;">
            <label for="attachment" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Lampiran (Surat Dokter/Bukti)</label>
            <input type="file" name="attachment" id="attachment" style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 8px; background: #f9fafb; font-family: 'Outfit', sans-serif; box-sizing: border-box;">
            <small style="display: block; margin-top: 0.5rem; color: #ef4444;">* Wajib dilampirkan untuk jenis cuti ini.</small>
        </div>

        <div style="text-align: right;">
            <button type="submit" class="btn btn-primary">Ajukan Permohonan</button>
        </div>
    </form>
</div>

<!-- Flatpickr CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<!-- Flatpickr JS -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/id.js"></script>

<script>
    const typeSelect = document.getElementById('leave_type_id');
    const fileContainer = document.getElementById('file_upload_container');
    const fileInput = document.getElementById('attachment');
    // Select inputs by ID
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');

    // Initialize Flatpickr
    flatpickr(startDateInput, {
        dateFormat: "Y-m-d", // Format sent to server
        altInput: true,      // Enable alternative input for display
        altFormat: "d/m/Y",  // Format displayed to user
        allowInput: true,
        locale: "id",        // Indonesian locale
        disableMobile: "true" // Force Flatpickr on mobile too ensuring format
    });

    flatpickr(endDateInput, {
        dateFormat: "Y-m-d",
        altInput: true,
        altFormat: "d/m/Y",
        allowInput: true,
        locale: "id",
        disableMobile: "true"
    });

    typeSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const requiresFile = selectedOption.getAttribute('data-file');
        
        if (requiresFile == '1') {
            fileContainer.style.display = 'block';
            fileInput.setAttribute('required', 'required');
        } else {
            fileContainer.style.display = 'none';
            fileInput.removeAttribute('required');
        }
    });

    // Simple date validation script
    startDateInput.addEventListener('change', validateDates);
    endDateInput.addEventListener('change', validateDates);

    function validateDates() {
        if(startDateInput.value && endDateInput.value) {
            if(startDateInput.value > endDateInput.value) {
                alert('Tanggal selesai tidak boleh lebih awal dari tanggal mulai');
                endDateInput._flatpickr.clear(); // Clear using flatpickr instance
            }
        }
    }
</script>
<?= $this->endSection() ?>
