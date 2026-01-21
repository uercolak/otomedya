<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <title>Giriş Yap | Sosyal Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="icon" type="image/png" href="<?= base_url('/logo2.png'); ?>">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= base_url('/assets/css/auth.css'); ?>" rel="stylesheet">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-side">
        <div>
            <div class="auth-brand mb-4 d-flex align-items-center gap-3">
                <div class="auth-logo" style="width:46px;height:46px;border-radius:14px;overflow:hidden;background:#fff;display:flex;align-items:center;justify-content:center;">
                    <img src="<?= base_url('/logo.png'); ?>" alt="Sosyal Panel" style="max-width:100%;max-height:100%;display:block;">
                </div>
                <div>
                    <div class="auth-brand-title">Sosyal Panel</div>
                    <div class="auth-brand-sub">Planla • Yayınla • Yönet</div>
                </div>
            </div>

            <div class="auth-hero">
                <div class="auth-hero-title">Tek panelden tüm içeriklerini yönet.</div>
                <div class="auth-hero-text">
                    Facebook, Instagram, YouTube ve TikTok gönderilerini tek takvim üzerinden planla,
                    otomatik olarak yayınla.
                </div>
            </div>

            <div class="mt-4 small text-muted">
                <a class="text-decoration-none" href="<?= base_url('/terms'); ?>">Kullanım Şartları</a>
                <span class="mx-2">•</span>
                <a class="text-decoration-none" href="<?= base_url('/privacy'); ?>">Gizlilik Politikası</a>
                <span class="mx-2">•</span>
                <a class="text-decoration-none" href="<?= base_url('/data-deletion'); ?>">Veri Silme Politikası</a>
            </div>
        </div>

        <div class="auth-footer mt-4">
            &copy; <?= date('Y'); ?> Sosyal Panel. Tüm hakları saklıdır.
        </div>
    </div>

    <div class="auth-form-side">
        <h1 class="auth-form-title">Giriş Yap</h1>
        <p class="auth-form-sub">
            Hesabına giriş yaparak planlanmış içeriklerini yönetebilirsin.
        </p>

        <?php $errors = session('errors') ?? []; ?>

        <form method="post" action="<?= base_url('auth/login'); ?>">
            <?= csrf_field(); ?>

            <div class="mb-3">
                <label class="form-label small text-muted">E-posta</label>
                <input type="email" name="email"
                       class="form-control form-control-sm"
                       value="<?= old('email'); ?>"
                       placeholder="ornek@mail.com">
                <?php if (isset($errors['email'])): ?>
                    <div class="auth-error mt-1"><?= esc($errors['email']); ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label class="form-label small text-muted">Şifre</label>
                <input type="password" name="password"
                       class="form-control form-control-sm"
                       placeholder="••••••">
                <?php if (isset($errors['password'])): ?>
                    <div class="auth-error mt-1"><?= esc($errors['password']); ?></div>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-gradient w-100 btn-sm mt-2">
                Giriş Yap
            </button>

            <div class="mt-3 small text-muted">
                Panel erişimi için lütfen sistem yöneticinizle iletişime geçin.
            </div>

            <div class="mt-3 small text-muted">
                <a class="text-decoration-none" href="<?= base_url('/terms'); ?>">Kullanım Şartları</a>
                <span class="mx-2">•</span>
                <a class="text-decoration-none" href="<?= base_url('/privacy'); ?>">Gizlilik Politikası</a>
                <span class="mx-2">•</span>
                <a class="text-decoration-none" href="<?= base_url('/data-deletion'); ?>">Veri Silme</a>
            </div>
        </form>
    </div>
</div>

</body>
</html>
