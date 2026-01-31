<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;

class Auth extends BaseController
{
    
    public function loginForm()
    {
        if (session('is_logged_in')) {
            return $this->redirectByRole();
        }

        return view('auth/login');
    }

    public function loginSubmit()
    {
        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required|min_length[6]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $email    = trim((string)$this->request->getPost('email'));
        $password = (string)$this->request->getPost('password');

        $userModel = new UserModel();
        $user = $userModel->where('email', $email)->first();

        if (! $user || empty($user['password_hash']) || ! password_verify($password, $user['password_hash'])) {
            return redirect()->back()
                ->withInput()
                ->with('errors', ['email' => 'E-posta veya şifre hatalı.']);
        }

        // ✅ TAM OLARAK BURAYA (session set etmeden önce)
        if (($user['status'] ?? 'active') !== 'active') {
            return redirect()->back()
                ->withInput()
                ->with('errors', ['email' => 'Bu hesap pasif. Yönetici ile iletişime geç.']);
        }

        $session = session();
        $session->regenerate(true);

        $session->set([
            'is_logged_in' => true,
            'user_id'      => $user['id'],
            'user_email'   => $user['email'],
            'user_name'    => $user['name'] ?? '',
            'user_role'    => $user['role'] ?? 'user',
        ]);

        return $this->redirectByRole();
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/auth/login');
    }

    private function redirectByRole()
    {
        return (session('user_role') === 'admin')
            ? redirect()->to('/admin')
            : redirect()->to('/panel/social-accounts');
    }
}
