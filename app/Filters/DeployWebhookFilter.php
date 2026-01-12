<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class DeployWebhookFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Ek olarak sadece POST kabul
        if (strtoupper($request->getMethod()) !== 'POST') {
            return service('response')->setStatusCode(405)->setJSON(['ok' => false, 'error' => 'method_not_allowed']);
        }

        // CSRF bu endpoint’te devre dışı bırakılacak (Config/Filters.php’te)
        // Burada ekstra bir şey yapmak şart değil; asıl auth controller’da token ile.

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // no-op
    }
}
