<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddInitialLeaveBalancesToUsers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'leave_balance_n' => [
                'type' => 'INT',
                'constraint' => 2,
                'default' => 12,
                'null' => false,
                'after' => 'join_date'
            ],
            'leave_balance_n1' => [
                'type' => 'INT',
                'constraint' => 2,
                'default' => 0,
                'null' => false,
                'after' => 'leave_balance_n'
            ],
            'leave_balance_n2' => [
                'type' => 'INT',
                'constraint' => 2,
                'default' => 0,
                'null' => false,
                'after' => 'leave_balance_n1'
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', ['leave_balance_n', 'leave_balance_n1', 'leave_balance_n2']);
    }
}
