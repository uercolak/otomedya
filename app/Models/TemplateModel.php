<?php

namespace App\Models;

use CodeIgniter\Model;

class TemplateModel extends Model
{
    protected $table            = 'templates';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useTimestamps    = false;

    protected $allowedFields = [
    'name',
    'description',
    'collection_id',  
    'platform_type',
    'type',
    'platform_scope',
    'format_key',
    'width',
    'height',
    'base_media_id',
    'thumb_path',
    'is_active',
    'is_featured',     
    'created_at',
    'updated_at',
    ];
}
