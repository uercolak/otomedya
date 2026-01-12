<?php

namespace App\Controllers;

use App\Models\ContentModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class MediaController extends BaseController
{
    public function show(int $id)
    {
        // 1️⃣ Content kaydını al
        $contentModel = new ContentModel();
        $content = $contentModel->find($id);

        if (!$content || empty($content['media_path'])) {
            throw PageNotFoundException::forPageNotFound();
        }

        // 2️⃣ Dosya yolu
        $relativePath = $content['media_path']; 
        $absolutePath = FCPATH . $relativePath;

        if (!is_file($absolutePath)) {
            throw PageNotFoundException::forPageNotFound();
        }

        // 3️⃣ MIME type
        $mimeType = mime_content_type($absolutePath);
        $fileSize = filesize($absolutePath);

        // 4️⃣ Header'lar (Meta uyumlu)
        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . $fileSize);
        header('Cache-Control: public, max-age=31536000');
        header('Accept-Ranges: bytes');

        // 5️⃣ Dosyayı stream et
        readfile($absolutePath);
        exit;
    }
}
