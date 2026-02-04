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
            $plat = strtoupper((string)$a['platform']);
            $label = $plat . ' — ';
            if (!empty($a['username'])) $label .= '@' . $a['username'];
            elseif (!empty($a['name'])) $label .= $a['name'];
            else $label .= 'Hesap #' . (int)$a['id'];
          ?>
          <label class="border rounded p-2 d-flex align-items-center justify-content-between">
            <span><?= esc($label) ?> <span class="text-muted">(ID: <?= (int)$a['id'] ?>)</span></span>
            <input
              class="form-check-input account-check"
              type="checkbox"
              name="account_ids[]"
              value="<?= (int)$a['id'] ?>"
              data-platform="<?= esc(strtolower((string)$a['platform'])) ?>"
            >
          </label>
        <?php endforeach; ?>
      </div>

      <div class="form-text mt-2">
        Birden fazla hesap seçersen aynı içerik seçilen tüm hesaplarda planlanır (platform ayarları platform bazlı uygulanır).
      </div>
    <?php endif; ?>
  </div>
</div>
