<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSeniorityAdjustmentsToUsers extends Migration
{
    public function up()
    {
        $fields = [
            'mkg_additional_years' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
                'after' => 'join_date'
            ],
            'mkg_additional_months' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
                'after' => 'mkg_additional_years'
            ],
            'mkg_adjustment_years' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
                'after' => 'mkg_additional_months'
            ],
            'mkg_adjustment_months' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
                'after' => 'mkg_adjustment_years'
            ],
        ];
        $this->forge->addColumn('users', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('users', ['mkg_additional_years', 'mkg_additional_months', 'mkg_adjustment_years', 'mkg_adjustment_months']);
    }
}
