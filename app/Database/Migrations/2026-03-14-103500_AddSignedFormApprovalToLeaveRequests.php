<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSignedFormApprovalToLeaveRequests extends Migration
{
    public function up()
    {
        $fields = [
            'signed_form_status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending_upload', 'pending_approval', 'approved', 'rejected'],
                'default'    => 'pending_upload',
                'after'      => 'signed_form'
            ],
            'signed_form_note' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'signed_form_status'
            ],
        ];
        $this->forge->addColumn('leave_requests', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('leave_requests', ['signed_form_status', 'signed_form_note']);
    }
}
