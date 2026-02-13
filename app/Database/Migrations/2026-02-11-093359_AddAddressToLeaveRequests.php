<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAddressToLeaveRequests extends Migration
{
    public function up()
    {
        $fields = [
            'address_during_leave' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'reason'
            ]
        ];
        $this->forge->addColumn('leave_requests', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('leave_requests', 'address_during_leave');
    }
}
