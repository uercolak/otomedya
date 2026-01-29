<?php

namespace App\Controllers\Panel;

use App\Controllers\BaseController;
use App\Models\UserModel;

class SettingsController extends BaseController
{
    public function index()
    {
        $userId = (int) (session('user.id') ?? session('user_id') ?? 0);
        if (!$userId) {
            return redirect()->to(site_url('panel/auth/login'));
        }

        $userModel = new UserModel();
        $user = $userModel->find($userId);

        return view('panel/settings/index', [
            'title' => 'Ayarlar',
            'user'  => $user,
        ]);
    }

    public function updatePassword()
    {
        $userId = (int) (session('user.id') ?? session('user_id') ?? 0);
        if (!$userId) {
            return redirect()->to(site_url('panel/auth/login'));
        }

        $rules = [
            'current_password' => 'required|min_length[6]',
            'new_password'     => 'required|min_length[8]',
            'new_password2'    => 'required|matches[new_password]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $userModel = new UserModel();
        $user = $userModel->find($userId);

        if (!$user) {
            return redirect()->back()->with('error', 'Kullanıcı bulunamadı.');
        }

        // Kullanıcı tablondaki alan adını burada eşle
        $hash = $user['password_hash'] ?? $user['password'] ?? null;

        if (!$hash || !password_verify((string)$this->request->getPost('current_password'), (string)$hash)) {
            return redirect()->back()->withInput()->with('error', 'Mevcut şifre hatalı.');
        }

        $newPass = (string)$this->request->getPost('new_password');

        // Aynı şifreyi engellemek istersen:
        if (password_verify($newPass, (string)$hash)) {
            return redirect()->back()->withInput()->with('error', 'Yeni şifre, mevcut şifre ile aynı olamaz.');
        }

        $newHash = password_hash($newPass, PASSWORD_DEFAULT);

        // Update alan adını burada eşle
        $data = [];
        if (array_key_exists('password_hash', $user)) {
            $data['password_hash'] = $newHash;
        } else {
            $data['password'] = $newHash;
        }

        $userModel->update($userId, $data);

        return redirect()->to(site_url('panel/settings'))->with('success', 'Şifren güncellendi.');
    }
}
