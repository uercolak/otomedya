<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateScheduledPostsAndLogsTables extends Migration
{
    public function up()
    {
        // scheduled_posts
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
            'social_account_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'platform' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'scheduled_at' => [
                'type' => 'DATETIME',
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 50, // draft, scheduled, processing, published, failed
                'default'    => 'scheduled',
            ],
            'publish_response' => [
                'type' => 'TEXT',
                'null' => true, // JSON
            ],
            'retry_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'last_attempt_at' => [
                'type' => 'DATETIME',
                'null' => true,
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
        $this->forge->addForeignKey('social_account_id', 'social_accounts', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('scheduled_posts');

        // logs
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'type' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'scheduled_post_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
            ],
            'message' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'context' => [
                'type' => 'TEXT',
                'null' => true, // JSON
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('scheduled_post_id', 'scheduled_posts', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('logs');
    }

    public function down()
    {
        $this->forge->dropTable('logs');
        $this->forge->dropTable('scheduled_posts');
    }
}
