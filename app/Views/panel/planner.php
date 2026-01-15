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
            <label class="form-label">Başlık</label>
            <input type="text" name="title" class="form-control" placeholder="Örn: Kampanya duyurusu">
            <div class="form-text">YouTube’da bu alan video başlığı olarak kullanılacak.</div>
          </div>

          <div class="mb-3">
            <label class="form-label">Metin</label>
            <textarea name="base_text" class="form-control" rows="6" placeholder="Caption / açıklama..."></textarea>
            <div class="form-text">
              Instagram/Facebook açıklaması buradan gider. YouTube için açıklama olarak kullanılacak.
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Medya (opsiyonel)</label>
            <input type="file" name="media" class="form-control" accept="image/*,video/*">
            <div class="form-text">
              Şimdilik tek dosya. (Video seçersen IG/FB/YT aynı videoyu kullanabilir.)
              YouTube seçiliyse video zorunlu.
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
            <div class="vstack gap-2">
              <?php foreach ($accounts as $a): ?>
                <?php
                  $label = strtoupper($a['platform']) . ' — ';
                  if (!empty($a['username'])) $label .= '@' . $a['username'];
                  elseif (!empty($a['name'])) $label .= $a['name'];
                  else $label .= 'Hesap #' . (int)$a['id'];
                ?>
                <label class="border rounded p-2 d-flex align-items-center justify-content-between">
                  <span><?= esc($label) ?> <span class="text-muted">(ID: <?= (int)$a['id'] ?>)</span></span>
                  <input class="form-check-input" type="checkbox" name="account_ids[]" value="<?= (int)$a['id'] ?>">
                </label>
              <?php endforeach; ?>
            </div>
            <div class="form-text mt-2">
              Birden fazla seçersen aynı içerik tüm seçilen hesaplarda planlanır.
              (AUTO seçersen her platform kendi doğru tipini seçer.)
            </div>
          <?php endif; ?>
        </div>
      </div>

      <div class="card">
        <div class="card-body">
          <h5 class="card-title mb-3">Zamanlama</h5>

          <div class="mb-3">
            <label class="form-label">Paylaşım Tipi</label>
            <select name="post_type" class="form-select" required>
                <option value="auto" selected>Auto (Platforma göre seç)</option>
                <option value="post">Post</option>
                <option value="reels">Reels</option>
                <option value="story">Story</option>
            </select>
            <div class="form-text">
              <strong>Auto</strong>: video ise IG=Reels, FB=Video, YT=Video; görsel ise IG/FB=Post.
              <br>
              <strong>YouTube</strong> için “Reels/Post/Story” yoktur → sistem otomatik “Video” olarak ele alır.
              <br>
              <strong>Facebook</strong> tarafında “Reels” seçilse bile video varsa “Video” akışına düşürürüz.
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

<?= $this->endSection() ?>
