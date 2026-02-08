<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<style>
  .card { border-radius: 16px; }
  .card.shadow-sm { box-shadow: 0 10px 30px rgba(17,24,39,.06) !important; }
  .table thead th { font-weight: 600; }
  .table td, .table th { padding-top: 14px; padding-bottom: 14px; }
  .table tbody tr:hover { background: rgba(124,58,237,.04); }

  /* Aksiyonları hover’da göster (premium his) */
  .row-actions { opacity: .0; transform: translateX(4px); transition: .15s ease; }
  tr:hover .row-actions { opacity: 1; transform: translateX(0); }

  .badge.rounded-pill { padding: .45rem .65rem; font-weight: 600; }

  /* küçük iyileştirme */
  .table-responsive { position: relative; z-index: 1; }
  .btn-group, .btn-group .btn { position: relative; z-index: 5; pointer-events: auto; }
</style>
<div class="page-header">
  <div>
    <h1 class="page-title">Kullanıcılar</h1>
    <p class="text-muted mb-0">Kullanıcı Hesap Yönetimi</p>
  </div>
</div>
<div class="d-flex justify-content-end mb-3">
  <a href="<?= base_url('admin/users/new') ?>" class="btn btn-primary">
    <i class="bi bi-plus-lg me-1"></i> Yeni Kullanıcı
  </a>
</div>

<?php if (session()->getFlashdata('success')): ?>
  <div class="alert alert-success d-flex align-items-center gap-2">
    <i class="bi bi-check-circle"></i>
    <div><?= esc(session()->getFlashdata('success')) ?></div>
  </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
  <div class="alert alert-danger d-flex align-items-center gap-2">
    <i class="bi bi-exclamation-triangle"></i>
    <div><?= esc(session()->getFlashdata('error')) ?></div>
  </div>
<?php endif; ?>

<?php $errors = session()->getFlashdata('errors') ?? []; ?>
<?php if (! empty($errors)): ?>
  <div class="alert alert-danger">
    <div class="fw-semibold mb-1">Hata</div>
    <ul class="mb-0">
      <?php foreach ($errors as $e): ?>
        <li><?= esc($e) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<div class="card border-0 shadow-sm mb-3">
  <div class="card-body">
    <form method="get" action="<?= base_url('admin/users') ?>" class="row g-2 align-items-center">
      <div class="col-12 col-lg">
        <div class="input-group">
          <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
          <input type="text" name="q" value="<?= esc($q ?? '') ?>" class="form-control"
                 placeholder="Ad veya e-posta ile ara…">
        </div>
      </div>

      <div class="col-12 col-md-6 col-lg-3">
        <select name="status" class="form-select">
          <option value="">Durum: Tümü</option>
          <option value="active"  <?= ($status ?? '') === 'active' ? 'selected' : '' ?>>Aktif</option>
          <option value="passive" <?= ($status ?? '') === 'passive' ? 'selected' : '' ?>>Pasif</option>
        </select>
      </div>

      <div class="col-12 col-md-auto d-grid d-md-flex gap-2 justify-content-md-end">
        <button class="btn btn-outline-secondary" type="submit">
          <i class="bi bi-funnel me-1"></i> Filtrele
        </button>

        <?php if (!empty($q) || !empty($status)): ?>
          <a href="<?= base_url('admin/users') ?>" class="btn btn-link text-decoration-none">Temizle</a>
        <?php endif; ?>
      </div>
    </form>
  </div>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table mb-0 align-middle">
        <thead class="table-light">
          <tr>
            <th style="width:70px;">#</th>
            <th>Ad Soyad</th>
            <th class="d-none d-md-table-cell">E-posta</th>
            <th style="width:120px;">Rol</th>
            <th style="width:120px;">Durum</th>
            <th class="d-none d-lg-table-cell" style="width:180px;">Oluşturma</th>
            <th style="width:140px;" class="text-end">İşlem</th>
          </tr>
        </thead>

        <tbody>
        <?php if (! empty($users)): ?>
          <?php foreach ($users as $u): ?>
            <tr>
              <td class="text-muted"><?= esc($u['id']) ?></td>

              <td>
                <div class="fw-semibold"><?= esc($u['name']) ?></div>
                <div class="text-muted small d-md-none"><?= esc($u['email']) ?></div>
              </td>

              <td class="d-none d-md-table-cell text-truncate" style="max-width:320px;">
                <?= esc($u['email']) ?>
              </td>

              <td>
                <?php if (($u['role'] ?? '') === 'admin'): ?>
                  <span class="badge rounded-pill text-bg-danger">
                    <i class="bi bi-shield-lock me-1"></i> admin
                  </span>
                <?php else: ?>
                  <span class="badge rounded-pill text-bg-secondary">
                    <i class="bi bi-person me-1"></i> user
                  </span>
                <?php endif; ?>
              </td>

              <td>
                <button
                  type="button"
                  class="badge rounded-pill border-0 js-status-toggle <?= (($u['status'] ?? 'active') === 'active') ? 'text-bg-success' : 'text-bg-secondary' ?>"
                  data-id="<?= (int)$u['id'] ?>"
                  data-status="<?= esc($u['status'] ?? 'active') ?>"
                  style="cursor:pointer;">
                  <?php if (($u['status'] ?? 'active') === 'active'): ?>
                    <i class="bi bi-check2-circle me-1"></i> aktif
                  <?php else: ?>
                    <i class="bi bi-slash-circle me-1"></i> pasif
                  <?php endif; ?>
                </button>
              </td>

              <td class="d-none d-lg-table-cell text-muted small">
                <?= esc($u['created_at'] ?? '-') ?>
              </td>

              <td class="text-end">
                <?php if (($u['role'] ?? 'user') !== 'admin'): ?>
                <form action="<?= base_url('admin/users/' . (int)$u['id'] . '/impersonate') ?>"
                        method="post" class="d-inline">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-sm btn-outline-primary" title="Bu hesaba bağlan">
                    <i class="bi bi-eye"></i>
                    </button>
                </form>
                <?php endif; ?>
                <div class="btn-group row-actions" role="group">
                  <a class="btn btn-sm btn-outline-secondary"
                     href="<?= base_url('admin/users/' . $u['id'] . '/edit') ?>">
                    <i class="bi bi-pencil"></i>
                  </a>

                  <?php if ((int)$u['id'] === (int)session('user_id')): ?>
                    <button class="btn btn-sm btn-outline-danger" disabled title="Kendi hesabını silemezsin">
                      <i class="bi bi-trash"></i>
                    </button>
                  <?php else: ?>
                    <button type="button" class="btn btn-sm btn-outline-danger js-delete-btn"
                            data-id="<?= (int)$u['id'] ?>"
                            data-name="<?= esc($u['name']) ?>"
                            data-email="<?= esc($u['email']) ?>">
                      <i class="bi bi-trash"></i>
                    </button>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="7" class="text-center py-5">
              <div class="fw-semibold mb-1">Kullanıcı bulunamadı</div>
              <div class="text-muted small mb-3">Filtreyi temizleyip tekrar deneyebilirsin.</div>
              <a href="<?= base_url('admin/users/new') ?>" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i> Yeni Kullanıcı
              </a>
            </td>
          </tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <?php if (isset($pager) && $pager): ?>
    <div class="card-footer bg-white border-0 d-flex justify-content-center">
      <?= $pager->links('default', 'bootstrap_full') ?>
    </div>
  <?php endif; ?>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteUserModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width: 520px;">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 18px; overflow: hidden;">
      <div class="modal-header border-0 pb-0">
        <div class="d-flex align-items-start gap-3">
          <div class="d-inline-flex align-items-center justify-content-center"
               style="width:44px;height:44px;border-radius:12px;background:rgba(220,53,69,.12);color:#dc3545;">
            <i class="bi bi-trash3 fs-5"></i>
          </div>
          <div>
            <h5 class="modal-title mb-1">Kullanıcıyı sil</h5>
            <div class="text-muted small">Bu işlem geri alınamaz.</div>
          </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
      </div>

      <div class="modal-body pt-3">
        <div class="p-3" style="border:1px solid rgba(0,0,0,.06);border-radius:14px;background:#fff;">
          <div class="text-muted small mb-1">Silinecek kullanıcı</div>
          <div class="fw-semibold" id="delName">-</div>
          <div class="text-muted small" id="delEmail">-</div>
        </div>

        <div class="d-flex align-items-start gap-2 mt-3 p-3"
             style="border-radius:14px;background:rgba(255,193,7,.14);border:1px solid rgba(255,193,7,.25);">
          <i class="bi bi-exclamation-triangle mt-1"></i>
          <div class="small">
            <div class="fw-semibold">Emin misin?</div>
            <div class="text-muted">Devam etmek istiyor musun?</div>
          </div>
        </div>
      </div>

      <div class="modal-footer border-0 pt-0 pb-4">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" style="border-radius:12px;">
          Vazgeç
        </button>

        <form id="deleteUserForm" method="post" class="m-0">
          <?= csrf_field() ?>
          <button type="submit" class="btn btn-danger" style="border-radius:12px;">
            <i class="bi bi-trash3 me-1"></i> Evet, Sil
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
  // delete modal
  document.addEventListener('DOMContentLoaded', () => {
    const modalEl = document.getElementById('deleteUserModal');
    if (!modalEl) return;

    const modal  = new bootstrap.Modal(modalEl);
    const nameEl = document.getElementById('delName');
    const emailEl = document.getElementById('delEmail');
    const formEl = document.getElementById('deleteUserForm');

    document.querySelectorAll('.js-delete-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        const id = btn.dataset.id;
        nameEl.textContent = btn.dataset.name || '-';
        emailEl.textContent = btn.dataset.email || '-';
        formEl.action = "<?= base_url('admin/users') ?>/" + id + "/delete";
        modal.show();
      });
    });
  });

  // status toggle (CSRF refresh)
  window.__csrfName = "<?= csrf_token() ?>";
  window.__csrfHash = "<?= csrf_hash() ?>";

  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('.js-status-toggle');
    if (!btn) return;

    const id = btn.dataset.id;
    btn.disabled = true;

    try {
      const res = await fetch("<?= base_url('admin/users') ?>/" + id + "/toggle-status", {
        method: "POST",
        headers: {
          "X-Requested-With": "XMLHttpRequest",
          "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
        },
        body: window.__csrfName + "=" + encodeURIComponent(window.__csrfHash)
      });

      const data = await res.json();
      if (!data.ok) {
        alert(data.msg || "İşlem başarısız");
        return;
      }

      // csrf refresh
      if (data.csrfName && data.csrfHash) {
        window.__csrfName = data.csrfName;
        window.__csrfHash = data.csrfHash;
      }

      const newStatus = data.status;
      btn.dataset.status = newStatus;

      if (newStatus === 'active') {
        btn.classList.remove('text-bg-secondary');
        btn.classList.add('text-bg-success');
        btn.innerHTML = '<i class="bi bi-check2-circle me-1"></i> aktif';
      } else {
        btn.classList.remove('text-bg-success');
        btn.classList.add('text-bg-secondary');
        btn.innerHTML = '<i class="bi bi-slash-circle me-1"></i> pasif';
      }

    } catch (err) {
      console.error(err);
      alert("Bağlantı hatası");
    } finally {
      btn.disabled = false;
    }
  });
</script>
<?= $this->endSection() ?>
