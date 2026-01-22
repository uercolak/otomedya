<?= $this->extend('layouts/panel') ?>
<?= $this->section('content') ?>

<style>
    /* Canva-like layout */
    .tpl-page { background: #f6f7fb; }
    .tpl-shell { display:flex; gap:16px; }
    .tpl-sidebar {
        width: 260px; flex: 0 0 260px;
        background: #fff; border: 1px solid rgba(0,0,0,.06);
        border-radius: 16px; padding: 14px;
        position: sticky; top: 16px; height: calc(100vh - 32px); overflow:auto;
    }
    .tpl-main { flex: 1; background: transparent; }

    .tpl-title { font-weight: 700; letter-spacing: -.2px; }
    .tpl-sub { color: rgba(0,0,0,.55); }

    .tpl-searchbar { padding: 14px; }

    .tpl-searchrow{
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap: 12px;
        flex-wrap: wrap;
    }

    .tpl-searchleft{
        flex: 1 1 520px;
        min-width: 280px;
    }

    .tpl-searchinput{ position: relative; }

    .tpl-searchinput .bi{
        position:absolute;
        left:14px;
        top:50%;
        transform:translateY(-50%);
        opacity:.55;
    }

    .tpl-searchinput input{
        padding-left: 40px;
        height: 46px;
        border-radius: 14px;
    }

    .tpl-searchright{
        display:flex;
        gap:10px;
        align-items:center;
        flex: 0 0 auto;
    }

    .tpl-searchright .btn{
        height: 46px;
        border-radius: 999px;
        padding: 0 18px;
        font-weight: 600;
        white-space: nowrap;
        display: inline-flex;       /* ✅ Temizle ortalama */
        align-items: center;        /* ✅ Temizle ortalama */
        justify-content: center;    /* ✅ Temizle ortalama */
        line-height: 1;             /* ✅ Temizle ortalama */
    }

    /* Canva-like primary button */
    .tpl-searchright .btn.btn-primary{
        border:0;
        background: linear-gradient(135deg, #6d28d9, #ec4899);
        box-shadow: 0 12px 26px rgba(236,72,153,.18);
    }

    @media (max-width: 992px){
        .tpl-searchleft{ flex: 1 1 100%; }
        .tpl-searchright{ flex: 1 1 100%; justify-content:flex-start; }
    }

    .tpl-chip {
        display:flex; align-items:center; justify-content:space-between;
        padding: 10px 12px; border-radius: 12px;
        border: 1px solid rgba(0,0,0,.06);
        background:#fff; cursor:pointer;
        transition: all .15s ease;
        font-size: 14px;
    }
    .tpl-chip:hover { transform: translateY(-1px); box-shadow: 0 8px 22px rgba(0,0,0,.06); }
    .tpl-chip.active {
        border-color: rgba(99,102,241,.45);
        background: rgba(99,102,241,.06);
        font-weight: 600;
    }
    .tpl-chip small { opacity:.75; font-weight: 600; } /* ✅ sayı daha net */

    .tpl-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
    }

    @media (max-width: 1400px) { .tpl-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
    @media (max-width: 992px)  { .tpl-shell { flex-direction: column; } .tpl-sidebar { position:relative; width:100%; height:auto; } .tpl-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 576px)  { .tpl-grid { grid-template-columns: 1fr; } }

    .tpl-card {
        position: relative;
        border-radius: 16px;
        background: #fff;
        border: 1px solid rgba(0,0,0,.06);
        overflow: hidden;
        transition: transform .15s ease, box-shadow .15s ease;
        box-shadow: 0 10px 24px rgba(0,0,0,.04);
    }
    .tpl-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 14px 34px rgba(0,0,0,.08);
    }

    .tpl-thumb {
        aspect-ratio: 1 / 1;
        width: 100%;
        background: linear-gradient(135deg, rgba(99,102,241,.10), rgba(236,72,153,.10));
        display:flex; align-items:center; justify-content:center;
        position: relative;
    }
    .tpl-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display:block;
    }

    .tpl-card-meta { padding: 10px 12px 12px 12px; }
    .tpl-name { font-weight: 700; margin: 0; font-size: 14px; line-height: 1.25; }
    .tpl-info { margin: 4px 0 0 0; font-size: 12px; color: rgba(0,0,0,.55); }

    .tpl-hover {
        position:absolute; inset:0;
        background: rgba(0,0,0,.35);
        display:flex; align-items:center; justify-content:center;
        opacity:0; transition: opacity .15s ease;
    }
    .tpl-card:hover .tpl-hover { opacity: 1; }

    .tpl-hover .btn {
        border-radius: 999px;
        padding: 10px 16px;
        font-weight: 600;
        box-shadow: 0 12px 30px rgba(0,0,0,.25);
    }

    .tpl-empty {
        background:#fff; border:1px dashed rgba(0,0,0,.18);
        border-radius: 16px; padding: 24px; text-align:center;
        color: rgba(0,0,0,.55);
    }

    .tpl-hover .btn.btn-primary{
        border:0;
        background: linear-gradient(135deg, #6d28d9, #ec4899);
    }
</style>

<?php
  // mevcut filters
  $q      = (string)($filters['q'] ?? '');
  $scope  = (string)($filters['scope'] ?? '');
  $format = (string)($filters['format'] ?? '');
  $type   = (string)($filters['type'] ?? '');

  $scopes = $scopeOptions ?? [];

  // ✅ controller’dan geliyor
  $categoryCounts = $categoryCounts ?? [];
  $totalCount = (int)($totalCount ?? 0);

  $scopeLabel = function($k){
    $k = (string)$k;
    return match($k){
      'instagram' => 'Instagram',
      'facebook'  => 'Facebook',
      'tiktok'    => 'TikTok',
      'youtube'   => 'YouTube',
      'universal' => 'Evrensel',
      default     => $k !== '' ? ucfirst($k) : 'Hepsi',
    };
  };

  $tplThumbUrl = function($tpl) {
    $baseMediaId = (int)($tpl['base_media_id'] ?? 0);
    $fp = (string)($tpl['file_path'] ?? '');
    if ($baseMediaId > 0) return site_url('media/' . $baseMediaId);
    if ($fp !== '') return base_url($fp);
    return '';
  };
?>

<div class="tpl-page p-3 p-lg-4">
  <div class="tpl-shell">
    <!-- Sidebar -->
    <aside class="tpl-sidebar">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <div class="fw-semibold">Kategoriler</div>
        <button id="btnClearFilters" type="button" class="btn btn-sm btn-outline-secondary" style="border-radius:999px;">
          Sıfırla
        </button>
      </div>

      <div class="d-grid gap-2">
        <?php $isAll = ($scope === ''); ?>

        <!-- ✅ Hepsi sayısı: totalCount -->
        <div class="tpl-chip <?= $isAll ? 'active' : '' ?>" data-scope="">
          <span>Hepsi</span>
          <small><?= (int)$totalCount ?></small>
        </div>

        <?php foreach ($scopes as $s): ?>
          <?php
            $sKey = (string)$s;
            $active = ($scope === $sKey);
            $cnt = (int)($categoryCounts[$sKey] ?? 0); // ✅ • yerine gerçek sayı
          ?>
          <div class="tpl-chip <?= $active ? 'active' : '' ?>" data-scope="<?= esc($sKey) ?>">
            <span><?= esc($scopeLabel($sKey)) ?></span>
            <small><?= $cnt ?></small>
          </div>
        <?php endforeach; ?>
      </div>

      <hr class="my-3">

      <div class="small text-muted mb-2">İpucu</div>
      <div class="small" style="color: rgba(0,0,0,.55); line-height:1.45;">
        Bir şablonun üstüne gelince <b>Düzenle</b> butonu çıkar.
        Düzenledikten sonra <b>Planla</b> ile içeriğe dönersin.
      </div>
    </aside>

    <!-- Main -->
    <main class="tpl-main">
      <form id="tplFilterForm" class="tpl-searchbar mb-3" method="get" action="<?= site_url('panel/templates') ?>">
        <input type="hidden" name="scope" id="inScope" value="<?= esc($scope) ?>">

        <!-- (Controller’da duruyor diye format/type hidden bırakalım; UI’da yok) -->
        <?php if ($format !== ''): ?><input type="hidden" name="format" value="<?= esc($format) ?>"><?php endif; ?>
        <?php if ($type !== ''): ?><input type="hidden" name="type" value="<?= esc($type) ?>"><?php endif; ?>

        <div class="tpl-searchrow">
          <div class="tpl-searchleft">
            <div class="tpl-searchinput">
              <i class="bi bi-search"></i>
              <input
                type="text"
                class="form-control"
                name="q"
                value="<?= esc($q) ?>"
                placeholder="Şablon ara (başlık/açıklama)">
            </div>
          </div>

          <div class="tpl-searchright">
            <?php if (trim((string)$q) !== ''): ?>
              <a class="btn btn-outline-secondary" href="<?= site_url('panel/templates') ?>">Temizle</a>
            <?php endif; ?>
            <button class="btn btn-primary" type="submit">Ara</button>
          </div>
        </div>
      </form>

      <!-- Grid -->
      <?php if (empty($rows)): ?>
        <div class="tpl-empty">
          <div class="fw-semibold mb-1">Şablon bulunamadı</div>
          Filtreleri temizleyip tekrar deneyebilirsin.
        </div>
      <?php else: ?>
        <div class="tpl-grid">
          <?php foreach ($rows as $tpl): ?>
            <?php
              $id = (int)($tpl['id'] ?? 0);
              $name = (string)($tpl['name'] ?? '');
              $w = (int)($tpl['width'] ?? 0);
              $h = (int)($tpl['height'] ?? 0);
              $fmt = (string)($tpl['format_key'] ?? '');
              $scp = (string)($tpl['platform_scope'] ?? '');
              $thumb = $tplThumbUrl($tpl);
              $editUrl = site_url('panel/templates/'.$id.'/edit');
            ?>

            <div class="tpl-card">
              <div class="tpl-thumb">
                <?php if ($thumb !== ''): ?>
                  <img src="<?= esc($thumb) ?>" alt="<?= esc($name) ?>" loading="lazy">
                <?php else: ?>
                  <div class="text-muted small">Önizleme yok</div>
                <?php endif; ?>

                <div class="tpl-hover">
                    <div class="d-flex flex-column gap-2">
                        <a href="<?= esc($editUrl) ?>" class="btn btn-light">
                        Düzenle
                        </a>

                        <a href="<?= esc($editUrl . '?autoplan=1') ?>" class="btn btn-primary">
                        Hızlı Planla
                        </a>
                    </div>
                </div>
              </div>

              <div class="tpl-card-meta">
                <p class="tpl-name"><?= esc($name) ?></p>
                <p class="tpl-info">
                  <?= esc($scopeLabel($scp)) ?> • <?= esc($fmt) ?> • <?= $w ?>×<?= $h ?>
                </p>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

    </main>
  </div>
</div>

<script>
(function(){
  const chips = document.querySelectorAll('.tpl-chip[data-scope]');
  const inScope = document.getElementById('inScope');
  const form = document.getElementById('tplFilterForm');
  const btnClear = document.getElementById('btnClearFilters');

  chips.forEach(ch => {
    ch.addEventListener('click', () => {
      const scope = ch.getAttribute('data-scope') || '';
      if (inScope) inScope.value = scope;
      if (form) form.submit();
    });
  });

  if (btnClear) {
    btnClear.addEventListener('click', () => {
      if (!form) return;

      const q = form.querySelector('input[name="q"]');
      if (q) q.value = '';

      // hidden olarak kalsa bile temizlemek istersek:
      const format = form.querySelector('input[name="format"]');
      const type   = form.querySelector('input[name="type"]');
      if (format) format.value = '';
      if (type) type.value = '';

      if (inScope) inScope.value = '';
      form.submit();
    });
  }
})();
</script>

<?= $this->endSection() ?>
