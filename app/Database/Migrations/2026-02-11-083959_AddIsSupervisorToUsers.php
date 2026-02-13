<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIsSupervisorToUsers extends Migration
{
    public function up()
    {
        $fields = [
            'is_supervisor' => ['type' => 'BOOLEAN', 'default' => 0, 'after' => 'photo'],
        ];
        $this->forge->addColumn('users', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'is_supervisor');
    }
}
