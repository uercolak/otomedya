<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <title>Giriş Yap | Sosyal Panel</title>
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

        .auth-shell{
            min-height:100vh;
            display:flex;
            align-items:stretch;
        }

        .auth-left{
            flex: 1 1 58%;
            padding:48px 48px 24px 48px;
            position:relative;
            overflow:hidden;
        }

        .brand{
            display:flex;
            align-items:center;
            gap:14px;
            margin-bottom:34px;
        }

        .brand-logo{
            width:85px;
            height:85px;
            border-radius:16px;
            background: rgba(255,255,255,.08);
            border:1px solid var(--border);
            display:flex;
            align-items:center;
            justify-content:center;
            overflow:hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,.25);
        }

        .brand-logo img{
            width:85px;
            height:85px;
            object-fit:contain;
            display:block;
        }

        .brand-title{
            font-weight:700;
            letter-spacing:.2px;
            font-size:18px;
            line-height:1.1;
        }
        .brand-sub{
            color:var(--muted2);
            font-size:13px;
            margin-top:2px;
        }

        .hero{
            max-width: 640px;
            margin-top: 36px;
        }
        .hero h1{
            font-weight:800;
            font-size:44px;
            letter-spacing:-.6px;
            margin:0 0 14px 0;
        }
        .hero p{
            color:var(--muted);
            font-size:16px;
            line-height:1.6;
            margin:0;
            max-width: 520px;
        }

        .feature-row{
            margin-top: 26px;
            display:flex;
            gap:12px;
            flex-wrap:wrap;
        }
        .pill{
            display:inline-flex;
            align-items:center;
            gap:8px;
            padding:10px 12px;
            border-radius:999px;
            border:1px solid var(--border);
            background: rgba(255,255,255,.06);
            color: rgba(255,255,255,.86);
            font-size:13px;
        }
        .dot{
            width:8px;height:8px;border-radius:50%;
            background: linear-gradient(135deg, rgba(168,85,247,.9), rgba(59,130,246,.9));
            box-shadow: 0 0 18px rgba(168,85,247,.45);
        }

        .auth-right{
            flex: 0 0 520px;
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

        .form-label{
            color: rgba(255,255,255,.75) !important;
        }

        .form-control{
            background: rgba(255,255,255,.08) !important;
            border: 1px solid rgba(255,255,255,.14) !important;
            color: var(--text) !important;
            border-radius: 14px !important;
            padding: 12px 12px !important;
        }
        .form-control::placeholder{
            color: rgba(255,255,255,.45) !important;
        }
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
        .btn-gradient:hover{
            filter: brightness(1.06);
            transform: translateY(-1px);
        }

        .small-muted{
            color: var(--muted2);
            font-size: 13px;
        }

        .legal-footer{
            margin-top: 18px;
            display:flex;
            gap:14px;
            flex-wrap:wrap;
            align-items:center;
            justify-content:center;
            color: rgba(255,255,255,.55);
            font-size: 12.5px;
        }

        .legal-footer a{
            color: rgba(255,255,255,.72);
            text-decoration:none;
            border-bottom: 1px solid rgba(255,255,255,.18);
        }
        .legal-footer a:hover{
            color: rgba(255,255,255,.92);
            border-bottom-color: rgba(255,255,255,.42);
        }

        .page-footer{
            position: absolute;
            left: 48px;
            bottom: 20px;
            color: rgba(255,255,255,.45);
            font-size: 12px;
        }

        /* Responsive */
        @media (max-width: 1100px){
            .auth-shell{ flex-direction:column; }
            .auth-right{ flex: 1 1 auto; padding: 16px 20px 26px 20px; }
            .auth-left{ padding: 28px 20px 18px 20px; }
            .page-footer{ position: static; margin-top: 22px; }
            .hero h1{ font-size: 34px; }
        }
    </style>
</head>
<body>
<div class="auth-shell">
    <div class="auth-left">
        <div class="brand">
            <div class="brand-logo">
                <img src="<?= base_url('/logo.png'); ?>" alt="Sosyal Panel">
            </div>
            <div>
                <div class="brand-title">Sosyal Panel</div>
                <div class="brand-sub">Planla • Yayınla • Yönet</div>
            </div>
        </div>

        <div class="hero">
            <h1>Tek panelden tüm içeriklerini yönet.</h1>
            <p>
                Facebook, Instagram, YouTube ve TikTok gönderilerini tek takvim üzerinden planla,
                otomatik olarak yayınla ve süreçlerini takip et.
            </p>

            <div class="feature-row">
                <div class="pill"><span class="dot"></span> Planlama & Takvim</div>
                <div class="pill"><span class="dot"></span> Otomatik Yayınlama</div>
                <div class="pill"><span class="dot"></span> Hata Takibi & Log</div>
            </div>
        </div>

        <div class="page-footer">
            &copy; <?= date('Y'); ?> Sosyal Panel. Tüm hakları saklıdır.
        </div>
    </div>

    <div class="auth-right">
        <div class="card-auth">
            <h2>Giriş Yap</h2>
            <p class="sub">Hesabına giriş yaparak planlanmış içeriklerini yönetebilirsin.</p>

            <?php $errors = session('errors') ?? []; ?>

            <form method="post" action="<?= base_url('auth/login'); ?>">
                <?= csrf_field(); ?>

                <div class="mb-3">
                    <label class="form-label small">E-posta</label>
                    <input type="email" name="email"
                           class="form-control"
                           value="<?= old('email'); ?>"
                           placeholder="ornek@mail.com">
                    <?php if (isset($errors['email'])): ?>
                        <div class="text-danger small mt-1"><?= esc($errors['email']); ?></div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label class="form-label small">Şifre</label>
                    <input type="password" name="password"
                           class="form-control"
                           placeholder="••••••••">
                    <?php if (isset($errors['password'])): ?>
                        <div class="text-danger small mt-1"><?= esc($errors['password']); ?></div>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn btn-gradient w-100">
                    Giriş Yap
                </button>

                <div class="mt-3 small-muted">
                    Panel erişimi için lütfen sistem yöneticinizle iletişime geçin.
                </div>

                <!-- ✅ Legal linkler sadece burada (tek yer) -->
                <div class="legal-footer">
                    <a href="<?= base_url('/terms'); ?>">Kullanım Şartları</a>
                    <span>•</span>
                    <a href="<?= base_url('/privacy'); ?>">Gizlilik Politikası</a>
                    <span>•</span>
                    <a href="<?= base_url('/data-deletion'); ?>">Veri Silme Politikası</a>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
