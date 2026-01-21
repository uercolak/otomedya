<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\MediaModel;

class MediaController extends BaseController
{
    public function show(int $id)
    {
        $media = (new MediaModel())->find($id);
        if (!$media) {
            return $this->response->setStatusCode(404)->setBody('Not found');
        }

        $relPath = ltrim((string)($media['file_path'] ?? ''), '/');
        if ($relPath === '') {
            return $this->response->setStatusCode(404)->setBody('Not found');
        }

        $fullPath = ROOTPATH . 'public/' . $relPath;
        if (!is_file($fullPath)) {
            return $this->response->setStatusCode(404)->setBody('File not found');
        }

        $mime = (string)($media['mime_type'] ?? '');
        if ($mime === '') {
            $mime = mime_content_type($fullPath) ?: 'application/octet-stream';
        }

        return $this->response
            ->setHeader('Content-Type', $mime)
            ->setHeader('Cache-Control', 'public, max-age=86400')
            ->setBody(file_get_contents($fullPath));
    }
}
