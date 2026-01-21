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

        $fullPath = rtrim(FCPATH, '/\\') . DIRECTORY_SEPARATOR . $relPath;
        if (!is_file($fullPath)) {
            return $this->response->setStatusCode(404)->setBody('File not found');
        }

        $mime = (string)($media['mime_type'] ?? '');
        if ($mime === '') {
            $mime = mime_content_type($fullPath) ?: 'application/octet-stream';
        }

        $size = filesize($fullPath) ?: 0;
        $mtime = filemtime($fullPath) ?: time();

        $etag = '"' . sha1($fullPath . '|' . $size . '|' . $mtime) . '"';
        $lastModified = gmdate('D, d M Y H:i:s', $mtime) . ' GMT';

        $ifNoneMatch = (string)($this->request->getHeaderLine('If-None-Match') ?? '');
        $ifModifiedSince = (string)($this->request->getHeaderLine('If-Modified-Since') ?? '');

        if ($ifNoneMatch !== '' && trim($ifNoneMatch) === $etag) {
            return $this->response
                ->setStatusCode(304)
                ->setHeader('ETag', $etag)
                ->setHeader('Last-Modified', $lastModified)
                ->setHeader('Cache-Control', 'public, max-age=31536000, immutable')
                ->setHeader('Access-Control-Allow-Origin', '*');
        }

        if ($ifModifiedSince !== '') {
            $sinceTs = strtotime($ifModifiedSince);
            if ($sinceTs !== false && $sinceTs >= $mtime) {
                return $this->response
                    ->setStatusCode(304)
                    ->setHeader('ETag', $etag)
                    ->setHeader('Last-Modified', $lastModified)
                    ->setHeader('Cache-Control', 'public, max-age=31536000, immutable')
                    ->setHeader('Access-Control-Allow-Origin', '*');
            }
        }

        $this->response
            ->setHeader('Content-Type', $mime)
            ->setHeader('Content-Length', (string)$size)
            ->setHeader('Accept-Ranges', 'bytes')
            ->setHeader('Cache-Control', 'public, max-age=31536000, immutable')
            ->setHeader('ETag', $etag)
            ->setHeader('Last-Modified', $lastModified)
            ->setHeader('Access-Control-Allow-Origin', '*');

        $this->response->setHeader('X-Content-Type-Options', 'nosniff');

        $bin = @file_get_contents($fullPath);
        if ($bin === false) {
            return $this->response->setStatusCode(500)->setBody('Read error');
        }

        return $this->response->setBody($bin);
    }
}
