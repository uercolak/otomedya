<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;

class Users extends BaseController
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        helper(['form', 'url']);
    }

    public function index()
    {
        $q      = trim((string) $this->request->getGet('q'));
        $status = trim((string) $this->request->getGet('status'));

        $builder = $this->userModel->select('id, name, email, role, status, created_at');

        if ($q !== '') {
            $builder->groupStart()
                ->like('name', $q)
                ->orLike('email', $q)
                ->groupEnd();
        }

        if ($status !== '') {
            $builder->where('status', $status);
        }

        $users = $builder->orderBy('id', 'DESC')->paginate(10);

        $this->userModel->pager->setPath('admin/users');

        return view('admin/users/index', [
            'pageTitle'    => 'Kullanıcılar',
            'pageSubtitle' => 'Sistemdeki kullanıcıları yönetin.',
            'q'            => $q,
            'status'       => $status,
            'users'        => $users,
            'pager'        => $this->userModel->pager,
        ]);
    }

    public function create()
    {
        return view('admin/users/create', [
            'pageTitle'    => 'Yeni Kullanıcı',
            'pageSubtitle' => 'Yeni kullanıcı oluşturun.',
            'errors'       => session()->getFlashdata('errors') ?? [],
        ]);
    }

    public function store()
    {
        $rules = [
            'name'             => 'required|min_length[3]|max_length[100]',
            'email'            => 'required|valid_email|is_unique[users.email]',
            'password'         => 'required|min_length[6]',
            'password_confirm' => 'required|matches[password]',
            'role'             => 'required|in_list[admin,user]',
            'status'           => 'required|in_list[active,passive]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $post = $this->request->getPost();

        $this->userModel->insert([
            'name'          => $post['name'],
            'email'         => $post['email'],
            'role'          => $post['role'],
            'status'        => $post['status'],
            'password_hash' => password_hash($post['password'], PASSWORD_DEFAULT),
        ]);

        return redirect()->to(base_url('admin/users'))
            ->with('success', 'Kullanıcı başarıyla oluşturuldu.');
    }

    public function edit(int $id)
    {
        $user = $this->userModel->find($id);

        if (! $user) {
            return redirect()->to(base_url('admin/users'))
                ->with('error', 'Kullanıcı bulunamadı.');
        }

        return view('admin/users/edit', [
            'pageTitle'    => 'Kullanıcı Düzenle',
            'pageSubtitle' => 'Kullanıcı bilgilerini güncelleyin.',
            'user'         => $user,
            'errors'       => session()->getFlashdata('errors') ?? [],
        ]);
    }

    public function update(int $id)
    {
        $user = $this->userModel->find($id);
        if (! $user) {
            return redirect()->to(base_url('admin/users'))
                ->with('error', 'Kullanıcı bulunamadı.');
        }

        $rules = [
            'name'             => 'required|min_length[3]|max_length[100]',
            'email'            => 'required|valid_email|is_unique[users.email,id,' . $id . ']',
            'role'             => 'required|in_list[admin,user]',
            'status'           => 'required|in_list[active,passive]',
            'password'         => 'permit_empty|min_length[6]',
            'password_confirm' => 'permit_empty|matches[password]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $post = $this->request->getPost();

        if (($user['role'] ?? '') === 'admin' && ($post['role'] ?? '') === 'user') {
            $adminCount = $this->userModel->where('role', 'admin')->countAllResults();
            if ($adminCount <= 1) {
                return redirect()->back()->withInput()
                    ->with('error', 'Son admin rolü user yapılamaz.');
            }
        }

        $updateData = [
            'name'   => $post['name'],
            'email'  => $post['email'],
            'role'   => $post['role'],
            'status' => $post['status'],
        ];

        if (! empty($post['password'])) {
            $updateData['password_hash'] = password_hash($post['password'], PASSWORD_DEFAULT);
        }

        $this->userModel->update($id, $updateData);

        return redirect()->to(base_url('admin/users'))
            ->with('success', 'Kullanıcı güncellendi.');
    }

    public function delete(int $id)
    {
        $user = $this->userModel->find($id);
        if (! $user) {
            return redirect()->to(base_url('admin/users'))
                ->with('error', 'Kullanıcı bulunamadı.');
        }

        if ((int) session('user_id') === (int) $id) {
            return redirect()->to(base_url('admin/users'))
                ->with('error', 'Kendi hesabını silemezsin.');
        }

        if (($user['role'] ?? '') === 'admin') {
            $adminCount = $this->userModel->where('role', 'admin')->countAllResults();
            if ($adminCount <= 1) {
                return redirect()->to(base_url('admin/users'))
                    ->with('error', 'Son admin silinemez.');
            }
        }

        $this->userModel->delete($id);

        return redirect()->to(base_url('admin/users'))
            ->with('success', 'Kullanıcı silindi.');
    }

    public function toggleStatus(int $id)
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['ok' => false, 'msg' => 'Bad request']);
        }

        $user = $this->userModel->find($id);
        if (! $user) {
            return $this->response->setStatusCode(404)->setJSON(['ok' => false, 'msg' => 'Kullanıcı bulunamadı']);
        }

        if ((int) session('user_id') === (int) $id) {
            return $this->response->setStatusCode(403)->setJSON(['ok' => false, 'msg' => 'Kendi hesabını pasif yapamazsın']);
        }

        $current = $user['status'] ?? 'active';
        $next    = ($current === 'active') ? 'passive' : 'active';

        $this->userModel->update($id, ['status' => $next]);

        return $this->response->setJSON([
            'ok'       => true,
            'status'   => $next,
            'csrfName' => csrf_token(),
            'csrfHash' => csrf_hash(),
        ]);
    }

    public function impersonate(int $id)
    {
        if (! session('is_logged_in') || session('user_role') !== 'admin') {
            return redirect()->to(base_url('auth/login'));
        }

        $target = $this->userModel->find($id);
        if (! $target) {
            return redirect()->back()->with('error', 'Kullanıcı bulunamadı.');
        }

        if ((int) session('user_id') === (int) $id) {
            return redirect()->back()->with('error', 'Zaten bu hesaptasın.');
        }

        $session = session();

        $session->set([
            'impersonator_id'   => (int) session('user_id'),
            'impersonator_role' => (string) session('user_role'),
            'impersonator_email'=> (string) session('user_email'),
            'impersonator_name' => (string) session('user_name'),
            'is_impersonating'  => true,
        ]);

        $session->set([
            'is_logged_in' => true,
            'user_id'      => (int) $target['id'],
            'user_email'   => (string) ($target['email'] ?? ''),
            'user_name'    => (string) ($target['name'] ?? ''),
            'user_role'    => (string) ($target['role'] ?? 'user'), 
        ]);

        return redirect()->to(base_url('panel'))
            ->with('success', 'Kullanıcı hesabına geçildi: ' . ($target['email'] ?? ''));
    }

    public function stopImpersonate()
    {
        if (! session('is_logged_in')) {
            return redirect()->to(base_url('auth/login'));
        }

        if (! session('is_impersonating') || ! session('impersonator_id')) {
            return redirect()->to(base_url('admin'));
        }

        $session = session();
        $adminId = (int) $session->get('impersonator_id');

        $admin = $this->userModel->find($adminId);
        if (! $admin) {

            $session->destroy();
            return redirect()->to(base_url('auth/login'));
        }

        $session->set([
            'is_logged_in' => true,
            'user_id'      => (int) $admin['id'],
            'user_email'   => (string) ($admin['email'] ?? ''),
            'user_name'    => (string) ($admin['name'] ?? ''),
            'user_role'    => (string) ($admin['role'] ?? 'admin'),
        ]);

        $session->remove([
            'impersonator_id',
            'impersonator_role',
            'impersonator_email',
            'impersonator_name',
            'is_impersonating',
        ]);

        return redirect()->to(base_url('admin/users'))
            ->with('success', 'Admin hesabına geri dönüldü.');
    }
}
