<?php

namespace App\Controllers\Panel;

use App\Controllers\BaseController;
use App\Models\TemplateModel;
use App\Models\TemplateDesignModel;

class TemplatesController extends BaseController
{
    private function ensureUser()
    {
        if (!session('is_logged_in')) {
            return redirect()->to(base_url('auth/login'));
        }
        return null;
    }

    public function index()
    {
        if ($r = $this->ensureUser()) return $r;

        $db = \Config\Database::connect();

        $q          = trim((string)($this->request->getGet('q') ?? ''));
        $scope      = trim((string)($this->request->getGet('scope') ?? ''));      
        $format     = trim((string)($this->request->getGet('format') ?? ''));    
        $type       = trim((string)($this->request->getGet('type') ?? ''));   
        $collection = trim((string)($this->request->getGet('collection') ?? ''));  

        $collections = $db->table('template_collections')
            ->select('id, name, slug')
            ->where('is_active', 1)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('name', 'ASC')
            ->get()->getResultArray();

        // slug -> id
        $collectionId = 0;
        if ($collection !== '') {
            $row = $db->table('template_collections')
                ->select('id')
                ->where('slug', $collection)
                ->where('is_active', 1)
                ->get()->getRowArray();
            $collectionId = (int)($row['id'] ?? 0);
        }

        $countBuilder = $db->table('templates')
            ->select('platform_scope, COUNT(*) AS cnt')
            ->where('is_active', 1);

        if ($q !== '') {
            $countBuilder->groupStart()
                ->like('name', $q)
                ->orLike('description', $q)
            ->groupEnd();
        }
        if ($collectionId > 0) $countBuilder->where('collection_id', $collectionId);
        if ($format !== '') $countBuilder->where('format_key', $format);
        if ($type !== '')   $countBuilder->where('type', $type);

        $countRows = $countBuilder
            ->groupBy('platform_scope')
            ->get()->getResultArray();

        $categoryCounts = [];
        $totalCount = 0;
        foreach ($countRows as $cr) {
            $k = (string)($cr['platform_scope'] ?? '');
            $c = (int)($cr['cnt'] ?? 0);
            if ($k !== '') {
                $categoryCounts[$k] = $c;
                $totalCount += $c;
            }
        }

        $colCountBuilder = $db->table('templates t')
            ->select('t.collection_id, COUNT(*) AS cnt')
            ->where('t.is_active', 1);

        if ($q !== '') {
            $colCountBuilder->groupStart()
                ->like('t.name', $q)
                ->orLike('t.description', $q)
            ->groupEnd();
        }
        if ($scope !== '')  $colCountBuilder->where('t.platform_scope', $scope);
        if ($format !== '') $colCountBuilder->where('t.format_key', $format);
        if ($type !== '')   $colCountBuilder->where('t.type', $type);

        $colCountRows = $colCountBuilder
            ->groupBy('t.collection_id')
            ->get()->getResultArray();

        $collectionCounts = [];
        foreach ($colCountRows as $r) {
            $cid = (int)($r['collection_id'] ?? 0);
            $cnt = (int)($r['cnt'] ?? 0);
            if ($cid > 0) $collectionCounts[$cid] = $cnt;
        }

        $builder = $db->table('templates')
            ->where('is_active', 1);

        if ($q !== '') {
            $builder->groupStart()
                ->like('name', $q)
                ->orLike('description', $q)
            ->groupEnd();
        }
        if ($collectionId > 0) $builder->where('collection_id', $collectionId);
        if ($scope !== '')     $builder->where('platform_scope', $scope);
        if ($format !== '')    $builder->where('format_key', $format);
        if ($type !== '')      $builder->where('type', $type);

        $rows = $builder->orderBy('id', 'DESC')->get()->getResultArray();

        $scopeOptions = ['instagram','facebook','tiktok','youtube'];

        return view('panel/templates/index', [
            'pageTitle'        => 'Hazır Şablonlar',
            'headerVariant'    => 'compact',
            'rows'             => $rows,
            'filters'          => [
                'q' => $q,
                'scope' => $scope,
                'format' => $format,
                'type' => $type,
                'collection' => $collection,
            ],
            'scopeOptions'     => $scopeOptions,
            'categoryCounts'   => $categoryCounts,
            'totalCount'       => $totalCount,
            'collections'      => $collections,
            'collectionCounts' => $collectionCounts,
        ]);
    }

    public function show(int $id)
    {
        if ($r = $this->ensureUser()) return $r;

        $tpl = (new TemplateModel())->find($id);
        if (!$tpl || (int)($tpl['is_active'] ?? 0) !== 1) {
            return redirect()->to(site_url('panel/templates'))->with('error', 'Şablon bulunamadı.');
        }

        return view('panel/templates/show', [
            'pageTitle' => 'Şablon',
            'headerVariant' => 'compact',
            'tpl' => $tpl,
        ]);
    }

    public function edit(int $id)
    {
        if ($r = $this->ensureUser()) return $r;

        $userId = (int)session('user_id');

        $tpl = (new TemplateModel())->find($id);
        if (!$tpl || (int)($tpl['is_active'] ?? 0) !== 1) {
            return redirect()->to(site_url('panel/templates'))->with('error', 'Şablon bulunamadı.');
        }

        $design = (new TemplateDesignModel())
            ->where('user_id', $userId)
            ->where('template_id', $id)
            ->where('format_key', (string)($tpl['format_key'] ?? ''))
            ->orderBy('id','DESC')
            ->first();

        $autoplan = ((string)$this->request->getGet('autoplan') === '1');

        return view('panel/templates/edit', [
            'pageTitle' => 'Şablonu Düzenle',
            'headerVariant' => 'compact',
            'tpl' => $tpl,
            'design' => $design,
            'autoplan' => $autoplan,
        ]);
    }

    public function save(int $id)
    {
        if ($r = $this->ensureUser()) return $r;

        if ($this->request->getMethod(true) !== 'POST') {
            return $this->response->setStatusCode(405)->setJSON(['ok'=>false,'message'=>'Method not allowed']);
        }

        $userId = (int)session('user_id');
        $db = \Config\Database::connect();
        $now = date('Y-m-d H:i:s');

        $tpl = (new TemplateModel())->find($id);
        if (!$tpl || (int)($tpl['is_active'] ?? 0) !== 1) {
            return $this->response->setStatusCode(404)->setJSON(['ok'=>false,'message'=>'Şablon yok']);
        }

        $formatKey = (string)($tpl['format_key'] ?? '');
        $cw = (int)($this->request->getPost('canvas_width') ?? 0);
        $ch = (int)($this->request->getPost('canvas_height') ?? 0);
        $stateJson = (string)($this->request->getPost('state_json') ?? '');

        if ($cw <= 0 || $ch <= 0 || $stateJson === '') {
            return $this->response->setStatusCode(400)->setJSON(['ok'=>false,'message'=>'Eksik veri.']);
        }

        $db->table('template_designs')->insert([
            'user_id'       => $userId,
            'template_id'   => (int)$id,
            'editor_type'   => 'fabric',
            'format_key'    => $formatKey,
            'canvas_width'  => $cw,
            'canvas_height' => $ch,
            'state_json'    => $stateJson,
            'created_at'    => $now,
            'updated_at'    => $now,
        ]);

        $designId = (int)$db->insertID();

        return $this->response->setJSON([
            'ok'        => true,
            'design_id' => $designId,
            'csrfName'  => csrf_token(),
            'csrfHash'  => csrf_hash(),
        ]);
    }

    public function export(int $id)
    {
        if ($r = $this->ensureUser()) return $r;

        if ($this->request->getMethod(true) !== 'POST') {
            return $this->response->setStatusCode(405)->setJSON(['ok'=>false,'message'=>'Method not allowed']);
        }

        $userId = (int)session('user_id');
        $db = \Config\Database::connect();
        $now = date('Y-m-d H:i:s');

        $tpl = (new TemplateModel())->find($id);
        if (!$tpl || (int)($tpl['is_active'] ?? 0) !== 1) {
            return $this->response->setStatusCode(404)->setJSON(['ok'=>false,'message'=>'Şablon yok']);
        }

        $designId = (int)($this->request->getPost('design_id') ?? 0);
        $dataUrl  = (string)($this->request->getPost('png_data') ?? '');
        $postType = strtolower(trim((string)($this->request->getPost('post_type') ?? 'post')));

        if (!in_array($postType, ['post','reels','story','auto'], true)) $postType = 'post';

        if ($designId <= 0 || $dataUrl === '' || !str_starts_with($dataUrl, 'data:image/png;base64,')) {
            return $this->response->setStatusCode(400)->setJSON(['ok'=>false,'message'=>'Eksik/Geçersiz export verisi.']);
        }

        // Design ownership check
        $design = $db->table('template_designs')
            ->where('id', $designId)
            ->where('user_id', $userId)
            ->where('template_id', $id)
            ->get()->getRowArray();

        if (!$design) {
            return $this->response->setStatusCode(403)->setJSON(['ok'=>false,'message'=>'Design yetkisiz.']);
        }

        // PNG decode
        $base64 = substr($dataUrl, strlen('data:image/png;base64,'));
        $bin = base64_decode($base64, true);
        if ($bin === false) {
            return $this->response->setStatusCode(400)->setJSON(['ok'=>false,'message'=>'Base64 çözülemedi.']);
        }

        // Kaydet: uploads/Y/m
        $subdir = date('Y') . '/' . date('m');
        $targetDir = FCPATH . 'uploads/' . $subdir;
        if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0775, true);
        }

        $filename = 'tpl_' . $id . '_d' . $designId . '_' . bin2hex(random_bytes(6)) . '.png';
        $relPath = 'uploads/' . $subdir . '/' . $filename;
        $absPath = FCPATH . $relPath;

        if (@file_put_contents($absPath, $bin) === false) {
            return $this->response->setStatusCode(500)->setJSON(['ok'=>false,'message'=>'Dosya yazılamadı. Permission kontrol et.']);
        }

        // contents üret (Planner ile aynı şema)
        $meta = [
            'post_type' => $postType,
            'template' => [
                'template_id' => (int)$id,
                'design_id'   => (int)$designId,
                'format_key'  => (string)($tpl['format_key'] ?? ''),
            ],
        ];

        $db->transStart();

        $db->table('contents')->insert([
            'user_id'     => $userId,
            'title'       => (string)($tpl['name'] ?? '') ?: null,
            'base_text'   => null,
            'media_type'  => 'image',
            'media_path'  => $relPath,
            'template_id' => (int)$id,
            'meta_json'   => json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'created_at'  => $now,
            'updated_at'  => $now,
        ]);
        $contentId = (int)$db->insertID();

        // template_designs güncelle (export yolu + content_id)
        $db->table('template_designs')->where('id', $designId)->where('user_id', $userId)->update([
            'export_file_path' => $relPath,
            'content_id'       => $contentId,
            'updated_at'       => $now,
        ]);

        $db->transComplete();

        if (!$db->transStatus()) {
            return $this->response->setStatusCode(500)->setJSON(['ok'=>false,'message'=>'DB işlem hatası.']);
        }

        return $this->response->setJSON([
            'ok'        => true,
            'content_id'=> $contentId,
            'redirect'  => site_url('panel/planner?content_id=' . $contentId),
            'csrfName'  => csrf_token(),
            'csrfHash'  => csrf_hash(),
        ]);
    }

    public function useVideo(int $id)
    {
        if ($r = $this->ensureUser()) return $r;

        $userId = (int)session('user_id');
        $db = \Config\Database::connect();
        $now = date('Y-m-d H:i:s');

        $tpl = (new TemplateModel())->find($id);
        if (!$tpl || (int)($tpl['is_active'] ?? 0) !== 1) {
            return redirect()->to(site_url('panel/templates'))->with('error', 'Şablon bulunamadı.');
        }

        if (($tpl['type'] ?? '') !== 'video') {
            return redirect()->to(site_url('panel/templates/'.$id.'/edit'));
        }

        $baseMediaId = (int)($tpl['base_media_id'] ?? 0);
        if ($baseMediaId <= 0) {
            return redirect()->to(site_url('panel/templates'))->with('error', 'Video dosyası yok.');
        }

        // media tablosundan dosya yolu
        $media = $db->table('media')->where('id', $baseMediaId)->get()->getRowArray();
        $mediaPath = (string)($media['file_path'] ?? '');
        if ($mediaPath === '') {
            return redirect()->to(site_url('panel/templates'))->with('error', 'Video path bulunamadı.');
        }

        $meta = [
            'post_type' => 'reels',
            'template' => [
                'template_id' => (int)$id,
                'design_id'   => null,
                'format_key'  => (string)($tpl['format_key'] ?? ''),
            ],
        ];

        $db->table('contents')->insert([
            'user_id'     => $userId,
            'title'       => (string)($tpl['name'] ?? '') ?: null,
            'base_text'   => null,
            'media_type'  => 'video',
            'media_path'  => $mediaPath,
            'template_id' => (int)$id,
            'meta_json'   => json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'created_at'  => $now,
            'updated_at'  => $now,
        ]);

        $contentId = (int)$db->insertID();

        return redirect()->to(site_url('panel/planner?content_id=' . $contentId));
    }

}
