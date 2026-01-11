<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <title>Kayıt Ol | Sosyal Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= base_url('public/assets/css/auth.css'); ?>" rel="stylesheet">
    <link href="<?= base_url('/assets/css/auth.css'); ?>" rel="stylesheet">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-side">
        <div>
            <div class="auth-brand mb-4">
                <div class="auth-logo">S</div>
                <div>
                    <div class="auth-brand-title">Sosyal Panel</div>
                    <div class="auth-brand-sub">Planla • Yayınla • Yönet</div>
                </div>
            </div>

            <div class="auth-hero">
                <div class="auth-hero-title">Dakikalar içinde panelini kur.</div>
                <div class="auth-hero-text">
                    Hazır şablonlar, takvim görünümü ve otomatik paylaşım ile içerik üretim sürecini hızlandır.
                </div>
            </div>
        </div>

        <div class="auth-footer mt-4">
            &copy; <?= date('Y'); ?> Sosyal Panel. Tüm hakları saklıdır.
        </div>
    </div>

    <div class="auth-form-side">
        <h1 class="auth-form-title">Kayıt Ol</h1>
        <p class="auth-form-sub">
            İlk hesabını oluştur, sosyal medya içeriklerini tek yerden planlamaya başla.
        </p>

        <?php $errors = session('errors') ?? []; ?>

        <form method="post" action="<?= base_url('auth/register'); ?>">
            <?= csrf_field(); ?>

            <div class="mb-2">
                <label class="form-label small text-muted">Ad Soyad</label>
                <input type="text" name="name"
                       class="form-control form-control-sm"
                       value="<?= old('name'); ?>"
                       placeholder="Ad Soyad">
                <?php if (isset($errors['name'])): ?>
                    <div class="auth-error mt-1"><?= esc($errors['name']); ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-2">
                <label class="form-label small text-muted">E-posta</label>
                <input type="email" name="email"
                       class="form-control form-control-sm"
                       value="<?= old('email'); ?>"
                       placeholder="ornek@mail.com">
                <?php if (isset($errors['email'])): ?>
                    <div class="auth-error mt-1"><?= esc($errors['email']); ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-2">
                <label class="form-label small text-muted">Şifre</label>
                <input type="password" name="password"
                       class="form-control form-control-sm"
                       placeholder="En az 6 karakter">
                <?php if (isset($errors['password'])): ?>
                    <div class="auth-error mt-1"><?= esc($errors['password']); ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label class="form-label small text-muted">Şifre (Tekrar)</label>
                <input type="password" name="password_confirm"
                       class="form-control form-control-sm"
                       placeholder="Şifreni tekrar yaz">
                <?php if (isset($errors['password_confirm'])): ?>
                    <div class="auth-error mt-1"><?= esc($errors['password_confirm']); ?></div>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-gradient w-100 btn-sm">
                Kayıt Ol
            </button>

            <div class="mt-3 small text-muted">
                Zaten hesabın var mı?
                <a href="<?= base_url('auth/login'); ?>" class="auth-link">Giriş yap</a>
            </div>
        </form>
    </div>
</div>

</body>
</html>
