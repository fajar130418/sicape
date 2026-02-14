<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\LeaveRequestModel;
use App\Models\UserModel;
use App\Models\LeaveTypeModel;

class Report extends BaseController
{
    protected $leaveRequestModel;
    protected $userModel;
    protected $leaveTypeModel;

    public function __construct()
    {
        $this->leaveRequestModel = new LeaveRequestModel();
        $this->userModel = new UserModel();
        $this->leaveTypeModel = new LeaveTypeModel();
    }

    public function index()
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/dashboard');
        }

        $data = [
            'title' => 'Laporan Cuti'
        ];

        return view('report/index', $data);
    }

    public function recap()
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/dashboard');
        }

        $year = $this->request->getVar('year') ?? date('Y');

        // Fetch all users with their leave counts for the year
        $users = $this->userModel->orderBy('name', 'ASC')->findAll();

        foreach ($users as &$user) {
            $user['recap'] = $this->leaveRequestModel->select('leave_types.name, SUM(duration) as total_days')
                ->join('leave_types', 'leave_types.id = leave_requests.leave_type_id')
                ->where('user_id', $user['id'])
                ->where('status', 'approved')
                ->where("YEAR(start_date) = $year")
                ->groupBy('leave_type_id')
                ->findAll();
        }

        $data = [
            'title' => 'Rekapitulasi Cuti Tahunan',
            'users' => $users,
            'year' => $year,
            'leave_types' => $this->leaveTypeModel->findAll()
        ];

        return view('report/recap', $data);
    }

    public function details()
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/dashboard');
        }

        $start_date = $this->request->getVar('start_date');
        $end_date = $this->request->getVar('end_date');
        $status = $this->request->getVar('status');
        $type = $this->request->getVar('type');

        $query = $this->leaveRequestModel->select('leave_requests.*, users.name as user_name, users.nip, leave_types.name as type_name')
            ->join('users', 'users.id = leave_requests.user_id')
            ->join('leave_types', 'leave_types.id = leave_requests.leave_type_id');

        if ($start_date)
            $query->where('start_date >=', $start_date);
        if ($end_date)
            $query->where('end_date <=', $end_date);
        if ($status)
            $query->where('status', $status);
        if ($type)
            $query->where('leave_type_id', $type);

        $requests = $query->orderBy('created_at', 'DESC')->findAll();

        $data = [
            'title' => 'Riwayat Detail Pengajuan Cuti',
            'requests' => $requests,
            'leave_types' => $this->leaveTypeModel->findAll(),
            'filters' => [
                'start_date' => $start_date,
                'end_date' => $end_date,
                'status' => $status,
                'type' => $type
            ]
        ];

        return view('report/details', $data);
    }

    public function quota()
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/dashboard');
        }

        $users = $this->userModel->orderBy('name', 'ASC')->findAll();

        $data = [
            'title' => 'Status Sisa Jatah Cuti Pegawai',
            'users' => $users
        ];

        return view('report/quota', $data);
    }
}
