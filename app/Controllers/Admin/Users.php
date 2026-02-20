<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;

class Users extends BaseController
{
    protected $userModel;
    protected $db;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->db = \Config\Database::connect();
        helper(['form', 'url']);
    }

    public function index()
    {
        $q      = trim((string) $this->request->getGet('q'));
        $status = trim((string) $this->request->getGet('status'));

        // dealer_id’yi de listeye ekleyelim (istersen view’de gösterirsin)
        $builder = $this->userModel->select('id, tenant_id, dealer_id, created_by, name, email, role, status, created_at');

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
        // Root ekranında user oluştururken bayiye bağlamak istersen diye dealer listesi
        $dealers = $this->userModel
            ->select('id, name, email, tenant_id')
            ->where('role', 'dealer')
            ->orderBy('id', 'DESC')
            ->findAll();

        return view('admin/users/create', [
            'pageTitle'    => 'Yeni Kullanıcı',
            'pageSubtitle' => 'Yeni kullanıcı oluşturun.',
            'errors'       => session()->getFlashdata('errors') ?? [],
            'dealers'      => $dealers,
        ]);
    }

    public function store()
    {
        /**
         * Önemli:
         * - tenant_id artık manuel istenmiyor.
         * - dealer oluşturulunca tenant otomatik açılacak.
         * - user oluşturulunca isterse dealer seçerek bağlanabilecek.
         */
        $rules = [
            'name'             => 'required|min_length[3]|max_length[150]',
            'email'            => 'required|valid_email|is_unique[users.email]',
            'password'         => 'required|min_length[6]',
            'password_confirm' => 'required|matches[password]',
            'role'             => 'required|in_list[root,dealer,user]',
            'status'           => 'required|in_list[active,passive]',
            // user için opsiyonel bayi seçimi
            'dealer_id'        => 'permit_empty|is_natural_no_zero',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $post = $this->request->getPost();

        $role = (string)($post['role'] ?? 'user');
        if ($role === 'admin') $role = 'root'; // geriye dönük

        $dealerId = $post['dealer_id'] ?? null;
        $dealerId = ($dealerId === '' || $dealerId === null) ? null : (int)$dealerId;

        // Transaction ile garanti altına alalım
        $this->db->transBegin();

        try {
            // ROOT: tenant_id her zaman null
            if ($role === 'root') {
                $this->userModel->insert([
                    'tenant_id'      => null,
                    'dealer_id'      => null,
                    'created_by'     => null,
                    'name'           => $post['name'],
                    'email'          => $post['email'],
                    'role'           => 'root',
                    'status'         => $post['status'],
                    'password_hash'  => password_hash($post['password'], PASSWORD_DEFAULT),
                    'created_at'     => date('Y-m-d H:i:s'),
                ]);

                $this->db->transCommit();

                return redirect()->to(base_url('admin/users'))
                    ->with('success', 'Root kullanıcı başarıyla oluşturuldu.');
            }

            // DEALER: önce user aç -> sonra tenant aç -> sonra user.tenant_id güncelle
            if ($role === 'dealer') {

                // 1) Dealer user kaydı (tenant_id şimdilik null)
                $this->userModel->insert([
                    'tenant_id'      => null,
                    'dealer_id'      => null,
                    'created_by'     => null,
                    'name'           => $post['name'],
                    'email'          => $post['email'],
                    'role'           => 'dealer',
                    'status'         => $post['status'],
                    'password_hash'  => password_hash($post['password'], PASSWORD_DEFAULT),
                    'created_at'     => date('Y-m-d H:i:s'),
                ]);

                $newDealerUserId = (int)$this->userModel->getInsertID();

                // 2) Tenant oluştur (AUTO_INCREMENT id)
                $this->db->table('tenants')->insert([
                    'name'          => $post['name'],          // istersen "Şirket adı" ayrı alan yaparız
                    'owner_user_id' => $newDealerUserId,
                    'created_at'    => date('Y-m-d H:i:s'),
                ]);

                $newTenantId = (int)$this->db->insertID();

                // 3) Dealer user'ı tenant'a bağla
                $this->userModel->update($newDealerUserId, [
                    'tenant_id' => $newTenantId,
                ]);

                $this->db->transCommit();

                return redirect()->to(base_url('admin/users'))
                    ->with('success', 'Bayi başarıyla oluşturuldu. Tenant otomatik atandı (ID: '.$newTenantId.').');
            }

            // USER:
            // - Root isterse genel kullanıcı açabilir (tenant null)
            // - Dealer seçerse: user o dealer'ın tenantına bağlanır + created_by/dealer_id set edilir
            $tenantId = null;
            $createdBy = null;

            if (!empty($dealerId)) {
                $dealer = $this->userModel->select('id, tenant_id')->find($dealerId);

                if (!$dealer || ($dealer['tenant_id'] ?? null) === null) {
                    throw new \RuntimeException('Seçilen bayi bulunamadı veya tenant bilgisi yok.');
                }

                $tenantId   = (int)$dealer['tenant_id'];
                $createdBy  = (int)$dealer['id'];
            }

            $this->userModel->insert([
                'tenant_id'      => $tenantId,          // null olabilir (genel user)
                'dealer_id'      => $dealerId,          // null olabilir
                'created_by'     => $createdBy,         // dealer seçildiyse dealer id
                'name'           => $post['name'],
                'email'          => $post['email'],
                'role'           => 'user',
                'status'         => $post['status'],
                'password_hash'  => password_hash($post['password'], PASSWORD_DEFAULT),
                'created_at'     => date('Y-m-d H:i:s'),
            ]);

            $this->db->transCommit();

            return redirect()->to(base_url('admin/users'))
                ->with('success', 'Kullanıcı başarıyla oluşturuldu.');
        }
        catch (\Throwable $e) {
            $this->db->transRollback();

            return redirect()->back()->withInput()->with('errors', [
                'store' => $e->getMessage()
            ]);
        }
    }

    public function edit(int $id)
    {
        $user = $this->userModel->find($id);

        if (! $user) {
            return redirect()->to(base_url('admin/users'))
                ->with('error', 'Kullanıcı bulunamadı.');
        }

        // edit ekranında da dealer listesi lazım olabilir (istersen view’e koyarız)
        $dealers = $this->userModel
            ->select('id, name, email, tenant_id')
            ->where('role', 'dealer')
            ->orderBy('id', 'DESC')
            ->findAll();

        return view('admin/users/edit', [
            'pageTitle'    => 'Kullanıcı Düzenle',
            'pageSubtitle' => 'Kullanıcı bilgilerini güncelleyin.',
            'user'         => $user,
            'dealers'      => $dealers,
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

        // rol edit ekranında zaten değişmiyor (senin view’in öyle)
        $role = $user['role'] ?? 'user';
        if ($role === 'admin') $role = 'root';

        $rules = [
            'name'             => 'required|min_length[3]|max_length[150]',
            'email'            => 'required|valid_email|is_unique[users.email,id,' . $id . ']',
            'status'           => 'required|in_list[active,passive]',
            'password'         => 'permit_empty|min_length[6]',
            'password_confirm' => 'permit_empty|matches[password]',
            // editte istersek user’ı bayiye bağlama için kullanılabilir
            'dealer_id'        => 'permit_empty|is_natural_no_zero',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $post = $this->request->getPost();

        $dealerId = $post['dealer_id'] ?? null;
        $dealerId = ($dealerId === '' || $dealerId === null) ? null : (int)$dealerId;

        $updateData = [
            'name'   => $post['name'],
            'email'  => $post['email'],
            'status' => $post['status'],
        ];

        // Root kullanıcıda bayi/tenant bağlama yok
        if ($role !== 'root') {
            // user ise: dealer seçildiyse bağla, seçilmediyse genel bırak (tenant null)
            if ($role === 'user') {
                $tenantId = null;
                $createdBy = null;

                if (!empty($dealerId)) {
                    $dealer = $this->userModel->select('id, tenant_id')->find($dealerId);
                    if (!$dealer || ($dealer['tenant_id'] ?? null) === null) {
                        return redirect()->back()->withInput()
                            ->with('errors', ['dealer_id' => 'Seçilen bayi bulunamadı veya tenant bilgisi yok.']);
                    }
                    $tenantId = (int)$dealer['tenant_id'];
                    $createdBy = (int)$dealer['id'];
                }

                $updateData['dealer_id']  = $dealerId;
                $updateData['tenant_id']  = $tenantId;
                $updateData['created_by'] = $createdBy;
            }
        }

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

        $role = $user['role'] ?? 'user';
        if ($role === 'admin') $role = 'root';

        if ($role === 'root') {
            $rootCount = $this->userModel->where('role', 'root')->countAllResults();
            if ($rootCount <= 1) {
                return redirect()->to(base_url('admin/users'))
                    ->with('error', 'Son root silinemez.');
            }
        }

        // Eğer silinen kullanıcı dealer ise, tenant temizliği de isteğe bağlı.
        // Şimdilik kullanıcıyı siliyoruz, tenant’ı ayrıca ele alırız.
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
        // admin->root uyumu
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

        // root hesabına geçişi engelle (isteğe bağlı güvenlik)
        $tRole = $target['role'] ?? 'user';
        if ($tRole === 'admin') $tRole = 'root';
        if ($tRole === 'root') {
            return redirect()->back()->with('error', 'Root hesabına impersonate yapılamaz.');
        }

        $session = session();

        // impersonator bilgilerini sakla
        $session->set([
            'impersonator_id'       => (int) session('user_id'),
            'impersonator_role'     => (string) session('user_role'),
            'impersonator_email'    => (string) session('user_email'),
            'impersonator_name'     => (string) session('user_name'),
            'impersonator_tenant_id'=> session('tenant_id'),
            'is_impersonating'      => true,
        ]);

        // hedef kullanıcıya geç (tenant_id dahil)
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
            'tenant_id'    => $root['tenant_id'] ?? null, // root için null beklenir
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
