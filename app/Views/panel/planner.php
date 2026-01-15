<?= $this->extend('layouts/panel') ?>
<?= $this->section('content') ?>
<div class="container-fluid py-3">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <div>
      <h3 class="mb-0">Yeni Gönderi Planla</h3>
      <div class="text-muted">İçerik oluştur, hesap(lar) seç, tarih/saat belirle ve kuyruğa al.</div>
    </div>
    <a href="<?= site_url('panel/calendar') ?>" class="btn btn-outline-secondary">Takvime Dön</a>
  </div>

  <?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
  <?php endif; ?>
  <?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
  <?php endif; ?>

  <form action="<?= site_url('panel/planner') ?>" method="post" enctype="multipart/form-data" class="row g-3">
    <?= csrf_field() ?>

    <div class="col-lg-7">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title mb-3">İçerik</h5>

          <div class="mb-3">
            <label class="form-label">Başlık (genel)</label>
            <input type="text" name="title" class="form-control" placeholder="Örn: Kampanya duyurusu">
            <div class="form-text">Bu alan genel başlık. YouTube başlığı için aşağıdaki YouTube alanını kullan.</div>
          </div>

          <div class="mb-3">
            <label class="form-label">Metin</label>
            <textarea name="base_text" class="form-control" rows="6" placeholder="Caption / açıklama..."></textarea>
            <div class="form-text">Instagram/Facebook açıklaması buradan gider. YouTube açıklaması da buradan gidebilir (istersen sonra ayrı alan ekleriz).</div>
          </div>

          <div class="mb-3">
            <label class="form-label">Medya</label>
            <input type="file" name="media" class="form-control" accept="image/*,video/*">
            <div class="form-text">
              Instagram Post/Story ve YouTube için medya gerekir.
              YouTube seçersen video zorunlu.
            </div>
          </div>

          <!-- YouTube alanları -->
          <div id="ytFields" class="border rounded p-3" style="display:none;">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <strong>YouTube Ayarları</strong>
              <span class="badge bg-light text-dark">YouTube seçilince açılır</span>
            </div>

            <div class="mb-3">
              <label class="form-label">YouTube Başlık <span class="text-danger">*</span></label>
              <input type="text" name="yt_title" class="form-control" placeholder="Örn: Test video">
              <div class="form-text">YouTube için başlık zorunlu.</div>
            </div>

            <div class="mb-0">
              <label class="form-label">Gizlilik</label>
              <select name="yt_privacy" class="form-select">
                <option value="unlisted" selected>Unlisted (liste dışı)</option>
                <option value="public">Public (herkese açık)</option>
                <option value="private">Private (özel)</option>
              </select>
            </div>
          </div>

        </div>
      </div>
    </div>

    <div class="col-lg-5">
      <div class="card mb-3">
        <div class="card-body">
          <h5 class="card-title mb-3">Hedef Hesaplar</h5>

          <?php if (empty($accounts)): ?>
            <div class="alert alert-warning mb-0">
              Henüz sosyal hesap yok. Önce <a href="<?= site_url('panel/social-accounts') ?>">Sosyal Hesaplar</a> bölümünden ekle.
            </div>
          <?php else: ?>
            <div class="vstack gap-2" id="accountsBox">
              <?php foreach ($accounts as $a): ?>
                <?php
                  $plat  = strtolower($a['platform'] ?? '');
                  $label = strtoupper($a['platform']) . ' — ';
                  if (!empty($a['username'])) $label .= '@' . $a['username'];
                  elseif (!empty($a['name'])) $label .= $a['name'];
                  else $label .= 'Hesap #' . (int)$a['id'];
                ?>
                <label class="border rounded p-2 d-flex align-items-center justify-content-between">
                  <span>
                    <?= esc($label) ?>
                    <span class="text-muted">(ID: <?= (int)$a['id'] ?>)</span>
                  </span>
                  <input
                    class="form-check-input accountChk"
                    type="checkbox"
                    name="account_ids[]"
                    value="<?= (int)$a['id'] ?>"
                    data-platform="<?= esc($plat) ?>"
                  >
                </label>
              <?php endforeach; ?>
            </div>
            <div class="form-text mt-2">Birden fazla seçersen aynı içerik tüm seçilen hesaplarda planlanır.</div>
          <?php endif; ?>
        </div>
      </div>

      <div class="card">
        <div class="card-body">
          <h5 class="card-title mb-3">Zamanlama</h5>

          <div class="mb-3">
            <label class="form-label">Instagram Paylaşım Tipi</label>
            <select name="ig_post_type" class="form-select">
              <option value="post" selected>Post</option>
              <option value="reels">Reels</option>
              <option value="story">Story</option>
            </select>
            <div class="form-text">
              Sadece Instagram hesapları için uygulanır.
              Reels=video zorunlu.
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Tarih/Saat</label>
            <input type="datetime-local" name="schedule_at" class="form-control" required>
            <div class="form-text">Kaydetmeden önce otomatik olarak Y-m-d H:i:s formatına çevrilir.</div>
          </div>

          <button type="submit" class="btn btn-primary w-100">Planla</button>
        </div>
      </div>
    </div>
  </form>
</div>

<script>
(function () {
  function refreshPlatformFields() {
    const chks = document.querySelectorAll('.accountChk:checked');
    let hasYT = false;

    chks.forEach(chk => {
      const p = (chk.getAttribute('data-platform') || '').toLowerCase();
      if (p === 'youtube') hasYT = true;
    });

    const ytBox = document.getElementById('ytFields');
    if (ytBox) ytBox.style.display = hasYT ? 'block' : 'none';
  }

  document.querySelectorAll('.accountChk').forEach(chk => {
    chk.addEventListener('change', refreshPlatformFields);
  });

  refreshPlatformFields();
})();
</script>

<?= $this->endSection() ?>
