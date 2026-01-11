<?php

namespace App\Models;

use CodeIgniter\Model;

class JobAttemptModel extends Model
{
    protected $table        = 'job_attempts';
    protected $primaryKey   = 'id';
    protected $returnType   = 'array';
    protected $useAutoIncrement = true;
    protected $protectFields    = true;

    protected $allowedFields = [
        'job_id','attempt_no','status',
        'started_at','finished_at',
        'error','response_json',
        'created_at'
    ];

    protected $useTimestamps = false;
}
