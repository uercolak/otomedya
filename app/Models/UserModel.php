<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';

    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    
    protected $allowedFields = [
    'name',
    'email',
    'password_hash',
    'role',
    'status',
    'tenant_id',
    'created_by',
    'created_at',
    'updated_at',
];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'name'  => 'required|min_length[2]',
        'email' => 'required|valid_email',
        'password' => 'permit_empty|min_length[6]',
    ];
}
