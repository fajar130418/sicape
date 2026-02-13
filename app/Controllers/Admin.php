<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class Admin extends BaseController
{
    public function index()
    {
        // Ensure only admin can access
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/dashboard');
        }

        $requestModel = new \App\Models\LeaveRequestModel();
        
        // Fetch pending requests with user and type info
        $requests = $requestModel->select('leave_requests.*, users.name as user_name, users.nip, leave_types.name as type_name, users.is_head_of_agency')
                                 ->join('users', 'users.id = leave_requests.user_id')
                                 ->join('leave_types', 'leave_types.id = leave_requests.leave_type_id')
                                 ->orderBy('leave_requests.created_at', 'ASC') // Oldest first
                                 ->findAll();

        $data = [
            'title' => 'Admin Panel',
            'requests' => $requests
        ];

        return view('admin/index', $data);
    }

    public function process($id, $status)
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/dashboard');
        }

        $model = new \App\Models\LeaveRequestModel();
        $request = $model->find($id);

        if (!$request) {
            return redirect()->back()->with('error', 'Permintaan tidak ditemukan.');
        }

        if (!in_array($status, ['approved', 'rejected'])) {
            return redirect()->back()->with('error', 'Status tidak valid.');
        }

        $updateData = [
            'status' => $status,
            'admin_note' => $this->request->getVar('admin_note') ?? ($status == 'approved' ? 'Disetujui' : 'Ditolak')
        ];

        $model->update($id, $updateData);

        return redirect()->to('/admin')->with('success', 'Permintaan berhasil diperbarui.');
    }

    public function recalculateDurations()
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/dashboard');
        }

        $db = \Config\Database::connect();
        $requestModel = new \App\Models\LeaveRequestModel();
        $holidayModel = new \App\Models\HolidayModel();
        
        // Fetch all holidays
        $holidays = $holidayModel->findColumn('date') ?? [];
        
        // Fetch all leave requests
        $allRequests = $requestModel->findAll();
        
        $updated = 0;
        
        foreach ($allRequests as $request) {
            $start = new \DateTime($request['start_date']);
            $end = new \DateTime($request['end_date']);
            
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
            
            // Update the duration
            $requestModel->update($request['id'], ['duration' => $duration]);
            $updated++;
        }
        
        return redirect()->to('/admin')->with('success', "Berhasil menghitung ulang durasi untuk {$updated} pengajuan cuti.");
    }
}
