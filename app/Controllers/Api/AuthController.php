<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\UserModel;

class AuthController extends BaseController
{
    /**
     * Public register KAPALI.
     * İleride admin üzerinden kullanıcı açılacak.
     */
    public function register()
    {
        return $this->response
            ->setStatusCode(403)
            ->setJSON([
                'status'  => 'error',
                'message' => 'Public register kapalı. Kullanıcı oluşturma admin panelinden yapılır.'
            ]);
    }

    public function login()
    {
        $userModel = new UserModel();

        $data = $this->request->getJSON(true) ?? [];

        $email    = trim($data['email'] ?? '');
        $password = (string)($data['password'] ?? '');

        if ($email === '' || $password === '') {
            return $this->response
                ->setStatusCode(400)
                ->setJSON([
                    'status'  => 'error',
                    'message' => 'email ve password zorunludur.'
                ]);
        }

        $user = $userModel->where('email', $email)->first();

        if (! $user || empty($user['password_hash']) || ! password_verify($password, $user['password_hash'])) {
            return $this->response
                ->setStatusCode(401)
                ->setJSON([
                    'status'  => 'error',
                    'message' => 'E-posta veya şifre hatalı.'
                ]);
        }

        // Session set - filtreler ve layoutlar bunu kullanacak
        $session = session();
        $session->regenerate(true); // session fixation önlemi

        $session->set([
            'is_logged_in' => true,
            'user_id'      => $user['id'],
            'user_role'    => $user['role'],
            'user_email'   => $user['email'],
            'user_name'    => $user['name'],
        ]);

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => [
                'id'    => $user['id'],
                'name'  => $user['name'],
                'email' => $user['email'],
                'role'  => $user['role'],
                // İstersen admin mi diye kolay flag:
                'is_admin' => ($user['role'] === 'admin'),
            ]
        ]);
    }

    public function logout()
    {
        $session = session();
        $session->destroy();

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Çıkış yapıldı.'
        ]);
    }
}
