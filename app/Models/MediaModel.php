<?php

namespace App\Models;

use CodeIgniter\Model;

class MediaModel extends Model
{
    protected $table            = 'media';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useTimestamps    = false; 

    protected $allowedFields = [
        'user_id',
        'type',
        'file_path',
        'mime_type',
        'width',
        'height',
        'duration',
        'created_at',
        'updated_at',
    ];
}
