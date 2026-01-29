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
    'platform_type',
    'type',
    'platform_scope',
    'format_key',
    'width',
    'height',
    'base_media_id',
    'thumb_path',
    'collection_id',
    'is_featured',
    'is_active',
    'created_at',
    'updated_at',
    ];
}
