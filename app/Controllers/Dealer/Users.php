<?php

namespace App\Controllers\Dealer;

use App\Controllers\BaseController;
use App\Models\UserModel;

class Users extends BaseController
{
    protected UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        helper(['form','url']);
    }

    public function index()
    {
        $dealerId = (int) session('user_id');
        $tenantId = (int) session('tenant_id');

        $q      = trim((string) $this->request->getGet('q'));
        $status = trim((string) $this->request->getGet('status'));

        $builder = $this->userModel
            ->select('id, name, email, role, status, created_at')
            ->where('tenant_id', $tenantId)
            ->where('created_by', $dealerId);

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
        $this->userModel->pager->setPath('dealer/users');

        return view('dealer/users/index', [
            'pageTitle'    => 'Kullanıcılar',
            'pageSubtitle' => 'Sadece kendi oluşturduğun kullanıcıları yönetebilirsin.',
            'q'            => $q,
            'status'       => $status,
            'users'        => $users,
            'pager'        => $this->userModel->pager,
        ]);
    }

    public function create()
    {
        return view('dealer/users/create', [
            'pageTitle'    => 'Yeni Kullanıcı',
            'pageSubtitle' => 'Alt kullanıcı oluştur.',
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
            'role'             => 'required|in_list[user]',
            'status'           => 'required|in_list[active,passive]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $tenantId = (int) session('tenant_id');
        $dealerId = (int) session('user_id');
        $post = $this->request->getPost();

        $this->userModel->insert([
            'name'          => $post['name'],
            'email'         => $post['email'],
            'role'          => 'user',
            'status'        => $post['status'],
            'tenant_id'     => $tenantId,
            'created_by'    => $dealerId,
            'password_hash' => password_hash($post['password'], PASSWORD_DEFAULT),
        ]);

        return redirect()->to(base_url('dealer/users'))
            ->with('success', 'Kullanıcı başarıyla oluşturuldu.');
    }

    public function edit(int $id)
    {
        $tenantId = (int) session('tenant_id');
        $dealerId = (int) session('user_id');

        $user = $this->userModel
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->where('created_by', $dealerId)
            ->first();

        if (! $user) {
            return redirect()->to(base_url('dealer/users'))
                ->with('error', 'Kullanıcı bulunamadı.');
        }

        return view('dealer/users/edit', [
            'pageTitle'    => 'Kullanıcı Düzenle',
            'pageSubtitle' => 'Kullanıcı bilgilerini güncelle.',
            'user'         => $user,
            'errors'       => session()->getFlashdata('errors') ?? [],
        ]);
    }

    public function update(int $id)
    {
        $tenantId = (int) session('tenant_id');
        $dealerId = (int) session('user_id');

        $user = $this->userModel
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->where('created_by', $dealerId)
            ->first();

        if (! $user) {
            return redirect()->to(base_url('dealer/users'))
                ->with('error', 'Kullanıcı bulunamadı.');
        }

        $rules = [
            'name'             => 'required|min_length[3]|max_length[100]',
            'email'            => 'required|valid_email|is_unique[users.email,id,' . $id . ']',
            'role'             => 'required|in_list[user]',
            'status'           => 'required|in_list[active,passive]',
            'password'         => 'permit_empty|min_length[6]',
            'password_confirm' => 'permit_empty|matches[password]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $post = $this->request->getPost();

        $updateData = [
            'name'   => $post['name'],
            'email'  => $post['email'],
            'role'   => 'user',
            'status' => $post['status'],
        ];

        if (! empty($post['password'])) {
            $updateData['password_hash'] = password_hash($post['password'], PASSWORD_DEFAULT);
        }

        $this->userModel->update($id, $updateData);

        return redirect()->to(base_url('dealer/users'))
            ->with('success', 'Kullanıcı güncellendi.');
    }

    public function delete(int $id)
    {
        $tenantId = (int) session('tenant_id');
        $dealerId = (int) session('user_id');

        $user = $this->userModel
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->where('created_by', $dealerId)
            ->first();

        if (! $user) {
            return redirect()->to(base_url('dealer/users'))
                ->with('error', 'Kullanıcı bulunamadı.');
        }

        $this->userModel->delete($id);

        return redirect()->to(base_url('dealer/users'))
            ->with('success', 'Kullanıcı silindi.');
    }

    public function toggleStatus(int $id)
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['ok' => false, 'msg' => 'Bad request']);
        }

        $tenantId = (int) session('tenant_id');
        $dealerId = (int) session('user_id');

        $user = $this->userModel
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->where('created_by', $dealerId)
            ->first();

        if (! $user) {
            return $this->response->setStatusCode(404)->setJSON(['ok' => false, 'msg' => 'Kullanıcı bulunamadı']);
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
}
