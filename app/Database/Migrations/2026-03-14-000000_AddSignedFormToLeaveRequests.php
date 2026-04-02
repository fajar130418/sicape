<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSignedFormToLeaveRequests extends Migration
{
    public function up()
    {
        $fields = [
            'signed_form' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
                'after'      => 'attachment'
            ],
        ];
        $this->forge->addColumn('leave_requests', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('leave_requests', 'signed_form');
    }
}
