<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateSchemaForCutiBesar extends Migration
{
    public function up()
    {
        // Add user_type to users table
        $this->forge->addColumn('users', [
            'user_type' => [
                'type' => 'ENUM',
                'constraint' => ['PNS', 'PPPK', 'PPPK Paruh Waktu'],
                'default' => 'PNS',
                'after' => 'role'
            ],
        ]);

        // Add category to leave_requests table
        $this->forge->addColumn('leave_requests', [
            'category' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null' => true,
                'after' => 'reason'
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'user_type');
        $this->forge->dropColumn('leave_requests', 'category');
    }
}
