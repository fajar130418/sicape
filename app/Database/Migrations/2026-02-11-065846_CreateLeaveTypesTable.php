<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLeaveTypesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'min_duration' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 1,
            ],
            'max_duration' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 12, // Default max for annual leave
            ],
            'requires_file' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('leave_types');
    }

    public function down()
    {
        $this->forge->dropTable('leave_types');
    }
}
