<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use App\Models\UserModel;

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

        // Re-use logic from Dashboard Controller or Model
        // Calculate Leave Balance
        $leaveDetails = $userModel->getDetailedRemainingLeave($userId, date('Y'));

        // Calculate Seniority
        $seniority = $userModel->calculateSeniority($userId);

        // Get Recent Leave History (last 5)
        $db = \Config\Database::connect();
        $builder = $db->table('leave_requests');
        $builder->select('leave_requests.*, leave_types.name as leave_type_name');
        $builder->join('leave_types', 'leave_types.id = leave_requests.leave_type_id');
        $builder->where('user_id', $userId);
        $builder->orderBy('created_at', 'DESC');
        $builder->limit(5);
        $recentLeaves = $builder->get()->getResultArray();

        return $this->respond([
            'status' => 200,
            'data' => [
                'leave_balance' => $leaveDetails,
                'seniority' => $seniority,
                'recent_leaves' => $recentLeaves,
                'server_time' => date('Y-m-d H:i:s')
            ]
        ]);
    }
}
