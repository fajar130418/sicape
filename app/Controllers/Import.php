<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\UserModel;

class Import extends BaseController
{
    public function index()
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/dashboard');
        }

        return view('import/index', [
            'title' => 'Impor Data Pegawai'
        ]);
    }

    public function downloadTemplate()
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/dashboard');
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header
        $headers = [
            'NIP',
            'NIK',
            'Nama Lengkap',
            'Email Aktif',
            'Pangkat / Golongan',
            'Pendidikan',
            'Jabatan',
            'Unit Kerja',
            'TMT (YYYY-MM-DD)',
            'Jenis Kelamin (L/P)',
            'No. HP',
            'Alamat',
            'Status Pegawai (PNS / PPPK)',
            'Hak Akses (admin / pegawai)',
            'Tgl Berakhir Kontrak (YYYY-MM-DD, Khusus PPPK)'
        ];

        $column = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($column . '1', $header);
            $sheet->getColumnDimension($column)->setAutoSize(true);
            $column++;
        }

        // Style Header
        $sheet->getStyle('A1:O1')->getFont()->setBold(true);
        $sheet->getStyle('A1:O1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFD3D3D3');

        // Contoh Data 1 (PNS)
        $sheet->setCellValue('A2', '199001012023011001');
        $sheet->setCellValue('B2', '1234567890123456');
        $sheet->setCellValue('C2', 'Budiman, S.Kom');
        $sheet->setCellValue('D2', 'budiman@gmail.com');
        $sheet->setCellValue('E2', 'Penata Muda (III/a)');
        $sheet->setCellValue('F2', 'S1');
        $sheet->setCellValue('G2', 'Pranata Komputer');
        $sheet->setCellValue('H2', 'Dinas Perpustakaan');
        $sheet->setCellValue('I2', '2023-01-01');
        $sheet->setCellValue('J2', 'L');
        $sheet->setCellValue('K2', '08123456789');
        $sheet->setCellValue('L2', 'Jl. Sudirman No. 123');
        $sheet->setCellValue('M2', 'PNS');
        $sheet->setCellValue('N2', 'pegawai');
        $sheet->setCellValue('O2', ''); // Kosong untuk PNS

        // Contoh Data 2 (PPPK)
        $sheet->setCellValue('A3', '198505052024021002');
        $sheet->setCellValue('B3', '6543210987654321');
        $sheet->setCellValue('C3', 'Siti Aminah, M.Pd');
        $sheet->setCellValue('D3', 'siti@gmail.com');
        $sheet->setCellValue('E3', 'Golongan IX');
        $sheet->setCellValue('F3', 'S2');
        $sheet->setCellValue('G3', 'Pustakawan Ahli');
        $sheet->setCellValue('H3', 'Dinas Kearsipan');
        $sheet->setCellValue('I3', '2024-02-01');
        $sheet->setCellValue('J3', 'P');
        $sheet->setCellValue('K3', '08987654321');
        $sheet->setCellValue('L3', 'Jl. Ahmad Yani No. 45');
        $sheet->setCellValue('M3', 'PPPK');
        $sheet->setCellValue('N3', 'admin');
        $sheet->setCellValue('O3', '2029-01-31');

        // Contoh Data 3 (PPPK Paruh Waktu)
        $sheet->setCellValue('A4', '200001012025011003');
        $sheet->setCellValue('B4', '1122334455667788');
        $sheet->setCellValue('C4', 'Eko Prasetyo');
        $sheet->setCellValue('D4', 'eko@gmail.com');
        $sheet->setCellValue('E4', '-');
        $sheet->setCellValue('F4', 'SMA');
        $sheet->setCellValue('G4', 'Tenaga Teknis');
        $sheet->setCellValue('H4', 'Dinas Perpustakaan');
        $sheet->setCellValue('I4', '2025-01-01');
        $sheet->setCellValue('J4', 'L');
        $sheet->setCellValue('K4', '08554433221');
        $sheet->setCellValue('L4', 'Jl. Gajah Mada No. 10');
        $sheet->setCellValue('M4', 'PPPK Paruh Waktu');
        $sheet->setCellValue('N4', 'pegawai');
        $sheet->setCellValue('O4', '2026-01-01');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'Template_Impor_Pegawai.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit();
    }

    public function process()
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/dashboard');
        }

        $file = $this->request->getFile('excel_file');

        if (!$file->isValid() || $file->getExtension() !== 'xlsx' && $file->getExtension() !== 'xls') {
            return redirect()->back()->with('error', 'Silakan unggah file Excel yang valid (.xlsx atau .xls).');
        }

        try {
            $spreadsheet = IOFactory::load($file->getTempName());
            $sheetData = $spreadsheet->getActiveSheet()->toArray();

            $userModel = new UserModel();
            $successCount = 0;
            $errorCount = 0;
            $errors = [];

            // Skip header row (index 0)
            for ($i = 1; $i < count($sheetData); $i++) {
                $row = $sheetData[$i];

                // Minimal data: NIP (Index 0) and Name (Index 2)
                if (empty($row[0]) || empty($row[2])) {
                    continue;
                }

                $nip = trim($row[0]);

                // Check if user already exists
                if ($userModel->where('nip', $nip)->first()) {
                    $errorCount++;
                    $errors[] = "Baris " . ($i + 1) . ": NIP {$nip} sudah terdaftar.";
                    continue;
                }

                $data = [
                    'nip' => $nip,
                    'nik' => $row[1] ?? null,
                    'name' => trim($row[2]),
                    'email' => $row[3] ?? null,
                    'rank' => $row[4] ?? null,
                    'education' => $row[5] ?? null,
                    'position' => $row[6] ?? null,
                    'unit' => $row[7] ?? 'Dinas Perpustakaan dan Kearsipan',
                    'join_date' => !empty($row[8]) ? date('Y-m-d', strtotime($row[8])) : date('Y-m-d'),
                    'gender' => (strtoupper($row[9] ?? '') == 'P') ? 'P' : 'L',
                    'phone' => $row[10] ?? null,
                    'address' => $row[11] ?? null,
                    'user_type' => !empty($row[12]) ? trim($row[12]) : 'PNS',
                    'role' => !empty($row[13]) ? strtolower(trim($row[13])) : 'pegawai',
                    'contract_end_date' => !empty($row[14]) ? date('Y-m-d', strtotime($row[14])) : null,
                    'password' => password_hash('123456', PASSWORD_DEFAULT), // Default password
                    'leave_balance_n' => 12,
                ];

                if ($userModel->save($data)) {
                    $successCount++;
                } else {
                    $errorCount++;
                    $errors[] = "Baris " . ($i + 1) . ": Gagal menyimpan data.";
                }
            }

            $message = "Berhasil mengimpor {$successCount} data.";
            if ($errorCount > 0) {
                $message .= " Gagal {$errorCount} data.";
            }

            $session = session();
            if (!empty($errors)) {
                $session->setFlashdata('import_errors', $errors);
            }

            return redirect()->to('/employee')->with('success', $message);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memproses file: ' . $e->getMessage());
        }
    }

    public function export()
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/dashboard');
        }

        $userModel = new UserModel();
        $employees = $userModel->findAll();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header
        $headers = ['NIP', 'NIK', 'Nama Lengkap', 'Email Aktif', 'Pangkat / Golongan', 'Pendidikan', 'Jabatan', 'Unit Kerja', 'TMT (YYYY-MM-DD)', 'Jenis Kelamin (L/P)', 'No. HP', 'Alamat', 'Status (PNS/PPPK)', 'Akses (admin/pegawai)', 'Tgl Berakhir Kontrak'];
        $column = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($column . '1', $header);
            $sheet->getColumnDimension($column)->setAutoSize(true);
            $column++;
        }

        // Style Header
        $sheet->getStyle('A1:O1')->getFont()->setBold(true);

        // Data
        $row = 2;
        foreach ($employees as $emp) {
            $sheet->setCellValue('A' . $row, $emp['nip']);
            $sheet->setCellValue('B' . $row, $emp['nik']);
            $sheet->setCellValue('C' . $row, $emp['name']);
            $sheet->setCellValue('D' . $row, $emp['email']);
            $sheet->setCellValue('E' . $row, $emp['rank']);
            $sheet->setCellValue('F' . $row, $emp['education']);
            $sheet->setCellValue('G' . $row, $emp['position']);
            $sheet->setCellValue('H' . $row, $emp['unit']);
            $sheet->setCellValue('I' . $row, $emp['join_date']);
            $sheet->setCellValue('J' . $row, ($emp['gender'] == 'P' ? 'P' : 'L'));
            $sheet->setCellValue('K' . $row, $emp['phone']);
            $sheet->setCellValue('L' . $row, $emp['address']);
            $sheet->setCellValue('M' . $row, $emp['user_type']);
            $sheet->setCellValue('N' . $row, $emp['role']);
            $sheet->setCellValue('O' . $row, $emp['contract_end_date']);
            $row++;
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'Data_Pegawai_' . date('Y-m-d_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit();
    }
}
