<?php

namespace App\Models;

use CodeIgniter\Model;

class ContentModel extends Model
{
    protected $table            = 'contents';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useTimestamps    = false;

    protected $allowedFields = [
        'user_id',
        'title',
        'base_text',
        'media_type',
        'media_path',
        'media_id',
        'template_id',
        'meta_json',
        'created_at',
        'updated_at',
    ];
}
