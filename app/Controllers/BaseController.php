<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

abstract class BaseController extends Controller
{
    protected $request;

    protected $helpers = [
        'url',
        'text',
        'form',
        'catalog',
        'ui'
    ];

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
    }

    protected function ensureDealer()
    {
        if (!session('isLoggedIn') || !session('user_id')) {
            return redirect()->to(site_url('auth/login'));
        }

        $role = (string) session('role');
        if ($role !== 'dealer') {
            return redirect()->to(site_url('panel'));
        }

        return null;
    }

    protected function ensureUser()
    {
        if (!session('isLoggedIn') || !session('user_id')) {
            return redirect()->to(site_url('auth/login'));
        }

        $role = (string) session('role');
        if (!in_array($role, ['user', 'dealer', 'admin', 'root'], true)) {
            return redirect()->to(site_url('auth/login'));
        }

        return null;
    }

    protected function ensureAdmin()
    {
        if (!session('isLoggedIn') || !session('user_id')) {
            return redirect()->to(site_url('auth/login'));
        }

        $role = (string) session('role');
        if (!in_array($role, ['admin', 'root'], true)) {
            return $this->response->setStatusCode(403)->setBody('Forbidden');
        }

        return null;
    }
}
