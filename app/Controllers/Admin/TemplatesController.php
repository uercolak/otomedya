<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\TemplateModel;
use App\Models\MediaModel;

class TemplatesController extends BaseController
{
    private array $formats = [
    // Instagram
    'ig_post_1_1'   => ['label' => 'Instagram Post (1:1)',   'w' => 1080, 'h' => 1080],
    'ig_post_4_5'   => ['label' => 'Instagram Post (4:5)',   'w' => 1080, 'h' => 1350],
    'ig_story_9_16' => ['label' => 'Instagram Story (9:16)', 'w' => 1080, 'h' => 1920],
    'ig_reels_9_16' => ['label' => 'Instagram Reels (9:16)', 'w' => 1080, 'h' => 1920],

    // Facebook (pratik)
    'fb_post_1_1'   => ['label' => 'Facebook Post (1:1)',    'w' => 1080, 'h' => 1080],
    'fb_story_9_16' => ['label' => 'Facebook Story (9:16)',  'w' => 1080, 'h' => 1920],

    // TikTok
    'tt_video_9_16' => ['label' => 'TikTok (9:16)',          'w' => 1080, 'h' => 1920],

    // YouTube
    'yt_thumb_16_9' => ['label' => 'YouTube Thumbnail (16:9)','w' => 1280, 'h' => 720],
    'yt_short_9_16' => ['label' => 'YouTube Shorts (9:16)',  'w' => 1080, 'h' => 1920],
    ];

    public function index()
    {
        $db = \Config\Database::connect();

        $q           = trim((string)($this->request->getGet('q') ?? ''));
        $type        = trim((string)($this->request->getGet('type') ?? ''));
        $scope       = trim((string)($this->request->getGet('scope') ?? ''));
        $format      = trim((string)($this->request->getGet('format') ?? ''));
        $active      = trim((string)($this->request->getGet('active') ?? ''));
        $collection  = trim((string)($this->request->getGet('collection') ?? '')); 
        $featured    = trim((string)($this->request->getGet('featured') ?? ''));   

        $builder = $db->table('templates t')
            ->select('t.*')
            ->select('m.file_path as media_file_path, m.mime_type as media_mime_type, m.width as media_width, m.height as media_height')
            ->select('c.name as collection_name, c.slug as collection_slug') 
            ->join('media m', 'm.id = t.base_media_id', 'left')
            ->join('template_collections c', 'c.id = t.collection_id', 'left')
            ->orderBy('t.id', 'DESC');

        if ($q !== '') {
            $builder->groupStart()
                ->like('t.name', $q)
                ->orLike('t.description', $q)
            ->groupEnd();
        }
        if ($type !== '')  $builder->where('t.type', $type);
        if ($scope !== '') $builder->where('t.platform_scope', $scope);
        if ($format !== '') $builder->where('t.format_key', $format);
        if ($active !== '') $builder->where('t.is_active', (int)$active);

        if ($collection !== '') {
            $builder->where('t.collection_id', (int)$collection);
        }
        if ($featured !== '') {
            $builder->where('t.is_featured', (int)$featured);
        }

        $rows = $builder->get()->getResultArray();

        // ✅ Tema listesi (dropdown için)
        $collections = $db->table('template_collections')
            ->select('id, name, slug, is_active, sort_order')
            ->where('is_active', 1)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('name', 'ASC')
            ->get()->getResultArray();

        return view('admin/templates/index', [
            'pageTitle'    => 'Hazır Şablonlar',
            'rows'         => $rows,
            'filters'      => compact('q','type','scope','format','active','collection','featured'),
            'formats'      => $this->formats,
            'collections'  => $collections,
        ]);
    }

    public function create()
    {
        $db = \Config\Database::connect();

        $collections = $db->table('template_collections')
            ->select('id, name, slug, is_active, sort_order')
            ->where('is_active', 1)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('name', 'ASC')
            ->get()->getResultArray();

        return view('admin/templates/create', [
            'pageTitle'    => 'Yeni Şablon Yükle',
            'formats'      => $this->formats,
            'collections'  => $collections,
            'errors'       => session()->getFlashdata('errors') ?? [],
        ]);
    }

    public function store()
    {
        helper(['form', 'url']);

        $type        = (string)$this->request->getPost('type');
        $scope       = (string)$this->request->getPost('platform_scope');
        $formatKey   = (string)($this->request->getPost('format_key') ?? '');
        $name        = trim((string)$this->request->getPost('name'));
        $desc        = trim((string)$this->request->getPost('description'));

        // ✅ yeni alanlar
        $collectionId = (int)($this->request->getPost('collection_id') ?? 0);
        $isFeatured   = (int)($this->request->getPost('is_featured') ?? 0);
        $isActive     = (int)($this->request->getPost('is_active') ?? 1);

        if ($name === '') {
            return redirect()->back()->withInput()->with('error', 'Başlık zorunlu.');
        }
        if (!in_array($type, ['image','video'], true)) {
            return redirect()->back()->withInput()->with('error', 'Type geçersiz.');
        }
        if (!in_array($scope, ['instagram','facebook','tiktok','youtube'], true)) {
            return redirect()->back()->withInput()->with('error', 'Platform scope geçersiz.');
        }

        // ✅ Tema kontrol (aktif tema seçilmeli)
        if ($collectionId <= 0) {
            return redirect()->back()->withInput()->with('error', 'Tema seçmelisin.');
        }
        $db = \Config\Database::connect();
        $col = $db->table('template_collections')->select('id')->where('id', $collectionId)->where('is_active', 1)->get()->getRowArray();
        if (!$col) {
            return redirect()->back()->withInput()->with('error', 'Seçilen tema geçersiz veya pasif.');
        }

        $file = $this->request->getFile('file');
        if (!$file || !$file->isValid()) {
            return redirect()->back()->withInput()->with('error', 'Dosya yüklenemedi.');
        }

        $mime = (string)$file->getMimeType();
        $now  = date('Y-m-d H:i:s');

        $width = null; $height = null;

        // Image ise strict boyut kontrol
        if ($type === 'image') {
            if ($formatKey === '' || !isset($this->formats[$formatKey])) {
                return redirect()->back()->withInput()->with('error', 'Format seçmelisin.');
            }

            $tmpPath = $file->getTempName();
            $info = @getimagesize($tmpPath);
            if (!$info) {
                return redirect()->back()->withInput()->with('error', 'Görsel okunamadı.');
            }

            $width  = (int)$info[0];
            $height = (int)$info[1];

            $expW = (int)$this->formats[$formatKey]['w'];
            $expH = (int)$this->formats[$formatKey]['h'];

            if ($width !== $expW || $height !== $expH) {
                return redirect()->back()->withInput()->with('error', "Boyut uyumsuz. Beklenen: {$expW}x{$expH}, Gelen: {$width}x{$height}");
            }
        } else {
            // video v1 sadece yükle (format boş kalabilir)
            $formatKey = null;
        }

        // Kaydet: public/uploads/templates/
        $targetDir = ROOTPATH . 'public/uploads/templates';
        if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0775, true);
        }

        $newName = time() . '_' . bin2hex(random_bytes(6)) . '.' . $file->getExtension();
        if (!$file->move($targetDir, $newName)) {
            return redirect()->back()->withInput()->with('error', 'Dosya diske yazılamadı.');
        }

        $relPath = 'uploads/templates/' . $newName;

        // media kaydı
        $mediaId = (new MediaModel())->insert([
            'user_id'    => session('user_id') ?: null,
            'type'       => $type,
            'file_path'  => $relPath,
            'mime_type'  => $mime ?: null,
            'width'      => $width,
            'height'     => $height,
            'duration'   => null,
            'created_at' => $now,
            'updated_at' => $now,
        ], true);

        if (!$mediaId) {
            return redirect()->back()->withInput()->with('error', 'Media kaydı oluşturulamadı.');
        }

        // templates kaydı
        $tplId = (new TemplateModel())->insert([
            'name'           => $name,
            'description'    => $desc ?: null,
            'collection_id'  => $collectionId,           
            'platform_type'  => null,
            'type'           => $type,
            'platform_scope' => $scope,
            'format_key'     => $formatKey,
            'width'          => $width,
            'height'         => $height,
            'base_media_id'  => (int)$mediaId,
            'thumb_path'     => null,
            'is_active'      => $isActive ? 1 : 0,       
            'is_featured'    => $isFeatured ? 1 : 0,     
            'created_at'     => $now,
            'updated_at'     => $now,
        ], true);

        if (!$tplId) {
            return redirect()->back()->withInput()->with('error', 'Template kaydı oluşturulamadı.');
        }

        return redirect()->to(site_url('admin/templates'))->with('success', 'Şablon yüklendi.');
    }

    public function toggle(int $id)
    {
        $model = new TemplateModel();
        $row = $model->find($id);
        if (!$row) return redirect()->to(site_url('admin/templates'))->with('error', 'Şablon bulunamadı.');

        $next = ((int)($row['is_active'] ?? 1) === 1) ? 0 : 1;
        $model->update($id, ['is_active' => $next]);

        return redirect()->to(site_url('admin/templates'))->with('success', 'Durum güncellendi.');
    }
}
