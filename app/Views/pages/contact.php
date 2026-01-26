<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <title>İletişim | Sosyal Medya Planlama</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container py-4">

    <a href="<?= base_url('/') ?>" class="text-decoration-none">&larr; Giriş sayfasına dön</a>

    <div class="card shadow-sm mt-3">
      <div class="card-body p-4">
        <h1 class="h3 mb-2">İletişim</h1>
        <p class="text-muted mb-4">
          Bizimle şu adresten iletişime geçebilirsiniz:
          <a href="mailto:info@sosyalmedyaplanlama.com">info@sosyalmedyaplanlama.com</a>
        </p>

        <?php if (session('success')): ?>
          <div class="alert alert-success"><?= esc(session('success')) ?></div>
        <?php endif; ?>

        <form method="post" action="<?= base_url('/contact') ?>">
          <?= csrf_field() ?>

          <div class="mb-3">
            <label class="form-label">Ad Soyad</label>
            <input type="text" name="name" class="form-control" placeholder="Adınız Soyadınız">
          </div>

          <div class="mb-3">
            <label class="form-label">E-posta</label>
            <input type="email" name="email" class="form-control" placeholder="ornek@mail.com">
          </div>

          <div class="mb-3">
            <label class="form-label">Mesaj</label>
            <textarea name="message" class="form-control" rows="5" placeholder="Kısaca mesajınızı yazın..."></textarea>
          </div>

          <button type="submit" class="btn btn-primary">
            Gönder
          </button>

          <p class="small text-muted mt-3 mb-0">
            Not: Bu form destek talebi amacıyla kullanılır. Gerektiğinde ek doğrulama isteyebiliriz.
          </p>
        </form>
      </div>
    </div>

  </div>
</body>
</html>
