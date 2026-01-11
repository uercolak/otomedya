<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSocialAccountsTables extends Migration
{
    public function up()
    {
        // social_accounts
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'platform' => [
                'type'       => 'VARCHAR',
                'constraint' => 50, // facebook_page, instagram_business, youtube_channel, tiktok
            ],
            'external_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 191,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'username' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'avatar_url' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
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
        $this->forge->createTable('social_accounts');

        // social_tokens
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'social_account_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'access_token' => [
                'type' => 'TEXT', // şifrelenmiş olarak tutulacak
            ],
            'refresh_token' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'expires_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'scopes' => [
                'type' => 'TEXT',
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
        $this->forge->addForeignKey('social_account_id', 'social_accounts', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('social_tokens');
    }

    public function down()
    {
        $this->forge->dropTable('social_tokens');
        $this->forge->dropTable('social_accounts');
    }
}
