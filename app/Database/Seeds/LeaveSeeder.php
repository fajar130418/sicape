<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class LeaveSeeder extends Seeder
{
    public function run()
    {
        $leaveTypes = [
            [
                'name' => 'Cuti Tahunan',
                'min_duration' => 1,
                'max_duration' => 12,
                'requires_file' => 0,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Cuti Besar',
                'min_duration' => 1,
                'max_duration' => 90, // 3 months
                'requires_file' => 0,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Cuti Sakit',
                'min_duration' => 1,
                'max_duration' => 365,
                'requires_file' => 1, // Requires doctor's note
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Cuti Melahirkan',
                'min_duration' => 90,
                'max_duration' => 90,
                'requires_file' => 0,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Cuti Karena Alasan Penting',
                'min_duration' => 1,
                'max_duration' => 30, // 1 month
                'requires_file' => 0, // Usually needs proof but keeping 0 for basic flow
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Cuti di Luar Tanggungan Negara',
                'min_duration' => 1,
                'max_duration' => 1095, // 3 years
                'requires_file' => 0,
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ];

        // Using Query Builder
        $this->db->table('leave_types')->insertBatch($leaveTypes);

        // Admin User
        $data = [
            'nip' => '199001012020011001',
            'name' => 'Administrator',
            'password' => password_hash('admin123', PASSWORD_DEFAULT),
            'role' => 'admin',
            'join_date' => '2010-01-01',
            'created_at' => date('Y-m-d H:i:s'),
        ];
        $this->db->table('users')->insert($data);
        
        // Employee User
        $data = [
            'nip' => '199505052022031002',
            'name' => 'Pegawai Contoh',
            'password' => password_hash('user123', PASSWORD_DEFAULT),
            'role' => 'pegawai',
            'join_date' => '2022-03-01',
            'created_at' => date('Y-m-d H:i:s'),
        ];
        $this->db->table('users')->insert($data);
    }
}
