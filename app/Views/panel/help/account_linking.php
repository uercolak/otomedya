<?= $this->extend('layouts/panel') ?>
<?= $this->section('content') ?>

<?php
$stats  = $stats  ?? ['fb_count' => 0, 'ig_count' => 0, 'yt_count' => 0, 'tt_count' => 0];
$checks = $checks ?? [
  'hasFbPage'      => false,
  'hasIgConnected' => false,
  'igLinkedToPage' => false,
  'isFbAdmin'      => null,
  'isIgBusiness'   => null,
  'hasYouTube'     => null,
  'hasTikTok'      => null,
];

$cbChecked = fn($val) => ($val === true) ? 'checked' : '';
$cbManual  = fn($val) => ($val === null) ? 'data-manual="1"' : 'data-manual="0"';

// px -> cm (300 dpi)
$pxToCm = function(int $px): float {
  return round(($px / 300) * 2.54, 2);
};

// oran badge
$ratioBadge = function(int $w, int $h): string {
  $g = function($a,$b){ while($b){ $t=$b; $b=$a%$b; $a=$t; } return $a; };
  $d = $g($w,$h);
  $rw = (int)($w/$d); $rh = (int)($h/$d);

  $map = [
    '1:1'   => '1:1',
    '4:5'   => '4:5',
    '9:16'  => '9:16',
    '16:9'  => '16:9',
    '1.91:1'=> '1.91:1',
  ];
  $key = $rw.':'.$rh;
  return $map[$key] ?? $key;
};

$metaShots = [
  ['file' => 'meta-1.png',  'title' => 'Meta Business Suite / Ayarlar', 'desc' => 'Facebook sayfa/işletme ayarlarına giriş.'],
  ['file' => 'meta-2.png',  'title' => 'Meta hesap ekranı',            'desc' => 'Bağlantı sürecine başlamadan önce hesap kontrolü.'],
  ['file' => 'meta-3.png',  'title' => 'Güvenlik / 2FA',               'desc' => '2 adımlı doğrulamayı aktif et.'],
  ['file' => 'meta-4-ig-baglama.png', 'title' => 'Instagram bağlama - adım 1', 'desc' => 'IG hesabını sayfaya bağlama başlangıcı.'],
  ['file' => 'meta-5-ig-baglama.png', 'title' => 'Instagram bağlama - adım 2', 'desc' => 'Doğru IG hesabını seç.'],
  ['file' => 'meta-6-ig-baglama.png', 'title' => 'Instagram bağlama - adım 3', 'desc' => 'İzin/erişim adımı.'],
  ['file' => 'meta-7-ig-baglama.png', 'title' => 'Instagram bağlama - adım 4', 'desc' => 'Bağlantıyı onayla/kaydet.'],
  ['file' => 'meta-8-ig-baglama.png', 'title' => 'Instagram giriş',           'desc' => 'IG login ekranı geldiyse giriş yap.'],
  ['file' => 'meta-9-ig-baglama.png', 'title' => 'İzin ekranı',               'desc' => 'Gerekli izinleri kabul et.'],
  ['file' => 'meta-10-ig-baglama.png','title' => 'Bağlantı tamam',            'desc' => 'Sayfa–IG bağlantısı tamamlandı.'],
  ['file' => 'meta-11-ig-baglama.png','title' => 'Son kontrol',               'desc' => 'Bağlı hesaplar doğru görünüyor mu?'],
];

$igShots = [
  ['file' => 'ig-01.png', 'title' => 'Instagram Ayarlar',       'desc' => 'Instagram uygulamasında ayarlara gir.'],
  ['file' => 'ig-02.png', 'title' => 'Hesap türü',             'desc' => 'Hesabı profesyonele çevirme menüsü.'],
  ['file' => 'ig-03.png', 'title' => 'Profesyonel hesaba geç', 'desc' => 'Business/Creator seçimi.'],
  ['file' => 'ig-04.png', 'title' => 'Kategori seçimi',        'desc' => 'İşletme kategorisini belirle.'],
  ['file' => 'ig-05.png', 'title' => 'İletişim bilgileri',     'desc' => 'Telefon/e-posta gibi alanlar.'],
  ['file' => 'ig-06.png', 'title' => 'Sayfa bağlantısı',       'desc' => 'Facebook sayfası ile bağlantı adımı.'],
  ['file' => 'ig-07.png', 'title' => 'Tamam',                  'desc' => 'Kurulum bitti.'],
];

// Boyut kartları — 300 DPI referans
$sizes = [
  'Instagram' => [
    ['k' => 'Post (Kare)',   'fmt'=>'POST',   'w'=>1080, 'h'=>1080],
    ['k' => 'Post (Dikey)',  'fmt'=>'POST',   'w'=>1080, 'h'=>1350],
    ['k' => 'Story / Reels', 'fmt'=>'STORY',  'w'=>1080, 'h'=>1920],
  ],
  'Facebook' => [
    ['k' => 'Post (Kare öneri)', 'fmt'=>'POST',  'w'=>1080, 'h'=>1080],
    ['k' => 'Post (Yatay link)', 'fmt'=>'POST',  'w'=>1200, 'h'=>630],
    ['k' => 'Story',             'fmt'=>'STORY', 'w'=>1080, 'h'=>1920],
  ],
  'YouTube' => [
    ['k' => 'Video',     'fmt'=>'VIDEO',  'w'=>1920, 'h'=>1080],
    ['k' => 'Shorts',    'fmt'=>'SHORTS', 'w'=>1080, 'h'=>1920],
    ['k' => 'Thumbnail', 'fmt'=>'THUMB',  'w'=>1280, 'h'=>720],
  ],
  'TikTok' => [
    ['k' => 'Video',         'fmt'=>'VIDEO', 'w'=>1080, 'h'=>1920],
    ['k' => 'Kapak (Cover)', 'fmt'=>'COVER', 'w'=>1080, 'h'=>1920],
  ],
];

/** ✅ Step set + progress */
$steps = [
  [
    'id' => 'cb_hasFbPage',
    'key' => 'hasFbPage',
    'title' => 'Facebook Sayfam var (bağlı)',
    'desc'  => 'Paylaşım Facebook Page üzerinden yapılır.',
    'icon'  => 'bi bi-facebook',
    'fix'   => 'Facebook’ta bir Sayfa oluştur ve uygulamaya bağla.',
  ],
  [
    'id' => 'cb_isFbAdmin',
    'key' => 'isFbAdmin',
    'title' => 'Sayfada Admin’im',
    'desc'  => 'İzinler ve yayın için yönetici yetkisi gerekli.',
    'icon'  => 'bi bi-shield-check',
    'fix'   => 'Sayfa rollerinden hesabını “Yönetici” yap.',
  ],
  [
    'id' => 'cb_isIgBusiness',
    'key' => 'isIgBusiness',
    'title' => 'Instagram Business/Creator',
    'desc'  => 'Reels/Story ve API tarafı için gerekli.',
    'icon'  => 'bi bi-instagram',
    'fix'   => 'IG hesabını Business/Creator’a çevir.',
  ],
  [
    'id' => 'cb_igLinkedToPage',
    'key' => 'igLinkedToPage',
    'title' => 'IG → Facebook Sayfasına bağlı',
    'desc'  => 'IG hesabı ilgili sayfaya bağlı olmalı.',
    'icon'  => 'bi bi-link-45deg',
    'fix'   => 'Meta Business Suite üzerinden IG ↔ Sayfa bağlantısını kur.',
  ],
];

$doneCount = 0;
$firstMissing = null;
foreach ($steps as $s) {
  $val = $checks[$s['key']] ?? null;
  if ($val === true) $doneCount++;
  if ($firstMissing === null && $val !== true) $firstMissing = $s;
}
$totalSteps = count($steps);
$percent = $totalSteps ? (int)round(($doneCount / $totalSteps) * 100) : 0;

$resetUrl = $resetUrl ?? null; // controller gönderebilirse kullanırız
?>

<style>
  .help-hero{ border:1px solid rgba(0,0,0,.06); border-radius:16px;
    background: linear-gradient(180deg, rgba(106,92,255,.10), rgba(255,79,216,.06)); }
  .help-muted{ color: rgba(0,0,0,.62); }
  .help-badge{ display:inline-flex; align-items:center; gap:8px;
    padding:7px 10px; border-radius:999px; background: rgba(255,255,255,.65);
    border: 1px solid rgba(0,0,0,.06); font-size:12px; font-weight:700; white-space:nowrap; }
  .help-card{ border:1px solid rgba(0,0,0,.06); border-radius:16px; }
  .help-card .card-header{ background:#fff; border-bottom:1px solid rgba(0,0,0,.06);
    border-top-left-radius:16px; border-top-right-radius:16px; }

  .help-acc .accordion-item{ border:1px solid rgba(0,0,0,.06); border-radius:14px; overflow:hidden; margin-bottom:10px; }
  .help-acc .accordion-button{ font-weight:800; background:#fff; }
  .help-acc .accordion-button:not(.collapsed){ box-shadow:none; background: rgba(0,0,0,.02); }

  .help-kbd{ font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
    font-size: .95em; padding: 2px 6px; border-radius: 8px; border: 1px solid rgba(0,0,0,.08); background: rgba(0,0,0,.03); }

  /* Gallery thumbnails */
  .shot-grid{ display:grid; grid-template-columns: repeat(4, 1fr); gap:10px; }
  @media (max-width: 1199.98px){ .shot-grid{ grid-template-columns: repeat(3, 1fr);} }
  @media (max-width: 767.98px){  .shot-grid{ grid-template-columns: repeat(2, 1fr);} }
  @media (max-width: 439.98px){  .shot-grid{ grid-template-columns: repeat(1, 1fr);} }

  .shot{ border:1px solid rgba(0,0,0,.08); border-radius:14px; overflow:hidden; background:#fff; cursor:pointer;
    transition: transform .12s ease, box-shadow .12s ease; }
  .shot:hover{ transform: translateY(-1px); box-shadow: 0 10px 24px rgba(0,0,0,.08); }
  .shot .thumb{ aspect-ratio: 16/10; background:#f6f7fb; display:flex; align-items:center; justify-content:center; overflow:hidden; }
  .shot img{ width:100%; height:100%; object-fit:contain; display:block; background:#fff; }
  .shot .meta{ padding:10px 10px 11px; }
  .shot .meta .t{ font-weight:800; font-size:12px; line-height:1.2; margin:0 0 4px; }
  .shot .meta .d{ color: rgba(0,0,0,.62); font-size:11.5px; line-height:1.25; margin:0; }

  /* Modal z-index */
  .modal{ z-index: 20050 !important; }
  .modal-backdrop{ z-index: 20040 !important; }

  #shotModal .modal-body{ padding: 14px 16px 12px; }
  .shot-modal-wrap{ width:100%; max-height: 70vh; overflow:auto; border-radius:14px; border:1px solid rgba(0,0,0,.08); background:#fff; }
  .shot-modal-img{ width:100%; height:auto; max-height: 70vh; object-fit:contain; display:block; }
  .shot-nav{ display:flex; gap:10px; justify-content:space-between; align-items:center; }
  .shot-nav .btn{ border-radius:12px; }

  /* Size cards */
  .size-card{ border:1px solid rgba(0,0,0,.08); border-radius:14px; background:#fff; padding:12px; }
  .size-top{ display:flex; align-items:flex-start; justify-content:space-between; gap:10px; }
  .size-title{ font-weight:800; margin:0; font-size:13px; }
  .size-sub{ margin:6px 0 0; font-size:12px; color: rgba(0,0,0,.65); display:flex; flex-wrap:wrap; gap:8px; align-items:center;}
  .btn-copy{ border-radius:12px; white-space:nowrap; }

  .pill{
    display:inline-flex; align-items:center; gap:6px;
    padding:3px 8px; border-radius:999px;
    border:1px solid rgba(0,0,0,.08);
    background: rgba(0,0,0,.03);
    font-size:11px; font-weight:800;
  }

  /* Right panel steps */
  .check-steps{ display:flex; flex-direction:column; gap:10px; }
  .check-step{
    border:1px solid rgba(0,0,0,.08);
    border-radius:14px;
    background:#fff;
    padding:10px 12px;
    display:flex;
    align-items:flex-start;
    gap:10px;
    cursor:pointer;
  }
  .check-step .ic{
    width:34px; height:34px;
    border-radius:12px;
    display:flex; align-items:center; justify-content:center;
    background: rgba(124,58,237,.08);
    border: 1px solid rgba(124,58,237,.14);
    color: rgba(124,58,237,.95);
    flex:0 0 auto;
  }
  .check-step .txt{ flex:1; min-width:0; }
  .check-step .t{ font-weight:800; font-size:13px; margin:0; line-height:1.2; }
  .check-step .d{ margin:2px 0 0; font-size:12px; color: rgba(0,0,0,.62); line-height:1.25; }
  .check-step .right{ display:flex; align-items:center; gap:8px; }

  .status-pill{
    font-size:11px; font-weight:800;
    padding:6px 9px;
    border-radius:999px;
    border:1px solid rgba(0,0,0,.08);
    background: rgba(0,0,0,.02);
    white-space:nowrap;
  }
  .status-ok{
    border-color: rgba(25,135,84,.20);
    background: rgba(25,135,84,.08);
    color: rgba(25,135,84,.95);
  }
  .status-warn{
    border-color: rgba(255,193,7,.25);
    background: rgba(255,193,7,.12);
    color: rgba(146,102,0,.95);
  }

  .progress-wrap{
    border:1px solid rgba(0,0,0,.08);
    background:#fff;
    border-radius:14px;
    padding:12px;
  }
  .progress-wrap .p-top{ display:flex; justify-content:space-between; align-items:center; gap:10px; }
  .progress-wrap .p-title{ font-weight:800; font-size:13px; margin:0; }
  .progress-wrap .p-sub{ font-size:12px; color: rgba(0,0,0,.62); margin:0; }
  .progress{ height:10px; border-radius:999px; background: rgba(0,0,0,.06); }
  .progress-bar{ border-radius:999px; }

  .tip-box{
    border:1px dashed rgba(0,0,0,.14);
    background: rgba(0,0,0,.02);
    border-radius:12px;
    padding:10px 12px;
    font-size:12px;
    color: rgba(0,0,0,.70);
    margin-top:10px;
  }

  .mini-list{ margin:0; padding-left: 18px; }
  .mini-list li{ margin-bottom:6px; color: rgba(0,0,0,.72); font-size:12px; }
  .mini-list b{ color: rgba(0,0,0,.86); }

  .action-btns{ display:grid; grid-template-columns: 1fr 1fr; gap:10px; }
  @media (max-width: 991.98px){ .action-btns{ grid-template-columns: 1fr; } }
  .action-btns .btn{ border-radius:14px; padding:10px 12px; font-weight:800; }
</style>

<div class="d-flex align-items-start justify-content-between mb-3">
  <div>
    <h1 class="h3 mb-1"><?= esc($title ?? 'Yardım / Hesap Bağlama Rehberi') ?></h1>
    <div class="help-muted">Instagram, Facebook, YouTube ve TikTok hesaplarını bağlamak için adım adım rehber.</div>
  </div>

  <a class="btn btn-outline-secondary" href="<?= site_url('panel/social-accounts') ?>">
    Sosyal Hesaplara Git
  </a>
</div>

<div class="help-hero p-3 p-lg-4 mb-3 d-flex align-items-start justify-content-between gap-3 flex-wrap">
  <div style="max-width:820px;">
    <div class="help-badge mb-2"><i class="bi bi-info-circle"></i> Kısa Özet</div>
    <div class="fw-semibold">
      Instagram paylaşımı için hesap <b>Business/Creator</b> olmalı ve bir <b>Facebook Sayfasına bağlı</b> olmalıdır.
      Facebook paylaşımı da <b>Sayfa</b> üzerinden yapılır. YouTube ve TikTok’ta ise ilk adım OAuth ile hesabı bağlamaktır.
    </div>
    <div class="help-muted small mt-2">
      İpucu: Bir şey takılırsa önce “Sosyal Hesaplar → Sıfırla” ile yeniden bağlamak çoğu zaman en hızlı çözümdür.
    </div>
  </div>

  <span class="help-badge">
    <i class="bi bi-shield-check"></i>
    Güvenli bağlantı (OAuth)
  </span>
</div>

<div class="row g-3">
  <!-- LEFT -->
  <div class="col-lg-7">

    <div class="card help-card mb-3">
      <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <strong>Görselli Rehber</strong>
        <span class="help-muted small">Görsellere tıkla → modalda açılır (ileri/geri).</span>
      </div>
      <div class="card-body">

        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
          <div class="help-badge"><i class="bi bi-meta"></i> Meta (Facebook / Instagram)</div>
          <button type="button" class="btn btn-sm btn-outline-secondary js-open-gallery" data-gallery="meta" data-index="0">
            İlk adımdan aç
          </button>
        </div>

        <div class="shot-grid mb-4" id="gridMeta">
          <?php foreach ($metaShots as $i => $s): ?>
            <?php $src = base_url('help/meta/' . $s['file']); ?>
            <div class="shot js-open-gallery" role="button" tabindex="0"
                 data-gallery="meta"
                 data-index="<?= $i ?>"
                 data-src="<?= esc($src) ?>"
                 data-title="<?= esc($s['title']) ?>"
                 data-desc="<?= esc($s['desc']) ?>">
              <div class="thumb">
                <img src="<?= esc($src) ?>" alt="<?= esc($s['title']) ?>">
              </div>
              <div class="meta">
                <p class="t"><?= ($i+1) ?>) <?= esc($s['title']) ?></p>
                <p class="d"><?= esc($s['desc']) ?></p>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
          <div class="help-badge"><i class="bi bi-instagram"></i> Instagram (Profesyonel Hesap)</div>
          <button type="button" class="btn btn-sm btn-outline-secondary js-open-gallery" data-gallery="ig" data-index="0">
            İlk adımdan aç
          </button>
        </div>

        <div class="shot-grid" id="gridIg">
          <?php foreach ($igShots as $i => $s): ?>
            <?php $src = base_url('help/ig/' . $s['file']); ?>
            <div class="shot js-open-gallery" role="button" tabindex="0"
                 data-gallery="ig"
                 data-index="<?= $i ?>"
                 data-src="<?= esc($src) ?>"
                 data-title="<?= esc($s['title']) ?>"
                 data-desc="<?= esc($s['desc']) ?>">
              <div class="thumb">
                <img src="<?= esc($src) ?>" alt="<?= esc($s['title']) ?>">
              </div>
              <div class="meta">
                <p class="t"><?= ($i+1) ?>) <?= esc($s['title']) ?></p>
                <p class="d"><?= esc($s['desc']) ?></p>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

      </div>
    </div>

    <div class="card help-card mb-3">
      <div class="card-header"><strong>Metin Rehber</strong></div>
      <div class="card-body">

        <div class="accordion help-acc" id="accHelp">

          <div class="accordion-item">
            <h2 class="accordion-header" id="hMetaReq">
              <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#cMetaReq" aria-expanded="true" aria-controls="cMetaReq">
                1) Instagram & Facebook (Meta) — Başlamadan önce gerekli şartlar
              </button>
            </h2>
            <div id="cMetaReq" class="accordion-collapse collapse show" aria-labelledby="hMetaReq" data-bs-parent="#accHelp">
              <div class="accordion-body">
                <ul class="mb-0">
                  <li><strong>Facebook Sayfası</strong> oluşturulmuş olmalı (Kişisel profil değil).</li>
                  <li>Paylaşım atılacak Facebook Sayfasında rolün <strong>Yönetici (Admin)</strong> olmalı.</li>
                  <li><strong>Instagram hesabı Business/Creator</strong> olmalı.</li>
                  <li>Instagram hesabı, ilgili <strong>Facebook Sayfasına bağlanmış</strong> olmalı.</li>
                </ul>
              </div>
            </div>
          </div>

          <div class="accordion-item">
            <h2 class="accordion-header" id="hMetaConnect">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#cMetaConnect" aria-expanded="false" aria-controls="cMetaConnect">
                2) Instagram’ı Meta ile bağlama (Facebook Page üzerinden)
              </button>
            </h2>
            <div id="cMetaConnect" class="accordion-collapse collapse" aria-labelledby="hMetaConnect" data-bs-parent="#accHelp">
              <div class="accordion-body">
                <ol class="mb-0">
                  <li><strong>Panel → Sosyal Hesaplar</strong> sayfasına gir.</li>
                  <li><strong>“Meta ile Bağla”</strong> butonuna tıkla.</li>
                  <li>Meta giriş ekranı gelirse Facebook hesabınla giriş yap.</li>
                  <li>İzin ekranlarında mümkünse “Mevcut ve gelecekteki…” seçeneğini kullan.</li>
                  <li>Son ekranda <strong>Kaydet / Devam</strong> diyerek bitir.</li>
                  <li>Uygulamaya dönünce sihirbazda Facebook Sayfanı ve bağlı Instagram’ı görürsün.</li>
                </ol>
              </div>
            </div>
          </div>

          <div class="accordion-item">
            <h2 class="accordion-header" id="hYouTube">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#cYouTube" aria-expanded="false" aria-controls="cYouTube">
                3) YouTube — Kanal bağlama (Google)
              </button>
            </h2>
            <div id="cYouTube" class="accordion-collapse collapse" aria-labelledby="hYouTube" data-bs-parent="#accHelp">
              <div class="accordion-body">
                <ol class="mb-0">
                  <li><strong>Panel → Sosyal Hesaplar</strong> sayfasına git.</li>
                  <li><strong>YouTube’u Bağla</strong> butonuna tıkla.</li>
                  <li>Google giriş ekranında ilgili hesabınla giriş yap.</li>
                  <li>İzin ver ve uygulamaya geri dön.</li>
                </ol>
              </div>
            </div>
          </div>

          <div class="accordion-item">
            <h2 class="accordion-header" id="hTikTok">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#cTikTok" aria-expanded="false" aria-controls="cTikTok">
                4) TikTok — Hesap bağlama (OAuth)
              </button>
            </h2>
            <div id="cTikTok" class="accordion-collapse collapse" aria-labelledby="hTikTok" data-bs-parent="#accHelp">
              <div class="accordion-body">
                <ol class="mb-0">
                  <li><strong>Panel → Sosyal Hesaplar</strong> sayfasına git.</li>
                  <li><strong>TikTok’u Bağla</strong> butonuna tıkla.</li>
                  <li>TikTok izin ekranında giriş yap ve izin ver.</li>
                  <li>Uygulamaya dön.</li>
                </ol>
              </div>
            </div>
          </div>

          <!-- Sizes -->
          <div class="accordion-item">
            <h2 class="accordion-header" id="hSizes">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#cSizes" aria-expanded="false" aria-controls="cSizes">
                5) Paylaşım Boyutları (px + cm) — Instagram / Facebook / YouTube / TikTok
              </button>
            </h2>
            <div id="cSizes" class="accordion-collapse collapse" aria-labelledby="hSizes" data-bs-parent="#accHelp">
              <div class="accordion-body">

                <div class="alert alert-light border">
                  <div class="fw-semibold mb-1">Not</div>
                  <div class="small">
                    Bu ölçüler önerilen standartlardır. <b>cm hesapları 300 DPI</b> referans alınarak verilmiştir.
                    Dijitalde DPI zorunlu değil ama tasarımcı için cm karşılığı faydalıdır.
                  </div>
                </div>

                <?php foreach ($sizes as $platform => $items): ?>
                  <div class="fw-bold mb-2"><?= esc($platform) ?></div>
                  <div class="row g-2 mb-3">
                    <?php foreach ($items as $it): ?>
                      <?php
                        $cmW = $pxToCm($it['w']);
                        $cmH = $pxToCm($it['h']);
                        $copyVal = $it['w'].'×'.$it['h'];
                        $ratio = $ratioBadge($it['w'], $it['h']);
                      ?>
                      <div class="col-md-6">
                        <div class="size-card">
                          <div class="size-top">
                            <div>
                              <p class="size-title mb-0"><?= esc($it['k']) ?></p>
                              <div class="size-sub">
                                <span class="pill"><?= esc($it['fmt']) ?></span>
                                <span class="pill"><?= esc($ratio) ?></span>
                                <span class="fw-semibold"><?= esc($copyVal) ?> px</span>
                                <span class="text-muted">≈ <?= $cmW ?> × <?= $cmH ?> cm (@300dpi)</span>
                              </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary btn-copy js-copy" data-copy="<?= esc($copyVal) ?>">
                              Kopyala
                            </button>
                          </div>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  </div>
                <?php endforeach; ?>

              </div>
            </div>
          </div>

        </div><!-- /accordion -->

      </div>
    </div>

  </div>

  <!-- RIGHT -->
  <div class="col-lg-5">

    <div class="card help-card mb-3">
      <div class="card-header d-flex align-items-center justify-content-between gap-2 flex-wrap">
        <strong>Hızlı Kontrol Listesi</strong>
        <span class="help-badge">
          Bağlı: FB <?= (int)($stats['fb_count'] ?? 0) ?>
          • IG <?= (int)($stats['ig_count'] ?? 0) ?>
          • YT <?= (int)($stats['yt_count'] ?? 0) ?>
          • TT <?= (int)($stats['tt_count'] ?? 0) ?>
        </span>
      </div>

      <div class="card-body">

        <!-- Progress -->
        <div class="progress-wrap mb-3">
          <div class="p-top">
            <p class="p-title mb-0">Hazırlık Durumu</p>
            <span class="status-pill <?= ($percent === 100 ? 'status-ok' : 'status-warn') ?>">
              <?= $doneCount ?>/<?= $totalSteps ?> • <?= $percent ?>%
            </span>
          </div>
          <p class="p-sub mt-1">Tüm adımlar tamamlanınca Meta Sihirbazı açılır.</p>

          <div class="progress mt-2">
            <div class="progress-bar" role="progressbar"
                 style="width: <?= $percent ?>%;"
                 aria-valuenow="<?= $percent ?>" aria-valuemin="0" aria-valuemax="100"></div>
          </div>

          <!-- ✅ Bonus: hangi adım eksik -->
          <div class="tip-box">
            <?php if ($percent === 100): ?>
              ✅ Her şey hazır. Artık Meta Sihirbazına gidebilirsin.
            <?php else: ?>
              <div class="fw-semibold mb-1">Şu an eksik:</div>
              <div>
                <b><?= esc($firstMissing['title'] ?? '—') ?></b><br>
                <span class="text-muted"><?= esc($firstMissing['fix'] ?? '') ?></span>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Steps -->
        <div class="check-steps mb-3">
          <?php foreach ($steps as $s): ?>
            <?php
              $key = $s['key'];
              $val = $checks[$key] ?? null;
              $isDone = ($val === true);
            ?>
            <label class="check-step" for="<?= esc($s['id']) ?>">
              <div class="ic"><i class="<?= esc($s['icon']) ?>"></i></div>

              <div class="txt">
                <p class="t"><?= esc($s['title']) ?></p>
                <p class="d"><?= esc($s['desc']) ?></p>
              </div>

              <div class="right">
                <span class="status-pill <?= $isDone ? 'status-ok' : 'status-warn' ?>">
                  <?= $isDone ? 'OK' : 'Eksik' ?>
                </span>
                <input class="form-check-input js-check mt-1" type="checkbox" id="<?= esc($s['id']) ?>"
                  <?= $cbChecked($checks[$key] ?? false) ?> <?= $cbManual($checks[$key] ?? null) ?> />
              </div>
            </label>
          <?php endforeach; ?>
        </div>

        <a id="btnGoWizard" href="<?= site_url('panel/social-accounts/meta/wizard') ?>"
           class="btn btn-primary w-100" style="display:none; border-radius:14px; font-weight:800;">
          Meta Sihirbazına Git
        </a>

        <a id="btnGoSocial" href="<?= site_url('panel/social-accounts') ?>"
           class="btn btn-outline-secondary w-100" style="border-radius:14px; font-weight:800;">
          Sosyal Hesaplara Git
        </a>

      </div>
    </div>

    <!-- ✅ Kart 1: Sık Karşılaşılan Sorunlar -->
    <div class="card help-card mb-3">
      <div class="card-header d-flex align-items-center justify-content-between">
        <strong>Sık Karşılaşılan Sorunlar</strong>
        <span class="help-badge"><i class="bi bi-life-preserver"></i> Mini çözüm</span>
      </div>
      <div class="card-body">
        <ul class="mini-list">
          <li><b>“IG Business görünmüyor”</b> → Instagram’ı profesyonel (Business/Creator) hesaba geçir.</li>
          <li><b>“Sayfa listesi gelmiyor”</b> → Facebook Sayfasında admin (yönetici) değilsin.</li>
          <li><b>“IG sayfaya bağlı değil”</b> → Meta Business Suite’ten IG ↔ Sayfa bağlantısını kur.</li>
          <li><b>“Bağla butonu dönüyor”</b> → Sosyal Hesaplar → Sıfırla yapıp tekrar bağla.</li>
        </ul>
      </div>
    </div>

  </div>
</div>

<!-- ✅ Tek modal -->
<div class="modal fade" id="shotModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content" style="border-radius:16px; overflow:hidden;">
      <div class="modal-header">
        <div>
          <div class="fw-bold" id="shotModalTitle">Görsel</div>
          <div class="small text-muted" id="shotModalDesc"></div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
      </div>
      <div class="modal-body">
        <div class="shot-modal-wrap">
          <img id="shotModalImg" class="shot-modal-img" src="" alt="">
        </div>
        <div class="shot-nav mt-3">
          <button type="button" class="btn btn-outline-secondary" id="btnPrev">
            <i class="bi bi-arrow-left"></i> Önceki
          </button>
          <div class="small text-muted" id="shotCounter">1 / 1</div>
          <button type="button" class="btn btn-outline-secondary" id="btnNext">
            Sonraki <i class="bi bi-arrow-right"></i>
          </button>
        </div>
      </div>
      <div class="modal-footer d-flex justify-content-between">
        <div class="small text-muted">İpucu: <span class="help-kbd">←</span> / <span class="help-kbd">→</span></div>
        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Tamam</button>
      </div>
    </div>
  </div>
</div>

<script>
(function () {
  if (typeof window.bootstrap === 'undefined' || !bootstrap.Modal) {
    console.error('Bootstrap bundle yok. layouts/panel.php içinde bootstrap.bundle var mı kontrol et.');
    return;
  }

  // ✅ Checklist buttons toggle
  const btnWizard = document.getElementById('btnGoWizard');
  const btnSocial = document.getElementById('btnGoSocial');
  const checks = [
    document.getElementById('cb_hasFbPage'),
    document.getElementById('cb_isFbAdmin'),
    document.getElementById('cb_isIgBusiness'),
    document.getElementById('cb_igLinkedToPage'),
  ].filter(Boolean);

  function refreshChecklist() {
    const allOk = checks.every(ch => ch.checked);
    if (btnWizard && btnSocial) {
      btnWizard.style.display = allOk ? '' : 'none';
      btnSocial.style.display = allOk ? 'none' : '';
    }
  }
  checks.forEach(ch => ch.addEventListener('change', refreshChecklist));
  refreshChecklist();

  // ✅ Copy
  async function copyText(text){
    try{
      await navigator.clipboard.writeText(text);
      return true;
    }catch(e){
      const ta = document.createElement('textarea');
      ta.value = text;
      ta.style.position = 'fixed';
      ta.style.left = '-9999px';
      document.body.appendChild(ta);
      ta.select();
      try{
        document.execCommand('copy');
        document.body.removeChild(ta);
        return true;
      }catch(err){
        document.body.removeChild(ta);
        return false;
      }
    }
  }

  document.querySelectorAll('.js-copy').forEach(btn => {
    btn.addEventListener('click', async () => {
      const val = btn.getAttribute('data-copy') || '';
      const ok = await copyText(val);
      const old = btn.textContent;
      btn.textContent = ok ? 'Kopyalandı' : 'Kopyalanamadı';
      setTimeout(() => btn.textContent = old, 900);
    });
  });

  // ✅ Gallery collect
  function collectGallery(name){
    const shots = Array.from(document.querySelectorAll('.shot.js-open-gallery[data-gallery="'+name+'"][data-src]'));
    return shots.map(el => ({
      src: el.getAttribute('data-src'),
      title: el.getAttribute('data-title') || 'Görsel',
      desc: el.getAttribute('data-desc') || '',
    }));
  }

  const galleries = {
    meta: collectGallery('meta'),
    ig: collectGallery('ig'),
  };

  const modalEl = document.getElementById('shotModal');
  const modal   = new bootstrap.Modal(modalEl, { backdrop: true, keyboard: true, focus: true });

  const imgEl   = document.getElementById('shotModalImg');
  const titleEl = document.getElementById('shotModalTitle');
  const descEl  = document.getElementById('shotModalDesc');
  const counter = document.getElementById('shotCounter');
  const btnPrev = document.getElementById('btnPrev');
  const btnNext = document.getElementById('btnNext');

  let currentGallery = 'meta';
  let currentIndex = 0;

  function render(){
    const arr = galleries[currentGallery] || [];
    const total = arr.length || 1;
    const item = arr[currentIndex] || {src:'', title:'Görsel', desc:''};

    imgEl.src = item.src;
    imgEl.alt = item.title;
    titleEl.textContent = item.title;
    descEl.textContent = item.desc;
    counter.textContent = (currentIndex + 1) + ' / ' + total;

    btnPrev.disabled = (currentIndex <= 0);
    btnNext.disabled = (currentIndex >= total - 1);
  }

  function openGallery(g, idx){
    currentGallery = g;
    currentIndex = Math.max(0, parseInt(idx || 0, 10) || 0);
    render();
    modal.show();
  }

  document.querySelectorAll('.js-open-gallery').forEach(el => {
    el.addEventListener('click', function(){
      const g = this.getAttribute('data-gallery') || 'meta';
      const i = this.getAttribute('data-index') || 0;
      openGallery(g, i);
    });
    el.addEventListener('keydown', function(e){
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        const g = this.getAttribute('data-gallery') || 'meta';
        const i = this.getAttribute('data-index') || 0;
        openGallery(g, i);
      }
    });
  });

  btnPrev.addEventListener('click', () => { if (currentIndex > 0) { currentIndex--; render(); } });
  btnNext.addEventListener('click', () => {
    const total = (galleries[currentGallery] || []).length;
    if (currentIndex < total - 1) { currentIndex++; render(); }
  });

  document.addEventListener('keydown', (e) => {
    if (!modalEl.classList.contains('show')) return;
    if (e.key === 'ArrowLeft') btnPrev.click();
    if (e.key === 'ArrowRight') btnNext.click();
  });

  modalEl.addEventListener('hidden.bs.modal', () => { imgEl.src = ''; });

})();
</script>

<?= $this->endSection() ?>
