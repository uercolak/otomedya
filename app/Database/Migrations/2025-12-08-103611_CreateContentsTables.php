<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateContentsTables extends Migration
{
    public function up()
    {
        // contents
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
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'base_text' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'media_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
            ],
            'template_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
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
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('media_id', 'media', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('template_id', 'templates', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('contents');

        // content_variants
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'content_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
            ],
            'platform' => [
                'type'       => 'VARCHAR',
                'constraint' => 50, // facebook, instagram, youtube, tiktok
            ],
            'text' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'thumbnail_media_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
            ],
            'extra_meta' => [
                'type' => 'TEXT',
                'null' => true, // JSON
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
        $this->forge->addForeignKey('content_id', 'contents', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('thumbnail_media_id', 'media', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('content_variants');
    }

    public function down()
    {
        $this->forge->dropTable('content_variants');
        $this->forge->dropTable('contents');
    }
}
