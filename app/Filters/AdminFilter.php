<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class AdminFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        if (! $session->get('is_logged_in')) {
            return redirect()->to(base_url('auth/login'));
        }

        if ($session->get('user_role') !== 'admin') {

            return redirect()->to(base_url('panel'))
                            ->with('error', 'Bu sayfaya erişim yetkiniz yok.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
