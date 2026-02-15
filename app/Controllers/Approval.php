<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Approval extends BaseController
{
    public function index()
    {
        $userId = session()->get('id');
        $requestModel = new \App\Models\LeaveRequestModel();

        // Requests needing Supervisor Approval from this user
        $requests = $requestModel->select('leave_requests.*, users.name as user_name, users.nip, leave_types.name as type_name')
            ->join('users', 'users.id = leave_requests.user_id')
            ->join('leave_types', 'leave_types.id = leave_requests.leave_type_id')
            ->where('leave_requests.supervisor_id', $userId)
            ->where('supervisor_status', 'pending')
            ->orderBy('leave_requests.created_at', 'ASC')
            ->findAll();

        $data = [
            'title' => 'Persetujuan Cuti (Atasan)',
            'requests' => $requests
        ];

        return view('approval/index', $data);
    }

    public function process($id, $action)
    {
        $userId = session()->get('id');
        $model = new \App\Models\LeaveRequestModel();
        $request = $model->find($id);

        if (!$request || $request['supervisor_id'] != $userId) {
            return redirect()->back()->with('error', 'Akses ditolak atau permintaan tidak ditemukan.');
        }

        if (!in_array($action, ['approved', 'rejected', 'deferred', 'changed'])) {
            return redirect()->back()->with('error', 'Aksi tidak valid.');
        }

        $note = $this->request->getVar('note');

        $updateData = [
            'supervisor_status' => $action,
            'supervisor_note' => $note,
            'supervisor_sign_as' => $this->request->getVar('supervisor_sign_as') ?: 'Definitif'
        ];
        // Logic: Supervisor Approval = Final Approval for now (as requested by user "oleh atasan pegawai")
        if ($action == 'rejected') {
            $updateData['status'] = 'rejected';
        } elseif ($action == 'approved') {
            $updateData['status'] = 'approved';
        }

        $model->update($id, $updateData);

        return redirect()->to('/approval')->with('success', 'Status pengajuan berhasil diperbarui.');
    }
}
