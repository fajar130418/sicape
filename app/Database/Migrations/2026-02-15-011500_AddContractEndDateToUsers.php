<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddContractEndDateToUsers extends Migration
{
    public function up()
    {
        $fields = [
            'contract_end_date' => [
                'type' => 'DATE',
                'null' => true,
                'after' => 'join_date'
            ],
        ];
        $this->forge->addColumn('users', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'contract_end_date');
    }
}
