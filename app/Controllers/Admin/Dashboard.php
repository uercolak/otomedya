<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;

class Dashboard extends BaseController
{
    public function index()
    {
        $db = db_connect();

        // Eğer sistem tenant bazlı çalışıyorsa session('tenant_id') dolu olur.
        // Boşsa "global" say.
        $tenantId = session('tenant_id');
        $tenantFilter = function ($builder) use ($tenantId) {
            if (!empty($tenantId)) {
                $builder->where('tenant_id', (int)$tenantId);
            }
            return $builder;
        };

        // -------------------------
        // ÜST KARTLAR
        // -------------------------
        $userModel = new UserModel();

        // Toplam kullanıcı (bu tenant içinde) + (istersen admin/root hariç saydırabiliriz)
        $totalUsersQ = $userModel->builder();
        $tenantFilter($totalUsersQ);
        $totalUsers = $totalUsersQ->countAllResults();

        // Yönetici sayısı (role = admin)
        $adminQ = $userModel->builder();
        $tenantFilter($adminQ);
        $admins = $adminQ->where('role', 'admin')->countAllResults();

        // Bayi sayısı (role = dealer)
        $dealerQ = $userModel->builder();
        $tenantFilter($dealerQ);
        $dealers = $dealerQ->where('role', 'dealer')->countAllResults();

        // Aktif kullanıcı sayısı (role=user + status=active)
        $activeUserQ = $userModel->builder();
        $tenantFilter($activeUserQ);
        $activeUsers = $activeUserQ->where('role', 'user')->where('status', 'active')->countAllResults();

        // -------------------------
        // BU HAFTA PLANLI + SON 5 YAKLAŞAN
        // publishes tablosu: schedule_at var (senin ekran görüntüsünde var)
        // -------------------------
        $now = date('Y-m-d H:i:s');

        // Haftanın başlangıcı (Pzt) - bitiş (Paz)
        $monday = date('Y-m-d 00:00:00', strtotime('monday this week'));
        $sunday = date('Y-m-d 23:59:59', strtotime('sunday this week'));

        $weekPlannedBuilder = $db->table('publishes p');
        $tenantFilter($weekPlannedBuilder);
        $weekPlanned = $weekPlannedBuilder
            ->where('p.schedule_at >=', $monday)
            ->where('p.schedule_at <=', $sunday)
            ->countAllResults();

        $upcomingBuilder = $db->table('publishes p');
        $tenantFilter($upcomingBuilder);
        $upcomingPlans = $upcomingBuilder
            ->select('p.id, p.platform, p.status, p.schedule_at, u.name as user_name, u.email as user_email')
            ->join('users u', 'u.id = p.user_id', 'left')
            ->where('p.schedule_at IS NOT NULL', null, false)
            ->where('p.schedule_at >=', $now)
            ->orderBy('p.schedule_at', 'ASC')
            ->limit(5)
            ->get()->getResultArray();

        // -------------------------
        // BAĞLI SOSYAL HESAPLAR (Son 3)
        // social_accounts tablosu var
        // -------------------------
        $accountsBuilder = $db->table('social_accounts sa');
        $tenantFilter($accountsBuilder);
        $accountsCount = $accountsBuilder->countAllResults();

        $accountsListBuilder = $db->table('social_accounts sa');
        $tenantFilter($accountsListBuilder);
        $accountsList = $accountsListBuilder
            ->select('sa.id, sa.platform, sa.username, sa.name, sa.status')
            ->orderBy('sa.id', 'DESC')
            ->limit(3)
            ->get()->getResultArray();

        // -------------------------
        // AKTİF ŞABLONLAR (Son 3) + küçük görsel (thumb_path)
        // templates tablosu var ve thumb_path alanı var.
        // -------------------------
        $tplBuilder = $db->table('templates t');
        $tenantFilter($tplBuilder);
        $activeTemplateCount = $tplBuilder->where('t.is_active', 1)->countAllResults();

        $tplListBuilder = $db->table('templates t');
        $tenantFilter($tplListBuilder);
        $activeTemplates = $tplListBuilder
            ->select('t.id, t.name, t.platform_scope, t.type, t.thumb_path')
            ->where('t.is_active', 1)
            ->orderBy('t.id', 'DESC')
            ->limit(3)
            ->get()->getResultArray();

        // -------------------------
        // TAKVİM ÖZETİ (o ayın günleri: planlı ve yayınlanan sayıları)
        // publishes.schedule_at / publishes.published_at ile dolduracağız
        // -------------------------
        $year  = (int)($this->request->getGet('y') ?: date('Y'));
        $month = (int)($this->request->getGet('m') ?: date('m'));

        $monthStart = sprintf('%04d-%02d-01 00:00:00', $year, $month);
        $monthEnd   = date('Y-m-t 23:59:59', strtotime($monthStart));

        // Planlı (schedule_at)
        $plannedMonthBuilder = $db->table('publishes p');
        $tenantFilter($plannedMonthBuilder);
        $plannedMonthRows = $plannedMonthBuilder
            ->select("DATE(p.schedule_at) as d, COUNT(*) as c")
            ->where('p.schedule_at IS NOT NULL', null, false)
            ->where('p.schedule_at >=', $monthStart)
            ->where('p.schedule_at <=', $monthEnd)
            ->groupBy('d')
            ->get()->getResultArray();

        // Yayınlanan (published_at)
        $publishedMonthBuilder = $db->table('publishes p');
        $tenantFilter($publishedMonthBuilder);
        $publishedMonthRows = $publishedMonthBuilder
            ->select("DATE(p.published_at) as d, COUNT(*) as c")
            ->where('p.published_at IS NOT NULL', null, false)
            ->where('p.published_at >=', $monthStart)
            ->where('p.published_at <=', $monthEnd)
            ->groupBy('d')
            ->get()->getResultArray();

        $calendarPlanned = [];
        foreach ($plannedMonthRows as $r) $calendarPlanned[$r['d']] = (int)$r['c'];

        $calendarPublished = [];
        foreach ($publishedMonthRows as $r) $calendarPublished[$r['d']] = (int)$r['c'];

        // -------------------------
        // SON AKTİVİTELER (logs tablosundan daha okunur)
        // "info / job.succeeded" yerine TR etiketleyeceğiz (View'da)
        // logs tablosu var.
        // -------------------------
        $logs = [];
        if ($db->tableExists('logs')) {
            $logBuilder = $db->table('logs l');
            $tenantFilter($logBuilder);
            $logs = $logBuilder
                ->select('l.level, l.message, l.created_at')
                ->orderBy('l.id', 'DESC')
                ->limit(5)
                ->get()->getResultArray();
        }

        return view('admin/dashboard', [
            'pageTitle'          => 'Yönetici Paneli',
            'pageSubtitle'       => 'Planlı gönderiler, bağlı hesaplar ve son işlemlerin özeti.',

            'totalUsers'         => $totalUsers,
            'admins'             => $admins,
            'dealers'            => $dealers,
            'activeUsers'        => $activeUsers,

            'weekPlanned'        => $weekPlanned,
            'upcomingPlans'      => $upcomingPlans,

            'accountsCount'      => $accountsCount,
            'accountsList'       => $accountsList,

            'activeTemplateCount'=> $activeTemplateCount,
            'activeTemplates'    => $activeTemplates,

            'calendarYear'       => $year,
            'calendarMonth'      => $month,
            'calendarPlanned'    => $calendarPlanned,
            'calendarPublished'  => $calendarPublished,

            'logs'               => $logs,
        ]);
    }
}
