<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddApprovalColumnsToLeaveRequests extends Migration
{
    public function up()
    {
        $fields = [
            'supervisor_id'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true, 'after' => 'attachment'],
            'supervisor_status'  => ['type' => 'ENUM', 'constraint' => ['pending', 'approved', 'rejected', 'changed', 'deferred'], 'default' => 'pending', 'after' => 'supervisor_id'],
            'supervisor_note'    => ['type' => 'TEXT', 'null' => true, 'after' => 'supervisor_status'],
            'head_id'            => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true, 'after' => 'supervisor_note'],
            'head_status'        => ['type' => 'ENUM', 'constraint' => ['pending', 'approved', 'rejected', 'changed', 'deferred'], 'default' => 'pending', 'after' => 'head_id'],
            'head_note'          => ['type' => 'TEXT', 'null' => true, 'after' => 'head_status'],
        ];
        $this->forge->addColumn('leave_requests', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('leave_requests', ['supervisor_id', 'supervisor_status', 'supervisor_note', 'head_id', 'head_status', 'head_note']);
    }
}
