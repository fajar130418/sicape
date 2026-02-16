<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use App\Models\LeaveRequestModel;
use App\Models\LeaveTypeModel;
use App\Models\UserModel;

class Approval extends BaseController
{
    use ResponseTrait;

    public function index()
    {
        $request = service('request');
        $userData = $request->userData;
        $userId = $userData['id'];

        $model = new LeaveRequestModel();

        // Pending Approvals (Supervisor)
        $supervisorApprovals = $model->select('leave_requests.*, users.name as user_name, users.nip, leave_types.name as leave_type_name')
            ->join('users', 'users.id = leave_requests.user_id')
            ->join('leave_types', 'leave_types.id = leave_requests.leave_type_id')
            ->where('supervisor_id', $userId)
            ->where('supervisor_status', 'pending')
            ->orderBy('created_at', 'ASC')
            ->findAll();

        // Pending Approvals (Head of Agency)
        $headApprovals = [];
        $userModel = new UserModel();
        $user = $userModel->find($userId);

        if ($user['is_head_of_agency']) {
            $headApprovals = $model->select('leave_requests.*, users.name as user_name, users.nip, leave_types.name as leave_type_name')
                ->join('users', 'users.id = leave_requests.user_id')
                ->join('leave_types', 'leave_types.id = leave_requests.leave_type_id')
                ->where('head_id', $userId)
                ->where('head_status', 'pending')
                ->groupStart()
                ->where('leave_types.name !=', 'Cuti Khusus')
                ->orWhere('supervisor_status', 'approved')
                ->groupEnd()
                ->orderBy('created_at', 'ASC')
                ->findAll();
        }

        return $this->respond([
            'status' => 200,
            'data' => [
                'supervisor_approvals' => $supervisorApprovals,
                'head_approvals' => $headApprovals
            ]
        ]);
    }

    public function process($id)
    {
        $request = service('request');
        $userData = $request->userData;
        $userId = $userData['id'];

        $json = $this->request->getJSON();
        $action = $json->action ?? null; // 'approved' or 'rejected'
        $role = $json->role ?? null;     // 'supervisor' or 'head'
        $note = $json->note ?? '';

        if (!in_array($action, ['approved', 'rejected'])) {
            return $this->fail('Invalid action. Use "approved" or "rejected".', 400);
        }

        $model = new LeaveRequestModel();
        $leaveRequest = $model->find($id);

        if (!$leaveRequest) {
            return $this->failNotFound('Leave request not found');
        }

        // Determine Role if not provided (safer to infer from ID)
        if (!$role) {
            if ($leaveRequest['supervisor_id'] == $userId)
                $role = 'supervisor';
            elseif ($leaveRequest['head_id'] == $userId)
                $role = 'head';
            else
                return $this->failForbidden('You are not authorized for this request');
        }

        $typeModel = new LeaveTypeModel();
        $leaveType = $typeModel->find($leaveRequest['leave_type_id']);
        $isSpecialLeave = ($leaveType['name'] === 'Cuti Khusus');

        $data = [];

        if ($role === 'supervisor') {
            if ($leaveRequest['supervisor_id'] != $userId) {
                return $this->failForbidden('You are not the supervisor for this request');
            }

            $data['supervisor_status'] = $action;
            $data['supervisor_note'] = $note;

            if ($action == 'rejected') {
                $data['status'] = 'rejected';
            } elseif ($action == 'approved') {
                // For non-Special Leave, Supervisor approval finalizes it (unless Head also required? Logic says:)
                // Existing Approval.php: if (!$isSpecialLeave) $updateData['status'] = 'approved';
                if (!$isSpecialLeave) {
                    $data['status'] = 'approved';
                }
            }
        } elseif ($role === 'head') {
            $userModel = new UserModel();
            $user = $userModel->find($userId);

            if (!$user['is_head_of_agency']) {
                return $this->failForbidden('You are not Head of Agency');
            }

            // Validation for Special Leave flow
            if ($isSpecialLeave && $leaveRequest['supervisor_status'] !== 'approved') {
                return $this->fail('Cuti Khusus must be approved by Supervisor first', 400);
            }

            $data['head_status'] = $action;
            $data['head_note'] = $note;

            if ($action == 'rejected') {
                $data['status'] = 'rejected';
            } elseif ($action == 'approved') {
                $data['status'] = 'approved';
            }
        } else {
            return $this->fail('Invalid role specified', 400);
        }

        try {
            $model->update($id, $data);

            return $this->respond([
                'status' => 200,
                'message' => 'Request processed successfully',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return $this->failServerError($e->getMessage());
        }
    }
}
