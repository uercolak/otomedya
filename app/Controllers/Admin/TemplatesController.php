<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\TemplateModel;
use App\Models\MediaModel;

class TemplatesController extends BaseController
{
    private array $formats = [
        'ig_post_1_1'   => ['label' => 'Instagram Post (1:1)',   'w' => 1080, 'h' => 1080],
        'ig_post_4_5'   => ['label' => 'Instagram Post (4:5)',   'w' => 1080, 'h' => 1350],
        'ig_story_9_16' => ['label' => 'Instagram Story (9:16)', 'w' => 1080, 'h' => 1920],
        'ig_reels_9_16' => ['label' => 'Instagram Reels (9:16)', 'w' => 1080, 'h' => 1920],
    ];

    public function index()
    {
        $db = \Config\Database::connect();

        $q      = trim((string)($this->request->getGet('q') ?? ''));
        $type   = trim((string)($this->request->getGet('type') ?? ''));
        $scope  = trim((string)($this->request->getGet('scope') ?? ''));
        $format = trim((string)($this->request->getGet('format') ?? ''));
        $active = trim((string)($this->request->getGet('active') ?? ''));

        $builder = $db->table('templates t')
            ->select('t.*')
            ->select('m.file_path as media_file_path, m.mime_type as media_mime_type, m.width as media_width, m.height as media_height')
            ->join('media m', 'm.id = t.base_media_id', 'left')
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

        $rows = $builder->get()->getResultArray();

        return view('admin/templates/index', [
            'pageTitle' => 'Hazır Şablonlar',
            'rows'      => $rows,
            'filters'   => compact('q','type','scope','format','active'),
            'formats'   => $this->formats,
        ]);
    }

    public function create()
    {
        return view('admin/templates/create', [
            'pageTitle' => 'Yeni Şablon Yükle',
            'formats'   => $this->formats,
            'errors'    => session()->getFlashdata('errors') ?? [],
        ]);
    }

    public function store()
    {
        helper(['form', 'url']);

        $type  = (string)$this->request->getPost('type');
        $scope = (string)$this->request->getPost('platform_scope');
        $formatKey = (string)($this->request->getPost('format_key') ?? '');
        $name  = trim((string)$this->request->getPost('name'));
        $desc  = trim((string)$this->request->getPost('description'));

        if ($name === '') {
            return redirect()->back()->withInput()->with('error', 'Başlık zorunlu.');
        }
        if (!in_array($type, ['image','video'], true)) {
            return redirect()->back()->withInput()->with('error', 'Type geçersiz.');
        }
        if (!in_array($scope, ['universal','instagram','facebook'], true)) {
            return redirect()->back()->withInput()->with('error', 'Platform scope geçersiz.');
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
            'platform_type'  => null,
            'type'           => $type,
            'platform_scope' => $scope,
            'format_key'     => $formatKey,
            'width'          => $width,
            'height'         => $height,
            'base_media_id'  => (int)$mediaId,
            'thumb_path'     => null,
            'is_active'      => 1,
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
