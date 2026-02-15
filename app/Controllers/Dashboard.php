<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class Dashboard extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $userId = session()->get('id');
        $joinDate = session()->get('join_date');

        $userModel = new \App\Models\UserModel();
        $user = $userModel->find($userId);

        $seniority = $userModel->calculateSeniority($userId);
        $yearsOfService = $seniority['years'];
        $monthsOfService = $seniority['months'];

        // ASN Annual Leave Logic (FIFO)
        $detailed = $userModel->getDetailedRemainingLeave($userId);

        $initialN = (int) ($user['leave_balance_n'] ?? 12);
        $initialN1 = min((int) ($user['leave_balance_n1'] ?? 0), 6);
        $initialN2 = min((int) ($user['leave_balance_n2'] ?? 0), 6);
        $annualLeaveQuota = $initialN + $initialN1 + $initialN2;

        $remainingAnnualLeave = $detailed['total'];
        $usedAnnualLeave = $annualLeaveQuota - $remainingAnnualLeave;

        // Fetch recent history
        $requestModel = new \App\Models\LeaveRequestModel();
        $history = $requestModel->select('leave_requests.*, leave_types.name as type_name')
            ->join('leave_types', 'leave_types.id = leave_requests.leave_type_id')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->findAll(5);

        $expiringPPPKCount = 0;
        if (session()->get('role') === 'admin') {
            $oneMonthLater = date('Y-m-d', strtotime('+1 month'));
            $expiringPPPKCount = $userModel->where('user_type !=', 'PNS')
                ->where('contract_end_date <=', $oneMonthLater)
                ->where('contract_end_date >=', date('Y-m-d'))
                ->countAllResults();
        }

        $data = [
            'title' => 'Dashboard',
            'yearsOfService' => $yearsOfService,
            'monthsOfService' => $monthsOfService,
            'annualLeaveQuota' => $annualLeaveQuota,
            'usedAnnualLeave' => $usedAnnualLeave,
            'remainingAnnualLeave' => $remainingAnnualLeave,
            'leaveBreakdown' => $detailed,
            'history' => $history,
            'expiringPPPKCount' => $expiringPPPKCount
        ];

        return view('dashboard/index', $data);
    }
}
