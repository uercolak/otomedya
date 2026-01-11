<?php

namespace App\Controllers\Panel;

use App\Controllers\BaseController;

class SocialAccountsController extends BaseController
{
    private function ensureUser(): ?\CodeIgniter\HTTP\RedirectResponse
    {
        if (!session('is_logged_in')) return redirect()->to(site_url('auth/login'));
        return null;
    }

    public function index()
    {
        if ($r = $this->ensureUser()) return $r;

        $userId = (int)session('user_id');
        $db = \Config\Database::connect();

        $rows = $db->table('social_accounts')
            ->where('user_id', $userId)
            ->orderBy('id','DESC')
            ->get()->getResultArray();

        return view('panel/social_accounts/index', [
            'rows' => $rows,
        ]);
    }

    public function store()
    {
        if ($r = $this->ensureUser()) return $r;

        $userId = (int)session('user_id');
        $db = \Config\Database::connect();

        $platform   = strtolower(trim((string)$this->request->getPost('platform')));
        $name       = trim((string)$this->request->getPost('name'));
        $username   = trim((string)$this->request->getPost('username'));
        $externalId = trim((string)$this->request->getPost('external_id'));

        if ($platform === '' || $name === '') {
            return redirect()->back()->withInput()->with('error','Platform ve ad zorunlu.');
        }

        $now = date('Y-m-d H:i:s');

        $db->table('social_accounts')->insert([
            'user_id'     => $userId,
            'platform'    => $platform,
            'external_id' => $externalId ?: null,
            'name'        => $name,
            'username'    => $username ?: null,
            'avatar_url'  => null,
            'created_at'  => $now,
            'updated_at'  => $now,
        ]);

        return redirect()->to(site_url('panel/social-accounts'))->with('success','Hesap eklendi.');
    }

    public function delete(int $id)
    {
        if ($r = $this->ensureUser()) return $r;

        $userId = (int)session('user_id');
        $db = \Config\Database::connect();

        $row = $db->table('social_accounts')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->get()->getRowArray();

        if (!$row) {
            return redirect()->to(site_url('panel/social-accounts'))->with('error', 'Hesap bulunamadı.');
        }

        $db->table('social_accounts')->where('id', $id)->delete();

        return redirect()->to(site_url('panel/social-accounts'))->with('success', 'Hesap silindi.');
    }

    /* ========= Helpers ========= */

    private function httpGetJson(string $url, array $query): array
    {
        $client = \Config\Services::curlrequest([
            'timeout' => 30,
            'http_errors' => false,
        ]);

        try {
            $resp = $client->get($url, ['query' => $query]);
            $body = (string)$resp->getBody();
            $json = json_decode($body, true);
            return is_array($json) ? $json : ['raw' => $body];
        } catch (\Throwable $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
