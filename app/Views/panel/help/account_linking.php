<?= $this->extend('layouts/panel') ?>
<?= $this->section('content') ?>

<?php
// Controller'dan beklenenler:
// $stats  = ['fb_count'=>0/1, 'ig_count'=>0/1]
// $checks = [
//   'hasFbPage'      => true/false,
//   'hasIgConnected' => true/false,
//   'igLinkedToPage' => true/false,
//   'isFbAdmin'      => null|true|false  (null => manuel)
//   'isIgBusiness'   => null|true|false  (null => manuel)
// ];

$stats  = $stats  ?? ['fb_count' => 0, 'ig_count' => 0];
$checks = $checks ?? [
  'hasFbPage' => false,
  'hasIgConnected' => false,
  'igLinkedToPage' => false,
  'isFbAdmin' => null,
  'isIgBusiness' => null,
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

<div class="d-flex align-items-start justify-content-between mb-3">
  <div>
    <h1 class="h3 mb-1"><?= esc($title ?? 'Yardım / Hesap Bağlama Rehberi') ?></h1>
    <div class="text-muted">Instagram & Facebook hesaplarını bağlamak için adım adım rehber.</div>
  </div>

  <a class="btn btn-outline-secondary" href="<?= site_url('panel/social-accounts') ?>">
    Sosyal Hesaplara Git
  </a>
</div>

<div class="alert alert-info">
  <strong>Kısa özet:</strong> Instagram paylaşımı için hesabın <strong>Business/Creator</strong> olması ve bir
  <strong>Facebook Sayfasına bağlı</strong> olması gerekir. Facebook paylaşımı da bir <strong>Sayfa</strong> üzerinden yapılır.
</div>

<div class="row g-3">
  <div class="col-lg-7">

    <div class="card mb-3">
      <div class="card-header"><strong>1) Başlamadan önce gerekli şartlar</strong></div>
      <div class="card-body">
        <ul class="mb-0">
          <li><strong>Facebook Sayfası</strong> oluşturulmuş olmalı (Kişisel profil değil).</li>
          <li>Paylaşım atılacak Facebook Sayfasında rolün <strong>Yönetici (Admin)</strong> olmalı.</li>
          <li><strong>Instagram hesabı Business/Creator</strong> olmalı.</li>
          <li>Instagram hesabı, ilgili <strong>Facebook Sayfasına bağlanmış</strong> olmalı (Meta Business Suite / IG ayarları).</li>
        </ul>
      </div>
    </div>

    <div class="card mb-3">
      <div class="card-header"><strong>2) Otomedya’da Meta ile bağlanma</strong></div>
      <div class="card-body">
        <ol class="mb-0">
          <li><strong>Panel → Sosyal Hesaplar</strong> sayfasına gir.</li>
          <li><strong>“Instagram’ı Meta ile Bağla”</strong> butonuna tıkla.</li>
          <li>Meta giriş ekranı gelirse Facebook hesabınla giriş yap.</li>
          <li>
            “Otomedya Social Planner’in erişmesini istediğin Sayfalar/İşletmeler/Instagram hesapları” ekranlarında:
            <ul class="mt-2">
              <li>Genelde en sorunsuz seçenek: <strong>“Mevcut ve gelecekteki tüm … öğelerine onay ver”</strong></li>
              <li>Kurumsal/çok sayfalı kullanımda: sadece gereken sayfaları seçebilirsin.</li>
            </ul>
          </li>
          <li>Son ekranda <strong>Kaydet / Devam</strong> diyerek izinleri tamamla.</li>
          <li>Uygulamaya dönünce “Meta bağlantısı tamamlandı” görürsün.</li>
        </ol>
      </div>
    </div>

    <div class="card mb-3">
      <div class="card-header"><strong>3) Instagram hesabını “Bağla” ile ekleme</strong></div>
      <div class="card-body">
        <ol class="mb-0">
          <li>Sihirbaz ekranında listelenen Facebook Sayfanı görmelisin.</li>
          <li>Sayfanın altındaki ilgili Instagram hesabını seçip <strong>“Bağla”</strong> de.</li>
          <li>Sonrasında <strong>Sosyal Hesaplar → Hesap Listesi</strong> tablosunda Facebook ve Instagram satırları görünür.</li>
        </ol>
      </div>
    </div>

    <div class="card mb-3">
      <div class="card-header"><strong>4) Test paylaşımı</strong></div>
      <div class="card-body">
        <ul class="mb-0">
          <li><strong>Facebook Post</strong> için medya zorunlu değildir (sadece metin atılabilir).</li>
          <li><strong>Instagram</strong> için paylaşım tipine göre medya kuralları vardır:
            <ul class="mt-2">
              <li>Post: image/video</li>
              <li>Story: image/video</li>
              <li>Reels: video</li>
            </ul>
          </li>
          <li>Paylaşım sonrası “Paylaşım Bağlantısı / ID” görürsen yayın başarılıdır.</li>
        </ul>
      </div>
    </div>

    <div class="card mb-3">
      <div class="card-header"><strong>5) Sık görülen hatalar ve çözümler</strong></div>
      <div class="card-body">
        <div class="mb-3">
          <div><strong>❌ (#200) pages_manage_posts gerekiyor</strong></div>
          <div class="text-muted">
            Facebook Sayfasına post atmak için Meta tarafında gerekli izin/policy gerekir.
          </div>
        </div>

        <div class="mb-3">
          <div><strong>❌ Sayfa listesi gelmiyor / boş</strong></div>
          <div class="text-muted">
            Facebook hesabının o sayfada Admin olup olmadığını kontrol et. Bazen “Facebook ile giriş yaptığım hesap”
            ile “Sayfanın yöneticisi olan hesap” farklı çıkabiliyor.
          </div>
        </div>

        <div class="mb-0">
          <div><strong>❌ Instagram hesabı listelenmiyor</strong></div>
          <div class="text-muted">
            IG hesabı Business/Creator değilse ya da Facebook Sayfasına bağlı değilse listede görünmez.
            Meta Business Suite’ten IG bağlantısını kontrol et.
          </div>
        </div>
      </div>
    </div>

  </div>

  <div class="col-lg-5">

    <div class="card mb-3">
      <div class="card-header"><strong>Hızlı Kontrol Listesi</strong></div>
      <div class="card-body">

        <div class="small text-muted mb-2">
          Bağlı hesaplar: FB <?= (int)($stats['fb_count'] ?? 0) ?> • IG <?= (int)($stats['ig_count'] ?? 0) ?>
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
          <div class="small text-muted ms-4">Bu maddeyi sistem her zaman net doğrulayamayabilir.</div>
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
          Hesap Bağlamaya Git
        </a>

        <a id="btnGoSocial"
           href="<?= site_url('panel/social-accounts') ?>"
           class="btn btn-outline-secondary w-100">
          Sosyal Hesaplara Git
        </a>

      </div>
    </div>

    <div class="card">
      <div class="card-header"><strong>Yakında eklenecek</strong></div>
      <div class="card-body">
        <ul class="mb-0">
          <li>TikTok hesap bağlama rehberi (OAuth)</li>
          <li>YouTube/Google hesap bağlama rehberi</li>
          <li>SSS + ekran görüntüleri ile görsel anlatım</li>
        </ul>
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
