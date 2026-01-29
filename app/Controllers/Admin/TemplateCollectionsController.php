<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\TemplateCollectionModel;

class TemplateCollectionsController extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();

        $q = trim((string)($this->request->getGet('q') ?? ''));
        $active = trim((string)($this->request->getGet('active') ?? ''));

        $builder = $db->table('template_collections')
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'DESC');

        if ($q !== '') {
            $builder->groupStart()
                ->like('name', $q)
                ->orLike('slug', $q)
            ->groupEnd();
        }
        if ($active !== '') {
            $builder->where('is_active', (int)$active);
        }

        $rows = $builder->get()->getResultArray();

        return view('admin/template_collections/index', [
            'pageTitle' => 'Temalar (Koleksiyonlar)',
            'rows'      => $rows,
            'filters'   => compact('q','active'),
        ]);
    }

    public function create()
    {
        return view('admin/template_collections/create', [
            'pageTitle' => 'Yeni Tema',
            'errors'    => session()->getFlashdata('errors') ?? [],
        ]);
    }

    public function store()
    {
        helper(['form', 'url']);

        $name = trim((string)$this->request->getPost('name'));
        $slug = trim((string)$this->request->getPost('slug'));
        $desc = trim((string)$this->request->getPost('description'));
        $sort = (int)($this->request->getPost('sort_order') ?? 0);
        $isActive = (int)($this->request->getPost('is_active') ?? 1);

        if ($name === '') {
            return redirect()->back()->withInput()->with('error', 'Tema adı zorunlu.');
        }

        // slug boşsa name'den üret
        if ($slug === '') {
            $slug = url_title($name, '-', true);
        }

        $model = new TemplateCollectionModel();

        // slug unique kontrol (db unique var ama kullanıcıya düzgün hata döndürmek için)
        $exists = $model->where('slug', $slug)->first();
        if ($exists) {
            return redirect()->back()->withInput()->with('error', 'Bu slug zaten kullanılıyor.');
        }

        $now = date('Y-m-d H:i:s');

        $model->insert([
            'name'        => $name,
            'slug'        => $slug,
            'description' => $desc ?: null,
            'sort_order'  => $sort,
            'is_active'   => $isActive ? 1 : 0,
            'created_at'  => $now,
            'updated_at'  => $now,
        ]);

        return redirect()->to(site_url('admin/template-collections'))->with('success', 'Tema eklendi.');
    }

    public function edit(int $id)
    {
        $model = new TemplateCollectionModel();
        $row = $model->find($id);
        if (!$row) {
            return redirect()->to(site_url('admin/template-collections'))->with('error', 'Tema bulunamadı.');
        }

        return view('admin/template_collections/edit', [
            'pageTitle' => 'Tema Düzenle',
            'row'       => $row,
        ]);
    }

    public function update(int $id)
    {
        helper(['form', 'url']);

        $model = new TemplateCollectionModel();
        $row = $model->find($id);
        if (!$row) {
            return redirect()->to(site_url('admin/template-collections'))->with('error', 'Tema bulunamadı.');
        }

        $name = trim((string)$this->request->getPost('name'));
        $slug = trim((string)$this->request->getPost('slug'));
        $desc = trim((string)$this->request->getPost('description'));
        $sort = (int)($this->request->getPost('sort_order') ?? 0);
        $isActive = (int)($this->request->getPost('is_active') ?? 1);

        if ($name === '') {
            return redirect()->back()->withInput()->with('error', 'Tema adı zorunlu.');
        }
        if ($slug === '') {
            $slug = url_title($name, '-', true);
        }

        $exists = $model->where('slug', $slug)->where('id !=', $id)->first();
        if ($exists) {
            return redirect()->back()->withInput()->with('error', 'Bu slug başka bir temada kullanılıyor.');
        }

        $model->update($id, [
            'name'        => $name,
            'slug'        => $slug,
            'description' => $desc ?: null,
            'sort_order'  => $sort,
            'is_active'   => $isActive ? 1 : 0,
        ]);

        return redirect()->to(site_url('admin/template-collections'))->with('success', 'Tema güncellendi.');
    }

    public function toggle(int $id)
    {
        $model = new TemplateCollectionModel();
        $row = $model->find($id);
        if (!$row) return redirect()->to(site_url('admin/template-collections'))->with('error', 'Tema bulunamadı.');

        $next = ((int)($row['is_active'] ?? 1) === 1) ? 0 : 1;
        $model->update($id, ['is_active' => $next]);

        return redirect()->to(site_url('admin/template-collections'))->with('success', 'Durum güncellendi.');
    }
}
