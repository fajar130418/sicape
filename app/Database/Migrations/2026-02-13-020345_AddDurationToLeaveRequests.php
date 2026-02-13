<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDurationToLeaveRequests extends Migration
{
    public function up()
    {
        $this->forge->addColumn('leave_requests', [
            'duration' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
                'after' => 'end_date'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('leave_requests', 'duration');
    }
}
