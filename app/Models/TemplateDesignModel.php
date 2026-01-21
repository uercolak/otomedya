<?php

namespace App\Models;

use CodeIgniter\Model;

class TemplateDesignModel extends Model
{
    protected $table         = 'template_designs';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'user_id',
        'template_id',
        'editor_type',
        'format_key',
        'canvas_width',
        'canvas_height',
        'state_json',
        'export_file_path',
        'thumb_path',
        'content_id',
        'created_at',
        'updated_at',
    ];
}
