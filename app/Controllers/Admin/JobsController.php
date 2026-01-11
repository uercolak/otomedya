<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\JobModel;
use App\Models\JobAttemptModel;

class JobsController extends BaseController
{
    public function index()
    {
        $filters = [
            'q'         => trim((string)($this->request->getGet('q') ?? '')),
            'status'    => trim((string)($this->request->getGet('status') ?? '')),
            'type'      => trim((string)($this->request->getGet('type') ?? '')),
            'locked'    => trim((string)($this->request->getGet('locked') ?? '')), // '1' | '0' | ''
            'date_from' => trim((string)($this->request->getGet('date_from') ?? '')),
            'date_to'   => trim((string)($this->request->getGet('date_to') ?? '')),
        ];

        $perPage = 15;

        $jobsQueryModel = new JobModel();
        $rows  = $jobsQueryModel->filtered($filters)->paginate($perPage, 'jobs');
        $pager = $jobsQueryModel->pager;
        $pager->setPath(site_url('admin/jobs'));

        $typeOptions = (new JobModel())
            ->select('type')
            ->distinct()
            ->orderBy('type', 'ASC')
            ->findAll();

        $typeOptions = array_values(array_filter(array_map(
            fn($r) => (string)($r['type'] ?? ''),
            $typeOptions
        )));

        return view('admin/jobs/index', [
            'rows'          => $rows,
            'pager'         => $pager,
            'filters'       => $filters,
            'statusOptions' => JobModel::STATUS_OPTIONS,
            'typeOptions'   => $typeOptions,
        ]);
    }

    public function show(int $id)
    {
        $jobs = new JobModel();
        $job  = $jobs->find($id);

        if (!$job) {
            return redirect()->to(site_url('admin/jobs'))->with('error', 'İş bulunamadı.');
        }

        $attempts = (new JobAttemptModel())
            ->where('job_id', $id)
            ->orderBy('attempt_no', 'DESC')
            ->findAll();

        return view('admin/jobs/show', [
            'job'      => $job,
            'attempts' => $attempts,
        ]);
    }

    public function retry(int $id)
    {
        $jobs = new JobModel();
        $job  = $jobs->find($id);

        if (!$job) {
            return redirect()->to(site_url('admin/jobs'))->with('error', 'İş bulunamadı.');
        }

        $now = $jobs->now();

        $jobs->update($id, [
            'status'     => 'queued',
            'run_at'     => $now,
            'locked_at'  => null,
            'locked_by'  => null,
            'updated_at' => $now,
        ]);

        return redirect()->to(site_url('admin/jobs/' . $id))->with('success', 'İş tekrar kuyruğa alındı (queued).');
    }

    public function reset(int $id)
    {
        $jobs = new JobModel();
        $job  = $jobs->find($id);

        if (!$job) {
            return redirect()->to(site_url('admin/jobs'))->with('error', 'İş bulunamadı.');
        }

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

        return redirect()->to(site_url('admin/jobs/' . $id))->with('success', 'İş sıfırlandı ve kuyruğa alındı.');
    }

    public function cancel(int $id)
    {
        $jobs = new JobModel();
        $job  = $jobs->find($id);

        if (!$job) {
            return redirect()->to(site_url('admin/jobs'))->with('error', 'İş bulunamadı.');
        }

        $now = $jobs->now();

        $jobs->update($id, [
            'status'     => 'canceled',
            'locked_at'  => null,
            'locked_by'  => null,
            'updated_at' => $now,
        ]);

        return redirect()->to(site_url('admin/jobs/' . $id))->with('success', 'İş iptal edildi (canceled).');
    }
}
