<?php

namespace App\Controllers;

use App\Models\ContentModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class MediaController extends BaseController
{
    public function show(int $id)
    {
        $contentModel = new ContentModel();
        $content = $contentModel->find($id);

        if (!$content || empty($content['media_path'])) {
            throw PageNotFoundException::forPageNotFound();
        }

        $relativePath = (string)$content['media_path'];
        $absolutePath = FCPATH . $relativePath;

        if (!is_file($absolutePath)) {
            throw PageNotFoundException::forPageNotFound();
        }

        $mimeType = mime_content_type($absolutePath) ?: 'application/octet-stream';
        $fileSize = (int) filesize($absolutePath);

        // CI Response üzerinden header bas
        $this->response
            ->setHeader('Content-Type', $mimeType)
            ->setHeader('Content-Length', (string)$fileSize)
            ->setHeader('Cache-Control', 'public, max-age=31536000')
            ->setHeader('Accept-Ranges', 'bytes');

        // ✅ HEAD request: body göndermeden çık (Meta için kritik)
        if ($this->request->getMethod(true) === 'HEAD') {
            return $this->response->setStatusCode(200);
        }

        // GET: dosyayı stream et
        return $this->response->setBody(file_get_contents($absolutePath));
    }
}
