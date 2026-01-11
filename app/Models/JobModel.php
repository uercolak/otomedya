<?php

namespace App\Models;

use CodeIgniter\Model;

class JobModel extends Model
{
    protected $table      = 'jobs';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'type','payload_json','status','priority','run_at',
        'locked_at','locked_by','attempts','max_attempts','last_error',
        'created_at','updated_at'
    ];

    protected $useTimestamps = false;

    public const STATUS_QUEUED      = 'queued';
    public const STATUS_RUNNING     = 'running';
    public const STATUS_DONE        = 'done';
    public const STATUS_FAILED      = 'failed';
    public const STATUS_CANCELED    = 'canceled';

    public const STATUS_OPTIONS = [
        self::STATUS_QUEUED,
        self::STATUS_RUNNING,
        self::STATUS_DONE,
        self::STATUS_FAILED,
        self::STATUS_CANCELED,
    ];

    public function now(): string
    {
        return date('Y-m-d H:i:s');
    }

    public function filtered(array $filters): self
        {
            $this->select('jobs.*');

            $q = trim((string)($filters['q'] ?? ''));
            if ($q !== '') {
                $this->groupStart()
                    ->like('jobs.type', $q)
                    ->orLike('jobs.payload_json', $q)
                    ->orLike('jobs.last_error', $q)
                ->groupEnd();
            }

            $status = trim((string)($filters['status'] ?? ''));
            if ($status !== '' && in_array($status, self::STATUS_OPTIONS, true)) {
                $this->where('jobs.status', $status);
            }

            $type = trim((string)($filters['type'] ?? ''));
            if ($type !== '') {
                $this->where('jobs.type', $type);
            }

            $locked = (string)($filters['locked'] ?? '');
            if ($locked === '1') {
                $this->where('jobs.locked_at IS NOT NULL', null, false);
            } elseif ($locked === '0') {
                $this->where('jobs.locked_at IS NULL', null, false);
            }

            $dateFrom = trim((string)($filters['date_from'] ?? ''));
            if ($dateFrom !== '') {
                $this->where('jobs.created_at >=', $dateFrom . ' 00:00:00');
            }

            $dateTo = trim((string)($filters['date_to'] ?? ''));
            if ($dateTo !== '') {
                $this->where('jobs.created_at <=', $dateTo . ' 23:59:59');
            }

            return $this->orderBy('jobs.id', 'DESC');
        }
}
