<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddNikAndPhotoToUsers extends Migration
{
    public function up()
    {
        $fields = [
            'nik'   => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true, 'after' => 'nip'],
            'photo' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'after' => 'signature'],
        ];
        $this->forge->addColumn('users', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('users', ['nik', 'photo']);
    }
}
