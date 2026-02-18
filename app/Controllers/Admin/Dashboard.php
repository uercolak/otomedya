<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;

class Dashboard extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $tenantId = session('tenant_id'); // varsa tenant bazlı filtreleyeceğiz

        // Tenant filtresi uygulayan küçük yardımcı
        $applyTenant = function($builder, $tableAlias = '') use ($tenantId) {
            if ($tenantId === null || $tenantId === '') return $builder;
            $col = $tableAlias ? $tableAlias . '.tenant_id' : 'tenant_id';
            return $builder->where($col, (int)$tenantId);
        };

        // ÜST KPI’lar
        $totalUsers = $applyTenant($db->table('users'))->countAllResults();

        $adminCount = $applyTenant($db->table('users'))
            ->whereIn('role', ['admin','root'])
            ->countAllResults();

        $bayiCount = $applyTenant($db->table('users'))
            ->where('role', 'dealer')
            ->countAllResults();

        $activeUserCount = $applyTenant($db->table('users'))
            ->where('role', 'user')
            ->where('status', 'active')
            ->countAllResults();

        // BAĞLI SOSYAL HESAP özeti (kısa)
        $connectedAccountsCount = $applyTenant($db->table('social_accounts sa'), 'sa')->countAllResults();

        $latestAccounts = $applyTenant($db->table('social_accounts sa'), 'sa')
            ->select('sa.id, sa.platform, sa.username, sa.created_at')
            ->orderBy('sa.id', 'DESC')
            ->limit(5)
            ->get()->getResultArray();

        // BU HAFTA yaklaşan planlı paylaşımlar (son 5)
        $now = date('Y-m-d H:i:s');
        $weekEnd = date('Y-m-d 23:59:59', strtotime('+7 days'));

        // scheduled_posts -> publishes -> users bağlantısı
        $weeklyPlans = $applyTenant($db->table('scheduled_posts sp'), 'sp')
            ->select("
                sp.id,
                sp.platform,
                sp.scheduled_at,
                sp.status,
                sp.retry_count,
                u.name as user_name,
                u.email as user_email
            ")
            ->join('publishes p', 'p.id = sp.publish_id', 'left')
            ->join('users u', 'u.id = p.user_id', 'left')
            ->where('sp.scheduled_at >=', $now)
            ->where('sp.scheduled_at <=', $weekEnd)
            ->orderBy('sp.scheduled_at', 'ASC')
            ->limit(5)
            ->get()->getResultArray();

        $weeklyPlansCount = $applyTenant($db->table('scheduled_posts sp'), 'sp')
            ->where('sp.scheduled_at >=', $now)
            ->where('sp.scheduled_at <=', $weekEnd)
            ->countAllResults();

        // AKTİF ŞABLONLAR (son 3)
        $activeTemplates = $applyTenant($db->table('templates t'), 't')
            ->select('t.id, t.name, t.platform_scope, t.type, t.thumb_path')
            ->where('t.is_active', 1)
            ->orderBy('t.id', 'DESC')
            ->limit(3)
            ->get()->getResultArray();

        $activeTemplatesCount = $applyTenant($db->table('templates t'), 't')
            ->where('t.is_active', 1)
            ->countAllResults();

        // TAKVİM (ayın planlı/yayınlanan özeti)
        $monthStart = date('Y-m-01 00:00:00');
        $monthEnd   = date('Y-m-t 23:59:59');

        $calendarRows = $applyTenant($db->table('scheduled_posts sp'), 'sp')
            ->select("
                DATE(sp.scheduled_at) as day,
                SUM(CASE WHEN sp.status IN ('scheduled','queued') THEN 1 ELSE 0 END) as planned_count,
                SUM(CASE WHEN sp.status IN ('posted','published') THEN 1 ELSE 0 END) as posted_count
            ")
            ->where('sp.scheduled_at >=', $monthStart)
            ->where('sp.scheduled_at <=', $monthEnd)
            ->groupBy('DATE(sp.scheduled_at)')
            ->get()->getResultArray();

        $dayStats = [];
        foreach ($calendarRows as $r) {
            $dayStats[$r['day']] = [
                'planned' => (int)$r['planned_count'],
                'posted'  => (int)$r['posted_count'],
            ];
        }

        // SON AKTİVİTELER (loglar) – varsa
        $recentLogs = $applyTenant($db->table('logs l'), 'l')
            ->select('l.id, l.level, l.message, l.created_at')
            ->orderBy('l.id', 'DESC')
            ->limit(5)
            ->get()->getResultArray();

        return view('admin/dashboard', [
            'pageTitle'               => 'Yönetici Paneli',
            'pageSubtitle'            => 'Planlı gönderiler, bağlı hesaplar ve sistem özeti.',

            'totalUsers'              => $totalUsers,
            'adminCount'              => $adminCount,
            'bayiCount'               => $bayiCount,
            'activeUserCount'         => $activeUserCount,

            'connectedAccountsCount'  => $connectedAccountsCount,
            'latestAccounts'          => $latestAccounts,

            'weeklyPlansCount'        => $weeklyPlansCount,
            'weeklyPlans'             => $weeklyPlans,

            'activeTemplatesCount'    => $activeTemplatesCount,
            'activeTemplates'         => $activeTemplates,

            'dayStats'                => $dayStats,
            'recentLogs'              => $recentLogs,

            'tenantId'                => $tenantId,
        ]);
    }
}
