<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use App\Models\UserModel;

class Profile extends BaseController
{
    use ResponseTrait;

    public function index()
    {
        $userData = $this->request->userData;
        if (!$userData) {
            return $this->failUnauthorized('Invalid token data');
        }

        $userModel = new UserModel();
        $user = $userModel->find($userData['id']);

        if (!$user) {
            return $this->failNotFound('User not found');
        }

        // Hapus password hash dari response untuk keamanan
        unset($user['password']);

        // Return user profile data
        return $this->respond([
            'status' => 200,
            'data' => $user
        ]);
    }

    public function update()
    {
        $userData = $this->request->userData;
        if (!$userData) {
            return $this->failUnauthorized('Invalid token data');
        }

        $userModel = new UserModel();
        $user = $userModel->find($userData['id']);

        if (!$user) {
            return $this->failNotFound('User not found');
        }

        $rules = [
            'name' => 'permit_empty|min_length[3]|max_length[255]',
            'email' => 'permit_empty|valid_email',
            'phone' => 'permit_empty|min_length[10]|max_length[20]',
        ];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        // Ambil payload JSON atau POST form data
        $input = $this->request->getJSON(true) ?? $this->request->getPost();

        // FIELD TERBATAS YANG HANYA BOLEH DIUBAH OLEH USER
        // Field krusial seperti role, leave_balance_n, supervisor_id, is_supervisor dll akan otomatis terabaikan
        $allowedFields = [
            'name',
            'email',
            'phone',
            'address',
            'pob',
            'dob',
            'gender',
            'education',
            'nik',
            'front_title',
            'back_title',
            'position',
            'unit',
            'rank',
            'user_type'
        ];

        // Opsional Update Password jika diisi oleh user
        if (isset($input['password']) && !empty($input['password'])) {
            if (strlen($input['password']) < 6) {
                return $this->failValidationErrors(['password' => 'Password minimal 6 karakter.']);
            }
            $input['password'] = password_hash($input['password'], PASSWORD_DEFAULT);
            $allowedFields[] = 'password';
        }

        $updateData = [];
        foreach ($allowedFields as $field) {
            if (isset($input[$field])) {
                $updateData[$field] = $input[$field];
            }
        }

        if (empty($updateData)) {
            return $this->respond([
                'status' => 200,
                'message' => 'No changes made.'
            ]);
        }

        if ($userModel->update($userData['id'], $updateData)) {
            return $this->respond([
                'status' => 200,
                'message' => 'Profile updated successfully',
            ]);
        } else {
            return $this->fail('Failed to update profile');
        }
    }

    public function updateFcmToken()
    {
        $userData = $this->request->userData;
        if (!$userData) {
            return $this->failUnauthorized('Invalid token data');
        }

        $input = $this->request->getJSON(true) ?? $this->request->getPost();
        if (empty($input['fcm_token'])) {
            return $this->failValidationErrors(['fcm_token' => 'Token required']);
        }

        $userModel = new UserModel();
        
        // Remove token from other users if it exists to avoid conflicts
        $db = \Config\Database::connect();
        $db->table('users')->where('fcm_token', $input['fcm_token'])->update(['fcm_token' => null]);
        
        // Update to current user
        if ($userModel->update($userData['id'], ['fcm_token' => $input['fcm_token']])) {
            return $this->respond([
                'status' => 200,
                'message' => 'FCM Token updated successfully',
            ]);
        }

        return $this->fail('Failed to update token');
    }
}
