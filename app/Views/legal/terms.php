<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <title>Kullanım Şartları | Sosyal Medya Planlama</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" type="image/png" href="<?= base_url('/logo2.png'); ?>">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    :root{
      --bg1:#0b1020; --bg2:#0f172a; --card:#0b1224cc;
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
      flex:1 1 58%;
      padding:48px 48px 24px 48px;
      position:relative;
      overflow:hidden;
    }

    .brand{ display:flex; align-items:center; gap:14px; margin:18px 0 26px 0; }
    /* LOGO ÇERÇEVESİ İSTEMİYORDUN: sadece görsel kalsın */
    .brand-logo{ width:110px; height:75px; border-radius:18px; overflow:hidden; }
    .brand-logo img{ width:110px; height:75px; object-fit:cover; display:block; }

    .brand-title{ font-weight:700; letter-spacing:.2px; font-size:18px; line-height:1.1; }
    .brand-sub{ color:var(--muted2); font-size:13px; margin-top:2px; }

    .hero{ max-width: 680px; margin-top: 16px; }
    .hero h1{ font-weight:800; font-size:40px; letter-spacing:-.6px; margin:0 0 10px 0; }
    .hero p{ color:var(--muted); font-size:16px; line-height:1.6; margin:0; max-width: 560px; }

    .auth-right{
      flex:0 0 620px;
      padding:48px 48px 24px 24px;
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
      padding: 26px;
      max-height: calc(100vh - 96px);
      overflow:auto;
    }
    .card-auth h2{ font-weight:800; font-size:24px; margin:0 0 6px 0; letter-spacing:-.3px; }
    .card-auth .sub{ color:var(--muted); margin:0 0 14px 0; font-size:13.5px; }

    .legal h3{ font-size:15px; font-weight:800; margin-top:18px; margin-bottom:8px; }
    .legal p, .legal li{
      color: rgba(255,255,255,.78);
      font-size: 14px;
      line-height: 1.65;
    }
    .legal ul, .legal ol{ padding-left: 18px; margin-bottom: 12px; }
    .legal a{ color: rgba(255,255,255,.86); text-decoration:none; border-bottom:1px solid rgba(255,255,255,.22); }
    .legal a:hover{ color:#fff; border-bottom-color: rgba(255,255,255,.45); }

    .back-link{
      color: rgba(255,255,255,.75);
      text-decoration:none;
      border-bottom: 1px solid rgba(255,255,255,.18);
    }
    .back-link:hover{ color: rgba(255,255,255,.92); border-bottom-color: rgba(255,255,255,.42); }

    .page-footer{
      position:absolute;
      left:48px;
      bottom:20px;
      color: rgba(255,255,255,.45);
      font-size: 12px;
    }

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
      padding-top: 6px;
    }
    .site-links a{
      color: rgba(255,255,255,.72);
      text-decoration:none;
      border-bottom: 1px solid rgba(255,255,255,.18);
    }
    .site-links a:hover{ color: rgba(255,255,255,.92); border-bottom-color: rgba(255,255,255,.42); }
    .site-links .sep{ opacity:.55; }

    @media (max-width: 1100px){
      .auth-shell{ flex-direction:column; }
      .auth-right{ flex:1 1 auto; padding: 16px 20px 26px 20px; }
      .auth-left{ padding: 28px 20px 18px 20px; }
      .page-footer{ position: static; margin-top: 18px; }
      .hero h1{ font-size: 32px; }
      .site-links{
        position: static;
        margin: 10px 20px 18px 20px;
        justify-content:center;
      }
      .card-auth{ max-height: none; }
    }
  </style>
</head>
<body>
<?php $lastUpdated = $lastUpdated ?? '2026-01-27'; ?>

<div class="auth-shell">

  <div class="auth-left">
    <a href="<?= base_url('/') ?>" class="back-link">&larr; Giriş sayfasına dön</a>

    <div class="brand">
      <div class="brand-logo">
        <img src="<?= base_url('/logo.png'); ?>" alt="Sosyal Medya Planlama">
      </div>
      <div>
        <div class="brand-title">Sosyal Medya Planlama</div>
        <div class="brand-sub">Planla • Yayınla • Yönet</div>
      </div>
    </div>

    <div class="hero">
      <h1>Kullanım Şartları</h1>
      <p>
        Hizmeti kullanarak bu şartları kabul etmiş olursun. Amaç: yetki, sorumluluk ve yayın süreçlerini netleştirmek.
      </p>
    </div>

    <div class="page-footer">
      &copy; <?= date('Y'); ?> Sosyal Panel. Tüm hakları saklıdır.
    </div>
  </div>

  <div class="auth-right">
    <div class="card-auth">
      <h2>Kullanım Şartları</h2>
      <p class="sub">Son güncelleme: <?= esc($lastUpdated) ?></p>

      <div class="legal">
        <p>
          Bu Kullanım Şartları (“Şartlar”), Sosyal Medya Planlama (“Hizmet”) ile ilgili kullanım koşullarını açıklar.
          Hizmeti kullanarak bu Şartları kabul etmiş olursunuz.
        </p>

        <h3>1) Hizmetin Kapsamı</h3>
        <ul>
          <li>Hizmet; kullanıcıların kendi sosyal medya hesaplarını bağlayarak içerik planlaması ve yayınlaması için sunulur.</li>
          <li>Hizmet; Facebook, Instagram, YouTube ve TikTok gibi üçüncü taraf platformlarla entegrasyonlar içerebilir.</li>
          <li>Üçüncü taraf platformlar; kendi kurallarını, kota/limitlerini ve onay süreçlerini uygulayabilir.</li>
        </ul>

        <h3>2) Uygun Kullanım ve Sorumluluk</h3>
        <ul>
          <li>Yalnızca size ait olan veya yönetim yetkinizin bulunduğu hesaplarda işlem yapmalısınız.</li>
          <li>Paylaştığınız içeriklerin (metin, görsel, video, müzik vb.) hukuka uygunluğundan ve telif haklarından siz sorumlusunuz.</li>
          <li>Hizmet; yasa dışı içerik, nefret söylemi, şiddet, yanıltıcı bilgi, spam veya platform kurallarına aykırı kullanım için kullanılamaz.</li>
        </ul>

        <h3>3) Hesap Bağlama (OAuth) ve Yetkiler</h3>
        <ul>
          <li>Hesap bağlama işlemi, ilgili platformların OAuth/izin ekranları üzerinden yürütülür.</li>
          <li>Verilen yetkiler, yalnızca kullanıcının talep ettiği işlemleri gerçekleştirmek (ör. planlı paylaşım) için kullanılır.</li>
          <li>Bağlantıyı dilediğiniz zaman kaldırabilir; hesap yetkilerini ilgili platform ayarlarından iptal edebilirsiniz.</li>
        </ul>

        <h3>4) Planlama, Kuyruk ve Yayınlama</h3>
        <ul>
          <li>Planlanan paylaşımlar sistemde kuyruğa alınır ve belirlenen zamanda çalıştırılmaya çalışılır.</li>
          <li>Üçüncü taraf API kesintileri, kota/limit, içerik kısıtları veya platform reddi nedeniyle paylaşım başarısız olabilir.</li>
          <li>Başarısız işlemler için hata detayları panelde görüntülenebilir; belirli hatalarda yeniden deneme yapılmayabilir.</li>
        </ul>

        <h3>5) Kesintiler ve Garanti Reddi</h3>
        <p>
          Hizmet “olduğu gibi” sunulur. Kesintisiz, hatasız veya her zaman başarılı yayınlama garantisi verilmez.
          Üçüncü taraf platformların değişiklikleri/limitleri Hizmetin bazı özelliklerini etkileyebilir.
        </p>

        <h3>6) Hesabın Askıya Alınması</h3>
        <p>
          Şartlara aykırı kullanım, kötüye kullanım veya güvenlik riski tespit edilmesi halinde hesabınız geçici veya kalıcı olarak askıya alınabilir.
        </p>

        <h3>7) Değişiklikler</h3>
        <p>Şartlar zaman zaman güncellenebilir. Güncellenmiş metin bu sayfada yayınlanır.</p>

        <h3>8) İletişim</h3>
        <p class="mb-0">
          Sorularınız için: <a href="mailto:info@sosyalmedyaplanlama.com">info@sosyalmedyaplanlama.com</a>
        </p>

        <hr style="border-color: rgba(255,255,255,.10);" class="my-4">
        <p style="color: rgba(255,255,255,.55); font-size:12.5px;" class="mb-0">
          Not: Bu metin bilgilendirme amaçlıdır ve hukuki danışmanlık değildir. Gerekirse hukuk danışmanınızla görüşünüz.
        </p>
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

</div>
</body>
</html>
