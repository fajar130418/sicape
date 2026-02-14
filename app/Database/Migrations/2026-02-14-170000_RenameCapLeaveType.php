<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RenameCapLeaveType extends Migration
{
    public function up()
    {
        $this->db->query("UPDATE leave_types SET name = 'Cuti Alasan Penting' WHERE name = 'Cuti Karena Alasan Penting'");
    }

    public function down()
    {
        $this->db->query("UPDATE leave_types SET name = 'Cuti Karena Alasan Penting' WHERE name = 'Cuti Alasan Penting'");
    }
}
