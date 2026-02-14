<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class Leave extends BaseController
{
    public function create()
    {
        $typeModel = new \App\Models\LeaveTypeModel();
        $data = [
            'title' => 'Ajukan Cuti',
            'leave_types' => $typeModel->findAll()
        ];

        // If Admin, fetch all users for dropdown
        if (session()->get('role') == 'admin') {
            $userModel = new \App\Models\UserModel();
            $data['users'] = $userModel->orderBy('name', 'ASC')->findAll();
        }

        return view('leave/create', $data);
    }

    public function store()
    {
        $request = \Config\Services::request();
        $db = \Config\Database::connect();
        $userModel = new \App\Models\UserModel();

        // Determine User ID (Admin can select other users)
        $currentUserId = session()->get('id');
        $targetUserId = $currentUserId;

        if (session()->get('role') == 'admin' && $request->getVar('target_user_id')) {
            $targetUserId = $request->getVar('target_user_id');
        }

        // Fetch Target User Data
        $targetUser = $userModel->find($targetUserId);
        $joinDate = $targetUser['join_date'];
        $supervisorId = $targetUser['supervisor_id'];

        $userId = $targetUserId; // Use this for the rest of the logic

        $rules = [
            'leave_type_id' => 'required',
            'start_date' => 'required|valid_date',
            'end_date' => 'required|valid_date',
            'reason' => 'required',
        ];

        // Custom validation for file upload if required
        $leaveTypeId = $request->getVar('leave_type_id');
        $typeModel = new \App\Models\LeaveTypeModel();
        $leaveType = $typeModel->find($leaveTypeId);

        $startDate = $request->getVar('start_date');
        $endDate = $request->getVar('end_date');

        // Calculate duration (Exclude Weekends & Holidays)
        $start = new \DateTime($startDate);
        $end = new \DateTime($endDate);

        if ($start > $end) {
            return redirect()->back()->withInput()->with('errors', ['Tanggal selesai tidak boleh lebih awal dari tanggal mulai.']);
        }

        // Fetch Holidays
        $holidayModel = new \App\Models\HolidayModel();
        $holidays = $holidayModel->findColumn('date') ?? []; // Get array of holiday dates

        $duration = 0;
        $period = new \DatePeriod($start, new \DateInterval('P1D'), $end->modify('+1 day'));

        foreach ($period as $dt) {
            $currDate = $dt->format('Y-m-d');
            $dayOfWeek = $dt->format('N'); // 1 (Mon) - 7 (Sun)

            // Skip Weekend (6=Sat, 7=Sun)
            if ($dayOfWeek >= 6) {
                continue;
            }

            // Skip Holiday
            if (in_array($currDate, $holidays)) {
                continue;
            }

            $duration++;
        }

        if ($duration < 1) {
            return redirect()->back()->withInput()->with('errors', ['Durasi cuti valid adalah 0 hari (mungkin karena hari libur/akhir pekan).']);
        }

        // Specific Rules Validation
        $joinDateObj = new \DateTime($joinDate);
        $today = new \DateTime();
        $yearsOfService = $joinDateObj->diff($today)->y;

        // 1. Cuti Tahunan Rules
        if ($leaveType['name'] == 'Cuti Tahunan') {
            if ($yearsOfService < 1) {
                return redirect()->back()->withInput()->with('errors', ['Anda belum berhak mengambil Cuti Tahunan (Masa kerja < 1 tahun).']);
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
                return redirect()->back()->withInput()->with('errors', ['Anda tidak dapat mengambil Cuti Tahunan karena sudah mengambil Cuti Besar tahun ini.']);
            }

            // Check Balance using cumulative FIFO logic (N, N-1, N-2)
            $builder = $db->table('leave_requests');
            $builder->select('SUM(duration) as used_days', false);
            $builder->join('leave_types', 'leave_types.id = leave_requests.leave_type_id');
            $builder->where('user_id', $userId);
            $builder->where('leave_types.name', 'Cuti Tahunan');
            $builder->whereIn('status', ['approved', 'pending']);
            $builder->where('YEAR(start_date)', date('Y'));
            $query = $builder->get();
            $result = $query->getRow();
            $usedInSystem = (int) ($result->used_days ?? 0);

            // Get Total Initial Quota (N + N-1 + N-2) with ASN constraints
            $initialN = (int) ($targetUser['leave_balance_n'] ?? 12);
            $initialN1 = min((int) ($targetUser['leave_balance_n1'] ?? 0), 6);
            $initialN2 = min((int) ($targetUser['leave_balance_n2'] ?? 0), 6);
            $totalQuota = $initialN + $initialN1 + $initialN2;

            $remainingTotal = max(0, $totalQuota - $usedInSystem);

            if ($duration > $remainingTotal) {
                return redirect()->back()->withInput()->with('errors', ["Sisa jatah cuti tahunan (termasuk akumulasi sisa tahun lalu) tidak mencukupi. Sisa Anda saat ini: " . $remainingTotal . " hari."]);
            }
        }

        // 2. Cuti Besar Rules
        if ($leaveType['name'] == 'Cuti Besar') {
            // Check User Type
            $userType = $targetUser['user_type'] ?? 'PNS';
            if ($userType !== 'PNS') {
                return redirect()->back()->withInput()->with('errors', ['Cuti Besar hanya diperuntukkan bagi PNS. PPPK dan PPPK Paruh Waktu tidak berhak mengajukan Cuti Besar.']);
            }

            $leaveCategory = $request->getVar('category');

            // Haji Exception: PNS yang belum mencapai 5 tahun boleh mengambil cuti besar untuk ibadah keagamaan (Haji Pertama).
            $isHaji = ($leaveCategory == 'Ibadah Keagamaan (Haji Pertama)');

            if ($yearsOfService < 5 && !$isHaji) {
                return redirect()->back()->withInput()->with('errors', ['Anda belum berhak mengambil Cuti Besar (Masa kerja < 5 tahun). Kecuali untuk Ibadah Keagamaan (Haji Pertama).']);
            }

            // Milestone-based Forfeiture Check: 
            // Once taken in a 5-year period, the "sisa hak" (remaining balance) for that period is forfeited.
            $milestoneIndex = floor($yearsOfService / 5);
            $lastMilestoneYears = $milestoneIndex * 5;
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
                return redirect()->back()->withInput()->with('errors', ['Anda sudah pernah mengambil Cuti Besar dalam siklus 5 tahun ini (Milestone: ' . $milestoneDate->format('d/m/Y') . '). Sesuai regulasi, sisa jatah hak Cuti Besar Anda di siklus ini dianggap hangus.']);
            }

            // Special Rule: ASN yang mengambil cuti besar tidak berhak lagi atas cuti tahunan di tahun yang sama.
            // Reset Annual Leave Balance for this year
            $userModel->update($userId, ['leave_balance_n' => 0]);
        }

        // 3. Max Duration Check
        if ($duration > $leaveType['max_duration']) {
            return redirect()->back()->withInput()->with('errors', ["Durasi cuti melebihi batas maksimal ({$leaveType['max_duration']} hari)."]);
        }

        // 4. File Requirement & CAP Specific Logic
        if ($leaveType['name'] == 'Cuti Alasan Penting') {
            $capCategory = $request->getVar('reason');
            $requiresAttachment = in_array($capCategory, [
                'Keluarga Inti Sakit Keras',
                'Musibah Bencana',
                'Faktor Kejiwaan'
            ]);

            if ($requiresAttachment) {
                $rules['attachment'] = 'uploaded[attachment]|max_size[attachment,2048]|ext_in[attachment,pdf,jpg,jpeg,png]';
            }
        } elseif ($leaveType['requires_file'] == 1) {
            $rules['attachment'] = 'uploaded[attachment]|max_size[attachment,2048]|ext_in[attachment,pdf,jpg,jpeg,png]';
        }

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $file = $this->request->getFile('attachment');
        $fileName = null;

        if ($file && $file->isValid() && !$file->hasMoved()) {
            $fileName = $file->getRandomName();
            $file->move('uploads', $fileName);
        }

        $model = new \App\Models\LeaveRequestModel();

        $data = [
            'user_id' => $userId,
            'leave_type_id' => $leaveTypeId,
            'start_date' => $request->getVar('start_date'),
            'end_date' => $request->getVar('end_date'),
            'duration' => $duration,
            'reason' => $request->getVar('reason'),
            'category' => $request->getVar('category'),
            'address_during_leave' => $request->getVar('address_during_leave'),
            'attachment' => $fileName,
            'status' => 'pending',
            'supervisor_id' => $supervisorId,
            'supervisor_status' => 'pending'
        ];

        $model->save($data);
        return redirect()->to('/dashboard')->with('success', 'Pengajuan cuti berhasil dikirim.');
    }

    public function history()
    {
        $userId = session()->get('id');
        $requestModel = new \App\Models\LeaveRequestModel();
        $history = $requestModel->select('leave_requests.*, leave_types.name as type_name, users.is_head_of_agency as is_head_of_agency')
            ->join('leave_types', 'leave_types.id = leave_requests.leave_type_id')
            ->join('users', 'users.id = leave_requests.user_id')
            ->where('leave_requests.user_id', $userId)
            ->orderBy('leave_requests.created_at', 'DESC')
            ->findAll();

        $userModel = new \App\Models\UserModel();
        $remainingLeave = $userModel->getRemainingLeave($userId);

        $data = [
            'title' => 'Riwayat Cuti',
            'history' => $history,
            'remainingLeave' => $remainingLeave
        ];

        return view('leave/history', $data);
    }
    public function print($id)
    {
        $userId = session()->get('id');
        $model = new \App\Models\LeaveRequestModel();

        $request = $model->select('leave_requests.*, users.name as user_name, users.nip, users.unit, users.position, users.join_date, users.phone, users.is_head_of_agency, leave_types.name as type_name, s.name as supervisor_name, s.nip as supervisor_nip')
            ->join('users', 'users.id = leave_requests.user_id')
            ->join('leave_types', 'leave_types.id = leave_requests.leave_type_id')
            ->join('users s', 's.id = leave_requests.supervisor_id', 'left')
            ->where('leave_requests.id', $id)
            ->first();

        // Security check: Only own request or admin/supervisor can view
        if (!$request) {
            return redirect()->back()->with('error', 'Permintaan tidak ditemukan.');
        }

        // Allow owner
        $isOwner = $request['user_id'] == $userId;
        // Allow supervisor
        $isSupervisor = $request['supervisor_id'] == $userId;
        // Allow admin
        $isAdmin = session()->get('role') == 'admin';

        if (!$isOwner && !$isSupervisor && !$isAdmin) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        // Only allow download if approved OR if user is Head of Agency (bypass approval)
        $userModel = new \App\Models\UserModel();
        $user = $userModel->find($request['user_id']);
        $isHeadOfAgency = $user['is_head_of_agency'] == 1;

        if ($request['status'] != 'approved' && !$isHeadOfAgency) {
            return redirect()->back()->with('error', 'Dokumen hanya dapat diunduh setelah disetujui.');
        }

        // Calculate tenure text
        $joinDate = new \DateTime($request['join_date']);
        $today = new \DateTime();
        $diff = $joinDate->diff($today);
        $tenure = $diff->y . " Tahun " . $diff->m . " Bulan";

        // Fetch Head of Agency
        $headOfAgency = $model->db->table('users')->where('is_head_of_agency', 1)->get()->getRowArray();

        // Check if Manual Signature Data Provided (For Head of Agency)
        $manualSignature = null;

        if (strtolower($this->request->getMethod()) === 'post') {
            // Retrieve manual signature data if posted
            $manualSignature = [
                'supervisor' => [
                    'name' => $this->request->getPost('supervisor_sign_name'),
                    'nip' => $this->request->getPost('supervisor_sign_nip'),
                    'position' => $this->request->getPost('supervisor_sign_position'),
                ],
                'official' => [
                    'name' => $this->request->getPost('official_sign_name'),
                    'nip' => $this->request->getPost('official_sign_nip'),
                    'position' => $this->request->getPost('official_sign_position'),
                ]
            ];
        }

        // Calculate Remaining Leave (N, N-1, N-2) using detailed FIFO logic
        $detailedRemaining = $userModel->getDetailedRemainingLeave($request['user_id']);

        $data = [
            'request' => $request,
            'tenure' => $tenure,
            'headOfAgency' => $headOfAgency,
            'manualSignature' => $manualSignature,
            'remainingN' => $detailedRemaining['n'],
            'remainingN1' => $detailedRemaining['n1'],
            'remainingN2' => $detailedRemaining['n2'],
            'currentYear' => date('Y')
        ];

        $dompdf = new \Dompdf\Dompdf();
        $html = view('leave/print', $data);
        $dompdf->loadHtml($html);
        // F4 size in points: 8.5inch x 13inch = 612pt x 936pt
        $dompdf->setPaper([0, 0, 612.00, 936.00], 'portrait');
        $dompdf->render();
        $dompdf->stream("Formulir_Cuti_" . $request['nip'] . ".pdf", ["Attachment" => true]);
    }
}
