<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\HolidayModel;

class Holiday extends BaseController
{
    public function index()
    {
        // Only Admin
        if (session()->get('role') != 'admin') {
            return redirect()->to('/dashboard');
        }

        $model = new HolidayModel();
        $data = [
            'title' => 'Manajemen Hari Libur',
            'holidays' => $model->orderBy('date', 'DESC')->findAll()
        ];

        return view('admin/holidays/index', $data);
    }

    public function store()
    {
        // Only Admin
        if (session()->get('role') != 'admin') {
            return redirect()->to('/dashboard');
        }

        $model = new HolidayModel();
        $rules = [
            'date' => 'required|valid_date|is_unique[holidays.date]',
            'description' => 'required|max_length[255]',
            'type' => 'required|in_list[Libur Nasional,Cuti Bersama]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $model->save([
            'date' => $this->request->getPost('date'),
            'description' => $this->request->getPost('description'),
            'type' => $this->request->getPost('type')
        ]);

        return redirect()->to('/admin/holidays')->with('success', 'Hari libur berhasil ditambahkan.');
    }

    public function delete($id)
    {
        // Only Admin
        if (session()->get('role') != 'admin') {
            return redirect()->to('/dashboard');
        }

        $model = new HolidayModel();
        $model->delete($id);

        return redirect()->to('/admin/holidays')->with('success', 'Hari libur berhasil dihapus.');
    }
}
