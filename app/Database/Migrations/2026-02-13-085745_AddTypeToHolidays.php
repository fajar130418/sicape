<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTypeToHolidays extends Migration
{
    public function up()
    {
        $this->forge->addColumn('holidays', [
            'type' => [
                'type'       => 'ENUM',
                'constraint' => ['Libur Nasional', 'Cuti Bersama'],
                'default'    => 'Libur Nasional',
                'after'      => 'date'
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('holidays', 'type');
    }
}
