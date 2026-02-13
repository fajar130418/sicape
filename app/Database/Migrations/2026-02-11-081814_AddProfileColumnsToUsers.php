<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddProfileColumnsToUsers extends Migration
{
    public function up()
    {
        $fields = [
            'front_title' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true, 'after' => 'name'],
            'back_title'  => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true, 'after' => 'front_title'],
            'pob'         => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true, 'after' => 'back_title'],
            'dob'         => ['type' => 'DATE', 'null' => true, 'after' => 'pob'],
            'gender'      => ['type' => 'ENUM', 'constraint' => ['L', 'P'], 'null' => true, 'after' => 'dob'],
            'rank'        => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true, 'after' => 'gender'],
            'address'     => ['type' => 'TEXT', 'null' => true, 'after' => 'rank'],
            'education'   => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true, 'after' => 'address'],
        ];
        $this->forge->addColumn('users', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('users', ['front_title', 'back_title', 'pob', 'dob', 'gender', 'rank', 'address', 'education']);
    }
}
