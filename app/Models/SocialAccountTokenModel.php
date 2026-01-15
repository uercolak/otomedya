<?php

namespace App\Models;

use CodeIgniter\Model;

class SocialAccountTokenModel extends Model
{
    protected $table         = 'social_account_tokens';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'social_account_id',
        'provider',
        'access_token',
        'refresh_token',
        'token_type',
        'expires_at',
        'scope',
        'meta_json',
    ];
}
