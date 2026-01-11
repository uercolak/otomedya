<?php

if (! function_exists('ui_status_badge')) {

    function ui_status_badge(string $status): array
    {
        $status = strtolower(trim($status));

        return match ($status) {
            'queued' => [
                'badge bg-secondary-subtle text-secondary border border-secondary-subtle',
                'Sırada',
                'bi-clock',
            ],
            'scheduled' => [
                'badge bg-info-subtle text-info border border-info-subtle',
                'Planlandı',
                'bi-calendar-event',
            ],
            'publishing' => [
                'badge bg-primary-subtle text-primary border border-primary-subtle',
                'Yayınlanıyor',
                'bi-arrow-repeat spin',
            ],
            'published' => [
                'badge bg-success-subtle text-success border border-success-subtle',
                'Yayınlandı',
                'bi-check-circle',
            ],
            'failed' => [
                'badge bg-danger-subtle text-danger border border-danger-subtle',
                'Hata',
                'bi-exclamation-triangle',
            ],
            'canceled' => [
                'badge bg-dark-subtle text-dark border border-dark-subtle',
                'İptal',
                'bi-x-circle',
            ],
            default => [
                'badge bg-light text-dark border',
                ucfirst($status ?: '—'),
                'bi-dot',
            ],
        };
    }
}

if (! function_exists('ui_job_status_badge')) {

    function ui_job_status_badge(string $status): array
    {
        $status = strtolower(trim($status));

        return match ($status) {
            'queued' => [
                'badge bg-secondary-subtle text-secondary border border-secondary-subtle',
                'Sırada',
            ],
            'running' => [
                'badge bg-warning-subtle text-warning border border-warning-subtle',
                'İşleniyor',
            ],
            'done' => [
                'badge bg-success-subtle text-success border border-success-subtle',
                'Tamamlandı',
            ],
            'failed' => [
                'badge bg-danger-subtle text-danger border border-danger-subtle',
                'Hata',
            ],
            'canceled' => [
                'badge bg-dark-subtle text-dark border border-dark-subtle',
                'İptal',
            ],
            default => [
                'badge bg-light text-dark border',
                ucfirst($status ?: '—'),
            ],
        };
    }
}

if (! function_exists('ui_meta_media_status_tr')) {

    function ui_meta_media_status_tr(?string $status): string
    {
        $status = strtolower(trim((string)$status));

        return match ($status) {
            'created'    => 'Oluşturuldu',
            'processing' => 'İşleniyor',
            'published'  => 'Yayınlandı',
            'failed'     => 'Hata',
            default      => ($status !== '' ? $status : '—'),
        };
    }
}

if (! function_exists('ui_meta_status_code_tr')) {

    function ui_meta_status_code_tr(?string $code): string
    {
        $code = strtoupper(trim((string)$code));

        return match ($code) {
            'IN_PROGRESS' => 'İşleniyor',
            'FINISHED'    => 'Bitti',
            'ERROR'       => 'Hata',
            'PUBLISHED'   => 'Yayınlandı',
            default       => ($code !== '' ? $code : '—'),
        };
    }
}

if (! function_exists('ui_dt')) {
    function ui_dt(?string $dt): string
    {
        if (!$dt) return '—';
        try {
            $d = new DateTime($dt);
            return $d->format('d.m.Y H:i');
        } catch (\Throwable $e) {
            return $dt;
        }
    }
}

if (! function_exists('ui_clip_btn')) {

    function ui_clip_btn(string $text, string $label = 'Kopyala'): string
    {
        $safe = esc($text);
        return '<button type="button" class="btn btn-sm btn-outline-secondary ms-2" data-clip="' . $safe . '">' . esc($label) . '</button>';
    }
}

if (! function_exists('ui_humanize_technical_note')) {

    function ui_humanize_technical_note(?string $text): string
    {
        $t = trim((string)$text);
        if ($t === '') return '';

        return match ($t) {
            'IN_PROGRESS' => 'İşleniyor (IN_PROGRESS)',
            'FINISHED'    => 'Bitti (FINISHED)',
            'ERROR'       => 'Hata (ERROR)',
            default       => (string)$text,
        };
    }
}
