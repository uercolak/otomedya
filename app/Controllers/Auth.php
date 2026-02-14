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

        if (($user['status'] ?? 'active') !== 'active') {
            return redirect()->back()
                ->withInput()
                ->with('errors', ['email' => 'Bu hesap pasif. Yönetici ile iletişime geç.']);
        }

        $session = session();
        $session->regenerate(true);

        // admin kalmış olabilir diye güvence
        $role = $user['role'] ?? 'user';
        if ($role === 'admin') {
            $role = 'root';
        }

        $session->set([
            'is_logged_in' => true,
            'user_id'      => (int)$user['id'],
            'user_email'   => $user['email'],
            'user_name'    => $user['name'] ?? '',
            'user_role'    => $role,
            'tenant_id'    => $user['tenant_id'] ?? null,
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
        $role = session('user_role') ?? 'user';

        if ($role === 'admin') {
            $role = 'root';
        }

        if ($role === 'root') {
            return redirect()->to('/admin');
        }

        if ($role === 'dealer') {
            return redirect()->to('/dealer');
        }

        return redirect()->to('/panel');
    }
}
