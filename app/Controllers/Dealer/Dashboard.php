<?php

namespace App\Controllers\Dealer;

use App\Controllers\BaseController;
use App\Models\UserModel;

class Dashboard extends BaseController
{
    /**
     * Dealer guard (BaseController’da olmadığı için burada tanımlıyoruz)
     */
    protected function ensureDealer()
    {
        if (!session('isLoggedIn')) {
            return redirect()->to(site_url('auth/login'));
        }

        // Projede hangi key kullanılıyorsa ikisini de kontrol edelim
        $role = (string)(session('role') ?? session('user_role') ?? '');

        // Bayi rolü sende "dealer" veya "bayi" olabilir; ikisini de güvene alalım
        if (!in_array($role, ['dealer', 'bayi'], true)) {
            return redirect()->to(site_url('panel'))->with('error', 'Bu sayfaya erişim yetkin yok.');
        }

        return null;
    }

    public function index()
    {
        if ($r = $this->ensureDealer()) return $r;

        $dealerId = (int) session('user_id');               // bayi user id
        $tenantId = (int) (session('tenant_id') ?? 0);

        $userModel = new UserModel();

        // sadece bayinin oluşturduğu alt kullanıcılar (created_by)
        $baseUsers = $userModel->where('tenant_id', $tenantId)
                               ->where('role', 'user')
                               ->where('created_by', $dealerId);

        $totalUsers   = (clone $baseUsers)->countAllResults();
        $activeUsers  = (clone $baseUsers)->where('status', 'active')->countAllResults();
        $passiveUsers = (clone $baseUsers)->where('status', 'passive')->countAllResults();

        // --- publish özetleri (dealer’a özel) ---
        $db = \Config\Database::connect();

        // Alt kullanıcı ID’leri
        $subUserIds = $db->table('users')
            ->select('id')
            ->where('tenant_id', $tenantId)
            ->where('role', 'user')
            ->where('created_by', $dealerId)
            ->get()->getResultArray();

        $subUserIds = array_map(fn($x) => (int)$x['id'], $subUserIds);

        // Eğer hiç alt kullanıcı yoksa boş metrikler
        $plannedCount   = 0;
        $failed7dCount  = 0;
        $recentPublishes = [];

        if (!empty($subUserIds)) {
            $now = date('Y-m-d H:i:s');
            $plus7 = date('Y-m-d H:i:s', time() + 7*24*3600);
            $minus7 = date('Y-m-d H:i:s', time() - 7*24*3600);

            // 7 gün içinde planlı (queued/scheduled/running)
            $plannedCount = (int)$db->table('publishes')
                ->whereIn('user_id', $subUserIds)
                ->where('schedule_at >=', $now)
                ->where('schedule_at <=', $plus7)
                ->whereIn('status', ['queued','scheduled','running'])
                ->countAllResults();

            // Son 7 gün hata sayısı (failed veya error dolu)
            $failed7dCount = (int)$db->table('publishes')
                ->groupStart()
                    ->whereIn('status', ['failed','error'])
                    ->orWhere('error IS NOT NULL', null, false)
                    ->orWhere("error <>", '')
                ->groupEnd()
                ->whereIn('user_id', $subUserIds)
                ->where('created_at >=', $minus7) // tablonda created_at yoksa schedule_at kullan
                ->countAllResults();

            // Son hareketler (son 8 publish)
            $recentPublishes = $db->table('publishes p')
                ->select('p.id, p.platform, p.status, p.schedule_at, p.published_at, u.name as user_name, u.email as user_email')
                ->join('users u', 'u.id = p.user_id', 'left')
                ->whereIn('p.user_id', $subUserIds)
                ->orderBy('p.id', 'DESC')
                ->limit(8)
                ->get()->getResultArray();
        }

        return view('dealer/dashboard', [
            'pageTitle'       => 'Gösterge Paneli',
            'pageSubtitle'    => 'Alt kullanıcıların ve paylaşımların özeti.',
            'totalUsers'      => $totalUsers,
            'activeUsers'     => $activeUsers,
            'passiveUsers'    => $passiveUsers,
            'plannedCount'    => $plannedCount,
            'failed7dCount'   => $failed7dCount,
            'recentPublishes' => $recentPublishes,
        ]);
    }
}
