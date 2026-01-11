<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\LogModel;

class LogsController extends BaseController
{
    public function index()
    {
        $model = new LogModel();

        $q          = trim((string)($this->request->getGet('q') ?? ''));
        $level      = trim((string)($this->request->getGet('level') ?? ''));
        $channel    = trim((string)($this->request->getGet('channel') ?? ''));
        $userId     = trim((string)($this->request->getGet('user_id') ?? ''));
        $dateFrom   = trim((string)($this->request->getGet('date_from') ?? ''));
        $dateTo     = trim((string)($this->request->getGet('date_to') ?? ''));
        $userSearch = trim((string)($this->request->getGet('user') ?? ''));

        $query = $model
            ->select('logs.id, logs.level, logs.channel, logs.message, logs.context_json, logs.user_id, logs.ip, logs.user_agent, logs.created_at')
            ->select('u.name as user_name, u.email as user_email')
            ->join('users u', 'u.id = logs.user_id', 'left');

        if ($q !== '') {
            $query->groupStart()
                ->like('logs.message', $q)
                ->orLike('logs.context_json', $q)
                ->groupEnd();
        }

        if ($userSearch !== '') {
            $query->groupStart()
                ->like('u.name', $userSearch)
                ->orLike('u.email', $userSearch)
                ->groupEnd();
        }

        if ($level !== '') {
            $query->where('logs.level', $level);
        }

        if ($channel !== '') {
            $query->where('logs.channel', $channel);
        }

        if ($userId !== '' && ctype_digit($userId)) {
            $query->where('logs.user_id', (int)$userId);
        }

        if ($dateFrom !== '') {
            $query->where('logs.created_at >=', $dateFrom . ' 00:00:00');
        }

        if ($dateTo !== '') {
            $query->where('logs.created_at <=', $dateTo . ' 23:59:59');
        }

        $query->orderBy('logs.id', 'DESC');

        $perPage = 15;
        $rows  = $query->paginate($perPage, 'logs');
        $pager = $model->pager;
        $pager->setPath(site_url('admin/logs'));

        // Dropdown: distinct channel list
        $db = \Config\Database::connect();
        $channels = $db->table('logs')
            ->select('channel')
            ->distinct()
            ->orderBy('channel', 'ASC')
            ->get()
            ->getResultArray();

        $channelOptions = array_values(array_filter(array_map(
            fn($r) => (string)($r['channel'] ?? ''),
            $channels
        )));

        return view('admin/logs/index', [
            'rows'    => $rows,
            'pager'   => $pager,
            'filters' => [
                'q'         => $q,
                'level'     => $level,
                'channel'   => $channel,
                'user_id'   => $userId,
                'user'      => $userSearch,
                'date_from' => $dateFrom,
                'date_to'   => $dateTo,
            ],
            'levels'   => ['info', 'warning', 'error'],
            'channels' => $channelOptions,
        ]);
    }
}
