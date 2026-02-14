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

    const cltnCategories = `
        <select name="category" id="category" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 8px; font-family: 'Outfit', sans-serif; box-sizing: border-box;">
            <option value="">-- Pilih Kategori CLTN --</option>
            <option value="Mengikuti Suami/Istri Tugas Negara/Belajar">Mengikuti Suami/Istri Tugas Negara/Belajar</option>
            <option value="Mendampingi Anak Butuh Perhatian Khusus">Mendampingi Anak Butuh Perhatian Khusus</option>
            <option value="Mendampingi Suami/Istri/Orang Tua Sakit Parah/Menua">Mendampingi Suami/Istri/Orang Tua Sakit Parah/Menua</option>
            <option value="Persalinan Anak ke-4 dan Seterusnya (Tanpa Jatah Cuti Besar)">Persalinan Anak ke-4 dan Seterusnya (Tanpa Jatah Cuti Besar)</option>
            <option value="Alasan Pribadi yang Sangat Penting & Mendesak">Alasan Pribadi yang Sangat Penting & Mendesak</option>
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
                let hint = '<span style="color: #ef4444; font-weight: 600;">PENTING:</span> Mengambil Cuti Besar akan <strong>menghapus jatah Cuti Tahunan</strong> Anda di tahun berjalan.<br><span style="color: #ef4444; font-weight: 600;">SISA HAK HAPUS:</span> Jika diambil kurang dari 3 bulan, sisa jatah di siklus ini akan hangus.';

                if (this.value === 'Ibadah Keagamaan (Haji Pertama)') {
                    hint += '<br><span style="color: #059669;">* Pengecualian masa kerja 5 tahun berlaku untuk Haji Pertama.</span>';
                } else if (this.value === 'Persalinan Anak ke-4 dan seterusnya') {
                    hint += '<br><span style="color: #059669; font-weight: 600;">TIPS:</span> Jika Anda belum berhak atas Cuti Besar (misal: masa kerja < 5 th), maka pengajuan persalinan anak ke-4+ diarahkan menggunakan <strong>Cuti di Luar Tanggungan Negara (CLTN)</strong>.';
                }

                document.getElementById('reason_hint').innerHTML = hint;
            });
        } else if (typeName === 'Cuti Sakit') {
            categoryContainer.style.display = 'block';
            categoryContainer.innerHTML = `
                <select name="category" id="category" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 8px; font-family: 'Outfit', sans-serif; box-sizing: border-box;">
                    <option value="Sakit Biasa">Sakit Biasa</option>
                    <option value="Gugur Kandungan">Gugur Kandungan (Max 45 Hari)</option>
                    <option value="Kecelakaan Kerja">Kecelakaan Kerja</option>
                </select>
            `;

            // Hide manual reason and replace with hidden sync
            reasonContainer.innerHTML = '<input type="hidden" name="reason" id="reason_hidden">';
            const reasonHidden = document.getElementById('reason_hidden');

            const updateSickHint = () => {
                const categorySelect = document.getElementById('category');
                const category = categorySelect.value;
                reasonHidden.value = category; // Sync category to reason

                const start = new Date(startDateInput.value);
                const end = new Date(endDateInput.value);
                let hint = '';

                if (startDateInput.value && endDateInput.value) {
                    const diffTime = Math.abs(end - start);
                    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;

                    if (category === 'Gugur Kandungan') {
                        hint = '<span style="color: #ef4444; font-weight: 600;">GUGUR KANDUNGAN:</span> Maksimal jatah 45 hari (1,5 bulan).';
                    } else if (category === 'Kecelakaan Kerja') {
                        hint = '<span style="color: #059669; font-weight: 600;">KECELAKAAN KERJA:</span> Diberikan sampai sembuh total tanpa batas waktu kaku.';
                    } else {
                        if (diffDays > 14) {
                            hint = '<span style="color: #ef4444; font-weight: 600;">DURASI > 14 HARI:</span> Wajib melampirkan Surat Keterangan dari <strong>Dokter Pemerintah</strong>.';
                        } else {
                            hint = '<span style="color: #6b7280;">DURASI 1-14 HARI:</span> Wajib melampirkan Surat Keterangan Dokter (Puskesmas/Klinik/RS).';
                        }
                    }
                }
                document.getElementById('reason_hint').innerHTML = hint;
            };

            document.getElementById('category').addEventListener('change', updateSickHint);
            startDateInput.addEventListener('change', updateSickHint);
            endDateInput.addEventListener('change', updateSickHint);
            updateSickHint();
        } else if (typeName === 'Cuti Melahirkan') {
            categoryContainer.style.display = 'block';
            categoryContainer.innerHTML = `
                <select name="category" id="category" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 8px; font-family: 'Outfit', sans-serif; box-sizing: border-box;">
                    <option value="">-- Pilih Urutan Kelahiran --</option>
                    <option value="Anak ke-1">Anak ke-1</option>
                    <option value="Anak ke-2">Anak ke-2</option>
                    <option value="Anak ke-3">Anak ke-3</option>
                    <option value="Anak ke-4 atau lebih">Anak ke-4 atau lebih (Berpindah ke Cuti Besar)</option>
                </select>
            `;

            // Hide manual reason and replace with hidden sync
            reasonContainer.innerHTML = '<input type="hidden" name="reason" id="reason_hidden">';
            const reasonHidden = document.getElementById('reason_hidden');

            const updateMaternityHint = () => {
                const categorySelect = document.getElementById('category');
                const category = categorySelect.value;
                reasonHidden.value = category ? 'Cuti Melahirkan - ' + category : 'Cuti Melahirkan';

                let hint = '<span style="color: #059669; font-weight: 600;">INFO:</span> Cuti diambil selama 3 bulan kalender (termasuk hari libur).';

                if (category === 'Anak ke-4 atau lebih') {
                    hint = '<span style="color: #ef4444; font-weight: 600;">PERHATIAN:</span> Sesuai Peraturan BKN, Cuti Melahirkan hanya diberikan s.d anak ke-3. Untuk anak ke-4+, silakan ajukan <strong>Cuti Besar</strong>. Jika jatah Cuti Besar tidak tersedia, pegawai diarahkan menggunakan <strong>CLTN</strong>.';
                } else if (startDateInput.value && endDateInput.value) {
                    const start = new Date(startDateInput.value);
                    const end = new Date(endDateInput.value);

                    // 3 Months check
                    const maxEnd = new Date(start);
                    maxEnd.setMonth(maxEnd.getMonth() + 3);
                    maxEnd.setDate(maxEnd.getDate() - 1);

                    hint = '<span style="color: #059669; font-weight: 600;">INFO:</span> Berdasarkan Peraturan BKN, cuti ini diambil total selama 3 bulan kalender.';
                    hint += '<br><span style="color: #6b7280;">Saran: Sebaiknya diajukan H-2 minggu atau 1 bulan sebelum HPL.</span>';

                    if (end > maxEnd) {
                        hint += `<br><span style="color: #ef4444; font-weight: 600;">PERINGATAN:</span> Durasi melebihi 3 bulan kalender (Batas: ${maxEnd.toLocaleDateString('id-ID')}).`;
                    }
                }

                document.getElementById('reason_hint').innerHTML = hint;
            };

            document.getElementById('category').addEventListener('change', updateMaternityHint);
            startDateInput.addEventListener('change', updateMaternityHint);
            endDateInput.addEventListener('change', updateMaternityHint);
            updateMaternityHint();
        } else if (typeName === 'Cuti di Luar Tanggungan Negara') {
            categoryContainer.style.display = 'block';
            categoryContainer.innerHTML = cltnCategories;

            // Hide manual reason and replace with hidden sync
            reasonContainer.innerHTML = '<input type="hidden" name="reason" id="reason_hidden">';
            const reasonHidden = document.getElementById('reason_hidden');

            const updateCltnHint = () => {
                const categorySelect = document.getElementById('category');
                const category = categorySelect.value;
                reasonHidden.value = category ? 'CLTN - ' + category : 'CLTN';

                let hint = '<div style="background: #fff1f2; border: 1px solid #fecaca; padding: 1rem; border-radius: 8px; margin-top: 0.5rem;">';
                hint += '<span style="color: #e11d48; font-weight: 700; font-size: 1rem; display: block; margin-bottom: 0.5rem;">⚠️ KONSEKUENSI PENTING (Wajib Tahu):</span>';
                hint += '<ul style="color: #9f1239; margin: 0; padding-left: 1.25rem; font-size: 0.95rem;">';
                hint += '<li><strong>Penghasilan Berhenti:</strong> PNS tidak menerima gaji dan tunjangan apapun dari negara.</li>';
                hint += '<li><strong>Masa Kerja Terhenti:</strong> Masa CLTN tidak dihitung sebagai masa kerja (Kenaikan Pangkat/Pensiun akan mundur).</li>';
                hint += '<li><strong>Jabatan Lowong:</strong> PNS otomatis diberhentikan dari jabatannya.</li>';
                hint += '<li><strong>Syarat 5 Tahun:</strong> Harus sudah bekerja minimal 5 tahun terus-menerus sebagai PNS.</li>';
                hint += '</ul></div>';

                document.getElementById('reason_hint').innerHTML = hint;
            };

            document.getElementById('category').addEventListener('change', updateCltnHint);
            updateCltnHint();
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