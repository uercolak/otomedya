<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class DeployWebhookController extends Controller
{
    
    public function github()
    {

    log_message('info', 'DEPLOY WEBHOOK HIT');
        // 1) Secret kontrol
        $secret = getenv('GITHUB_WEBHOOK_SECRET');
        if (!$secret) {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Webhook secret missing']);
        }

        // 2) GitHub event kontrol
        $event = $this->request->getHeaderLine('X-GitHub-Event');
        if ($event !== 'push') {
            return $this->response->setStatusCode(202)->setJSON(['ok' => true, 'ignored' => 'not push']);
        }

        // 3) Raw body + signature doğrulama
        $raw = $this->request->getBody(); // ham body
        $sig = $this->request->getHeaderLine('X-Hub-Signature-256'); // "sha256=...."

        if (!$sig || !str_starts_with($sig, 'sha256=')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Missing signature']);
        }

        $their = substr($sig, 7);
        $ours  = hash_hmac('sha256', $raw, $secret);

        if (!hash_equals($ours, $their)) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Invalid signature']);
        }

        // 4) Sadece main branch push ise çalıştır
        $payload = json_decode($raw, true);
        $ref = $payload['ref'] ?? '';
        if ($ref !== 'refs/heads/main') {
            return $this->response->setStatusCode(202)->setJSON(['ok' => true, 'ignored' => $ref]);
        }

        // 5) systemd deploy service tetikle (oneshot)
        // www-data kullanıcısına sadece bu komut için sudo izni vereceğiz.
        $out = trim((string) shell_exec('sudo -n /bin/systemctl start otomedya-deploy.service 2>&1'));

        // 6) Logla (debug için)
        log_message('info', 'Webhook deploy triggered. ref={ref} output={out}', [
            'ref' => $ref,
            'out' => $out,
        ]);

        return $this->response->setStatusCode(202)->setJSON([
            'ok' => true,
            'triggered' => true,
        ]);
    }
}
