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
        $model = new LeaveTypeModel();
        $types = $model->findAll();

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

        // Get JSON Input
        $json = $this->request->getJSON();

        if (!$json) {
            return $this->fail('Invalid JSON input', 400);
        }

        $leaveTypeId = $json->leave_type_id ?? null;
        $startDate = $json->start_date ?? null;
        $endDate = $json->end_date ?? null;
        $reason = $json->reason ?? '';
        $workAddress = $json->work_address ?? '';

        if (!$leaveTypeId || !$startDate || !$endDate) {
            return $this->fail('Missing required fields', 400);
        }

        // Calculate Duration
        $start = new \DateTime($startDate);
        $end = new \DateTime($endDate);

        // Basic duration calculation (can be improved with holiday checking logic from existing controller)
        $diff = $start->diff($end);
        $duration = $diff->days + 1;

        // Validation (simplified for API)
        $userModel = new UserModel();
        $user = $userModel->find($userId);

        // TODO: Add detailed quota validation logic here similar to Web Controller
        // For now, basic insertion

        $data = [
            'user_id' => $userId,
            'leave_type_id' => $leaveTypeId,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'duration' => $duration,
            'reason' => $reason,
            'address_during_leave' => $workAddress,
            'status' => 'pending',
            'supervisor_id' => $user['supervisor_id'] // Assuming supervisor logic
        ];

        $leaveModel = new LeaveRequestModel();

        // Handle file upload if multipart/form-data (API usually sends base64 or separate file endpoint, 
        // but for simplicity let's assume JSON first. If attachment needed, logic changes).
        // For this MVP, avoiding attachments in JSON.

        try {
            if ($leaveModel->insert($data)) {
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
}
