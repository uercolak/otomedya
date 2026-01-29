<?php

namespace App\Models;

use CodeIgniter\Model;

class TemplateCollectionModel extends Model
{
    protected $table            = 'template_collections';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useTimestamps    = false;

    protected $allowedFields = [
        'name',
        'slug',
        'description',
        'sort_order',
        'is_active',
        'created_at',
        'updated_at',
    ];
}
