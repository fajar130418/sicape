<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Formulir Cuti</title>
    <style>
        @page {
            margin: 15px 25px;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9pt;
            line-height: 1.1;
        }

        .header {
            margin-bottom: 15px;
            font-size: 9pt;
        }

        .header table {
            border: none;
            width: 100%;
            margin-bottom: 5px;
        }

        .header td {
            border: none;
            padding: 0;
            vertical-align: top;
        }

        .header p {
            margin: 2px 0;
            line-height: 1.3;
        }

        .title {
            text-align: center;
            font-weight: bold;
            margin: 8px 0;
            text-transform: uppercase;
            font-size: 11pt;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 3px;
        }

        th,
        td {
            border: 1px solid black;
            padding: 2px 4px;
            vertical-align: top;
        }

        .no-border {
            border: none;
        }

        .section-title {
            font-weight: bold;
            background-color: #f0f0f0;
            font-size: 9pt;
        }

        .check-box {
            width: 10px;
            height: 10px;
            border: 1px solid black;
            display: inline-block;
            text-align: center;
            line-height: 8px;
            font-size: 8px;
            margin-right: 3px;
        }

        .checked {
            content: "✔";
        }

        .signature-box {
            height: 50px;
        }

        .center {
            text-align: center;
        }

        /* Layout Helpers */
        .col-half {
            width: 50%;
        }

        .col-label {
            width: 25%;
        }

        .col-val {
            width: 25%;
        }
    </style>
</head>

<body>
    <div class="header">
        <table>
            <tr>
                <td style="width: 55%;"></td>
                <td style="width: 45%; text-align: left; vertical-align: top; padding: 0;">
                    <p>Kuala Pembuang, <?= date('d F Y', strtotime($request['created_at'])) ?></p>
                    <p style="margin-top: 8px;">Kepada</p>
                    <table style="width: 100%; border: none; margin: 0; margin-left: -30px;">
                        <tr>
                            <td style="width: 25px; border: none; padding: 0; text-align: left;">Yth.</td>
                            <td style="border: none; padding: 0 0 0 5px;">Kepala Dinas Perpustakaan dan Kearsipan</td>
                        </tr>
                        <tr>
                            <td style="border: none; padding: 0;"></td>
                            <td style="border: none; padding: 0 0 0 5px;">Kabupaten Seruyan</td>
                        </tr>
                    </table>
                    <p style="margin-left: 0px;">di –</p>
                    <p style="margin-left: 60px;">Kuala Pembuang</p>
                </td>
            </tr>
        </table>
    </div>

    <div class="title">FORMULIR PERMINTAAN DAN PEMBERIAN CUTI</div>

    <!-- I. DATA PEGAWAI -->
    <table>
        <tr>
            <td colspan="4" class="section-title">I. DATA PEGAWAI</td>
        </tr>
        <tr>
            <td width="15%">Nama</td>
            <td width="35%"><?= $request['user_name'] ?></td>
            <td width="15%">NIP</td>
            <td width="35%"><?= $request['nip'] ?></td>
        </tr>
        <tr>
            <td>Jabatan</td>
            <td><?= $request['position'] ?? '-' ?></td>
            <td>Masa Kerja</td>
            <td><?= $tenure ?></td>
        </tr>
        <tr>
            <td>Unit Kerja</td>
            <td colspan="3"><?= $request['unit'] ?? 'Dinas Perpustakaan dan Kearsipan' ?></td>
        </tr>
    </table>

    <!-- II. JENIS CUTI -->
    <table>
        <tr>
            <td colspan="4" class="section-title">II. JENIS CUTI YANG DIAMBIL**</td>
        </tr>
        <tr>
            <td width="40%">1. Cuti Tahunan</td>
            <td width="10%" class="center"><?= $request['type_name'] == 'Cuti Tahunan' ? '✔' : '' ?></td>
            <td width="40%">2. Cuti Besar</td>
            <td width="10%" class="center"><?= $request['type_name'] == 'Cuti Besar' ? '✔' : '' ?></td>
        </tr>
        <tr>
            <td>3. Cuti Sakit</td>
            <td class="center"><?= $request['type_name'] == 'Cuti Sakit' ? '✔' : '' ?></td>
            <td>4. Cuti Melahirkan</td>
            <td class="center"><?= $request['type_name'] == 'Cuti Melahirkan' ? '✔' : '' ?></td>
        </tr>
        <tr>
            <td>5. Cuti Karena Alasan Penting</td>
            <td class="center"><?= $request['type_name'] == 'Cuti Alasan Penting' ? '✔' : '' ?></td>
            <td>6. Cuti di Luar Tanggungan Negara</td>
            <td class="center"><?= $request['type_name'] == 'CLTN' ? '✔' : '' ?></td>
        </tr>
    </table>

    <!-- III. ALASAN CUTI -->
    <table>
        <tr>
            <td class="section-title">III. ALASAN CUTI</td>
        </tr>
        <tr>
            <td><?= $request['reason'] ?></td>
        </tr>
    </table>

    <!-- IV. LAMANYA CUTI -->
    <?php
    $start = new DateTime($request['start_date']);
    $end = new DateTime($request['end_date']);
    $days = $start->diff($end)->days + 1;
    ?>
    <table>
        <tr>
            <td colspan="6" class="section-title">IV. LAMANYA CUTI</td>
        </tr>
        <tr>
            <td width="10%">Selama</td>
            <td width="15%" class="center"><?= $days ?> Hari</td>
            <td width="15%">Mulai Tanggal</td>
            <td width="25%" class="center"><?= date('d F Y', strtotime($request['start_date'])) ?></td>
            <td width="5%">s/d</td>
            <td width="30%" class="center"><?= date('d F Y', strtotime($request['end_date'])) ?></td>
        </tr>
    </table>

    <!-- V. CATATAN CUTI -->
    <table>
        <tr>
            <td colspan="5" class="section-title">V. CATATAN CUTI***</td>
        </tr>
        <tr>
            <td colspan="3" width="50%">1. CUTI TAHUNAN</td>
            <td colspan="2" width="50%">2. CUTI BESAR</td>
        </tr>
        <tr>
            <td width="15%" class="center">Tahun</td>
            <td width="15%" class="center">Sisa</td>
            <td width="20%" class="center">Keterangan</td>
            <td colspan="2">3. CUTI SAKIT</td>
        </tr>
        <tr>
            <td class="center">N-2</td>
            <td class="center"><?= $remainingN2 ?></td>
            <td></td>
            <td colspan="2">4. CUTI MELAHIRKAN</td>
        </tr>
        <tr>
            <td class="center">N-1</td>
            <td class="center"><?= $remainingN1 ?></td>
            <td></td>
            <td colspan="2">5. CUTI KARENA ALASAN PENTING</td>
        </tr>
        <tr>
            <td class="center">N</td>
            <td class="center"><?= $remainingN ?></td>
            <td></td>
            <td colspan="2">6. CUTI DI LUAR TANGGUNGAN NEGARA</td>
        </tr>
    </table>

    <!-- VI. ALAMAT -->
    <table>
        <tr>
            <td colspan="3" class="section-title">VI. ALAMAT SELAMA MENJALANKAN CUTI</td>
        </tr>
        <tr>
            <td width="60%" rowspan="2" style="vertical-align: top; height: 80px;">
                <?= $request['address_during_leave'] ? nl2br($request['address_during_leave']) : 'Belum diisi (Alamat Default: Kuala Pembuang)' ?>
            </td>
            <td width="10%">TELP / HP</td>
            <td width="30%"><?= $request['phone'] ?? '-' ?></td>
        </tr>
        <tr>
            <td colspan="2" class="center">
                Hormat saya,<br><br><br><br>
                <strong><?= $request['user_name'] ?></strong><br>
                NIP. <?= $request['nip'] ?>
            </td>
        </tr>
    </table>

    <!-- VII. PERTIMBANGAN ATASAN -->
    <table>
        <tr>
            <td colspan="4" class="section-title">VII. PERTIMBANGAN ATASAN LANGSUNG**</td>
        </tr>
        <tr>
            <td width="20%">DISETUJUI</td>
            <td width="20%">PERUBAHAN****</td>
            <td width="20%">DITANGGUHKAN****</td>
            <td width="40%">TIDAK DISETUJUI****</td>
        </tr>
        <tr>
            <td class="center"><?= $request['supervisor_status'] == 'approved' ? '✔' : '' ?></td>
            <td class="center"><?= $request['supervisor_status'] == 'changed' ? '✔' : '' ?></td>
            <td class="center"><?= $request['supervisor_status'] == 'deferred' ? '✔' : '' ?></td>
            <td class="center"><?= $request['supervisor_status'] == 'rejected' ? '✔' : '' ?></td>
        </tr>
        <tr>
            <td colspan="3" style="border-right: none;"></td>
            <td style="border-left: none;" class="center">
                <?php if (isset($manualSignature) && $manualSignature): ?>
                    <?= strtoupper($manualSignature['supervisor']['position']) ?>,<br><br><br><br>
                    <strong><?= $manualSignature['supervisor']['name'] ?></strong><br>
                    NIP. <?= $manualSignature['supervisor']['nip'] ?>
                <?php else: ?>
                    <?php
                    $signerPrefix = ($request['supervisor_sign_as'] ?? 'Definitif') != 'Definitif' ? $request['supervisor_sign_as'] . ' ' : '';
                    ?>
                    <?= $signerPrefix ?>Atasan Langsung,<br><br><br><br>
                    <strong><?= $request['supervisor_name'] ?? '(Nama Atasan)' ?></strong><br>
                    NIP. <?= $request['supervisor_nip'] ?? '...' ?>
                <?php endif; ?>
            </td>
        </tr>
    </table>

    <!-- VIII. KEPUTUSAN PEJABAT -->
    <table>
        <tr>
            <td colspan="4" class="section-title">VIII. KEPUTUSAN PEJABAT YANG BERWENANG MEMBERIKAN CUTI**</td>
        </tr>
        <tr>
            <td width="20%">DISETUJUI</td>
            <td width="20%">PERUBAHAN****</td>
            <td width="20%">DITANGGUHKAN****</td>
            <td width="40%">TIDAK DISETUJUI****</td>
        </tr>
        <tr>
            <td class="center"><?= $request['status'] == 'approved' ? '✔' : '' ?></td>
            <td class="center"></td>
            <td class="center"></td>
            <td class="center"><?= $request['status'] == 'rejected' ? '✔' : '' ?></td>
        </tr>
        <tr>
            <td colspan="3" style="border-right: none;"></td>
            <td style="border-left: none;" class="center">
                <?php if (isset($manualSignature) && $manualSignature): ?>
                    <?= strtoupper($manualSignature['official']['position']) ?><br><br><br><br>
                    <strong><?= $manualSignature['official']['name'] ?></strong><br>
                    <?= $manualSignature['official']['nip'] ? 'NIP. ' . $manualSignature['official']['nip'] : '' ?>
                <?php else: ?>
                    <?php
                    $adminPrefix = ($request['admin_sign_as'] ?? 'Definitif') != 'Definitif' ? $request['admin_sign_as'] . ' ' : '';
                    ?>
                    <?= $adminPrefix ?>Kepala Dinas,<br><br><br><br>
                    <strong><?= strtoupper($headOfAgency['name'] ?? '(BELUM ADA KEPALA DINAS)') ?></strong><br>
                    NIP. <?= $headOfAgency['nip'] ?? '...' ?>
                <?php endif; ?>
            </td>
        </tr>
    </table>

    <div style="font-size: 8pt; margin-top: 10px;">
        <strong>Catatan:</strong><br>
        * Coret yang tidak perlu<br>
        ** Pilih salah satu dengan memberi tanda centang<br>
        *** Diisi oleh pejabat yang menangani bidang kepegawaian sebelum PNS mengajukan cuti<br>
        **** Diberi tanda centang dan alasanya
    </div>

</body>

</html>