<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCutiKhususType extends Migration
{
    public function up()
    {
        $data = [
            'name' => 'Cuti Khusus',
            'min_duration' => 1,
            'max_duration' => 365,
            'requires_file' => 1
        ];
        $this->db->table('leave_types')->insert($data);
    }

    public function down()
    {
        $this->db->table('leave_types')->where('name', 'Cuti Khusus')->delete();
    }
}
