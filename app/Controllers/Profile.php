<?php

namespace App\Controllers;

use App\Models\UserModel;

class Profile extends BaseController
{
    public function index()
    {
        $userId = session()->get('id');
        if (!$userId) {
            return redirect()->to('/login');
        }

        $userModel = new UserModel();
        $user = $userModel->find($userId);

        if (!$user) {
            return redirect()->to('/login')->with('error', 'User not found');
        }

        $data = [
            'user' => $user
        ];

        return view('profile/index', $data);
    }

    public function update()
    {
        $userId = session()->get('id');
        if (!$userId) {
            return redirect()->to('/login');
        }

        $userModel = new UserModel();
        $user = $userModel->find($userId);

        if (!$user) {
            return redirect()->back()->with('error', 'User not found');
        }

        $rules = [
            'name' => 'permit_empty|min_length[3]|max_length[255]',
            'email' => 'permit_empty|valid_email',
            'phone' => 'permit_empty|min_length[10]|max_length[20]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $input = $this->request->getPost();

        // FIELD TERBATAS YANG HANYA BOLEH DIUBAH OLEH USER
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

        // Opsional Update Password
        if (!empty($input['password'])) {
            if (strlen($input['password']) < 6) {
                return redirect()->back()->withInput()->with('error', 'Password minimal 6 karakter');
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

        // Handle Photo Upload Opsional jika ingin didukung dari web
        $file = $this->request->getFile('photo');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move(FCPATH . 'uploads/photos', $newName);

            // Delete old photo
            if ($user['photo'] && file_exists(FCPATH . 'uploads/photos/' . $user['photo'])) {
                unlink(FCPATH . 'uploads/photos/' . $user['photo']);
            }
            $updateData['photo'] = $newName;
            session()->set('photo', $newName);
        }

        if (empty($updateData)) {
            return redirect()->back()->with('info', 'Tidak ada data profil yang berubah.');
        }

        if ($userModel->update($userId, $updateData)) {
            // Update session data
            if (isset($updateData['name'])) {
                session()->set('name', $updateData['name']);
            }
            return redirect()->to('/profile')->with('success', 'Profil berhasil diperbarui.');
        } else {
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui profil.');
        }
    }
}
