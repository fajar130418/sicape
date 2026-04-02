<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIsBypassedToLeaveRequests extends Migration
{
    public function up()
    {
        $fields = [
            'is_bypassed' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'after'      => 'signed_form'
            ],
        ];
        $this->forge->addColumn('leave_requests', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('leave_requests', 'is_bypassed');
    }
}
