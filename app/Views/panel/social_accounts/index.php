<?= $this->extend('layouts/panel') ?>
<?= $this->section('content') ?>

<style>
  .sa-page-title{ font-size:36px; letter-spacing:-.6px; }
  .sa-sub{ max-width: 760px; }

  .sa-connect-card{
    border: 1px solid rgba(0,0,0,.06);
    border-radius: 18px;
    background: #fff;
    overflow: hidden;
  }
  .sa-connect-head{
    display:flex; align-items:center; justify-content:space-between;
    gap:10px;
    padding:14px 14px 10px 14px;
  }
  .sa-connect-badge{
    display:inline-flex; align-items:center; gap:8px;
    padding:7px 10px; border-radius:999px;
    background: rgba(0,0,0,.04);
    border: 1px solid rgba(0,0,0,.06);
    font-size:12px; font-weight:700;
    white-space:nowrap;
  }
  .sa-connect-body{ padding:0 14px 14px 14px; }
  .sa-connect-desc{ color: rgba(0,0,0,.62); font-size: 13px; line-height: 1.4; }

  .btn-brand{
    background: linear-gradient(90deg, #6a5cff, #ff4fd8);
    border: 0;
    color: #fff;
    font-weight: 900;
    border-radius: 999px;
  }
  .btn-brand:hover{ filter: brightness(.98); color:#fff; }
  .btn-soft{
    border-radius: 999px;
    border: 1px solid rgba(0,0,0,.10);
    background: #fff;
    font-weight: 800;
  }
  .btn-soft:hover{ background: rgba(0,0,0,.03); }

  .sa-list-card{
    border: 1px solid rgba(0,0,0,.06);
    border-radius: 18px;
    background: #fff;
    overflow:hidden;
  }

  .sa-item{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:14px;
    padding:14px;
    border-top: 1px solid rgba(0,0,0,.06);
  }
  .sa-item:first-child{ border-top:0; }

  .sa-left{ display:flex; align-items:center; gap:12px; min-width:0; }
  .sa-icon{
    width:42px; height:42px; border-radius:12px;
    display:flex; align-items:center; justify-content:center;
    border: 1px solid rgba(0,0,0,.06);
    background: rgba(0,0,0,.03);
    flex: 0 0 auto;
    font-size:18px;
  }

  .sa-meta{ min-width:0; }
  .sa-name{ font-weight:900; line-height:1.15; }
  .sa-subline{ color: rgba(0,0,0,.55); font-size: 12.5px; }

  .sa-status{
    display:inline-flex; align-items:center; gap:7px;
    padding:6px 10px;
    border-radius:999px;
    border: 1px solid rgba(0,0,0,.07);
    background: rgba(0,0,0,.02);
    font-size:12px;
    font-weight:800;
    margin-top:8px;
    width: fit-content;
  }

  .sa-actions{ display:flex; align-items:center; gap:8px; flex:0 0 auto; }

  .sa-empty{
    padding: 22px 14px;
    color: rgba(0,0,0,.55);
    text-align:center;
  }

  .sa-info{
    border:1px solid rgba(0,0,0,.06);
    background: rgba(0,0,0,.02);
    border-radius: 14px;
    padding: 12px 14px;
    color: rgba(0,0,0,.70);
    font-size: 13px;
    line-height: 1.45;
  }

  @media (max-width: 991.98px){
    .sa-page-title{ font-size:28px; }
    .sa-item{ align-items:flex-start; flex-direction:column; }
    .sa-actions{ width:100%; }
    .sa-actions form{ width:100%; }
    .sa-actions .btn{ width:100%; }
  }
</style>

<div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
  <div>
    <div class="sa-page-title fw-bold">TikTok Bağlantısı</div>
    <div class="text-muted sa-sub">
      TikTok hesabını bağla ve içeriklerini panel üzerinden paylaş. Bağlantı sonrası “Post to TikTok” ekranında gerekli alanları doldurup yayınlayabilirsin.
    </div>
  </div>

  <!-- İstersen bunu tamamen kaldırabiliriz. Audit videosunda şart değil. -->
  <a href="<?= site_url('panel/publishes') ?>" class="btn btn-soft btn-sm">
    <i class="bi bi-plus-circle me-1"></i> Paylaşım Oluştur
  </a>
</div>

<?php if (session()->getFlashdata('success')): ?>
  <div class="alert alert-success py-2"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
  <div class="alert alert-danger py-2"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<!-- CONNECT -->
<div class="row g-3 mb-3">
  <div class="col-lg-6">
    <div class="sa-connect-card h-100">
      <div class="sa-connect-head">
        <div class="fw-bold">TikTok</div>
        <span class="sa-connect-badge"><i class="bi bi-music-note-beamed"></i> Direct Post</span>
      </div>

      <div class="sa-connect-body">
        <div class="sa-connect-desc mb-3">
          “TikTok’u Bağla” butonuna tıkladığında TikTok izin ekranı açılır. İzin verdikten sonra hesabın bağlanır ve paylaşım ekranında seçilebilir hale gelir.
        </div>

        <a href="<?= site_url('panel/auth/tiktok') ?>"
           class="btn btn-brand w-100 btn-sm">
          <i class="bi bi-link-45deg me-1"></i> TikTok’u Bağla
        </a>

        <div class="text-muted small mt-2">
          Not: İlk bağlantıda TikTok izin ekranı açılır.
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="sa-info h-100">
      <div class="fw-bold mb-1"><i class="bi bi-shield-check me-1"></i> Güvenlik & Şeffaflık</div>
      <div>
        Bağlantı yalnızca TikTok hesabını yetkilendirmenle gerçekleşir. Dilediğin zaman aşağıdaki listeden bağlantıyı kaldırabilirsin.
      </div>
    </div>
  </div>
</div>

<!-- CONNECTED ACCOUNTS LIST -->
<div class="sa-list-card">
  <div class="p-3 d-flex justify-content-between align-items-center gap-2 flex-wrap">
    <div>
      <div class="fw-bold" style="font-size:16px;">Bağlı TikTok Hesapları</div>
      <div class="text-muted small">Paylaşım ekranında seçilebilir. İstersen buradan kaldırabilirsin.</div>
    </div>
    <span class="sa-connect-badge"><i class="bi bi-shield-check"></i> Bağlantılar</span>
  </div>

  <?php
    // Güvenlik: TikTok dışı hesaplar varsa bu audit build’de göstermeyelim
    $tiktokRows = [];
    foreach (($rows ?? []) as $r) {
      $p = strtolower((string)($r['platform'] ?? ''));
      if ($p === 'tiktok') $tiktokRows[] = $r;
    }
  ?>

  <?php if (empty($tiktokRows)): ?>
    <div class="sa-empty">
      Henüz TikTok hesabı eklenmemiş. Yukarıdan “TikTok’u Bağla” ile başlayabilirsin.
    </div>
  <?php else: ?>

    <?php foreach ($tiktokRows as $r): ?>
      <?php
        $platform = 'tiktok';
        $name     = trim((string)($r['name'] ?? ''));
        $username = trim((string)($r['username'] ?? ''));

        // Görünür başlık: name varsa onu, yoksa username, yoksa “TikTok hesabı”
        $title = $name !== '' ? $name : ($username !== '' ? '@'.$username : 'TikTok hesabı');
      ?>

      <div class="sa-item">
        <div class="sa-left">
          <div class="sa-icon">
            <i class="bi bi-music-note-beamed"></i>
          </div>

          <div class="sa-meta">
            <div class="sa-name text-truncate"><?= esc($title) ?></div>

            <div class="sa-subline">
              TikTok
              <?php if ($username !== ''): ?>
                · <span class="fw-semibold">@<?= esc($username) ?></span>
              <?php else: ?>
                · <span class="text-muted">Kullanıcı adı alınamadı</span>
              <?php endif; ?>
            </div>

            <div class="sa-status">
              <i class="bi bi-check2-circle"></i> Bağlı
            </div>
          </div>
        </div>

        <div class="sa-actions">
          <form method="post"
                action="<?= site_url('panel/social-accounts/' . (int)$r['id'] . '/delete') ?>"
                class="d-inline">
            <?= csrf_field() ?>
            <button type="submit"
                    class="btn btn-outline-danger btn-sm"
                    style="border-radius:999px; font-weight:900;"
                    data-confirm="Bu TikTok hesabının bağlantısını kaldırmak istediğine emin misin?">
              <i class="bi bi-trash me-1"></i> Bağlantıyı kaldır
            </button>
          </form>
        </div>
      </div>
    <?php endforeach; ?>

  <?php endif; ?>
</div>

<?= $this->endSection() ?>
