<?php

namespace App\Queue;

interface JobHandlerInterface
{
    /**
     * İş başarılıysa true döndür.
     * Hata fırlatılırsa worker yakalayıp failed yapar.
     */
    public function handle(array $payload): bool;
}
