<?php

namespace App\Services;

class LogFormatter
{
    public function title(array $logRow): string
    {
        $msg = (string)($logRow['message'] ?? '');
        $ctx = $this->context($logRow);

        // event key öncelikli: context['_event'] varsa onu al
        $event = (string)($ctx['_event'] ?? $msg);

        // event key yoksa veya "normal cümle" ise direkt message göster
        if ($event === '' || str_contains($event, ' ')) {
            return $msg ?: '—';
        }

        // publish eventleri: başlığı zenginleştir
        if (str_starts_with($event, 'publish.')) {
            $platform = (string)($ctx['platform'] ?? '');
            $accId    = (string)($ctx['account_id'] ?? '');
            $content  = (string)($ctx['content_id'] ?? '');

            $base = log_event_label($event);

            $parts = [];
            if ($platform) $parts[] = strtoupper($platform);
            if ($accId)    $parts[] = 'Hesap #' . $accId;
            if ($content)  $parts[] = 'İçerik #' . $content;

            if ($parts) {
                $base = implode(' · ', $parts) . ' · ' . $base;
            }

            // hata varsa kısa ek
            $err = (string)($ctx['hata'] ?? $ctx['error'] ?? '');
            if ($err && $event === 'publish.failed') {
                $base .= ' — ' . mb_substr($err, 0, 120);
            }

            return $base;
        }

        // default: job.* vb.
        $base = log_event_label($event);

        $type = (string)($ctx['type'] ?? $ctx['job_type'] ?? '');
        if ($type !== '') {
            $base = job_type_label($type) . ' · ' . $base;
        }

        $err = (string)($ctx['hata'] ?? $ctx['error'] ?? '');
        if ($err && ($event === 'job.failed')) {
            $base .= ' — ' . mb_substr($err, 0, 120);
        }

        return $base;
    }

    public function human(array $logRow): string
    {
        $level   = (string)($logRow['level'] ?? '');
        $channel = (string)($logRow['channel'] ?? '');
        $ctx     = $this->context($logRow);

        $event = (string)($ctx['_event'] ?? '');

        // publish eventleri için daha açıklayıcı açıklama
        if (str_starts_with($event, 'publish.')) {
            return match ($event) {
                'publish.started' =>
                    'Paylaşım işlemi başlatıldı. Sistem içeriği ilgili platforma göndermek için hazırlık yapıyor.',
                'publish.succeeded' =>
                    'Paylaşım tamamlandı. Platformdan bir “yayın id” (remote_id) alındı ve kayıt güncellendi.',
                'publish.failed' =>
                    'Paylaşım başarısız oldu. Sistem otomatik yeniden deneme yapabilir; gerekirse teknik detaylardan hatayı inceleyebilirsiniz.',
                default =>
                    'Paylaşım süreciyle ilgili bir kayıt oluştu.',
            };
        }

        // queue genel açıklama
        if ($channel === 'queue') {
            return match ($level) {
                'error'   => 'Arka planda bir işlem hata verdi. Gerekirse “Teknik Detaylar” bölümünden inceleyebilirsiniz.',
                'warning' => 'Arka planda işlem çalıştı ancak dikkat edilmesi gereken bir durum var.',
                default   => 'Arka planda bir işlem gerçekleşti (sıraya alma / çalıştırma / tamamlanma).',
            };
        }

        // diğer kanallar
        return match ($level) {
            'error'   => 'Sistemde bir hata kaydı oluştu. Detaylar teknik bölümde.',
            'warning' => 'Sistemde dikkat edilmesi gereken bir durum kaydı oluştu.',
            default   => 'Sistemde bilgilendirme amaçlı bir kayıt oluştu.',
        };
    }

    public function context(array $logRow): array
    {
        $raw = (string)($logRow['context_json'] ?? '');
        if (!$raw) return [];

        try {
            $obj = json_decode($raw, true);
            return is_array($obj) ? $obj : [];
        } catch (\Throwable $e) {
            return [];
        }
    }
}
