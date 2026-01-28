<?php

namespace App\Controllers\Panel;

use App\Controllers\BaseController;

class PublishesController extends BaseController
{
    private function ensureUser(): ?\CodeIgniter\HTTP\RedirectResponse
    {
        if (!session('is_logged_in')) {
            return redirect()->to(site_url('auth/login'));
        }
        return null;
    }

    public function index()
    {
        if ($r = $this->ensureUser()) return $r;

        $userId = (int) session('user_id');
        $db = \Config\Database::connect();

        $q        = trim((string)($this->request->getGet('q') ?? ''));
        $platform = trim((string)($this->request->getGet('platform') ?? ''));
        $status   = trim((string)($this->request->getGet('status') ?? ''));
        $dateFrom = trim((string)($this->request->getGet('date_from') ?? ''));
        $dateTo   = trim((string)($this->request->getGet('date_to') ?? ''));

        $builder = $db->table('publishes p')
            ->select('p.*')
            ->select('sa.name as sa_name, sa.username as sa_username')
            ->select('c.title as content_title')
            ->join('social_accounts sa', 'sa.id = p.account_id', 'left')
            ->join('contents c', 'c.id = p.content_id', 'left')
            ->where('p.user_id', $userId);

        if ($q !== '') {
            $builder->groupStart()
                ->like('p.remote_id', $q)
                ->orLike('c.title', $q)
                ->orLike('p.error', $q)
            ->groupEnd();
        }

        if ($platform !== '') $builder->where('p.platform', $platform);
        if ($status !== '')   $builder->where('p.status', $status);

        // Tarih filtreleri (created_at üzerinden)
        if ($dateFrom !== '') $builder->where('p.created_at >=', $dateFrom . ' 00:00:00');
        if ($dateTo !== '')   $builder->where('p.created_at <=', $dateTo . ' 23:59:59');

        $builder->orderBy('p.id', 'DESC');

        // Pagination
        $perPage = 15;
        $page = max(1, (int)($this->request->getGet('page') ?? 1));
        $offset = ($page - 1) * $perPage;

        // countAllResults(false) -> builder reset olmasın
        $total = (clone $builder)->countAllResults(false);
        $rows  = $builder->limit($perPage, $offset)->get()->getResultArray();

        $platforms = $db->table('publishes')
            ->select('platform')
            ->distinct()
            ->where('user_id', $userId)
            ->orderBy('platform', 'ASC')
            ->get()->getResultArray();

        $platformOptions = array_values(array_filter(array_map(fn($r)=> (string)($r['platform'] ?? ''), $platforms)));

        $statusOptions = ['queued','scheduled','publishing','published','failed','canceled'];

        return view('panel/publishes/index', [
            'rows' => $rows,
            'filters' => [
                'q' => $q,
                'platform' => $platform,
                'status' => $status,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
            'platformOptions' => $platformOptions,
            'statusOptions' => $statusOptions,
            'pagination' => [
                'total' => $total,
                'perPage' => $perPage,
                'page' => $page,
                'pages' => (int)ceil(($total ?: 0) / $perPage),
            ],
        ]);
    }

    public function show(int $id)
    {
        if ($r = $this->ensureUser()) return $r;

        $userId = (int) session('user_id');
        $db = \Config\Database::connect();

        $row = $db->table('publishes p')
            ->select('p.*')
            ->select('sa.name as sa_name, sa.username as sa_username')

            // içerik metni + medya alanları (ALIAS ile show.php standart okur)
            ->select('c.title as content_title, c.base_text as content_text')
            ->select('c.media_path as content_media_path')     // ✅ varsa: uploads/.... (relative)
            ->select('c.media_kind as content_media_kind')     // ✅ image/video (varsa)
            ->select('c.thumb_path as content_thumb_path')     // ✅ varsa
            ->join('social_accounts sa', 'sa.id = p.account_id', 'left')
            ->join('contents c', 'c.id = p.content_id', 'left')
            ->where('p.id', $id)
            ->where('p.user_id', $userId)
            ->get()->getRowArray();

        if (!$row) {
            return redirect()->to(site_url('panel/publishes'))->with('error', 'Paylaşım kaydı bulunamadı.');
        }

        // ✅ Öncelik: meta_json içindeki permalink (sende YouTube/IG/FB var)
        $previewUrl = $this->extractPermalinkFromMetaJson((string)($row['meta_json'] ?? ''));

        // fallback: remote_id URL ise onu kullan
        if (!$previewUrl) {
            $remoteId = trim((string)($row['remote_id'] ?? ''));
            if ($remoteId !== '' && preg_match('~^https?://~i', $remoteId)) {
                $previewUrl = $remoteId;
            }
        }

        // En son fallback: eski kaba mapping (isteğe bağlı)
        if (!$previewUrl && (($row['status'] ?? '') === 'published')) {
            $previewUrl = $this->buildPreviewUrl(
                (string)($row['platform'] ?? ''),
                (string)($row['remote_id'] ?? ''),
                (string)($row['sa_username'] ?? '')
            );
        }

        return view('panel/publishes/show', [
            'row' => $row,
            'previewUrl' => $previewUrl,
        ]);
    }

    /**
     * meta_json içinden permalink çek (Meta/YouTube/IG/FB için)
     * Örn:
     * {"meta":{"permalink":"https://youtu.be/..."}} veya {"meta":{"permalink":"https://instagram.com/reel/..."}} vb.
     * TikTok örneğinde permalink yok (published_without_post_id) -> null döner.
     */
    private function extractPermalinkFromMetaJson(string $metaJson): ?string
    {
        $metaJson = trim($metaJson);
        if ($metaJson === '') return null;

        $arr = json_decode($metaJson, true);
        if (!is_array($arr)) return null;

        // Sende örnek: {"meta":{"permalink":"..."}} (IG/YT/FB)
        if (!empty($arr['meta']['permalink']) && is_string($arr['meta']['permalink'])) {
            $u = trim($arr['meta']['permalink']);
            if ($u !== '' && preg_match('~^https?://~i', $u)) return $u;
        }

        // bazı durumlarda platform kökünde olabilir
        if (!empty($arr['permalink']) && is_string($arr['permalink'])) {
            $u = trim($arr['permalink']);
            if ($u !== '' && preg_match('~^https?://~i', $u)) return $u;
        }

        return null;
    }

    private function buildPreviewUrl(?string $platform, ?string $remoteId, ?string $username = null): ?string
    {
        $platform = strtolower(trim((string)$platform));
        $remoteId = trim((string)$remoteId);
        $username = trim((string)$username);

        if ($remoteId === '') return null;

        // remote_id bazen zaten URL olabilir
        if (preg_match('~^https?://~i', $remoteId)) {
            return $remoteId;
        }

        switch ($platform) {
            case 'x':
            case 'twitter':
                return 'https://x.com/i/web/status/' . rawurlencode($remoteId);

            case 'instagram':
                // NOT: Sende gerçek permalink meta_json'da var; burası fallback
                return 'https://www.instagram.com/p/' . rawurlencode($remoteId) . '/';

            case 'facebook':
                // NOT: Sende gerçek permalink meta_json'da var; burası fallback
                return 'https://www.facebook.com/' . rawurlencode($remoteId);

            case 'youtube':
                // remoteId video id ise
                return 'https://youtu.be/' . rawurlencode($remoteId);

            case 'linkedin':
                return 'https://www.linkedin.com/feed/update/' . rawurlencode($remoteId);

            case 'tiktok':
                // TikTok remote_id çoğu zaman publish_id oluyor (post id değil) -> link üretmek yanlış olabilir.
                // username + videoId varsa kullanılabilir; yoksa null.
                if ($username !== '' && preg_match('~^\d+$~', $remoteId)) {
                    return 'https://www.tiktok.com/@' . rawurlencode($username) . '/video/' . rawurlencode($remoteId);
                }
                return null;

            default:
                return null;
        }
    }

    public function cancel(int $id)
    {
        if ($r = $this->ensureUser()) return $r;

        $userId = (int) session('user_id');
        $db = \Config\Database::connect();

        $row = $db->table('publishes')
            ->select('id,status,user_id,job_id')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->get()->getRowArray();

        if (!$row) {
            return redirect()->to(site_url('panel/publishes'))->with('error', 'Paylaşım kaydı bulunamadı.');
        }

        $status = (string)($row['status'] ?? '');
        $cancelable = in_array($status, ['queued', 'scheduled'], true);

        if (!$cancelable) {
            return redirect()->to(site_url('panel/publishes/' . $id))
                ->with('error', 'Bu paylaşım iptal edilemez. (Sadece sıradaki/planlanan paylaşımlar iptal edilebilir)');
        }

        $now = date('Y-m-d H:i:s');
        $db->transStart();

        $db->table('publishes')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->update([
                'status'     => 'canceled',
                'updated_at' => $now,
            ]);

        $jobId = (int)($row['job_id'] ?? 0);
        if ($jobId > 0) {
            $db->table('jobs')
                ->where('id', $jobId)
                ->where('status', 'queued')
                ->update([
                    'status'     => 'canceled',
                    'last_error' => 'Canceled by user',
                    'locked_at'  => null,
                    'locked_by'  => null,
                    'updated_at' => $now,
                ]);
        }

        $db->transComplete();

        if (!$db->transStatus()) {
            return redirect()->to(site_url('panel/publishes/' . $id))
                ->with('error', 'İptal işlemi sırasında hata oluştu.');
        }

        return redirect()->to(site_url('panel/publishes/' . $id))
            ->with('success', 'Paylaşım iptal edildi.');
    }
}
