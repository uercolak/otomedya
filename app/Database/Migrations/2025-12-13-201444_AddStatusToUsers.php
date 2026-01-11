<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddStatusToUsers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'active',
                'after'      => 'role',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'status');
    }
}
