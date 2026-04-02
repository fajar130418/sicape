<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use App\Models\LeaveRequestModel;
use App\Models\LeaveTypeModel;
use App\Models\UserModel;
use Config\Database;

class Leave extends BaseController
{
    use ResponseTrait;

    public function index()
    {
        $request = service('request');
        $userData = $request->userData;
        $userId = $userData['id'];

        $model = new LeaveRequestModel();

        // Fetch leave history
        $history = $model->select('leave_requests.*, leave_types.name as leave_type_name')
            ->join('leave_types', 'leave_types.id = leave_requests.leave_type_id')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->findAll();

        return $this->respond([
            'status' => 200,
            'data' => $history
        ]);
    }

    public function types()
    {
        $request = service('request');
        $userData = $request->userData;
        
        $userModel = new \App\Models\UserModel();
        $user = $userModel->find($userData['id']);
        
        $model = new LeaveTypeModel();
        $types = $model->findAll();

        if ($user && $user['role'] !== 'admin') {
            $types = array_values(array_filter($types, function ($type) {
                return $type['name'] !== 'Cuti Khusus';
            }));
        }

        return $this->respond([
            'status' => 200,
            'data' => $types
        ]);
    }

    public function store()
    {
        $request = service('request');
        $userData = $request->userData;
        $userId = $userData['id'];

        $leaveTypeId = $this->request->getPost('leave_type_id');
        $startDate = $this->request->getPost('start_date');
        $endDate = $this->request->getPost('end_date');
        $reason = $this->request->getPost('reason') ?? '';
        $category = $this->request->getPost('category') ?? '';
        $workAddress = $this->request->getPost('address_during_leave') ?? '';

        if (!$leaveTypeId || !$startDate || !$endDate) {
            return $this->fail('Missing required fields', 400);
        }

        // Calculate Duration
        $start = new \DateTime($startDate);
        $end = new \DateTime($endDate);

        // Fetch Leave Type
        $typeModel = new LeaveTypeModel();
        $leaveType = $typeModel->find($leaveTypeId);

        if (!$leaveType) {
            return $this->fail('Leave type not found', 404);
        }

        // Fetch Holidays
        $holidayModel = new \App\Models\HolidayModel();
        $holidays = $holidayModel->findColumn('date') ?? [];

        // Calculate Duration (Parity with web controller)
        $isCalendarDuration = in_array($leaveType['name'], ['Cuti Besar', 'Cuti Sakit', 'Cuti Melahirkan', 'Cuti di Luar Tanggungan Negara']);
        $duration = 0;
        $period = new \DatePeriod($start, new \DateInterval('P1D'), (clone $end)->modify('+1 day'));

        foreach ($period as $dt) {
            $currDate = $dt->format('Y-m-d');
            $dayOfWeek = $dt->format('N'); // 1 (Mon) - 7 (Sun)

            if (!$isCalendarDuration) {
                // Skip Weekend (6=Sat, 7=Sun) for non-calendar types
                if ($dayOfWeek >= 6) {
                    continue;
                }

                // Skip Holiday
                if (in_array($currDate, $holidays)) {
                    continue;
                }
            }

            $duration++;
        }

        if ($duration < 1) {
            return $this->fail('Invalid leave duration calculated (0 days). Check your dates for weekends and holidays.', 400);
        }

        $userModel = new UserModel();
        $user = $userModel->find($userId);
        $db = Database::connect();

        // Get Head of Agency
        $headOfAgencyDataset = $db->table('users')->where('is_head_of_agency', 1)->get()->getRowArray();
        $headId = $headOfAgencyDataset ? $headOfAgencyDataset['id'] : null;

        // --- Global Locking Mechanism (Full Admin Approval) ---
        // Block all leave requests if there are previous approved leaves that haven't been APPROVED by Admin.
        // Exclude leaves that have been manually bypassed by Admin.
        $pendingUpload = $db->table('leave_requests')
            ->where('user_id', $userId)
            ->where('status', 'approved')
            ->groupStart()
                ->where('signed_form_status !=', 'approved')
                ->where('is_bypassed', 0)
            ->groupEnd()
            ->countAllResults();

        if ($pendingUpload > 0) {
            return $this->fail('Anda memiliki pengajuan cuti sebelumnya yang sudah disetujui namun form bertanda-tangan belum disetujui oleh Admin. Silakan unggah form tersebut dan tunggu verifikasi Admin, atau hubungi Admin untuk bantuan.', 400);
        }

        // --- Ported Logic from Web Controller ---

        // 1. Calculate Seniority
        $seniority = $userModel->calculateSeniority($userId);
        $yearsOfService = $seniority['years'];

        // 2. Specific Rules Validation
        // 2a. Cuti Tahunan Rules
        if ($leaveType['name'] == 'Cuti Tahunan') {
            if ($yearsOfService < 1) {
                return $this->fail('Anda belum berhak mengambil Cuti Tahunan (Masa kerja < 1 tahun).', 400);
            }

            // Check if Cuti Besar already taken this year
            $bigLeaveTaken = $db->table('leave_requests')
                ->join('leave_types', 'leave_types.id = leave_requests.leave_type_id')
                ->where('user_id', $userId)
                ->where('leave_types.name', 'Cuti Besar')
                ->where('status', 'approved')
                ->where('YEAR(start_date)', date('Y'))
                ->countAllResults();

            if ($bigLeaveTaken > 0) {
                return $this->fail('Anda tidak dapat mengambil Cuti Tahunan karena sudah mengambil Cuti Besar tahun ini.', 400);
            }

            // Check Balance using cumulative FIFO logic (N, N-1, N-2)
            $detailedRemaining = $userModel->getDetailedRemainingLeave($userId);
            $remainingTotal = $detailedRemaining['remaining']['total'];

            if ($duration > $remainingTotal) {
                return $this->fail("Sisa jatah cuti tahunan tidak mencukupi. Sisa Anda saat ini: " . $remainingTotal . " hari.", 400);
            }
        }

        // 2b. Cuti Khusus Rules
        $isSpecialLeave = ($leaveType['name'] == 'Cuti Khusus');
        if ($isSpecialLeave) {
            if ($user['role'] !== 'admin') {
                return $this->fail('Hanya Administrator yang dapat mengajukan Cuti Khusus.', 403);
            }

            $currentRemaining = $userModel->getRemainingLeave($userId);
            if ($currentRemaining > 0) {
                return $this->fail("Cuti Khusus hanya dapat diambil jika Cuti Tahunan sudah habis. Sisa Cuti Tahunan Anda saat ini: " . $currentRemaining . " hari.", 400);
            }
        }

        // 2c. Type-specific Constraints
        if (!$isSpecialLeave) {
            if ($leaveType['name'] == 'Cuti Besar') {
                $userType = $user['user_type'] ?? 'PNS';
                if ($userType !== 'PNS') {
                    return $this->fail('Cuti Besar hanya diperuntukkan bagi Pegawai Negeri Sipil (PNS).', 400);
                }

                $isHaji = ($category == 'Ibadah Keagamaan (Haji Pertama)');
                if ($yearsOfService < 5 && !$isHaji) {
                    return $this->fail('Anda belum berhak mengambil Cuti Besar (Masa kerja < 5 tahun). Kecuali untuk Haji Pertama.', 400);
                }

                // Milestone-based Forfeiture Check
                $milestoneIndex = floor($yearsOfService / 5);
                $lastMilestoneYears = $milestoneIndex * 5;
                $joinDateObj = new \DateTime($user['join_date']);
                $milestoneDate = clone $joinDateObj;
                $milestoneDate->modify("+$lastMilestoneYears years");

                $previousBigLeave = $db->table('leave_requests')
                    ->join('leave_types', 'leave_types.id = leave_requests.leave_type_id')
                    ->where('user_id', $userId)
                    ->where('leave_types.name', 'Cuti Besar')
                    ->whereIn('status', ['approved', 'pending'])
                    ->where('start_date >=', $milestoneDate->format('Y-m-d'))
                    ->countAllResults();

                if ($previousBigLeave > 0) {
                    return $this->fail('Anda sudah pernah mengambil Cuti Besar dalam siklus 5 tahun ini.', 400);
                }
                
                // Reset Annual Leave Balance (Parity with web)
                $userModel->update($userId, ['leave_balance_n' => 0]);
            }

            if ($leaveType['name'] == 'Cuti Sakit') {
                if ($user['user_type'] !== 'PNS' && !empty($user['contract_end_date'])) {
                    $contractEnd = new \DateTime($user['contract_end_date']);
                    if ($end > $contractEnd) {
                        return $this->fail('Masa cuti sakit melampaui masa berlaku kontrak Anda.', 400);
                    }
                }
                if ($category == 'Gugur Kandungan' && $duration > 45) {
                    return $this->fail('Cuti Sakit Gugur Kandungan maksimal 45 hari.', 400);
                }
                if ($category == 'Kecelakaan Kerja') {
                    $leaveType['max_duration'] = 9999;
                }
            }

            if ($leaveType['name'] == 'Cuti Melahirkan') {
                if ($category == 'Anak ke-4 atau lebih') {
                    return $this->fail('Cuti Melahirkan hanya s.d anak ke-3. Anak ke-4+ gunakan Cuti Besar.', 400);
                }
                $maxEnd = clone $start;
                $maxEnd->modify('+3 months')->modify('-1 day');
                if ($end > $maxEnd) {
                    return $this->fail('Maksimal durasi Cuti Melahirkan adalah 3 bulan.', 400);
                }
                $leaveType['max_duration'] = 999;
            }

            if ($leaveType['name'] == 'Cuti di Luar Tanggungan Negara') {
                if ($user['user_type'] != 'PNS' || $yearsOfService < 5) {
                    return $this->fail('Syarat CLTN: PNS dan masa kerja minimal 5 tahun.', 400);
                }
                if ($duration > 1095) {
                    return $this->fail('Maksimal durasi CLTN adalah 3 tahun.', 400);
                }
                $leaveType['max_duration'] = 9999;
            }
        }

        // 3. Max Duration Check
        if (!$isSpecialLeave && $duration > $leaveType['max_duration']) {
            return $this->fail("Durasi cuti melebihi batas maksimal ({$leaveType['max_duration']} hari).", 400);
        }

        // 4. File Requirement
        $requiresFile = ($leaveType['requires_file'] == 1);
        if ($leaveType['name'] == 'Cuti Alasan Penting' || $leaveType['name'] == 'Cuti Karena Alasan Penting') {
            if (in_array($reason, ['Keluarga Inti Sakit Keras', 'Musibah Bencana', 'Faktor Kejiwaan'])) {
                $requiresFile = true;
            }
        }

        $file = $this->request->getFile('attachment');
        if ($requiresFile && (!$file || !$file->isValid())) {
            return $this->fail('Lampiran pendukung wajib diunggah untuk jenis cuti/alasan ini.', 400);
        }

        $fileName = null;

        if ($file && $file->isValid() && !$file->hasMoved()) {
            $fileName = $file->getRandomName();
            $file->move('uploads', $fileName);
        }

        $data = [
            'user_id' => $userId,
            'leave_type_id' => $leaveTypeId,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'duration' => $duration,
            'reason' => $reason,
            'category' => $category,
            'address_during_leave' => $workAddress,
            'attachment' => $fileName,
            'status' => 'pending',
            'supervisor_id' => $user['supervisor_id'],
            'supervisor_status' => 'pending',
            'head_id' => $headId,
            'head_status' => 'pending',
            'signed_form_status' => 'pending_upload'
        ];

        $leaveModel = new LeaveRequestModel();

        try {
            if ($leaveModel->insert($data)) {
                
                // Send Notification to Supervisor or Head
                $title = "Pengajuan Cuti Baru";
                $body = "Ada pengajuan cuti baru dari " . $user['name'] . " menunggu persetujuan Anda.";
                
                // If it's Cuti Khusus, notify Head of Agency directly? Or supervisor first.
                // Standard flow: notify supervisor first
                if ($user['supervisor_id']) {
                    $supervisor = $userModel->find($user['supervisor_id']);
                    if (!empty($supervisor['fcm_token'])) {
                        \App\Helpers\FirebaseHelper::sendNotification($supervisor['fcm_token'], $title, $body, ['type' => 'new_leave_request']);
                    }
                }

                return $this->respondCreated([
                    'status' => 201,
                    'message' => 'Leave Request Created',
                    'data' => $data
                ]);
            } else {
                return $this->fail($leaveModel->errors());
            }
        } catch (\Exception $e) {
            return $this->failServerError($e->getMessage());
        }
    }

    public function print($id)
    {
        $request = service('request');
        $userData = $request->userData;
        $userId = $userData['id'];

        $model = new LeaveRequestModel();
        $leaveRequest = $model->select('leave_requests.*, users.name as user_name, users.nip, users.unit, users.position, users.join_date, users.phone, users.is_head_of_agency, leave_types.name as type_name, s.name as supervisor_name, s.nip as supervisor_nip')
            ->join('users', 'users.id = leave_requests.user_id')
            ->join('leave_types', 'leave_types.id = leave_requests.leave_type_id')
            ->join('users s', 's.id = leave_requests.supervisor_id', 'left')
            ->where('leave_requests.id', $id)
            ->first();

        if (!$leaveRequest) {
            return $this->failNotFound('Leave request not found');
        }

        // Security check
        $isOwner = $leaveRequest['user_id'] == $userId;
        $isSupervisor = $leaveRequest['supervisor_id'] == $userId;
        $userModel = new UserModel();
        $currentUser = $userModel->find($userId);
        $isAdmin = ($currentUser['role'] == 'admin');

        if (!$isOwner && !$isSupervisor && !$isAdmin) {
            return $this->failForbidden('Akses ditolak.');
        }

        // Only allow download if approved OR if user is Head of Agency
        $requester = $userModel->find($leaveRequest['user_id']);
        $isHeadOfAgency = $requester['is_head_of_agency'] == 1;

        if ($leaveRequest['status'] != 'approved' && !$isHeadOfAgency) {
            return $this->fail('Dokumen hanya dapat diunduh setelah disetujui.', 400);
        }

        if ($leaveRequest['type_name'] == 'Cuti Khusus') {
            if ($leaveRequest['supervisor_status'] != 'approved' || $leaveRequest['head_status'] != 'approved') {
                return $this->fail('Cuti Khusus harus disetujui oleh Atasan Langsung DAN Kepala Dinas sebelum dicetak.', 400);
            }
        }

        // Reusing the same logic as the web version
        $seniority = $userModel->calculateSeniority($leaveRequest['user_id']);
        $tenure = $seniority['years'] . " Tahun " . $seniority['months'] . " Bulan";
        $headOfAgency = $model->db->table('users')->where('is_head_of_agency', 1)->get()->getRowArray();
        $detailedRemaining = $userModel->getDetailedRemainingLeave($leaveRequest['user_id']);

        $data = [
            'request' => $leaveRequest,
            'tenure' => $tenure,
            'headOfAgency' => $headOfAgency,
            'manualSignature' => null, // Manual sign limited to web interface for now
            'remainingN' => $detailedRemaining['remaining']['n'],
            'remainingN1' => $detailedRemaining['remaining']['n1'],
            'remainingN2' => $detailedRemaining['remaining']['n2'],
            'currentYear' => date('Y')
        ];

        $dompdf = new \Dompdf\Dompdf();
        $html = view('leave/print', $data);
        $dompdf->loadHtml($html);
        $dompdf->setPaper([0, 0, 612.00, 936.00], 'portrait');
        $dompdf->render();
        
        $filename = "Formulir_Cuti_" . $leaveRequest['nip'] . ".pdf";
        
        return $this->response->setHeader('Content-Type', 'application/pdf')
                              ->setHeader('Content-Disposition', 'attachment; filename="'.$filename.'"')
                               ->setBody($dompdf->output());
    }

    public function upload_signed_form($id)
    {
        $request = service('request');
        $userData = $request->userData;
        $userId = $userData['id'];

        $model = new LeaveRequestModel();
        $leaveRequest = $model->find($id);

        if (!$leaveRequest) {
            return $this->failNotFound('Leave request not found');
        }

        // Security check: Only owner can upload
        if ($leaveRequest['user_id'] != $userId) {
            return $this->failForbidden('Akses ditolak.');
        }

        if ($leaveRequest['status'] != 'approved') {
            return $this->fail('Form hanya dapat diunggah untuk cuti yang sudah disetujui.', 400);
        }

        $file = $this->request->getFile('signed_form');
        if (!$file || !$file->isValid()) {
            return $this->fail('File tidak valid atau tidak ditemukan.', 400);
        }

        if ($file->hasMoved()) {
            return $this->fail('File sudah dipindahkan.', 400);
        }

        $fileName = $file->getRandomName();
        $file->move('uploads/signed_forms', $fileName);

        if ($model->update($id, [
            'signed_form' => 'signed_forms/' . $fileName,
            'signed_form_status' => 'pending_approval'
        ])) {
            return $this->respond([
                'status' => 200,
                'message' => 'Form tanda tangan berhasil diunggah. Mohon tunggu verifikasi dari Admin.',
                'data' => ['signed_form' => 'signed_forms/' . $fileName]
            ]);
        } else {
            return $this->fail($model->errors());
        }
    }

    /**
     * Admin view for all pending signed form approvals
     */
    public function pending_uploads()
    {
        $request = service('request');
        $userData = $request->userData;
        
        $userModel = new UserModel();
        $user = $userModel->find($userData['id']);
        
        if ($user['role'] !== 'admin') {
            return $this->failForbidden('Hanya Administrator yang dapat mengakses menu ini.');
        }

        $model = new LeaveRequestModel();
        $pending = $model->select('leave_requests.*, users.name as user_name, users.nip, leave_types.name as type_name')
            ->join('users', 'users.id = leave_requests.user_id')
            ->join('leave_types', 'leave_types.id = leave_requests.leave_type_id')
            ->where('leave_requests.status', 'approved')
            ->where('leave_requests.signed_form_status', 'pending_approval')
            ->where('leave_requests.is_bypassed', 0)
            ->orderBy('leave_requests.updated_at', 'ASC')
            ->findAll();

        return $this->respond([
            'status' => 200,
            'data' => $pending
        ]);
    }

    /**
     * Admin approve signed form
     */
    public function approve_signed_form($id)
    {
        $request = service('request');
        $userData = $request->userData;
        
        $userModel = new UserModel();
        $user = $userModel->find($userData['id']);
        
        if ($user['role'] !== 'admin') {
            return $this->failForbidden();
        }

        $model = new LeaveRequestModel();
        if ($model->update($id, ['signed_form_status' => 'approved'])) {
            return $this->respond([
                'status' => 200,
                'message' => 'Form tanda tangan disetujui. Kunci cuti pegawai telah dibuka.'
            ]);
        } else {
            return $this->fail($model->errors());
        }
    }

    /**
     * Admin reject signed form
     */
    public function reject_signed_form($id)
    {
        $request = service('request');
        $userData = $request->userData;
        
        $inputNote = $this->request->getPost('note');
        if (empty($inputNote)) {
            $json = $this->request->getJSON();
            $inputNote = $json->note ?? null;
        }
        $note = $inputNote ?: 'Ditolak Admin. Silakan unggah ulang.';
        
        $userModel = new UserModel();
        $user = $userModel->find($userData['id']);
        
        if ($user['role'] !== 'admin') {
            return $this->failForbidden();
        }

        $model = new LeaveRequestModel();
        if ($model->update($id, [
            'signed_form_status' => 'rejected',
            'signed_form_note' => $note
        ])) {
            return $this->respond([
                'status' => 200,
                'message' => 'Form tanda tangan ditolak.'
            ]);
        } else {
            return $this->fail($model->errors());
        }
    }

    /**
     * Admin bypass lock for a specific leave request
     */
    public function bypass_lock($id)
    {
        $request = service('request');
        $userData = $request->userData;
        
        $userModel = new UserModel();
        $user = $userModel->find($userData['id']);
        
        if ($user['role'] !== 'admin') {
            return $this->failForbidden('Hanya Administrator yang dapat melakukan bypass.');
        }

        $model = new LeaveRequestModel();
        if ($model->update($id, ['is_bypassed' => 1])) {
            return $this->respond([
                'status' => 200,
                'message' => 'Kunci cuti berhasil dibuka (Bypass aktif).'
            ]);
        } else {
            return $this->fail($model->errors());
        }
    }
}
