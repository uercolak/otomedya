<?php

namespace App\Models;

use CodeIgniter\Model;

class SocialAccountModel extends Model
{
    protected $table            = 'social_accounts';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useTimestamps    = true;

    protected $allowedFields = [
        'user_id',
        'platform',
        'external_id',
        'meta_page_id',
        'access_token',
        'token_expires_at',
        'name',
        'username',
        'avatar_url',
    ];
}
