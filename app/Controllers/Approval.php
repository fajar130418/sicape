<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Approval extends BaseController
{
    public function index()
    {
        $userId = session()->get('id');
        $requestModel = new \App\Models\LeaveRequestModel();

        // 1. Requests needing Supervisor Approval
        $supervisorRequests = $requestModel->select('leave_requests.*, users.name as user_name, users.nip, leave_types.name as type_name')
            ->join('users', 'users.id = leave_requests.user_id')
            ->join('leave_types', 'leave_types.id = leave_requests.leave_type_id')
            ->where('leave_requests.supervisor_id', $userId)
            ->where('supervisor_status', 'pending')
            ->orderBy('leave_requests.created_at', 'ASC')
            ->findAll();

        // 2. Requests needing Head of Agency Approval (only after Supervisor approved if it's Cuti Khusus)
        $headRequests = $requestModel->select('leave_requests.*, users.name as user_name, users.nip, leave_types.name as type_name')
            ->join('users', 'users.id = leave_requests.user_id')
            ->join('leave_types', 'leave_types.id = leave_requests.leave_type_id')
            ->where('leave_requests.head_id', $userId)
            ->where('head_status', 'pending')
            ->groupStart()
            ->where('leave_types.name !=', 'Cuti Khusus')
            ->orWhere('supervisor_status', 'approved')
            ->groupEnd()
            ->orderBy('leave_requests.created_at', 'ASC')
            ->findAll();

        $data = [
            'title' => 'Persetujuan Cuti',
            'supervisorRequests' => $supervisorRequests,
            'headRequests' => $headRequests
        ];

        return view('approval/index', $data);
    }

    public function process($id, $action)
    {
        $userId = session()->get('id');
        $model = new \App\Models\LeaveRequestModel();
        $request = $model->find($id);

        if (!$request) {
            return redirect()->back()->with('error', 'Permintaan tidak ditemukan.');
        }

        $isSupervisor = ($request['supervisor_id'] == $userId);
        $isHead = ($request['head_id'] == $userId);

        if (!$isSupervisor && !$isHead) {
            return redirect()->back()->with('error', 'Akses ditolak atau Anda bukan penandatangan untuk permohonan ini.');
        }

        $typeModel = new \App\Models\LeaveTypeModel();
        $leaveType = $typeModel->find($request['leave_type_id']);
        $isSpecialLeave = ($leaveType['name'] === 'Cuti Khusus');

        $note = $this->request->getVar('note');
        $updateData = [];

        if ($isSupervisor) {
            $updateData['supervisor_status'] = $action;
            $updateData['supervisor_note'] = $note;
            $updateData['supervisor_sign_as'] = $this->request->getVar('supervisor_sign_as') ?: 'Definitif';

            if ($action == 'rejected') {
                $updateData['status'] = 'rejected';
            } elseif ($action == 'approved') {
                // Jika Cuti Khusus, status global tetap pending sampai Kepala Dinas setuju
                if (!$isSpecialLeave) {
                    $updateData['status'] = 'approved';
                }
            }
        } elseif ($isHead) {
            // Validasi: Jika Cuti Khusus, Atasan harus setuju dulu
            if ($isSpecialLeave && $request['supervisor_status'] !== 'approved') {
                return redirect()->back()->with('error', 'Cuti Khusus harus disetujui oleh Atasan Langsung terlebih dahulu.');
            }

            $updateData['head_status'] = $action;
            $updateData['head_note'] = $note;

            if ($action == 'rejected') {
                $updateData['status'] = 'rejected';
            } elseif ($action == 'approved') {
                $updateData['status'] = 'approved';
            }
        }

        $model->update($id, $updateData);

        return redirect()->to('/approval')->with('success', 'Status pengajuan berhasil diperbarui.');
    }
}
