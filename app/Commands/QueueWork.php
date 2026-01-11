<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use Config\Database;
use App\Models\JobModel;
use App\Models\JobAttemptModel;
use App\Services\LogService;
use App\Queue\HandlerRegistry;

class QueueWork extends BaseCommand
{
    protected $group       = 'Queue';
    protected $name        = 'queue:work';
    protected $description = 'Process queued jobs (DB-backed).';

    protected $usage       = 'queue:work [--once] [--sleep=2] [--limit=50]';
    protected $options     = [
        '--once'  => 'Run only one iteration then exit (best for cron).',
        '--sleep' => 'Seconds to sleep when no job found (default: 2).',
        '--limit' => 'Max jobs to process in one run (default: 50).',
    ];

    // 🔒 Worker kilidi için TTL (worker ölürse job tekrar alınabilsin)
    private const LOCK_TTL_SECONDS = 600; // 10 dk

    public function run(array $params)
    {
        $once  = array_key_exists('once', $params);
        $sleep = (int)($params['sleep'] ?? 2);
        $limit = (int)($params['limit'] ?? 50);

        $workerId = gethostname() . ':' . getmypid();

        $jobs      = new JobModel();
        $attempts  = new JobAttemptModel();
        $logger    = new LogService();
        $registry  = new HandlerRegistry();

        $processed = 0;

        while (true) {
            $job = $this->reserveNextJob($jobs, $workerId);

            if (!$job) {
                if ($once) return;
                sleep(max(1, $sleep));
                continue;
            }

            $processed++;
            $jobId = (int)$job['id'];

            $logger->event('info', 'queue', 'job.reserved', [
                'job_id' => $jobId,
                'type'   => $job['type'],
                'worker' => $workerId,
                'pid'    => getmypid(),
            ]);

            $attemptNo = ((int)$job['attempts']) + 1;

            // Attempt kaydı aç (started)
            $attemptId = $attempts->insert([
                'job_id'        => $jobId,
                'attempt_no'    => $attemptNo,
                'status'        => 'started',
                'started_at'    => date('Y-m-d H:i:s'),
                'finished_at'   => null,
                'error'         => null,
                'response_json' => null,
                'created_at'    => date('Y-m-d H:i:s'),
            ], true);

            try {
                $payload = json_decode($job['payload_json'], true) ?: [];

                $handler = $registry->resolve($job['type']);
                $ok = $handler->handle($payload);

                if ($ok !== true) {
                    throw new \RuntimeException('Job handler returned false');
                }

                // attempt success
                $attempts->update($attemptId, [
                    'status'      => 'success',
                    'finished_at' => date('Y-m-d H:i:s'),
                ]);

                // job done
                $jobs->update($jobId, [
                    'status'     => 'done',
                    'locked_at'  => null,
                    'locked_by'  => null,
                    'updated_at' => $jobs->now(),
                ]);

                $logger->event('info', 'queue', 'job.succeeded', [
                    'job_id' => $jobId,
                    'type'   => $job['type'],
                    'deneme' => $attemptNo,
                    'worker' => $workerId,
                    'pid'    => getmypid(),
                ]);

            } catch (\Throwable $e) {
                $err = $e->getMessage();

                // attempt failed
                $attempts->update($attemptId, [
                    'status'      => 'failed',
                    'finished_at' => date('Y-m-d H:i:s'),
                    'error'       => $e->getMessage() . "\n" . $e->getTraceAsString(),
                ]);

                // attempts++ ve retry logic
                $currentAttempts = (int)$job['attempts'] + 1;
                $maxAttempts     = (int)$job['max_attempts'];

                // 🔁 Retry olacaksa queued'a dön
                $nextStatus = ($currentAttempts >= $maxAttempts) ? 'failed' : 'queued';
                $nextRunAt  = ($nextStatus === 'queued')
                    ? date('Y-m-d H:i:s', time() + $this->backoffSeconds($currentAttempts))
                    : $job['run_at'];

                    try {
                        $payload = json_decode($job['payload_json'] ?? '', true) ?: [];
                        $publishId = (int)($payload['publish_id'] ?? 0);

                        if ($publishId > 0) {
                            $db = \Config\Database::connect();

                            // Job retry olacaksa publish'i "queued"e geri al, son denemede "failed" yap
                            $currentAttempts = (int)$job['attempts'] + 1;
                            $maxAttempts     = (int)$job['max_attempts'];
                            $publishNextStatus = ($currentAttempts >= $maxAttempts) ? 'failed' : 'queued';

                            $db->table('publishes')
                                ->where('id', $publishId)
                                ->update([
                                    'status'     => $publishNextStatus,
                                    'error'      => $e->getMessage(),
                                    'updated_at' => date('Y-m-d H:i:s'),
                                ]);
                        }
                    } catch (\Throwable $ignored) {
                        // best-effort
                    }

                $jobs->update($jobId, [
                    'status'     => $nextStatus,
                    'attempts'   => $currentAttempts,
                    'last_error' => $err,
                    'locked_at'  => null,
                    'locked_by'  => null,
                    'run_at'     => $nextRunAt,
                    'updated_at' => $jobs->now(),
                ]);

                $logger->event('error', 'queue', 'job.failed', [
                    'job_id'        => $jobId,
                    'type'          => $job['type'],
                    'deneme'        => $currentAttempts,
                    'max_deneme'    => $maxAttempts,
                    'sonraki_durum' => $nextStatus,
                    'sonraki_zaman' => $nextRunAt,
                    'hata'          => $err,
                    'worker'        => $workerId,
                    'pid'           => getmypid(),
                ]);
            }

            if ($once) return;
            if ($processed >= $limit) return;
        }
    }

    private function backoffSeconds(int $attemptNo): int
    {
        // 1->10s, 2->30s, 3->60s, 4+->120s
        return match (true) {
            $attemptNo <= 1 => 10,
            $attemptNo === 2 => 30,
            $attemptNo === 3 => 60,
            default => 120,
        };
    }

    private function reserveNextJob(JobModel $jobs, string $workerId): ?array
    {
        $db  = Database::connect();
        $now = date('Y-m-d H:i:s');

        $staleCutoff = date('Y-m-d H:i:s', time() - self::LOCK_TTL_SECONDS);

        // 1) aday job al (queued + run_at <= now + lock yok veya stale)
        $row = $db->table('jobs')
            ->select('*')
            ->where('status', 'queued')
            ->where('run_at <=', $now)
            ->groupStart()
                ->where('locked_at IS NULL', null, false)
                ->orWhere('locked_at <', $staleCutoff)
            ->groupEnd()
            ->orderBy('priority', 'ASC')
            ->orderBy('run_at', 'ASC')
            ->get(1)
            ->getRowArray();

        if (!$row) return null;

        // 2) atomik kilitleme (race condition önler) + TTL koşulu
        $affected = $db->table('jobs')
            ->where('id', $row['id'])
            ->where('status', 'queued')
            ->groupStart()
                ->where('locked_at IS NULL', null, false)
                ->orWhere('locked_at <', $staleCutoff)
            ->groupEnd()
            ->update([
                'status'     => 'running',
                'locked_at'  => $now,
                'locked_by'  => $workerId,
                'updated_at' => $now,
            ]);

        if (!$affected) return null;

        // 3) güncel halini dön
        return $db->table('jobs')->where('id', $row['id'])->get(1)->getRowArray();
    }
}
