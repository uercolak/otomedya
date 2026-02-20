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

        $builder = $this->userModel->select('id, tenant_id, created_by, name, email, role, status, created_at');

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
        // tenant_id artık input olarak zorunlu değil (dealer için otomatik üretilecek)
        $rules = [
            'name'             => 'required|min_length[3]|max_length[100]',
            'email'            => 'required|valid_email|is_unique[users.email]',
            'password'         => 'required|min_length[6]',
            'password_confirm' => 'required|matches[password]',
            'role'             => 'required|in_list[root,dealer,user]',
            'status'           => 'required|in_list[active,passive]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $post = $this->request->getPost();

        $role = (string) ($post['role'] ?? 'user');
        if ($role === 'admin') $role = 'root';

        // Root oluşturduğu user: tenant_id NULL kalsın (genel kullanıcı)
        // Dealer: tenant otomatik açılacak
        $tenantId = null;
        if ($role === 'root') {
            $tenantId = null;
        }

        $db = \Config\Database::connect();
        $db->transStart();

        // 1) user kaydı
        $this->userModel->insert([
            'tenant_id'     => $tenantId,
            'created_by'    => null,
            'name'          => $post['name'],
            'email'         => $post['email'],
            'role'          => $role,
            'status'        => $post['status'],
            'password_hash' => password_hash($post['password'], PASSWORD_DEFAULT),
        ]);

        $newUserId = (int) $this->userModel->getInsertID();

        // 2) dealer ise tenant oluştur ve bağla
        if ($role === 'dealer') {
            // tenants tablosu: (id AI), name, owner_user_id (unique), created_at
            $db->table('tenants')->insert([
                'name'          => (string) $post['name'],
                'owner_user_id' => $newUserId,
            ]);

            $newTenantId = (int) $db->insertID();

            $this->userModel->update($newUserId, [
                'tenant_id' => $newTenantId,
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()
                ->with('error', 'Kayıt sırasında hata oluştu. Lütfen tekrar deneyin.');
        }

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
            'role'             => 'required|in_list[root,dealer,user]',
            'status'           => 'required|in_list[active,passive]',
            'password'         => 'permit_empty|min_length[6]',
            'password_confirm' => 'permit_empty|matches[password]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $post = $this->request->getPost();

        $newRole = (string) ($post['role'] ?? ($user['role'] ?? 'user'));
        if ($newRole === 'admin') $newRole = 'root';

        // son root koruması
        $oldRole = (string)($user['role'] ?? 'user');
        if ($oldRole === 'admin') $oldRole = 'root';

        if ($oldRole === 'root' && $newRole !== 'root') {
            $rootCount = $this->userModel->where('role', 'root')->countAllResults();
            if ($rootCount <= 1) {
                return redirect()->back()->withInput()
                    ->with('error', 'Son root rolü değiştirilemez.');
            }
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $updateData = [
            'name'   => $post['name'],
            'email'  => $post['email'],
            'role'   => $newRole,
            'status' => $post['status'],
        ];

        if (! empty($post['password'])) {
            $updateData['password_hash'] = password_hash($post['password'], PASSWORD_DEFAULT);
        }

        // Root için tenant temizliği
        if ($newRole === 'root') {
            $updateData['tenant_id'] = null;
        }

        $this->userModel->update($id, $updateData);

        // Eğer dealer olup tenant_id yoksa otomatik üret (eski kayıtları da kurtarır)
        $fresh = $this->userModel->find($id);
        if (($fresh['role'] ?? '') === 'dealer' && empty($fresh['tenant_id'])) {
            // tenant var mı owner_user_id üzerinden kontrol
            $tenant = $db->table('tenants')->where('owner_user_id', $id)->get()->getRowArray();

            if (! $tenant) {
                $db->table('tenants')->insert([
                    'name'          => (string)($fresh['name'] ?? ('Dealer #' . $id)),
                    'owner_user_id' => $id,
                ]);
                $tenantId = (int)$db->insertID();
            } else {
                $tenantId = (int)$tenant['id'];
            }

            $this->userModel->update($id, ['tenant_id' => $tenantId]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()
                ->with('error', 'Güncelleme sırasında hata oluştu.');
        }

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

        $role = (string)($user['role'] ?? 'user');
        if ($role === 'admin') $role = 'root';

        if ($role === 'root') {
            $rootCount = $this->userModel->where('role', 'root')->countAllResults();
            if ($rootCount <= 1) {
                return redirect()->to(base_url('admin/users'))
                    ->with('error', 'Son root silinemez.');
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
        $role = session('user_role') ?? 'user';
        if ($role === 'admin') $role = 'root';

        if (! session('is_logged_in') || $role !== 'root') {
            return redirect()->to(base_url('auth/login'));
        }

        $target = $this->userModel->find($id);
        if (! $target) {
            return redirect()->back()->with('error', 'Kullanıcı bulunamadı.');
        }

        if ((int) session('user_id') === (int) $id) {
            return redirect()->back()->with('error', 'Zaten bu hesaptasın.');
        }

        $tRole = $target['role'] ?? 'user';
        if ($tRole === 'admin') $tRole = 'root';
        if ($tRole === 'root') {
            return redirect()->back()->with('error', 'Root hesabına impersonate yapılamaz.');
        }

        $session = session();

        $session->set([
            'impersonator_id'        => (int) session('user_id'),
            'impersonator_role'      => (string) session('user_role'),
            'impersonator_email'     => (string) session('user_email'),
            'impersonator_name'      => (string) session('user_name'),
            'impersonator_tenant_id' => session('tenant_id'),
            'is_impersonating'       => true,
        ]);

        $session->set([
            'is_logged_in' => true,
            'user_id'      => (int) $target['id'],
            'user_email'   => (string) ($target['email'] ?? ''),
            'user_name'    => (string) ($target['name'] ?? ''),
            'user_role'    => (string) ($tRole ?? 'user'),
            'tenant_id'    => $target['tenant_id'] ?? null,
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

        $rootId = (int) $session->get('impersonator_id');
        $root   = $this->userModel->find($rootId);

        if (! $root) {
            $session->destroy();
            return redirect()->to(base_url('auth/login'));
        }

        $session->regenerate(true);

        $rRole = (string) ($root['role'] ?? 'root');
        if ($rRole === 'admin') $rRole = 'root';

        $session->set([
            'is_logged_in' => true,
            'user_id'      => (int) $root['id'],
            'user_email'   => (string) ($root['email'] ?? ''),
            'user_name'    => (string) ($root['name'] ?? ''),
            'user_role'    => $rRole,
            'tenant_id'    => $root['tenant_id'] ?? null,
        ]);

        $session->remove([
            'impersonator_id',
            'impersonator_role',
            'impersonator_email',
            'impersonator_name',
            'impersonator_tenant_id',
            'is_impersonating',
        ]);

        return redirect()->to(base_url('admin/users'))
            ->with('success', 'Root hesabına geri dönüldü.');
    }
}