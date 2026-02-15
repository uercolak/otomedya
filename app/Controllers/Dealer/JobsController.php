<?php

namespace App\Controllers\Dealer;

use App\Controllers\BaseController;
use App\Models\JobModel;
use App\Models\JobAttemptModel;

class JobsController extends BaseController
{
    private function db()
    {
        return \Config\Database::connect();
    }

    private function dealerUserIds(): array
    {
        $dealerId = (int) session('user_id');
        $tenantId = (int) session('tenant_id');
        if ($dealerId <= 0 || $tenantId <= 0) return [];

        $rows = $this->db()->table('users')
            ->select('id')
            ->where('tenant_id', $tenantId)
            ->where('created_by', $dealerId)
            ->get()->getResultArray();

        return array_map(fn($r) => (int)$r['id'], $rows);
    }

    private function canAccessJob(int $jobId): bool
    {
        $ids = $this->dealerUserIds();
        if (empty($ids)) return false;

        // job -> publishes üzerinden scope
        $row = $this->db()->table('publishes')
            ->select('id')
            ->where('job_id', $jobId)
            ->whereIn('user_id', $ids)
            ->get()->getRowArray();

        return (bool)$row;
    }

    public function index()
    {
        // Admin jobs/index’i model->filtered ile yapıyor ama dealer scope gerekiyor.
        // En sağlamı: jobs’u publishes ile joinleyip sayfalayalım.

        $db = $this->db();
        $ids = $this->dealerUserIds();

        $filters = [
            'q'         => trim((string)($this->request->getGet('q') ?? '')),
            'status'    => trim((string)($this->request->getGet('status') ?? '')),
            'type'      => trim((string)($this->request->getGet('type') ?? '')),
            'locked'    => trim((string)($this->request->getGet('locked') ?? '')),
            'date_from' => trim((string)($this->request->getGet('date_from') ?? '')),
            'date_to'   => trim((string)($this->request->getGet('date_to') ?? '')),
        ];

        if (empty($ids)) {
            return view('dealer/jobs/index', [
                'rows' => [],
                'pager' => null,
                'filters' => $filters,
                'statusOptions' => JobModel::STATUS_OPTIONS,
                'typeOptions' => [],
            ]);
        }

        $builder = $db->table('jobs j')
            ->select('j.*')
            ->select('p.id as publish_id, p.platform as publish_platform, p.status as publish_status')
            ->join('publishes p', 'p.job_id = j.id', 'inner')
            ->whereIn('p.user_id', $ids);

        if ($filters['q'] !== '') {
            $builder->groupStart()
                ->like('j.id', $filters['q'])
                ->orLike('j.type', $filters['q'])
                ->orLike('j.last_error', $filters['q'])
                ->orLike('j.payload_json', $filters['q'])
            ->groupEnd();
        }

        if ($filters['status'] !== '') $builder->where('j.status', $filters['status']);
        if ($filters['type'] !== '')   $builder->where('j.type', $filters['type']);

        if ($filters['locked'] === '1') $builder->where('j.locked_at IS NOT NULL', null, false);
        if ($filters['locked'] === '0') $builder->where('j.locked_at IS NULL', null, false);

        if ($filters['date_from'] !== '') $builder->where('j.created_at >=', $filters['date_from'] . ' 00:00:00');
        if ($filters['date_to'] !== '')   $builder->where('j.created_at <=', $filters['date_to'] . ' 23:59:59');

        $builder->orderBy('j.id', 'DESC');

        // basit pagination
        $perPage = 15;
        $page = (int)($this->request->getGet('page') ?? 1);
        $offset = ($page - 1) * $perPage;

        $total = (clone $builder)->countAllResults(false);
        $rows  = $builder->limit($perPage, $offset)->get()->getResultArray();

        // type options (dealer scope)
        $typeRows = $db->table('jobs j')
            ->select('j.type')->distinct()
            ->join('publishes p', 'p.job_id = j.id', 'inner')
            ->whereIn('p.user_id', $ids)
            ->orderBy('j.type', 'ASC')->get()->getResultArray();

        $typeOptions = array_values(array_filter(array_map(fn($r)=> (string)$r['type'], $typeRows)));

        return view('dealer/jobs/index', [
            'rows' => $rows,
            'filters' => $filters,
            'statusOptions' => JobModel::STATUS_OPTIONS,
            'typeOptions' => $typeOptions,
            'pagination' => [
                'total' => $total,
                'perPage' => $perPage,
                'page' => $page,
                'pages' => (int)ceil($total / $perPage),
            ],
        ]);
    }

    public function show(int $id)
    {
        if (!$this->canAccessJob($id)) {
            return redirect()->to(site_url('dealer/jobs'))->with('error', 'Bu işe erişimin yok.');
        }

        $jobs = new JobModel();
        $job  = $jobs->find($id);

        if (!$job) {
            return redirect()->to(site_url('dealer/jobs'))->with('error', 'İş bulunamadı.');
        }

        $attempts = (new JobAttemptModel())
            ->where('job_id', $id)
            ->orderBy('attempt_no', 'DESC')
            ->findAll();

        return view('dealer/jobs/show', [
            'job' => $job,
            'attempts' => $attempts,
        ]);
    }

    public function retry(int $id)
    {
        if (!$this->canAccessJob($id)) {
            return redirect()->to(site_url('dealer/jobs'))->with('error', 'Bu işe erişimin yok.');
        }

        $jobs = new JobModel();
        $job  = $jobs->find($id);
        if (!$job) return redirect()->to(site_url('dealer/jobs'))->with('error', 'İş bulunamadı.');

        $now = $jobs->now();

        $jobs->update($id, [
            'status'     => 'queued',
            'run_at'     => $now,
            'locked_at'  => null,
            'locked_by'  => null,
            'updated_at' => $now,
        ]);

        return redirect()->to(site_url('dealer/jobs/' . $id))->with('success', 'İş tekrar kuyruğa alındı (queued).');
    }

    public function reset(int $id)
    {
        if (!$this->canAccessJob($id)) {
            return redirect()->to(site_url('dealer/jobs'))->with('error', 'Bu işe erişimin yok.');
        }

        $jobs = new JobModel();
        $job  = $jobs->find($id);
        if (!$job) return redirect()->to(site_url('dealer/jobs'))->with('error', 'İş bulunamadı.');

        $now = $jobs->now();

        $jobs->update($id, [
            'status'     => 'queued',
            'attempts'   => 0,
            'last_error' => null,
            'run_at'     => $now,
            'locked_at'  => null,
            'locked_by'  => null,
            'updated_at' => $now,
        ]);

        return redirect()->to(site_url('dealer/jobs/' . $id))->with('success', 'İş sıfırlandı ve kuyruğa alındı.');
    }

    public function cancel(int $id)
    {
        if (!$this->canAccessJob($id)) {
            return redirect()->to(site_url('dealer/jobs'))->with('error', 'Bu işe erişimin yok.');
        }

        $jobs = new JobModel();
        $job  = $jobs->find($id);
        if (!$job) return redirect()->to(site_url('dealer/jobs'))->with('error', 'İş bulunamadı.');

        $now = $jobs->now();

        $jobs->update($id, [
            'status'     => 'canceled',
            'locked_at'  => null,
            'locked_by'  => null,
            'updated_at' => $now,
        ]);

        return redirect()->to(site_url('dealer/jobs/' . $id))->with('success', 'İş iptal edildi (canceled).');
    }
}
