<!doctype html>
<html>
<head>
    <title>Dealer Panel</title>
</head>
<body>
    <h1>Dealer Panel</h1>

    <p>Hoşgeldin <?= esc(session('user_name')); ?></p>

    <p>Tenant ID: <?= esc(session('tenant_id')); ?></p>

    <p><a href="<?= base_url('dealer/users'); ?>">Kullanıcıları Yönet</a></p>
</body>
</html>
