<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\MediaModel;

class MediaController extends BaseController
{
    public function show(int $id)
        {
            $media = (new \App\Models\MediaModel())->find($id);
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

            $size  = filesize($fullPath) ?: 0;
            $mtime = filemtime($fullPath) ?: time();

            $etag         = '"' . sha1($fullPath . '|' . $size . '|' . $mtime) . '"';
            $lastModified = gmdate('D, d M Y H:i:s', $mtime) . ' GMT';

            $this->response
                ->setHeader('Content-Type', $mime)
                ->setHeader('Accept-Ranges', 'bytes')
                ->setHeader('Cache-Control', 'public, max-age=31536000, immutable')
                ->setHeader('ETag', $etag)
                ->setHeader('Last-Modified', $lastModified)
                ->setHeader('Access-Control-Allow-Origin', '*')
                ->setHeader('X-Content-Type-Options', 'nosniff');

            // ETag / If-Modified-Since
            $ifNoneMatch     = (string)($this->request->getHeaderLine('If-None-Match') ?? '');
            $ifModifiedSince = (string)($this->request->getHeaderLine('If-Modified-Since') ?? '');

            if ($ifNoneMatch !== '' && trim($ifNoneMatch) === $etag) {
                return $this->response->setStatusCode(304);
            }
            if ($ifModifiedSince !== '') {
                $sinceTs = strtotime($ifModifiedSince);
                if ($sinceTs !== false && $sinceTs >= $mtime) {
                    return $this->response->setStatusCode(304);
                }
            }

            // ✅ Range support (video için şart)
            $range = $this->request->getHeaderLine('Range');
            if ($range && preg_match('/bytes=(\d+)-(\d*)/', $range, $m)) {
                $start = (int)$m[1];
                $end   = ($m[2] !== '') ? (int)$m[2] : ($size - 1);

                if ($start > $end || $start >= $size) {
                    return $this->response
                        ->setStatusCode(416)
                        ->setHeader('Content-Range', "bytes */{$size}");
                }

                $length = ($end - $start) + 1;

                $fh = fopen($fullPath, 'rb');
                if (!$fh) {
                    return $this->response->setStatusCode(500)->setBody('Read error');
                }

                fseek($fh, $start);

                $this->response
                    ->setStatusCode(206)
                    ->setHeader('Content-Length', (string)$length)
                    ->setHeader('Content-Range', "bytes {$start}-{$end}/{$size}");

                // chunked read
                $chunkSize = 1024 * 256; // 256KB
                $out = '';
                $remaining = $length;

                while ($remaining > 0 && !feof($fh)) {
                    $read = fread($fh, min($chunkSize, $remaining));
                    if ($read === false) break;
                    $out .= $read;
                    $remaining -= strlen($read);
                }
                fclose($fh);

                return $this->response->setBody($out);
            }

            // normal full response
            $this->response->setHeader('Content-Length', (string)$size);

            $bin = @file_get_contents($fullPath);
            if ($bin === false) {
                return $this->response->setStatusCode(500)->setBody('Read error');
            }

            return $this->response->setBody($bin);
        }
}
