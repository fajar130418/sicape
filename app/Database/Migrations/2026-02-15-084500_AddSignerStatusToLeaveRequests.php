<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSignerStatusToLeaveRequests extends Migration
{
    public function up()
    {
        $fields = [
            'supervisor_sign_as' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'Definitif',
                'after' => 'supervisor_note'
            ],
            'admin_sign_as' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'Definitif',
                'after' => 'admin_note'
            ],
        ];
        $this->forge->addColumn('leave_requests', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('leave_requests', ['supervisor_sign_as', 'admin_sign_as']);
    }
}
