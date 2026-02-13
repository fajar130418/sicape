<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddHierarchyColumnsToUsers extends Migration
{
    public function up()
    {
        $fields = [
            'position'      => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true, 'after' => 'role'],
            'unit'          => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true, 'after' => 'position'],
            'phone'         => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true, 'after' => 'unit'],
            'supervisor_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true, 'after' => 'phone'],
            'signature'     => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'after' => 'supervisor_id'],
        ];
        $this->forge->addColumn('users', $fields);
        
        // Add foreign key for supervisor
        // Note: CI4 addForeignKey in addColumn context might be tricky, usually done separately or via raw sql iftable exists
        // keeping it simple for now, just the column.
    }

    public function down()
    {
        $this->forge->dropColumn('users', ['position', 'unit', 'phone', 'supervisor_id', 'signature']);
    }
}
