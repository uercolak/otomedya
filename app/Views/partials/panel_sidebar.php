    <?php helper('url'); ?>
    <?php $uri = service('uri'); ?>

    <?php
    $segments = $uri->getSegments();
    $seg1 = $segments[0] ?? '';
    $seg2 = $segments[1] ?? '';
    $seg3 = $segments[2] ?? '';
    ?>

    <nav class="nav flex-column side-nav">
    <div class="nav-section-label">Genel</div>

    <a href="<?= site_url('panel'); ?>"
        class="nav-link <?= ($seg1 === 'panel' && $seg2 === '') ? 'active' : '' ?>">
        <i class="bi bi-speedometer2"></i> <span>Gösterge Paneli</span>
    </a>

    <a href="<?= site_url('panel/calendar'); ?>"
        class="nav-link <?= ($seg1 === 'panel' && $seg2 === 'calendar') ? 'active' : '' ?>">
        <i class="bi bi-calendar3"></i> <span>Takvim &amp; Planlama</span>
    </a>

    <a href="<?= site_url('panel/publishes') ?>"
        class="nav-link <?= ($seg1 === 'panel' && $seg2 === 'publishes') ? 'active' : '' ?>">
        <span class="me-2">📤</span> <span>Paylaşımlar</span>
    </a>

    <div class="nav-section-label">İçerik</div>

    <a href="<?= site_url('panel/templates'); ?>"
        class="nav-link <?= ($seg1 === 'panel' && $seg2 === 'templates') ? 'active' : '' ?>">
        <i class="bi bi-images"></i> <span>Hazır Şablonlar</span>
    </a>

    <div class="nav-section-label">Yönetim</div>

    <a href="<?= site_url('panel/social-accounts'); ?>"
        class="nav-link <?= ($seg1 === 'panel' && $seg2 === 'social-accounts') ? 'active' : '' ?>">
        <i class="bi bi-share"></i> <span>Sosyal Hesaplar</span>
    </a>
    
    <div class="nav-section-label">Güvenlik</div>

    <a href="<?= site_url('panel/settings'); ?>"
        class="nav-link <?= ($seg1 === 'panel' && $seg2 === 'settings') ? 'active' : '' ?>">
        <i class="bi bi-gear"></i> <span>Ayarlar</span>
    </a>

        <a href="<?= site_url('panel/help/account-linking'); ?>"
        class="nav-link <?= ($seg1 === 'panel' && $seg2 === 'help' && $seg3 === 'account-linking') ? 'active' : '' ?>">
        <i class="bi bi-question-circle"></i> <span>Yardım / Hesap Bağlama Rehberi</span>
    </a>

    <?php if (session('user_role') === 'admin'): ?>
        <div class="nav-section-label">Admin</div>

        <a href="<?= site_url('admin/users') ?>"
        class="nav-link <?= ($seg1 === 'admin' && ($seg2 ?? '') === 'users') ? 'active' : '' ?>">
        <i class="bi bi-people"></i> <span>Kullanıcılar</span>
        </a>
    <?php endif; ?>
    </nav>
