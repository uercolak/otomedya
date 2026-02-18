<?php

namespace App\Controllers\Dealer;

use App\Controllers\BaseController;
use App\Models\JobAttemptModel;
use App\Models\JobModel;
use Config\Database;

class JobsController extends BaseController
{
    private function db()
    {
        return Database::connect();
    }

    /**
     * Bayi id (login olan bayi user id)
     */
    private function dealerId(): int
    {
        return (int)(session('user_id') ?? 0);
    }

    /**
     * Bu job gerçekten bu bayiye mi ait? (publish->user created_by = dealerId)
     */
    private function getDealerJobRow(int $jobId): ?array
    {
        $dealerId = $this->dealerId();
        if ($dealerId <= 0) return null;

        $db = $this->db();

        return $db->table('jobs j')
            ->select('j.*')
            ->select('p.id as publish_id, p.platform as publish_platform, p.status as publish_status, p.user_id as publish_user_id')
            ->select('u.name as user_name, u.email as user_email, u.created_by as user_created_by')
            ->join('publishes p', 'p.job_id = j.id', 'left')
            ->join('users u', 'u.id = p.user_id', 'left')
            ->where('j.id', $jobId)
            ->where('u.created_by', $dealerId)
            ->get()
            ->getRowArray();
    }

    public function index()
    {
        $dealerId = $this->dealerId();
        if ($dealerId <= 0) {
            return redirect()->to(site_url('auth/login'));
        }

        helper('catalog');

        $filters = [
            'q'         => trim((string)($this->request->getGet('q') ?? '')),
            'status'    => trim((string)($this->request->getGet('status') ?? '')),
            'type'      => trim((string)($this->request->getGet('type') ?? '')),
            'locked'    => trim((string)($this->request->getGet('locked') ?? '')), // '1' | '0' | ''
            'date_from' => trim((string)($this->request->getGet('date_from') ?? '')),
            'date_to'   => trim((string)($this->request->getGet('date_to') ?? '')),
        ];

        $db = $this->db();

        // Sadece bu bayinin oluşturduğu user'ların publishlerine bağlı joblar:
        $builder = $db->table('jobs j')
            ->select('j.*')
            ->select('p.id as publish_id, p.platform as publish_platform, p.status as publish_status')
            ->select('u.name as user_name, u.email as user_email')
            ->join('publishes p', 'p.job_id = j.id', 'left')
            ->join('users u', 'u.id = p.user_id', 'left')
            ->where('u.created_by', $dealerId);

        if ($filters['q'] !== '') {
            $q = $filters['q'];
            $builder->groupStart()
                ->like('j.type', $q)
                ->orLike('j.payload_json', $q)
                ->orLike('j.last_error', $q)
            ->groupEnd();
        }

        if ($filters['status'] !== '') $builder->where('j.status', $filters['status']);
        if ($filters['type'] !== '')   $builder->where('j.type', $filters['type']);

        if ($filters['locked'] === '1') {
            $builder->where('j.locked_at IS NOT NULL', null, false);
        } elseif ($filters['locked'] === '0') {
            $builder->where('j.locked_at IS NULL', null, false);
        }

        if ($filters['date_from'] !== '') $builder->where('j.created_at >=', $filters['date_from'] . ' 00:00:00');
        if ($filters['date_to'] !== '')   $builder->where('j.created_at <=', $filters['date_to'] . ' 23:59:59');

        $builder->orderBy('j.id', 'DESC');

        // Manual pagination (admin publishes gibi)
        $perPage = 15;
        $page = (int)($this->request->getGet('page') ?? 1);
        if ($page < 1) $page = 1;
        $offset = ($page - 1) * $perPage;

        $total = (clone $builder)->countAllResults(false);
        $rows  = $builder->limit($perPage, $offset)->get()->getResultArray();

        // typeOptions (sadece bu bayi scope’unda görünen job tipleri)
        $types = $db->table('jobs j')
            ->select('j.type')
            ->distinct()
            ->join('publishes p', 'p.job_id = j.id', 'left')
            ->join('users u', 'u.id = p.user_id', 'left')
            ->where('u.created_by', $dealerId)
            ->orderBy('j.type', 'ASC')
            ->get()->getResultArray();

        $typeOptions = array_values(array_filter(array_map(fn($r) => (string)($r['type'] ?? ''), $types)));

        return view('dealer/jobs/index', [
            'pageTitle'     => 'Arka Plan İşleri',
            'rows'          => $rows,
            'filters'       => $filters,
            'statusOptions' => JobModel::STATUS_OPTIONS,
            'typeOptions'   => $typeOptions,
            'pagination'    => [
                'total'   => $total,
                'perPage' => $perPage,
                'page'    => $page,
                'pages'   => (int)ceil($total / $perPage),
            ],
        ]);
    }

    public function show(int $id)
    {
        $dealerId = $this->dealerId();
        if ($dealerId <= 0) return redirect()->to(site_url('auth/login'));

        helper('catalog');

        $job = $this->getDealerJobRow($id);
        if (!$job) {
            return redirect()->to(site_url('dealer/jobs'))->with('error', 'İş bulunamadı veya yetkiniz yok.');
        }

        $attempts = (new JobAttemptModel())
            ->where('job_id', $id)
            ->orderBy('attempt_no', 'DESC')
            ->findAll();

        return view('dealer/jobs/show', [
            'pageTitle' => 'İş Detayı',
            'job'       => $job,
            'attempts'  => $attempts,
        ]);
    }

    public function retry(int $id)
    {
        $job = $this->getDealerJobRow($id);
        if (!$job) return redirect()->to(site_url('dealer/jobs'))->with('error', 'Yetkiniz yok.');

        $jobs = new JobModel();
        $now  = $jobs->now();

        $jobs->update($id, [
            'status'     => 'queued',
            'run_at'     => $now,
            'locked_at'  => null,
            'locked_by'  => null,
            'updated_at' => $now,
        ]);

        // publish senkron (best-effort)
        $pubId = (int)($job['publish_id'] ?? 0);
        if ($pubId > 0) {
            $this->db()->table('publishes')->where('id', $pubId)->update([
                'status'     => 'queued',
                'error'      => null,
                'updated_at' => $now,
            ]);
        }

        return redirect()->to(site_url('dealer/jobs/' . $id))->with('success', 'İş tekrar kuyruğa alındı (queued).');
    }

    public function reset(int $id)
    {
        $job = $this->getDealerJobRow($id);
        if (!$job) return redirect()->to(site_url('dealer/jobs'))->with('error', 'Yetkiniz yok.');

        $jobs = new JobModel();
        $now  = $jobs->now();

        $jobs->update($id, [
            'status'     => 'queued',
            'attempts'   => 0,
            'last_error' => null,
            'run_at'     => $now,
            'locked_at'  => null,
            'locked_by'  => null,
            'updated_at' => $now,
        ]);

        $pubId = (int)($job['publish_id'] ?? 0);
        if ($pubId > 0) {
            $this->db()->table('publishes')->where('id', $pubId)->update([
                'status'     => 'queued',
                'error'      => null,
                'updated_at' => $now,
            ]);
        }

        return redirect()->to(site_url('dealer/jobs/' . $id))->with('success', 'İş sıfırlandı ve kuyruğa alındı.');
    }

    public function cancel(int $id)
    {
        $job = $this->getDealerJobRow($id);
        if (!$job) return redirect()->to(site_url('dealer/jobs'))->with('error', 'Yetkiniz yok.');

        $jobs = new JobModel();
        $now  = $jobs->now();

        $jobs->update($id, [
            'status'     => 'canceled',
            'locked_at'  => null,
            'locked_by'  => null,
            'updated_at' => $now,
        ]);

        $pubId = (int)($job['publish_id'] ?? 0);
        if ($pubId > 0) {
            $this->db()->table('publishes')->where('id', $pubId)->update([
                'status'     => 'canceled',
                'updated_at' => $now,
            ]);
        }

        return redirect()->to(site_url('dealer/jobs/' . $id))->with('success', 'İş iptal edildi (canceled).');
    }
}
