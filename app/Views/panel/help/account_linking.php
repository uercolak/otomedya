<?= $this->extend('layouts/panel') ?>
<?= $this->section('content') ?>

<?php
$stats  = $stats  ?? ['fb_count' => 0, 'ig_count' => 0, 'yt_count' => 0, 'tt_count' => 0];
$checks = $checks ?? [
  'hasFbPage' => false,
  'hasIgConnected' => false,
  'igLinkedToPage' => false,
  'isFbAdmin' => null,
  'isIgBusiness' => null,

  // opsiyonel (controller göndermiyorsa null/false kalır)
  'hasYouTube' => null,
  'hasTikTok'  => null,
];

// checkbox helper: null => manuel (enabled), true/false => otomatik
$cbChecked = function ($val) {
  if ($val === true) return 'checked';
  return '';
};
$cbManual = function ($val) {
  // null => manuel; true/false => otomatik
  return ($val === null) ? 'data-manual="1"' : 'data-manual="0"';
};
?>

<style>
  .help-hero{
    border:1px solid rgba(0,0,0,.06);
    border-radius:16px;
    background: linear-gradient(180deg, rgba(106,92,255,.10), rgba(255,79,216,.06));
  }
  .help-hero h1{ letter-spacing:-.4px; }
  .help-muted{ color: rgba(0,0,0,.62); }
  .help-badge{
    display:inline-flex; align-items:center; gap:8px;
    padding:7px 10px; border-radius:999px;
    background: rgba(255,255,255,.65);
    border: 1px solid rgba(0,0,0,.06);
    font-size:12px; font-weight:700;
    white-space:nowrap;
  }
  .help-card{
    border:1px solid rgba(0,0,0,.06);
    border-radius:16px;
  }
  .help-card .card-header{
    background:#fff;
    border-bottom:1px solid rgba(0,0,0,.06);
    border-top-left-radius:16px;
    border-top-right-radius:16px;
  }
  .help-acc .accordion-item{
    border:1px solid rgba(0,0,0,.06);
    border-radius:14px;
    overflow:hidden;
    margin-bottom:10px;
  }
  .help-acc .accordion-button{
    font-weight:800;
    background:#fff;
  }
  .help-acc .accordion-button:not(.collapsed){
    box-shadow:none;
    background: rgba(0,0,0,.02);
  }
  .help-kbd{
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
    font-size: .95em;
    padding: 2px 6px;
    border-radius: 8px;
    border: 1px solid rgba(0,0,0,.08);
    background: rgba(0,0,0,.03);
  }
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
  <!-- LEFT: ACCORDION GUIDE -->
  <div class="col-lg-7">

    <div class="card help-card mb-3">
      <div class="card-header"><strong>Rehber</strong></div>
      <div class="card-body">

        <div class="accordion help-acc" id="accHelp">

          <!-- 1) META prerequisites -->
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
                  <li>Instagram hesabı, ilgili <strong>Facebook Sayfasına bağlanmış</strong> olmalı (Meta Business Suite / IG ayarları).</li>
                </ul>
              </div>
            </div>
          </div>

          <!-- 2) META connect steps -->
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
                  <li>
                    İzin ekranlarında genelde en sorunsuz seçenek:
                    <span class="help-kbd">“Mevcut ve gelecekteki tüm … öğelerine onay ver”</span>
                    <div class="small help-muted mt-1">Kurumsal/çok sayfalı kullanımda sadece gerekli sayfaları seçebilirsin.</div>
                  </li>
                  <li>Son ekranda <strong>Kaydet / Devam</strong> diyerek izinleri tamamla.</li>
                  <li>Uygulamaya dönünce sihirbaz ekranında Facebook Sayfanı ve bağlı Instagram hesabını görürsün.</li>
                  <li>İlgili Instagram satırında <strong>“Bağla”</strong> deyip işlemi bitir.</li>
                </ol>
              </div>
            </div>
          </div>

          <!-- 3) YouTube -->
          <div class="accordion-item">
            <h2 class="accordion-header" id="hYouTube">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#cYouTube" aria-expanded="false" aria-controls="cYouTube">
                3) YouTube — Kanal bağlama (Google)
              </button>
            </h2>
            <div id="cYouTube" class="accordion-collapse collapse" aria-labelledby="hYouTube" data-bs-parent="#accHelp">
              <div class="accordion-body">
                <ol class="mb-2">
                  <li><strong>Panel → Sosyal Hesaplar</strong> sayfasına git.</li>
                  <li><strong>YouTube’u Bağla</strong> butonuna tıkla.</li>
                  <li>Google giriş ekranında ilgili hesabınla giriş yap.</li>
                  <li>İzin ekranında onay ver ve uygulamaya geri dön.</li>
                  <li>Bağlı hesaplar listesinde YouTube kanalın görünür.</li>
                </ol>

                <div class="alert alert-light border mb-0">
                  <div class="fw-semibold mb-1">Sık sorunlar</div>
                  <ul class="mb-0 small">
                    <li>Yanlış Google hesabıyla giriş: çıkış yapıp doğru hesapla tekrar bağla.</li>
                    <li>Kanal görünmüyor: YouTube kanalının ilgili Google hesabında olduğundan emin ol.</li>
                    <li>İzin iptali: “Sosyal Hesaplar → kaldır” → tekrar bağla.</li>
                  </ul>
                </div>
              </div>
            </div>
          </div>

          <!-- 4) TikTok -->
          <div class="accordion-item">
            <h2 class="accordion-header" id="hTikTok">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#cTikTok" aria-expanded="false" aria-controls="cTikTok">
                4) TikTok — Hesap bağlama (OAuth)
              </button>
            </h2>
            <div id="cTikTok" class="accordion-collapse collapse" aria-labelledby="hTikTok" data-bs-parent="#accHelp">
              <div class="accordion-body">
                <ol class="mb-2">
                  <li><strong>Panel → Sosyal Hesaplar</strong> sayfasına git.</li>
                  <li><strong>TikTok’u Bağla</strong> butonuna tıkla.</li>
                  <li>TikTok izin ekranında giriş yap ve izin ver.</li>
                  <li>Uygulamaya dönünce TikTok hesabın bağlı listesinde görünür.</li>
                </ol>

                <div class="alert alert-light border mb-0">
                  <div class="fw-semibold mb-1">Sık sorunlar</div>
                  <ul class="mb-0 small">
                    <li>“Kullanıcı adı alınamadı” görürsen: bazı hesaplarda platform username alanını paylaşmayabilir; bağlanma yine de geçerli olabilir.</li>
                    <li>Hesap görünmüyorsa: “Bağlantıyı kaldır” → tekrar bağla.</li>
                    <li>Çoklu hesap kullanımı: TikTok’ta doğru hesapla giriş yaptığından emin ol.</li>
                  </ul>
                </div>
              </div>
            </div>
          </div>

          <!-- 5) Test & yayın kuralları -->
          <div class="accordion-item">
            <h2 class="accordion-header" id="hTest">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#cTest" aria-expanded="false" aria-controls="cTest">
                5) Test paylaşımı ve temel kurallar
              </button>
            </h2>
            <div id="cTest" class="accordion-collapse collapse" aria-labelledby="hTest" data-bs-parent="#accHelp">
              <div class="accordion-body">
                <ul class="mb-0">
                  <li><strong>Facebook Post</strong> için medya zorunlu değildir (sadece metin olabilir).</li>
                  <li><strong>Instagram</strong> paylaşım tipine göre medya gerekir:
                    <ul class="mt-2">
                      <li>Post: image/video</li>
                      <li>Story: image/video</li>
                      <li>Reels: video</li>
                    </ul>
                  </li>
                  <li><strong>YouTube</strong> için video gereklidir; başlık/açıklama/etiketler opsiyoneldir.</li>
                  <li><strong>TikTok</strong> için video gereklidir; açıklama ve hashtag ile performans artar.</li>
                </ul>
              </div>
            </div>
          </div>

          <!-- 6) Common errors -->
          <div class="accordion-item">
            <h2 class="accordion-header" id="hErrors">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#cErrors" aria-expanded="false" aria-controls="cErrors">
                6) Sık görülen hatalar ve çözümler
              </button>
            </h2>
            <div id="cErrors" class="accordion-collapse collapse" aria-labelledby="hErrors" data-bs-parent="#accHelp">
              <div class="accordion-body">
                <div class="mb-3">
                  <div class="fw-semibold">❌ (#200) pages_manage_posts gerekiyor</div>
                  <div class="help-muted">Facebook Sayfasına post atmak için Meta tarafında gerekli izin/policy gerekir.</div>
                </div>

                <div class="mb-3">
                  <div class="fw-semibold">❌ Sayfa listesi gelmiyor / boş</div>
                  <div class="help-muted">
                    Facebook hesabının o sayfada Admin olup olmadığını kontrol et. Bazen “Facebook ile giriş yaptığım hesap”
                    ile “Sayfanın yöneticisi olan hesap” farklı çıkabiliyor.
                  </div>
                </div>

                <div class="mb-0">
                  <div class="fw-semibold">❌ Instagram hesabı listelenmiyor</div>
                  <div class="help-muted">
                    IG hesabı Business/Creator değilse ya da Facebook Sayfasına bağlı değilse listede görünmez.
                    Meta Business Suite’ten IG bağlantısını kontrol et.
                  </div>
                </div>

                <hr class="my-3">

                <div class="alert alert-warning mb-0">
                  <strong>En hızlı çözüm:</strong> Çoğu bağlantı/izin sorununda
                  <strong>Sosyal Hesaplar → “Bağlantıyı kaldır / Sıfırla”</strong> yapıp yeniden bağlamak işe yarar.
                </div>
              </div>
            </div>
          </div>

        </div><!-- /accordion -->

      </div>
    </div>

  </div>

  <!-- RIGHT: QUICK CHECKLIST -->
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

        <div class="small help-muted mb-2">
          Bu liste, Meta bağlantısında en sık takılan yerleri hızlıca kontrol etmen için.
        </div>

        <div class="form-check">
          <input class="form-check-input js-check"
                 type="checkbox"
                 id="cb_hasFbPage"
                 <?= $cbChecked($checks['hasFbPage'] ?? false) ?>
                 <?= $cbManual($checks['hasFbPage'] ?? false) ?>>
          <label class="form-check-label" for="cb_hasFbPage">Facebook Sayfam var (bağlı)</label>
        </div>

        <div class="form-check">
          <input class="form-check-input js-check"
                 type="checkbox"
                 id="cb_isFbAdmin"
                 <?= $cbChecked($checks['isFbAdmin'] ?? null) ?>
                 <?= $cbManual($checks['isFbAdmin'] ?? null) ?>>
          <label class="form-check-label" for="cb_isFbAdmin">Sayfada Admin’im</label>
          <div class="small help-muted ms-4">Bu maddeyi sistem her zaman net doğrulayamayabilir.</div>
        </div>

        <div class="form-check">
          <input class="form-check-input js-check"
                 type="checkbox"
                 id="cb_isIgBusiness"
                 <?= $cbChecked($checks['isIgBusiness'] ?? null) ?>
                 <?= $cbManual($checks['isIgBusiness'] ?? null) ?>>
          <label class="form-check-label" for="cb_isIgBusiness">Instagram Business/Creator</label>
        </div>

        <div class="form-check mb-3">
          <input class="form-check-input js-check"
                 type="checkbox"
                 id="cb_igLinkedToPage"
                 <?= $cbChecked($checks['igLinkedToPage'] ?? false) ?>
                 <?= $cbManual($checks['igLinkedToPage'] ?? false) ?>>
          <label class="form-check-label" for="cb_igLinkedToPage">IG → Facebook Sayfasına bağlı</label>
        </div>

        <div class="alert alert-warning mb-3">
          <strong>Not:</strong> Token/izin sorunlarında bazen en hızlı çözüm
          <strong>Sosyal Hesaplar → “Sıfırla”</strong> ile yeniden bağlanmaktır.
        </div>

        <a id="btnGoWizard"
           href="<?= site_url('panel/social-accounts/meta/wizard') ?>"
           class="btn btn-primary w-100"
           style="display:none;">
          Meta Sihirbazına Git
        </a>

        <a id="btnGoSocial"
           href="<?= site_url('panel/social-accounts') ?>"
           class="btn btn-outline-secondary w-100">
          Sosyal Hesaplara Git
        </a>

        <div class="small help-muted mt-3">
          YouTube ve TikTok bağlantıları için doğrudan <strong>Sosyal Hesaplar</strong> sayfasındaki butonları kullanabilirsin.
        </div>
      </div>
    </div>

  </div>
</div>

<script>
(function () {
  const btnWizard = document.getElementById('btnGoWizard');
  const btnSocial = document.getElementById('btnGoSocial');
  const checks = [
    document.getElementById('cb_hasFbPage'),
    document.getElementById('cb_isFbAdmin'),
    document.getElementById('cb_isIgBusiness'),
    document.getElementById('cb_igLinkedToPage'),
  ].filter(Boolean);

  function refresh() {
    const allOk = checks.every(ch => ch.checked);

    // Hazırsa wizard butonu göster, değilse sosyal hesaplar butonu
    if (allOk) {
      btnWizard.style.display = '';
      btnSocial.style.display = 'none';
    } else {
      btnWizard.style.display = 'none';
      btnSocial.style.display = '';
    }
  }

  checks.forEach(ch => ch.addEventListener('change', refresh));
  refresh();
})();
</script>

<?= $this->endSection() ?>
