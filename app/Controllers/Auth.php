<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class Auth extends BaseController
{
    public function index()
    {
        if (session()->get('logged_in')) {
            return redirect()->to(session()->get('role') === 'admin' ? '/admin' : '/dashboard');
        }
        return view('auth/login');
    }

    public function login()
    {
        $session = session();
        $model = new \App\Models\UserModel();
        $nip = $this->request->getVar('nip');
        $password = $this->request->getVar('password');

        $data = $model->where('nip', $nip)->first();

        if ($data) {
            $pass = $data['password'];
            $verify_pass = password_verify($password, $pass);
            if ($verify_pass) {
                $ses_data = [
                    'id'       => $data['id'],
                    'nip'      => $data['nip'],
                    'name'     => $data['name'],
                    'role'     => $data['role'],
                    'join_date'=> $data['join_date'],
                    'logged_in'     => TRUE
                ];
                $session->set($ses_data);
                return redirect()->to($data['role'] === 'admin' ? '/admin' : '/dashboard');
            } else {
                $session->setFlashdata('msg', 'Password Salah');
                return redirect()->to('/login');
            }
        } else {
            $session->setFlashdata('msg', 'NIP Tidak Ditemukan');
            return redirect()->to('/login');
        }
    }

    public function logout()
    {
        $session = session();
        $session->destroy();
        return redirect()->to('/login');
    }
}
