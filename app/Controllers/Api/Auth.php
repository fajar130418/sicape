<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use App\Models\UserModel;
use App\Libraries\JwtLibrary;

class Auth extends BaseController
{
    use ResponseTrait;

    public function login()
    {
        $model = new UserModel();
        $nip = $this->request->getVar('nip');
        $password = $this->request->getVar('password');

        if (!$nip || !$password) {
            return $this->fail('NIP and Password are required', 400);
        }

        $user = $model->where('nip', $nip)->first();

        if (!$user) {
            return $this->failNotFound('NIP not found');
        }

        if (!password_verify($password, $user['password'])) {
            return $this->fail('Wrong Password', 401);
        }

        $jwtLib = new JwtLibrary();
        $token = $jwtLib->generateToken($user);

        return $this->respond([
            'status' => 200,
            'message' => 'Login Successful',
            'token' => $token,
            'user' => [
                'id' => $user['id'],
                'nip' => $user['nip'],
                'name' => $user['name'],
                'role' => $user['role'],
                'photo' => $user['photo'] ? base_url('uploads/photos/' . $user['photo']) : null
            ]
        ]);
    }

    public function logout()
    {
        // Client-side just needs to delete the token. 
        // We can return a success message here.
        return $this->respond([
            'status' => 200,
            'message' => 'Logout Successful'
        ]);
    }
}
