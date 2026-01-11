<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMediaAndTemplatesTables extends Migration
{
    public function up()
    {
        // media
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'type' => [
                'type'       => 'VARCHAR',
                'constraint' => 20, // image | video
            ],
            'file_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
            ],
            'mime_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'width' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'height' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'duration' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true, // saniye
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
        $this->forge->addForeignKey('user_id', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('media');

        // templates
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'platform_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50, // story, post, reels_cover vs.
                'null'       => true,
            ],
            'base_media_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
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
        $this->forge->addForeignKey('base_media_id', 'media', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('templates');
    }

    public function down()
    {
        $this->forge->dropTable('templates');
        $this->forge->dropTable('media');
    }
}
