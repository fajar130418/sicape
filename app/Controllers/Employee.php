<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Employee extends BaseController
{
    public function index()
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/dashboard');
        }

        $userModel = new \App\Models\UserModel();
        $data = [
            'title' => 'Manajemen Pegawai',
            'employees' => $userModel->where('role', 'pegawai')->findAll(),
            'potential_supervisors' => $userModel->groupStart()
                ->where('role', 'admin')
                ->orWhere('is_supervisor', 1)
                ->groupEnd()
                ->findAll()
        ];

        foreach ($data['employees'] as &$emp) {
            $emp['remaining_leave'] = $userModel->getRemainingLeave($emp['id']);
        }

        return view('employee/index', $data);
    }

    public function create()
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/dashboard');
        }

        $userModel = new \App\Models\UserModel();
        $data = [
            'title' => 'Tambah Pegawai',
            'potential_supervisors' => $userModel->groupStart()
                ->where('role', 'admin')
                ->orWhere('is_supervisor', 1)
                ->groupEnd()
                ->findAll()
        ];
        return view('employee/create', $data);
    }

    public function store()
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/dashboard');
        }

        $rules = [
            'nip' => 'required|is_unique[users.nip]',
            'nik' => 'required|is_unique[users.nik]',
            'name' => 'required|min_length[3]',
            'password' => 'required|min_length[6]',
            'join_date' => 'required|valid_date',
            'pob' => 'required',
            'dob' => 'required|valid_date',
            'gender' => 'required',
            'rank' => 'required',
            'education' => 'required',
            'photo' => 'permit_empty|max_size[photo,2048]|is_image[photo]|mime_in[photo,image/jpg,image/jpeg,image/png]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Handle Photo Upload
        $filePhoto = $this->request->getFile('photo');
        $photoName = null;
        if ($filePhoto && $filePhoto->isValid() && !$filePhoto->hasMoved()) {
            $photoName = $filePhoto->getRandomName();
            $filePhoto->move('uploads/photos', $photoName);
        }

        $userModel = new \App\Models\UserModel();
        $data = [
            'nip' => $this->request->getVar('nip'),
            'nik' => $this->request->getVar('nik'),
            'name' => $this->request->getVar('name'),
            'front_title' => $this->request->getVar('front_title'),
            'back_title' => $this->request->getVar('back_title'),
            'pob' => $this->request->getVar('pob'),
            'dob' => $this->request->getVar('dob'),
            'gender' => $this->request->getVar('gender'),
            'rank' => $this->request->getVar('rank'),
            'education' => $this->request->getVar('education'),
            'address' => $this->request->getVar('address'),
            'password' => password_hash($this->request->getVar('password'), PASSWORD_DEFAULT),
            'role' => $this->request->getVar('role') ?: 'pegawai',
            'user_type' => $this->request->getVar('user_type') ?: 'PNS',
            'contract_end_date' => $this->request->getVar('contract_end_date') ?: null,
            'join_date' => $this->request->getVar('join_date'),
            'position' => $this->request->getVar('position'),
            'unit' => $this->request->getVar('unit'),
            'phone' => $this->request->getVar('phone'),
            'email' => $this->request->getVar('email'),
            'photo' => $photoName,
            'is_supervisor' => $this->request->getVar('is_supervisor') ? 1 : 0,
            'is_head_of_agency' => $this->request->getVar('is_head_of_agency') ? 1 : 0,
            'supervisor_id' => $this->request->getVar('supervisor_id') ? $this->request->getVar('supervisor_id') : null,
            'leave_balance_n' => $this->request->getVar('leave_balance_n') ?: 12,
            'leave_balance_n1' => $this->request->getVar('leave_balance_n1') ?: 0,
            'leave_balance_n2' => $this->request->getVar('leave_balance_n2') ?: 0,
            'mkg_additional_years' => $this->request->getVar('mkg_additional_years') ?: 0,
            'mkg_additional_months' => $this->request->getVar('mkg_additional_months') ?: 0,
            'mkg_adjustment_years' => $this->request->getVar('mkg_adjustment_years') ?: 0,
            'mkg_adjustment_months' => $this->request->getVar('mkg_adjustment_months') ?: 0,
        ];

        $userModel->save($data);
        return redirect()->to('/employee')->with('success', 'Pegawai berhasil ditambahkan.');
    }

    public function edit($id)
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/dashboard');
        }

        $userModel = new \App\Models\UserModel();
        $employee = $userModel->find($id);

        if (!$employee) {
            return redirect()->to('/employee')->with('error', 'Pegawai tidak ditemukan.');
        }

        $data = [
            'title' => 'Edit Pegawai',
            'employee' => $employee,
            'potential_supervisors' => $userModel->where('id !=', $id)
                ->groupStart()
                ->where('role', 'admin')
                ->orWhere('is_supervisor', 1)
                ->groupEnd()
                ->findAll()
        ];

        return view('employee/edit', $data);
    }

    public function update($id)
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/dashboard');
        }

        $userModel = new \App\Models\UserModel();
        $employee = $userModel->find($id);

        if (!$employee) {
            return redirect()->back()->with('error', 'Pegawai tidak ditemukan');
        }

        $rules = [
            'name' => 'required|min_length[3]',
            'join_date' => 'required|valid_date',
            'pob' => 'required',
            'dob' => 'required|valid_date',
            'gender' => 'required',
            'rank' => 'required',
            'education' => 'required',
            'photo' => 'max_size[photo,2048]|is_image[photo]|mime_in[photo,image/jpg,image/jpeg,image/png]',
        ];

        // Only validate NIP/NIK uniqueness if it changed
        if ($employee['nip'] != $this->request->getVar('nip')) {
            $rules['nip'] = 'required|is_unique[users.nip]';
        }
        if ($employee['nik'] != $this->request->getVar('nik')) {
            $rules['nik'] = 'required|is_unique[users.nik]';
        }

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Handle Photo Upload
        $filePhoto = $this->request->getFile('photo');
        $photoName = $employee['photo']; // Default to old photo
        if ($filePhoto && $filePhoto->isValid() && !$filePhoto->hasMoved()) {
            $photoName = $filePhoto->getRandomName();
            $filePhoto->move('uploads/photos', $photoName);
            // Optionally delete old photo here if desired
        }

        $data = [
            'id' => $id,
            'nip' => $this->request->getVar('nip'),
            'nik' => $this->request->getVar('nik'),
            'name' => $this->request->getVar('name'),
            'front_title' => $this->request->getVar('front_title'),
            'back_title' => $this->request->getVar('back_title'),
            'pob' => $this->request->getVar('pob'),
            'dob' => $this->request->getVar('dob'),
            'gender' => $this->request->getVar('gender'),
            'rank' => $this->request->getVar('rank'),
            'education' => $this->request->getVar('education'),
            'address' => $this->request->getVar('address'),
            'role' => $this->request->getVar('role') ?: 'pegawai',
            'user_type' => $this->request->getVar('user_type') ?: 'PNS',
            'contract_end_date' => $this->request->getVar('contract_end_date') ?: null,
            'join_date' => $this->request->getVar('join_date'),
            'position' => $this->request->getVar('position'),
            'unit' => $this->request->getVar('unit'),
            'phone' => $this->request->getVar('phone'),
            'email' => $this->request->getVar('email'),
            'photo' => $photoName,
            'is_supervisor' => $this->request->getVar('is_supervisor') ? 1 : 0,
            'is_head_of_agency' => $this->request->getVar('is_head_of_agency') ? 1 : 0,
            'supervisor_id' => $this->request->getVar('supervisor_id') ? $this->request->getVar('supervisor_id') : null,
            'leave_balance_n' => $this->request->getVar('leave_balance_n') ?: 12,
            'leave_balance_n1' => $this->request->getVar('leave_balance_n1') ?: 0,
            'leave_balance_n2' => $this->request->getVar('leave_balance_n2') ?: 0,
            'mkg_additional_years' => $this->request->getVar('mkg_additional_years') ?: 0,
            'mkg_additional_months' => $this->request->getVar('mkg_additional_months') ?: 0,
            'mkg_adjustment_years' => $this->request->getVar('mkg_adjustment_years') ?: 0,
            'mkg_adjustment_months' => $this->request->getVar('mkg_adjustment_months') ?: 0,
        ];

        // Update password only if provided
        $password = $this->request->getVar('password');
        if (!empty($password)) {
            $data['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        $userModel->save($data);
        return redirect()->to('/employee')->with('success', 'Data pegawai berhasil diperbarui.');
    }

    public function supervisors()
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/dashboard');
        }

        $userModel = new \App\Models\UserModel();
        $data = [
            'title' => 'Data Atasan / Pejabat',
            'supervisors' => $userModel->where('is_supervisor', 1)->findAll()
        ];
        return view('employee/supervisors', $data);
    }

    public function admins()
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/dashboard');
        }

        $userModel = new \App\Models\UserModel();
        $data = [
            'title' => 'Data Administrator',
            'admins' => $userModel->where('role', 'admin')->findAll()
        ];
        return view('employee/admins', $data);
    }

    public function delete($id)
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/dashboard');
        }

        $userModel = new \App\Models\UserModel();
        $userModel->delete($id);

        return redirect()->to('/employee')->with('success', 'Pegawai berhasil dihapus.');
    }

    public function contracts()
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/dashboard');
        }

        $userModel = new \App\Models\UserModel();

        // Only fetch PPPK (PPPK & PPPK Paruh Waktu)
        $pppk = $userModel->where('user_type !=', 'PNS')
            ->orderBy('contract_end_date', 'ASC')
            ->findAll();

        $data = [
            'title' => 'Manajemen Kontrak PPPK',
            'pppk' => $pppk
        ];

        return view('employee/contracts', $data);
    }

    public function massRenew()
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/dashboard');
        }

        $employeeIds = $this->request->getPost('employee_ids');
        $newDate = $this->request->getPost('new_contract_end_date');

        if (empty($employeeIds) || empty($newDate)) {
            return redirect()->back()->with('error', 'Data tidak lengkap.');
        }

        $userModel = new \App\Models\UserModel();
        $userModel->whereIn('id', $employeeIds)
            ->set(['contract_end_date' => $newDate])
            ->update();

        $count = count($employeeIds);
        return redirect()->to('/employee/contracts')->with('success', "Berhasil memperbarui {$count} kontrak pegawai.");
    }

    public function massAssignSupervisor()
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/dashboard');
        }

        $employeeIds = $this->request->getPost('employee_ids');
        $supervisorId = $this->request->getPost('supervisor_id');

        if (empty($employeeIds) || empty($supervisorId)) {
            return redirect()->back()->with('error', 'Data tidak lengkap.');
        }

        $userModel = new \App\Models\UserModel();
        $userModel->whereIn('id', $employeeIds)
            ->set(['supervisor_id' => $supervisorId])
            ->update();

        $count = count($employeeIds);
        return redirect()->to('/employee')->with('success', "Berhasil menetapkan atasan untuk {$count} pegawai.");
    }

    public function hierarchy()
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/dashboard');
        }

        $userModel = new \App\Models\UserModel();

        // Fetch all possible supervisors
        $supervisors = $userModel->groupStart()
            ->where('role', 'admin')
            ->orWhere('is_supervisor', 1)
            ->groupEnd()
            ->findAll();

        $hierarchy = [];
        foreach ($supervisors as $s) {
            $subordinates = $userModel->where('supervisor_id', $s['id'])->findAll();
            if (!empty($subordinates)) {
                $hierarchy[] = [
                    'supervisor' => $s,
                    'subordinates' => $subordinates
                ];
            }
        }

        // Also fetch employees with NO supervisor
        $noSupervisor = $userModel->where('role', 'pegawai')
            ->where('supervisor_id', null)
            ->findAll();

        $data = [
            'title' => 'Manajemen Hirarki Pegawai',
            'hierarchy' => $hierarchy,
            'noSupervisor' => $noSupervisor,
            'potential_supervisors' => $supervisors
        ];

        return view('employee/hierarchy', $data);
    }
}
