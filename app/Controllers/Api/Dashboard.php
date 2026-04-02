<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use App\Models\UserModel;

use App\Controllers\Kgb as KgbController;

class Dashboard extends BaseController
{
    use ResponseTrait;

    public function index()
    {
        // Access user data from the filter (ensure property exists or retrieve from request)
        // In CI4 filters, passing data to controller is tricky strictly via request properties dynamically, 
        // but let's assume valid token means we can trust the sub in payload if we re-decode or passed via request.
        // For now, let's re-validate or just use the user ID if we can get it.
        // Actually, ApiAuth filter put userData into $request->userData.

        $request = service('request');
        if (!property_exists($request, 'userData')) {
            return $this->failUnauthorized('Invalid Session');
        }

        $userData = $request->userData;
        $userId = $userData['id'];

        $userModel = new UserModel();
        $user = $userModel->find($userId);

        // Re-use logic from Dashboard Controller or Model
        // Calculate Leave Balance
        $leaveDetails = $userModel->getDetailedRemainingLeave($userId, date('Y'));

        // Calculate Seniority
        $seniority = $userModel->calculateSeniority($userId);

        // Calculate KGB Info
        $kgbInfo = null;
        if (in_array($user['user_type'] ?? '', ['PNS', 'PPPK'])) {
            $kgbInfo = KgbController::calculateKgb($user, new \DateTime());
        }

        // Get Recent Leave History (last 5)
        $db = \Config\Database::connect();
        $builder = $db->table('leave_requests');
        $builder->select('leave_requests.*, leave_types.name as leave_type_name');
        $builder->join('leave_types', 'leave_types.id = leave_requests.leave_type_id');
        $builder->where('user_id', $userId);
        $builder->orderBy('created_at', 'DESC');
        $builder->limit(5);
        $recentLeaves = $builder->get()->getResultArray();

        // Calculate Pending Approvals Count
        $pendingCount = 0;
        
        // 1. As Supervisor
        $pendingCount += $db->table('leave_requests')
            ->where('supervisor_id', $userId)
            ->where('supervisor_status', 'pending')
            ->countAllResults();

        // 2. As Head of Agency
        if ($user['is_head_of_agency']) {
            $pendingCount += $db->table('leave_requests')
                ->join('leave_types', 'leave_types.id = leave_requests.leave_type_id')
                ->where('head_id', $userId)
                ->where('head_status', 'pending')
                ->groupStart()
                    ->where('leave_types.name !=', 'Cuti Khusus')
                    ->orWhere('leave_requests.supervisor_status', 'approved')
                ->groupEnd()
                ->countAllResults();
        }

        // 3. As Admin (Signed Form Verification)
        if ($user['role'] == 'admin') {
            $pendingCount += $db->table('leave_requests')
                ->where('signed_form_status', 'pending_approval')
                ->where('is_bypassed', 0)
                ->countAllResults();
        }

        // 4. Staff KGB Alerts for Admins
        $kgbAlerts = [];
        if ($user['role'] == 'admin') {
            $staff = $userModel
                ->whereIn('user_type', ['PNS', 'PPPK'])
                ->where('id !=', $userId) // Optional: exclude self if already shown in kgbInfo
                ->findAll();
            
            $today = new \DateTime();
            foreach ($staff as $emp) {
                $empKgb = KgbController::calculateKgb($emp, $today);
                if (in_array($empKgb['kgb_status'], [KgbController::STATUS_OVERDUE, KgbController::STATUS_WARNING])) {
                    $kgbAlerts[] = [
                        'user_id' => $emp['id'],
                        'name' => $emp['name'],
                        'nip' => $emp['nip'],
                        'kgb_status' => $empKgb['kgb_status'],
                        'kgb_next_date' => $empKgb['kgb_next_date'],
                        'kgb_days_left' => $empKgb['kgb_days_left'],
                    ];
                }
            }
        }

        return $this->respond([
            'status' => 200,
            'data' => [
                'leave_balance' => $leaveDetails,
                'seniority' => $seniority,
                'kgb_info' => $kgbInfo,
                'recent_leaves' => $recentLeaves,
                'pending_approvals_count' => $pendingCount,
                'kgb_alerts' => $kgbAlerts, // New field for admins
                'server_time' => date('Y-m-d H:i:s')
            ]
        ]);
    }
}
