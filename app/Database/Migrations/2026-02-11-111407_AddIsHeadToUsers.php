<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIsHeadToUsers extends Migration
{
    public function up()
    {
        $fields = [
            'is_head_of_agency' => [
                'type' => 'BOOLEAN',
                'default' => false,
                'after' => 'is_supervisor'
            ]
        ];
        $this->forge->addColumn('users', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'is_head_of_agency');
    }
}
