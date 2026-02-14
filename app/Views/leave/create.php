<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>Ajukan Cuti<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header">
    <h1 class="page-title">Ajukan Cuti Baru</h1>
</div>

<?php if (session()->getFlashdata('errors')): ?>
    <div
        style="background-color: #fee2e2; border: 1px solid #fca5a5; color: #b91c1c; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
        <ul>
            <?php foreach (session()->getFlashdata('errors') as $error): ?>
                <li><?= esc($error) ?></li>
            <?php endforeach ?>
        </ul>
    </div>
<?php endif; ?>

<div class="card">
    <form action="<?= base_url('leave/store') ?>" method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <?php if (isset($users) && !empty($users)): ?>
            <div
                style="margin-bottom: 1.5rem; background-color: #f0f9ff; padding: 1rem; border-radius: 8px; border: 1px solid #bae6fd;">
                <label for="target_user_id"
                    style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #0369a1;">Buat Pengajuan Untuk
                    Pegawai (Mode Admin)</label>
                <select name="target_user_id" id="target_user_id"
                    style="width: 100%; padding: 0.75rem; border: 1px solid #93c5fd; border-radius: 8px; font-family: 'Outfit', sans-serif;">
                    <option value="">-- Pilih Pegawai --</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?= $user['id'] ?>" <?= (session()->get('id') == $user['id']) ? 'selected' : '' ?>>
                            <?= $user['nip'] ?> - <?= $user['name'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small style="display: block; margin-top: 0.5rem; color: #0ea5e9;">Biarkan default jika untuk diri
                    sendiri.</small>
            </div>
        <?php endif; ?>

        <div style="margin-bottom: 1.5rem;">
            <label for="leave_type_id" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Jenis
                Cuti</label>
            <select name="leave_type_id" id="leave_type_id" required
                style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 8px; font-family: 'Outfit', sans-serif;">
                <option value="">-- Pilih Jenis Cuti --</option>
                <?php foreach ($leave_types as $type): ?>
                    <option value="<?= $type['id'] ?>" data-file="<?= $type['requires_file'] ?>"
                        data-min="<?= $type['min_duration'] ?>" data-max="<?= $type['max_duration'] ?>">
                        <?= $type['name'] ?> (Maks <?= $type['max_duration'] ?> hari)
                    </option>
                <?php endforeach; ?>
            </select>
            <small id="leave_desc" style="display: block; margin-top: 0.5rem; color: #6b7280;"></small>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
            <div>
                <label for="start_date" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Tanggal
                    Mulai</label>
                <input type="text" name="start_date" id="start_date" placeholder="dd/mm/yyyy" required
                    style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 8px; font-family: 'Outfit', sans-serif; box-sizing: border-box; background-color: #fff;">
            </div>
            <div>
                <label for="end_date" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Tanggal
                    Selesai</label>
                <input type="text" name="end_date" id="end_date" placeholder="dd/mm/yyyy" required
                    style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 8px; font-family: 'Outfit', sans-serif; box-sizing: border-box; background-color: #fff;">
            </div>
        </div>

        <div style="margin-bottom: 1.5rem;">
            <label for="address_during_leave" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Alamat
                Selama Menjalankan Cuti</label>
            <textarea name="address_during_leave" id="address_during_leave" rows="3" required
                style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 8px; font-family: 'Outfit', sans-serif; box-sizing: border-box;"></textarea>
        </div>

        <div style="margin-bottom: 1.5rem;">
            <label for="reason" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Alasan Cuti</label>
            <div id="category_container" style="margin-bottom: 0.5rem; display: none;">
                <!-- Category select will be injected here -->
            </div>
            <div id="reason_container">
                <textarea name="reason" id="reason" rows="4" required
                    style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 8px; font-family: 'Outfit', sans-serif; box-sizing: border-box;"></textarea>
            </div>
            <small id="reason_hint" style="display: block; margin-top: 0.5rem; color: #6b7280;"></small>
        </div>

        <div id="file_upload_container" style="margin-bottom: 1.5rem; display: none;">
            <label for="attachment" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Lampiran (Bukti
                Pendukung)</label>
            <input type="file" name="attachment" id="attachment"
                style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 8px; background: #f9fafb; font-family: 'Outfit', sans-serif; box-sizing: border-box;">
            <small id="attachment_note" style="display: block; margin-top: 0.5rem; color: #ef4444;">* Wajib dilampirkan
                untuk jenis/alasan cuti ini.</small>
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
    const categoryContainer = document.getElementById('category_container');
    const reasonContainer = document.getElementById('reason_container');
    const fileContainer = document.getElementById('file_upload_container');
    const fileInput = document.getElementById('attachment');
    const attachmentNote = document.getElementById('attachment_note');
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');

    const capOptions = `
        <select name="reason" id="reason" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 8px; font-family: 'Outfit', sans-serif; box-sizing: border-box;">
            <option value="">-- Pilih Alasan Cuti --</option>
            <option value="Keluarga Inti Sakit Keras" data-req="1">Keluarga Inti Sakit Keras (Wajib Lampiran)</option>
            <option value="Keluarga Inti Meninggal Dunia" data-req="0">Keluarga Inti Meninggal Dunia (Opsional Lampiran)</option>
            <option value="Mengurus Hak Keluarga" data-req="0">Mengurus Hak Keluarga (Opsional Lampiran)</option>
            <option value="Melangsungkan Pernikahan" data-req="0">Melangsungkan Pernikahan (Opsional Lampiran)</option>
            <option value="Istri Melahirkan/Operasi Caesar" data-req="0">Istri Melahirkan/Operasi Caesar (Opsional Lampiran)</option>
            <option value="Musibah Bencana" data-req="1">Musibah Bencana (Wajib Lampiran)</option>
            <option value="Faktor Kejiwaan" data-req="1">Faktor Kejiwaan (Wajib Lampiran)</option>
        </select>
    `;

    const bigLeaveCategories = `
        <select name="category" id="category" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 8px; font-family: 'Outfit', sans-serif; box-sizing: border-box;">
            <option value="">-- Pilih Kategori Cuti Besar --</option>
            <option value="Ibadah Keagamaan (Haji Pertama)">Ibadah Keagamaan (Haji Pertama)</option>
            <option value="Ibadah Keagamaan (Umrah/Haji Lanjutan)">Ibadah Keagamaan (Umrah/Haji Lanjutan)</option>
            <option value="Keperluan Keluarga (Sakit Keras/Pemulihan)">Keperluan Keluarga (Sakit Keras/Pemulihan)</option>
            <option value="Persalinan Anak ke-4 dan seterusnya">Persalinan Anak ke-4 dan seterusnya</option>
            <option value="Keperluan Pribadi Mendesak">Keperluan Pribadi Mendesak</option>
        </select>
    `;

    const defaultReason = `
        <textarea name="reason" id="reason" rows="4" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 8px; font-family: 'Outfit', sans-serif; box-sizing: border-box;"></textarea>
    `;

    // Initialize Flatpickr
    flatpickr(startDateInput, {
        dateFormat: "Y-m-d", altInput: true, altFormat: "d/m/Y", allowInput: true, locale: "id", disableMobile: "true"
    });

    flatpickr(endDateInput, {
        dateFormat: "Y-m-d", altInput: true, altFormat: "d/m/Y", allowInput: true, locale: "id", disableMobile: "true"
    });

    typeSelect.addEventListener('change', function () {
        const selectedOption = this.options[this.selectedIndex];
        const typeName = selectedOption.text.split(' (')[0];
        const requiresFile = selectedOption.getAttribute('data-file');

        // Reset state
        categoryContainer.style.display = 'none';
        categoryContainer.innerHTML = '';
        reasonContainer.innerHTML = defaultReason;
        document.getElementById('reason_hint').innerHTML = '';
        handleFileUpload(requiresFile == '1');

        if (typeName === 'Cuti Alasan Penting') {
            reasonContainer.innerHTML = capOptions;
            handleFileUpload(false); // Reset file upload visibility

            // Add hint about core family
            document.getElementById('reason_hint').innerHTML = '<strong>Keluarga Inti:</strong> Ibu, Bapak, Istri/Suami, Anak, Adik, Kakak, Mertua, atau Menantu.';

            // Add event listener to new select
            const capReasonSelect = document.getElementById('reason');
            capReasonSelect.addEventListener('change', function () {
                const opt = this.options[this.selectedIndex];
                const req = opt.getAttribute('data-req') === '1';
                handleFileUpload(req, true); // Always show for CAP, but toggle requirement
            });
        } else if (typeName === 'Cuti Besar') {
            categoryContainer.style.display = 'block';
            categoryContainer.innerHTML = bigLeaveCategories;

            // Hide manual reason and replace with hidden sync
            reasonContainer.innerHTML = '<input type="hidden" name="reason" id="reason_hidden">';

            document.getElementById('reason_hint').innerHTML = '<span style="color: #ef4444; font-weight: 600;">PENTING:</span> Mengambil Cuti Besar akan <strong>menghapus jatah Cuti Tahunan</strong> Anda di tahun berjalan.';

            const categorySelect = document.getElementById('category');
            const reasonHidden = document.getElementById('reason_hidden');

            categorySelect.addEventListener('change', function () {
                reasonHidden.value = this.value;
                if (this.value === 'Ibadah Keagamaan (Haji Pertama)') {
                    document.getElementById('reason_hint').innerHTML = '<span style="color: #ef4444; font-weight: 600;">PENTING:</span> Mengambil Cuti Besar akan <strong>menghapus jatah Cuti Tahunan</strong> Anda di tahun berjalan.<br><span style="color: #ef4444; font-weight: 600;">SISA HAK HAPUS:</span> Jika diambil kurang dari 3 bulan, sisa jatah di siklus ini akan hangus.<br><span style="color: #059669;">* Pengecualian masa kerja 5 tahun berlaku untuk Haji Pertama.</span>';
                } else {
                    document.getElementById('reason_hint').innerHTML = '<span style="color: #ef4444; font-weight: 600;">PENTING:</span> Mengambil Cuti Besar akan <strong>menghapus jatah Cuti Tahunan</strong> Anda di tahun berjalan.<br><span style="color: #ef4444; font-weight: 600;">SISA HAK HAPUS:</span> Jika diambil kurang dari 3 bulan, sisa jatah di siklus ini akan hangus.';
                }
            });
        }
    });

    function handleFileUpload(required, showAlways = false) {
        if (showAlways || required) {
            fileContainer.style.display = 'block';
            if (required) {
                fileInput.setAttribute('required', 'required');
                attachmentNote.innerHTML = '* Wajib dilampirkan (Surat Dokter/Kematian/Bukti RT)';
                attachmentNote.style.color = '#ef4444';
            } else {
                fileInput.removeAttribute('required');
                attachmentNote.innerHTML = 'Lampiran bersifat opsional (jika ada)';
                attachmentNote.style.color = '#6b7280';
            }
        } else {
            fileContainer.style.display = 'none';
            fileInput.removeAttribute('required');
        }
    }

    startDateInput.addEventListener('change', validateDates);
    endDateInput.addEventListener('change', validateDates);

    function validateDates() {
        if (startDateInput.value && endDateInput.value) {
            if (startDateInput.value > endDateInput.value) {
                alert('Tanggal selesai tidak boleh lebih awal dari tanggal mulai');
                endDateInput._flatpickr.clear();
            }
        }
    }
</script>
<?= $this->endSection() ?>