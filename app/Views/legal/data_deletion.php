<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <title>Veri Silme Politikası | Sosyal Medya Planlama</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="icon" type="image/png" href="<?= base_url('/logo2.png'); ?>">
</head>
<body class="bg-light">
  <div class="container py-5">
    <div class="mb-4">
      <a href="<?= base_url('/auth/login'); ?>" class="text-decoration-none">&larr; Giriş sayfasına dön</a>
    </div>

    <div class="card shadow-sm">
      <div class="card-body p-4 p-md-5">
        <h1 class="mb-2">Veri Silme Politikası</h1>
        <p class="text-muted mb-4">Son güncelleme: <?= date('Y-m-d'); ?></p>

        <p>
          Sosyal Medya Planlama’da hesabınıza ait verilerin silinmesini talep edebilirsiniz.
          Talebiniz alındıktan sonra kimlik doğrulaması yaparak işlemi tamamlarız.
        </p>

        <h2 class="h5 mt-4">Silme Talebi Nasıl Yapılır?</h2>
        <ol>
          <li>
            <a href="mailto:destek@sosyalmedyaplanlama.com?subject=Veri%20Silme%20Talebi">destek@sosyalmedyaplanlama.com</a>
            adresine e-posta gönderin.
          </li>
          <li>E-postada hesabınıza kayıtlı e-posta adresinizi ve mümkünse kullanıcı ID’nizi belirtin.</li>
          <li>Güvenlik için sizden ek doğrulama isteyebiliriz.</li>
        </ol>

        <h2 class="h5 mt-4">Neler Silinir?</h2>
        <ul>
          <li>Hesap profili ve oturumla ilişkili temel veriler</li>
          <li>Bağlanan sosyal hesap kayıtları</li>
          <li>Planlanan içerikler / yayın kayıtları (yasal zorunluluk yoksa)</li>
          <li>OAuth token kayıtları (erişim/yenileme belirteçleri)</li>
        </ul>

        <h2 class="h5 mt-4">Neler Silinmeyebilir?</h2>
        <ul>
          <li>Yasal yükümlülükler nedeniyle saklanması gereken kayıtlar</li>
          <li>Dolandırıcılık/güvenlik amaçlı minimal log kayıtları (mümkünse anonimleştirilmiş)</li>
        </ul>

        <h2 class="h5 mt-4">Tamamlanma Süresi</h2>
        <p class="mb-0">
          Talebiniz doğrulandıktan sonra silme işlemini makul süre içinde tamamlarız.
        </p>

        <h2 class="h5 mt-4">İletişim</h2>
        <p class="mb-0">
          <a href="mailto:destek@sosyalmedyaplanlama.com">destek@sosyalmedyaplanlama.com</a>
        </p>

        <hr class="my-4">
        <p class="text-muted small mb-0">
          Not: Bu metin bilgilendirme amaçlıdır ve hukuki danışmanlık değildir. Gerekirse hukuk danışmanınızla görüşünüz.
        </p>
      </div>
    </div>
  </div>
</body>
</html>
