<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class DealerFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        if (! $session->get('is_logged_in')) {
            return redirect()->to(base_url('auth/login'));
        }

        $role = $session->get('user_role') ?? 'user';

        if ($role === 'admin') {
            $role = 'root';
        }

        if ($role !== 'dealer') {
            if ($role === 'root') {
                return redirect()->to(base_url('admin'))
                    ->with('error', 'Bu sayfaya erişim yetkiniz yok.');
            }

            return redirect()->to(base_url('panel'))
                ->with('error', 'Bu sayfaya erişim yetkiniz yok.');
        }

        if (! $session->get('tenant_id')) {
            $session->destroy();
            return redirect()->to(base_url('auth/login'))
                ->with('error', 'Oturum doğrulanamadı. Lütfen tekrar giriş yapın.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
