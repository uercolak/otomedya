<?php

namespace App\Services;

use App\Models\JobModel;

class QueueService
{
    public function __construct(private readonly JobModel $jobs = new JobModel())
    {
    }

    public function dispatch(
        string $type,
        array $payload,
        ?string $runAt = null,
        int $priority = 100,
        int $maxAttempts = 3
    ): int {
        $now = date('Y-m-d H:i:s');

        $id = $this->jobs->insert([
            'type'         => $type,
            'payload_json' => json_encode($payload, JSON_UNESCAPED_UNICODE),
            'status'       => 'queued',
            'priority'     => $priority,
            'run_at'       => $runAt ?? $now,
            'locked_at'    => null,
            'locked_by'    => null,
            'attempts'     => 0,
            'max_attempts' => $maxAttempts,
            'last_error'   => null,
            'created_at'   => $now,
            'updated_at'   => $now,
        ], true);

        return (int)$id;
    }
}
