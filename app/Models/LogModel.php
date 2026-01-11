<?php

namespace App\Models;

use CodeIgniter\Model;

class LogModel extends Model
{
    protected $table            = 'logs';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields    = [
        'level','channel','message','context_json',
        'user_id','ip','user_agent','created_at'
    ];

    protected $useTimestamps    = false;
}
