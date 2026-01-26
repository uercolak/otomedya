<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <title>İletişim | Sosyal Medya Planlama</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" type="image/png" href="<?= base_url('/logo2.png'); ?>">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    :root{
      --bg1:#0b1020;
      --bg2:#0f172a;
      --card:#0b1224cc;
      --border:rgba(255,255,255,.10);
      --muted:rgba(255,255,255,.70);
      --muted2:rgba(255,255,255,.55);
      --text:#f8fafc;
    }

    body{
      min-height:100vh;
      background:
        radial-gradient(1200px 600px at 20% 10%, rgba(168,85,247,.25), transparent 60%),
        radial-gradient(900px 500px at 75% 35%, rgba(59,130,246,.22), transparent 55%),
        radial-gradient(900px 500px at 40% 90%, rgba(236,72,153,.18), transparent 60%),
        linear-gradient(180deg, var(--bg1), var(--bg2));
      color:var(--text);
    }

    .auth-shell{ min-height:100vh; display:flex; align-items:stretch; }
    .auth-left{
      flex: 1 1 58%;
      padding:48px 48px 24px 48px;
      position:relative;
      overflow:hidden;
    }

    .brand{ display:flex; align-items:center; gap:14px; margin-bottom:28px; }
    .brand-logo{
      width:110px; height:75px; border-radius:18px;
      background: rgba(255,255,255,.14);
      border: 1px solid rgba(255,255,255,.22);
      display:flex; align-items:center; justify-content:center;
      overflow:hidden;
      box-shadow:
        0 18px 45px rgba(0,0,0,.35),
        0 0 0 6px rgba(255,255,255,.05),
        0 0 40px rgba(168,85,247,.22);
      backdrop-filter: blur(10px);
      -webkit-backdrop-filter: blur(10px);
    }
    .brand-logo img{
      width:110px; height:75px; object-fit:cover; display:block;
      filter: saturate(1.15) contrast(1.15) brightness(1.05);
    }
    .brand-title{ font-weight:700; letter-spacing:.2px; font-size:18px; line-height:1.1; }
    .brand-sub{ color:var(--muted2); font-size:13px; margin-top:2px; }

    .hero{ max-width: 640px; margin-top: 36px; }
    .hero h1{
      font-weight:800;
      font-size:40px;
      letter-spacing:-.6px;
      margin:0 0 12px 0;
    }
    .hero p{
      color:var(--muted);
      font-size:16px;
      line-height:1.6;
      margin:0;
      max-width: 520px;
    }

    .auth-right{
    flex: 0 0 520px;
    padding:48px 48px 80px 24px; 
    display:flex;
    flex-direction:column;
    justify-content:center;
    }

    .card-auth{
      border:1px solid var(--border);
      background: var(--card);
      backdrop-filter: blur(14px);
      -webkit-backdrop-filter: blur(14px);
      border-radius: 22px;
      box-shadow: 0 22px 60px rgba(0,0,0,.35);
      padding: 28px;
    }

    .card-auth h2{
      font-weight:800;
      font-size:26px;
      margin:0 0 6px 0;
      letter-spacing:-.3px;
    }
    .card-auth .sub{
      color:var(--muted);
      margin:0 0 18px 0;
      font-size:14px;
    }

    .form-label{ color: rgba(255,255,255,.75) !important; }
    .form-control{
      background: rgba(255,255,255,.08) !important;
      border: 1px solid rgba(255,255,255,.14) !important;
      color: var(--text) !important;
      border-radius: 14px !important;
      padding: 12px 12px !important;
    }
    .form-control::placeholder{ color: rgba(255,255,255,.45) !important; }
    .form-control:focus{
      box-shadow: 0 0 0 .25rem rgba(168,85,247,.18) !important;
      border-color: rgba(168,85,247,.45) !important;
    }

    .btn-gradient{
      border:0;
      border-radius: 14px;
      padding: 12px 14px;
      font-weight:700;
      color:#fff;
      background: linear-gradient(90deg, rgba(99,102,241,1), rgba(168,85,247,1), rgba(236,72,153,1));
      box-shadow: 0 12px 28px rgba(0,0,0,.28);
    }
    .btn-gradient:hover{ filter: brightness(1.06); transform: translateY(-1px); }

    .small-muted{ color: var(--muted2); font-size: 13px; }

    .back-link{
      color: rgba(255,255,255,.75);
      text-decoration:none;
      border-bottom: 1px solid rgba(255,255,255,.18);
    }
    .back-link:hover{
      color: rgba(255,255,255,.92);
      border-bottom-color: rgba(255,255,255,.42);
    }

    .page-footer{
      position:absolute;
      left:48px;
      bottom:20px;
      color: rgba(255,255,255,.45);
      font-size: 12px;
    }

    /* bottom-right site links */
    .site-links{
      position: fixed;
      right: 28px;
      bottom: 18px;
      display:flex;
      gap:14px;
      align-items:center;
      flex-wrap:wrap;
      z-index: 10;
      color: rgba(255,255,255,.55);
      font-size: 12.5px;
      padding-top:6px;
    }
    .site-links a{
      color: rgba(255,255,255,.72);
      text-decoration:none;
      border-bottom: 1px solid rgba(255,255,255,.18);
    }
    .site-links a:hover{
      color: rgba(255,255,255,.92);
      border-bottom-color: rgba(255,255,255,.42);
    }
    .site-links .sep{ opacity:.55; }

    @media (max-width: 1100px){
      .auth-shell{ flex-direction:column; }
      .auth-right{ flex: 1 1 auto; padding: 16px 20px 26px 20px; }
      .auth-left{ padding: 28px 20px 18px 20px; }
      .page-footer{ position: static; margin-top: 22px; }
      .hero h1{ font-size: 32px; }
      .site-links{
        position: static;
        margin: 14px 20px 18px 20px;
        justify-content:center;
      }
    }
  </style>
</head>
<body>

<div class="auth-shell">
  <div class="auth-left">

    <a href="<?= base_url('/') ?>" class="back-link">&larr; Giriş sayfasına dön</a>

    <div class="brand mt-3">
      <div class="brand-logo">
        <img src="<?= base_url('/logo.png'); ?>" alt="Sosyal Panel">
      </div>
      <div>
        <div class="brand-title">Sosyal Medya Planlama</div>
        <div class="brand-sub">Planla • Yayınla • Yönet</div>
      </div>
    </div>

    <div class="hero">
      <h1>İletişim</h1>
        <p>
        Destek ve başvuru için bize e-posta gönderebilirsin:
        <a class="back-link" href="mailto:info@sosyalmedyaplanlama.com">info@sosyalmedyaplanlama.com</a>
        </p>
    </div>

    <div class="page-footer">
      &copy; <?= date('Y'); ?> Sosyal Panel. Tüm hakları saklıdır.
    </div>
  </div>

  <div class="auth-right">
    <div class="card-auth">
      <h2>Destek & Başvuru</h2>
            <p class="sub">
            Destek talebi veya iş birliği / başvuru için bize yazabilirsin.
            İstersen e-posta ile de ulaşabilirsin.
            </p>

      <?php if (session('success')): ?>
        <div class="alert alert-success"><?= esc(session('success')) ?></div>
      <?php endif; ?>

      <form method="post" action="<?= base_url('/contact') ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="recaptcha_token" id="recaptcha_token">
        <div class="mb-3">
          <label class="form-label small">Ad Soyad</label>
          <input type="text" name="name" class="form-control" placeholder="Adınız Soyadınız">
        </div>

        <div class="mb-3">
          <label class="form-label small">E-posta</label>
          <input type="email" name="email" class="form-control" placeholder="ornek@mail.com">
        </div>

        <div class="mb-3">
          <label class="form-label small">Mesaj</label>
          <textarea name="message" class="form-control" rows="5" placeholder="Kısaca mesajınızı yazın..."></textarea>
        </div>

        <input type="text" name="website" style="display:none">

        <button type="submit" class="btn btn-gradient w-100">Gönder</button>

       <div class="mt-4 p-3 rounded small-muted" style="background:rgba(255,255,255,.04);">
            Not: Bu form destek ve başvuru talepleri içindir.
            Güvenlik amacıyla gerektiğinde ek doğrulama isteyebiliriz.
        </div>
      </form>
    </div>
  </div>
</div>
  <div class="site-links">
  <a href="<?= base_url('/terms'); ?>">Kullanım Şartları</a>
  <span class="sep">•</span>
  <a href="<?= base_url('/privacy'); ?>">Gizlilik Politikası</a>
  <span class="sep">•</span>
  <a href="<?= base_url('/data-deletion'); ?>">Veri Silme Politikası</a>
  <span class="sep">•</span>
  <a href="<?= base_url('/contact'); ?>">İletişim</a>
</div>

<script src="https://www.google.com/recaptcha/api.js?render=6Lc7MVcsAAAAACA7DWi66XwgT6PXQLrKKyhjKhIW"></script>
<script>
grecaptcha.ready(function () {
  grecaptcha.execute('6Lc7MVcsAAAAACA7DWi66XwgT6PXQLrKKyhjKhIW', {action: 'contact'}).then(function (token) {
    document.getElementById('recaptcha_token').value = token;
  });
});
</script>

</body>
</html>
