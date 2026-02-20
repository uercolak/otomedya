<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<?php
  // set_select / set_value kullanmayalım; form helper bağımsız olsun.
  $old = session()->getFlashdata('_ci_old_input') ?? [];
  $oldPost = $old['post'] ?? [];

  $oldContentId  = (string)($oldPost['content_id'] ?? '');
  $oldAccountId  = (string)($oldPost['account_id'] ?? '');
  $oldScheduleAt = (string)($oldPost['schedule_at'] ?? '');
  $oldUserId     = (string)($oldPost['user_id'] ?? '');
?>

<div class="page-header">
  <div>
    <h1 class="page-title">Planlı Paylaşım Oluştur</h1>
    <p class="text-muted mb-4">İçerik + sosyal hesap seçerek publish kaydı açar ve işi kuyruğa ekler.</p>
  </div>
</div>

<?php if (session('error')): ?>
  <div class="alert alert-danger"><?= esc(session('error')) ?></div>
<?php endif; ?>
<?php if (session('success')): ?>
  <div class="alert alert-success"><?= esc(session('success')) ?></div>
<?php endif; ?>

<form method="post" action="<?= site_url('admin/publishes') ?>" class="card">
  <?= csrf_field() ?>
  <div class="card-body">
    <div class="row g-3">

      <div class="col-md-6">
        <label class="form-label">İçerik</label>
        <select name="content_id" class="form-select" required>
          <option value="">Seçiniz</option>
          <?php foreach (($contents ?? []) as $c): ?>
            <?php
              $label = '#' . $c['id'];
              if (!empty($c['title'])) $label .= ' — ' . $c['title'];
              $selected = ((string)$c['id'] === $oldContentId) ? 'selected' : '';
            ?>
            <option value="<?= (int)$c['id'] ?>" <?= $selected ?>>
              <?= esc($label) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <div class="form-text">Content ID + başlık ile listelenir.</div>
      </div>

      <div class="col-md-6">
        <label class="form-label">Sosyal Hesap</label>
        <select name="account_id" class="form-select" required>
          <option value="">Seçiniz</option>
          <?php foreach (($accounts ?? []) as $a): ?>
            <?php
              $name = $a['name'] ?: ($a['username'] ?: '');
              $label = '#' . $a['id'] . ' — ' . strtoupper((string)$a['platform']);
              if ($name) $label .= ' — ' . $name;
              $selected = ((string)$a['id'] === $oldAccountId) ? 'selected' : '';
            ?>
            <option value="<?= (int)$a['id'] ?>" <?= $selected ?>>
              <?= esc($label) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <div class="form-text">Platform + isim/username ile listelenir.</div>
      </div>

      <div class="col-md-6">
        <label class="form-label">Planlanan Zaman (opsiyonel)</label>
        <input name="schedule_at" value="<?= esc($oldScheduleAt) ?>"
               class="form-control" placeholder="2025-01-01 12:00:00">
        <div class="form-text">Boş bırakırsan hemen kuyruğa girer.</div>
      </div>

      <div class="col-md-6">
        <label class="form-label">Kullanıcı ID (opsiyonel)</label>
        <input name="user_id" value="<?= esc($oldUserId) ?>" class="form-control"
               placeholder="Boş bırak → içerik sahibinden alınır">
        <div class="form-text">Admin destek işlemleri için. Boşsa içerik/user’dan türetilir.</div>
      </div>

    </div>

    <div class="d-flex gap-2 mt-4">
      <button class="btn btn-primary" type="submit">Oluştur</button>
      <a class="btn btn-outline-secondary" href="<?= site_url('admin/jobs') ?>">İşlere dön</a>
    </div>
  </div>
</form>

<?= $this->endSection() ?>
