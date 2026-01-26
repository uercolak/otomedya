<?php

namespace App\Controllers;

class PagesController extends BaseController
{
    public function contact()
    {
        return view('pages/contact');
    }

    public function contactPost()
    {
        // 1) Honeypot (botlar takılır)
        if ($this->request->getPost('website')) {
            return redirect()->back();
        }

        // 2) reCAPTCHA v3 doğrula
        $token  = (string) $this->request->getPost('recaptcha_token');
        $secret = (string) getenv('RECAPTCHA_SECRET');

        if ($token === '' || $secret === '') {
            return redirect()->back()
                ->with('error', 'Güvenlik doğrulaması eksik.')
                ->withInput();
        }

        $client = \Config\Services::curlrequest();

        try {
            $resp = $client->post('https://www.google.com/recaptcha/api/siteverify', [
                'form_params' => [
                    'secret'   => $secret,
                    'response' => $token,
                    'remoteip' => $this->request->getIPAddress(),
                ],
                'timeout' => 4,
            ]);

            $result = json_decode($resp->getBody(), true);
        } catch (\Throwable $e) {
            log_message('error', 'reCAPTCHA verify error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Güvenlik doğrulaması şu an yapılamıyor. Lütfen tekrar deneyin.')
                ->withInput();
        }

        // 3) success + score + action kontrolü
        $score  = (float) ($result['score'] ?? 0);
        $action = (string) ($result['action'] ?? '');

        if (empty($result['success']) || $score < 0.5 || $action !== 'contact') {
            return redirect()->back()
                ->with('error', 'Güvenlik doğrulaması başarısız.')
                ->withInput();
        }

        // 4) (Opsiyonel) Basit validasyon
        $name    = trim((string) $this->request->getPost('name'));
        $email   = trim((string) $this->request->getPost('email'));
        $message = trim((string) $this->request->getPost('message'));

        if ($email === '' || $message === '') {
            return redirect()->back()
                ->with('error', 'Lütfen e-posta ve mesaj alanlarını doldurun.')
                ->withInput();
        }

        // 5) Logla (artık temiz)
        log_message('info', 'Contact form submitted: ' . json_encode([
            'name' => $name,
            'email' => $email,
            'message_len' => mb_strlen($message),
            'ip' => $this->request->getIPAddress(),
            'score' => $score,
        ]));

        return redirect()->to(base_url('/contact'))
            ->with('success', 'Mesajınız alınmıştır. En kısa sürede dönüş yapacağız.');
    }
}
