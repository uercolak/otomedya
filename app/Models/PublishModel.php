<?php

namespace App\Models;

use CodeIgniter\Model;

class PublishModel extends Model
{
    protected $table         = 'publishes';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'job_id',
        'user_id',
        'platform',
        'content_kind',
        'media_kind',
        'account_id',
        'content_id',
        'status',
        'schedule_at',
        'idempotency_key',
        'remote_id',
        'published_at',
        'error',
        'meta_json',
    ];

    public const STATUS_QUEUED     = 'queued';
    public const STATUS_SCHEDULED  = 'scheduled';
    public const STATUS_PUBLISHING = 'publishing';
    public const STATUS_PUBLISHED  = 'published';
    public const STATUS_FAILED     = 'failed';
    public const STATUS_CANCELED   = 'canceled';

    public const STATUS_OPTIONS = [
        self::STATUS_QUEUED,
        self::STATUS_SCHEDULED,
        self::STATUS_PUBLISHING,
        self::STATUS_PUBLISHED,
        self::STATUS_FAILED,
        self::STATUS_CANCELED,
    ];

    public const STATUS_LABELS_TR = [
        self::STATUS_QUEUED     => 'Sırada',
        self::STATUS_SCHEDULED  => 'Planlandı',
        self::STATUS_PUBLISHING => 'Yayınlanıyor',
        self::STATUS_PUBLISHED  => 'Yayınlandı',
        self::STATUS_FAILED     => 'Hata',
        self::STATUS_CANCELED   => 'İptal Edildi',
    ];

    public const STATUS_BADGE_CLASSES = [
        self::STATUS_QUEUED     => 'secondary',
        self::STATUS_SCHEDULED  => 'info',
        self::STATUS_PUBLISHING => 'warning',
        self::STATUS_PUBLISHED  => 'success',
        self::STATUS_FAILED     => 'danger',
        self::STATUS_CANCELED   => 'dark',
    ];

    public static function label(string $status): string
    {
        return self::STATUS_LABELS_TR[$status] ?? $status;
    }

    /**
     * 🔹 Badge class getter
     */
    public static function badge(string $status): string
    {
        return self::STATUS_BADGE_CLASSES[$status] ?? 'secondary';
    }
}
