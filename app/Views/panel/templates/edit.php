<?= $this->extend('layouts/panel') ?>
<?= $this->section('content') ?>

<?php
  $tplId = (int)($tpl['id'] ?? 0);
  $w = (int)($tpl['width'] ?? 1080);
  $h = (int)($tpl['height'] ?? 1080);

  $bgUrl = !empty($tpl['base_media_id'])
    ? site_url('media/' . (int)$tpl['base_media_id'])
    : (!empty($tpl['file_path']) ? base_url($tpl['file_path']) : '');

  $formatKey   = (string)($tpl['format_key'] ?? '');
  $savedState  = (string)($design['state_json'] ?? '');
  $designIdInt = (int)($design['id'] ?? 0);
  $templateState = (string)($tpl['template_state_json'] ?? '');

  $autoplan = (bool)($autoplan ?? false);

  $editorConfig = [
    'W' => $w,
    'H' => $h,
    'BG' => (string)$bgUrl,
    'SAVED' => (string)$savedState,
    'TEMPLATE_STATE' => (string)$templateState,
    'AUTOPLAN' => $autoplan,
    'CSRF' => [
      'name' => csrf_token(),
      'hash' => csrf_hash(),
    ],
    'saveUrl' => site_url('panel/templates/'.$tplId.'/save'),
    'exportUrl' => site_url('panel/templates/'.$tplId.'/export'),
    'designId' => $designIdInt,
  ];
?>

<?php include __DIR__ . '/editor_css.php'; ?>

<div class="container-fluid py-3">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <div>
      <h3 class="mb-0">Şablonu Düzenle</h3>
      <div class="text-muted">
        <?= esc($tpl['name'] ?? '') ?> • <?= esc($formatKey) ?> • <?= $w ?>×<?= $h ?>
      </div>
    </div>

    <div class="d-flex gap-2">
      <a href="<?= site_url('panel/templates') ?>" class="btn btn-outline-secondary">Geri</a>
      <button id="btnSave" class="btn btn-outline-primary">Kaydet</button>
      <button id="btnExport" class="btn btn-primary">Planla</button>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-lg-9">
      <div class="card p-3">
        <div id="canvasWrap" style="max-width:100%; overflow:auto;">
          <canvas id="c"
                  width="<?= $w ?>"
                  height="<?= $h ?>"
                  style="border-radius:14px; border:1px solid rgba(0,0,0,.08);"></canvas>
        </div>
      </div>
    </div>

    <div class="col-lg-3">
      <?php include __DIR__ . '/editor_toolbar.php'; ?>
    </div>
  </div>
</div>

<div id="omAutoplanOverlay" class="om-autoplan-overlay" aria-live="polite" aria-busy="true">
  <div class="om-autoplan-box">
    <div class="om-spinner"></div>
    <div class="om-autoplan-title">Yönlendiriliyorsunuz…</div>
    <p class="om-autoplan-sub">Şablon kaydediliyor ve planlama ekranı hazırlanıyor.</p>
  </div>
</div>

<div id="omToastWrap" class="om-toast-wrap" aria-live="polite" aria-atomic="true"></div>

<script>
  window.OM_EDITOR_CONFIG = <?= json_encode($editorConfig, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) ?>;
</script>

<script src="https://cdn.jsdelivr.net/npm/fabric@5.3.0/dist/fabric.min.js"></script>
<?php include __DIR__ . '/editor_js.php'; ?>

<?= $this->endSection() ?>
