<?= $this->extend('layouts/panel') ?>
<?= $this->section('content') ?>

<style>
  /* --- Preview UI --- */
  .pv-card { background:#fff; }
  .pv-grid { display:grid; gap:14px; }
  @media (min-width: 992px){ .pv-grid { grid-template-columns: 1fr; } }

  .pv-platform { border:1px solid rgba(0,0,0,.08); border-radius:16px; padding:14px; background:#fff; }
  .pv-head { display:flex; align-items:center; justify-content:space-between; gap:10px; margin-bottom:10px; }
  .pv-title { font-weight:600; }
  .pv-sub { color:#6c757d; font-size:12px; margin-top:2px; }

  .pv-badges { display:flex; gap:6px; flex-wrap:wrap; }
  .pv-badge { font-size:11px; padding:4px 8px; border-radius:999px; border:1px solid rgba(0,0,0,.08); background:#f8f9fa; color:#111; }

  /* Phone mock */
  .pv-phone {
    width: 320px;
    max-width: 100%;
    border-radius: 26px;
    background: #0b0b0c;
    padding: 12px;
    border: 1px solid rgba(0,0,0,.15);
    box-shadow: 0 10px 30px rgba(0,0,0,.08);
  }
  .pv-screen {
    background:#111;
    border-radius: 18px;
    overflow:hidden;
    position:relative;
  }
  .pv-ratio-9x16 { aspect-ratio: 9/16; }
  .pv-ratio-4x5  { aspect-ratio: 4/5; }
  .pv-ratio-1x1  { aspect-ratio: 1/1; }
  .pv-ratio-16x9 { aspect-ratio: 16/9; }

  .pv-media, .pv-media video, .pv-media img {
    width:100%;
    height:100%;
    object-fit: cover;
    display:block;
  }

  /* IG header */
  .pv-ig-top {
    position:absolute; top:10px; left:10px; right:10px;
    display:flex; align-items:center; justify-content:space-between;
    color:#fff; font-size:12px; text-shadow:0 1px 8px rgba(0,0,0,.5);
    pointer-events:none;
  }
  .pv-ig-pill { background:rgba(0,0,0,.45); padding:4px 8px; border-radius:999px; }

  /* TikTok overlay */
  .pv-tt-left {
    position:absolute; left:10px; bottom:12px; right:60px;
    color:#fff; font-size:12px;
    text-shadow:0 1px 10px rgba(0,0,0,.65);
    pointer-events:none;
  }
  .pv-tt-user { font-weight:700; margin-bottom:6px; }
  .pv-tt-caption { opacity:.95; white-space:pre-wrap; max-height:72px; overflow:hidden; }
  .pv-tt-right {
    position:absolute; right:10px; bottom:16px;
    display:flex; flex-direction:column; gap:10px;
    pointer-events:none;
  }
  .pv-tt-btn {
    width:36px; height:36px; border-radius:999px;
    background:rgba(255,255,255,.12);
    border:1px solid rgba(255,255,255,.18);
  }

  /* Feed card (FB) */
  .pv-feed {
    border:1px solid rgba(0,0,0,.08);
    border-radius:14px;
    overflow:hidden;
    background:#fff;
  }
  .pv-feed-head { padding:10px 12px; display:flex; align-items:center; gap:10px; }
  .pv-avatar { width:28px; height:28px; border-radius:999px; background:#e9ecef; }
  .pv-feed-name { font-size:12px; font-weight:600; line-height:1; }
  .pv-feed-sub { font-size:11px; color:#6c757d; }
  .pv-feed-body { padding:10px 12px; font-size:12px; white-space:pre-wrap; }

  /* YT */
  .pv-yt-title { font-weight:700; font-size:13px; margin-top:10px; }
  .pv-yt-meta { color:#6c757d; font-size:11px; margin-top:2px; }

  .pv-row { display:flex; gap:14px; flex-wrap:wrap; align-items:flex-start; }
</style>


<div class="container-fluid py-3">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <div>
      <h3 class="mb-0">Yeni Gönderi Planla</h3>
      <div class="text-muted">İçerik oluştur, hesap(lar) seç, tarih/saat belirle ve kuyruğa al.</div>
    </div>
    <a href="<?= site_url('panel/calendar') ?>" class="btn btn-outline-secondary">Takvime Dön</a>
    <a href="<?= site_url('panel/templates') ?>" class="btn btn-outline-primary">Şablondan Oluştur</a>
  </div>

  <?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
  <?php endif; ?>
  <?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
  <?php endif; ?>

  <form action="<?= site_url('panel/planner') ?>" method="post" enctype="multipart/form-data" class="row g-3">
    <?= csrf_field() ?>

    <?php if (!empty($prefill['id'])): ?>
      <input type="hidden" name="content_id" value="<?= (int)$prefill['id'] ?>">
    <?php endif; ?>

    <div class="col-lg-7">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title mb-3">İçerik</h5>

          <div class="mb-3">
            <label class="form-label">Başlık (genel)</label>
            <input type="text" name="title" class="form-control" value="<?= esc($prefill['title'] ?? '') ?>" placeholder="Örn: Kampanya duyurusu">
            <div class="form-text">Bu alan genel başlık. YouTube başlığı için aşağıdaki YouTube alanını kullan.</div>
          </div>

          <div class="mb-3">
            <label class="form-label">Metin</label>
            <textarea name="base_text" class="form-control" rows="6" placeholder="Caption / açıklama..."><?= esc($prefill['base_text'] ?? '') ?></textarea>
            <div class="form-text">Instagram/Facebook açıklaması buradan gider. YouTube açıklaması da buradan gidebilir.</div>
          </div>

            <div class="mb-3">
                        <label class="form-label">Medya</label>

                        <?php if (!empty($prefill) && !empty($prefill['media_path'])): ?>
                        <div class="border rounded p-2 bg-light">
                            <div class="small text-muted mb-2">Şablondan üretilen medya:</div>

                            <?php if (($prefill['media_type'] ?? '') === 'image'): ?>
                            <img src="<?= base_url($prefill['media_path']) ?>"
                                style="max-width: 100%; height: auto; border-radius: 10px; display:block;">
                            <?php elseif (($prefill['media_type'] ?? '') === 'video'): ?>
                            <video controls style="max-width:100%; border-radius:10px; display:block;">
                                <source src="<?= base_url($prefill['media_path']) ?>">
                            </video>
                            <?php else: ?>
                            <div class="text-danger small">Medya tipi bulunamadı.</div>
                            <?php endif; ?>

                            <div class="small text-muted mt-2">
                            Bu içerik hazır. Yeni dosya yükleme alanı gizlendi.
                            </div>
                        </div>
                        <?php else: ?>
                        <input type="file" name="media" class="form-control" accept="image/*,video/*">
                        <div class="form-text">
                            Instagram Post/Story ve YouTube için medya gerekir. YouTube seçersen video zorunlu.
                        </div>
                        <?php endif; ?>
            </div>

          <div id="ytSettings" class="mt-4" style="display:none;">
            <div class="d-flex align-items-center justify-content-between">
              <h5 class="mb-2">YouTube Ayarları</h5>
              <span class="badge bg-light text-dark">YouTube seçilince açılır</span>
            </div>

            <div class="mb-3">
              <label class="form-label">YouTube Başlık <span class="text-danger">*</span></label>
              <input type="text" name="youtube_title" class="form-control" placeholder="YouTube video başlığı">
              <div class="form-text">YouTube için başlık zorunlu.</div>
            </div>

            <div class="mb-3">
              <label class="form-label">Gizlilik</label>
              <select name="youtube_privacy" class="form-select">
                <option value="public" selected>Public (herkese açık)</option>
                <option value="unlisted">Unlisted (liste dışı)</option>
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
            <div class="form-text mt-2">Birden fazla seçersen aynı içerik tüm seçilen hesaplarda planlanır.</div>
          <?php endif; ?>
        </div>
      </div>

      <div class="card">
        <div class="card-body">
          <h5 class="card-title mb-3">Zamanlama</h5>

          <div class="mb-3">
            <label class="form-label">Instagram Paylaşım Tipi</label>
            <select name="post_type" class="form-select" required>
              <option value="auto" selected>AUTO (video→reels, görsel→post)</option>
              <option value="post">Post</option>
              <option value="reels">Reels</option>
              <option value="story">Story</option>
            </select>
            <div class="form-text">
              AUTO önerilir. (Video feed için Meta çoğu zaman reels ister.)
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

    <!-- Ön İzleme -->
    <div class="card mb-3" id="previewCard" style="display:none;">
    <div class="card-body">
        <div class="d-flex align-items-center justify-content-between mb-2">
        <h5 class="card-title mb-0">Ön İzleme</h5>
        <span class="badge bg-light text-dark">Seçilen platformlara göre</span>
        </div>

        <div class="text-muted small mb-3">
        Gönderinizi Bu ön izleme ile nasıl gözükeceğini görebilirsiniz.
        </div>

        <div id="previewWrap" class="vstack gap-3"></div>
    </div>
    </div>

<script>
(function(){
  const checks  = Array.from(document.querySelectorAll('.account-check'));
  const ytBox   = document.getElementById('ytSettings');

  const previewCard = document.getElementById('previewCard');
  const previewWrap = document.getElementById('previewWrap');

  const inputTitle     = document.querySelector('input[name="title"]');
  const textareaBase   = document.querySelector('textarea[name="base_text"]');
  const inputMedia     = document.querySelector('input[name="media"]');
  const selectPostType = document.querySelector('select[name="post_type"]');

  const inputYtTitle   = document.querySelector('input[name="youtube_title"]');
  const selectYtPriv   = document.querySelector('select[name="youtube_privacy"]');

  // Prefill medya varsa (şablondan geldiyse)
  const PREFILL_MEDIA_URL  = <?= !empty($prefill['media_path']) ? json_encode(base_url($prefill['media_path'])) : 'null' ?>;
  const PREFILL_MEDIA_TYPE = <?= !empty($prefill['media_type']) ? json_encode($prefill['media_type']) : 'null' ?>;

  function escapeHtml(s){
    return (s ?? '').toString()
      .replace(/&/g,'&amp;')
      .replace(/</g,'&lt;')
      .replace(/>/g,'&gt;')
      .replace(/"/g,'&quot;')
      .replace(/'/g,'&#039;');
  }

  function getSelectedPlatforms(){
    const selected = checks.filter(ch => ch.checked);
    const map = {}; // platform => [{id,label}]
    selected.forEach(ch => {
      const plat = (ch.dataset.platform || '').toLowerCase();
      const label = (ch.closest('label')?.querySelector('span')?.innerText || '').trim();
      if (!map[plat]) map[plat] = [];
      map[plat].push({ id: ch.value, label });
    });
    return map;
  }

  function getMediaSource(){
    // 1) prefill medya varsa onu kullan
    if (PREFILL_MEDIA_URL && PREFILL_MEDIA_TYPE) {
      return { url: PREFILL_MEDIA_URL, type: PREFILL_MEDIA_TYPE }; // image | video
    }

    // 2) dosya seçildiyse objectURL
    if (inputMedia && inputMedia.files && inputMedia.files[0]) {
      const f = inputMedia.files[0];
      const mime = (f.type || '').toLowerCase();
      let type = null;
      if (mime.startsWith('image/')) type = 'image';
      else if (mime.startsWith('video/')) type = 'video';
      return { url: URL.createObjectURL(f), type };
    }

    return { url: null, type: null };
  }

  function mediaHtml(media, ratioClass){
    if (!media.url || !media.type) {
        return `<div class="text-muted small">Medya seçilmedi.</div>`;
    }

    const inner = (media.type === 'image')
        ? `<img src="${escapeHtml(media.url)}" alt="">`
        : `<video controls muted playsinline><source src="${escapeHtml(media.url)}"></video>`;

    return `
        <div class="pv-phone">
        <div class="pv-screen ${ratioClass}">
            <div class="pv-media">${inner}</div>
        </div>
        </div>
    `;
    }

    function buildPlatformCard(title, subtitle, badgesHtml, bodyHtml){
        return `
            <div class="pv-platform">
            <div class="pv-head">
                <div>
                <div class="pv-title">${escapeHtml(title)}</div>
                <div class="pv-sub">${escapeHtml(subtitle || '')}</div>
                </div>
                <div class="pv-badges">${badgesHtml || ''}</div>
            </div>
            ${bodyHtml}
            </div>
        `;
    }

  function render(){
    // YouTube ayarlarını aç/kapa (eski davranış)
    const hasYT = checks.some(ch => ch.checked && (ch.dataset.platform === 'youtube'));
    if (ytBox) ytBox.style.display = hasYT ? 'block' : 'none';

    const platforms = getSelectedPlatforms();
    const keys = Object.keys(platforms);

    // preview card göster/gizle
    if (!previewCard || !previewWrap) return;
    if (keys.length === 0) {
      previewCard.style.display = 'none';
      previewWrap.innerHTML = '';
      return;
    }
    previewCard.style.display = 'block';

    const title = (inputTitle?.value || '').trim();
    const caption = (textareaBase?.value || '').trim();
    const postType = (selectPostType?.value || 'auto').toUpperCase();

    const ytTitle = (inputYtTitle?.value || '').trim();
    const ytPriv  = (selectYtPriv?.value || 'public').trim();

    const media = getMediaSource();

    let html = '';

    // IG
    if (platforms.instagram?.length) {
    const accounts = platforms.instagram.map(a => a.label).join(' • ');
    const ratio = (selectPostType?.value === 'story') ? 'pv-ratio-9x16'
                : (media.type === 'video') ? 'pv-ratio-9x16'
                : 'pv-ratio-4x5';

    const badges = `
        <span class="pv-badge">Instagram</span>
        <span class="pv-badge">${escapeHtml(postType)}</span>
    `;

    const body = `
        <div class="pv-row">
        <div style="flex:0 0 auto;">
            <div style="position:relative;">
            ${mediaHtml(media, ratio)}
            <div class="pv-ig-top">
                <span class="pv-ig-pill">@${escapeHtml(platforms.instagram[0]?.label?.includes('@') ? '' : 'sosyalmedyaplanlama')}</span>
                <span class="pv-ig-pill">${escapeHtml(postType)}</span>
            </div>
            </div>
        </div>
        <div style="flex:1 1 240px;">
            <div class="text-muted small mb-1">Caption</div>
            <div style="white-space:pre-wrap;">${escapeHtml(caption || '—')}</div>
        </div>
        </div>
    `;

    html += buildPlatformCard('Instagram Ön İzleme', accounts, badges, body);
    }

    // FB
        if (platforms.facebook?.length) {
        const accounts = platforms.facebook.map(a => a.label).join(' • ');
        const badges = `<span class="pv-badge">Facebook</span>`;

        const body = `
            <div class="pv-feed" style="max-width:520px;">
            <div class="pv-feed-head">
                <div class="pv-avatar"></div>
                <div>
                <div class="pv-feed-name">Sosyal Medya Planlama</div>
                <div class="pv-feed-sub">Just now • Public</div>
                </div>
            </div>
            <div style="background:#111;">
                <div class="pv-screen pv-ratio-4x5" style="border-radius:0;">
                <div class="pv-media">
                    ${(!media.url) ? '' : (media.type === 'image'
                    ? `<img src="${escapeHtml(media.url)}" alt="">`
                    : `<video controls muted playsinline><source src="${escapeHtml(media.url)}"></video>`
                    )}
                </div>
                </div>
            </div>
            <div class="pv-feed-body">${escapeHtml(caption || '—')}</div>
            </div>
        `;

        html += buildPlatformCard('Facebook Ön İzleme', accounts, badges, body);
        }

    // TikTok
    if (platforms.tiktok?.length) {
    const accounts = platforms.tiktok.map(a => a.label).join(' • ');
    const badges = `
        <span class="pv-badge">TikTok</span>
        <span class="pv-badge">Video</span>
    `;

    const body = `
        <div class="pv-row">
        <div style="flex:0 0 auto;">
            <div style="position:relative; width:320px; max-width:100%;">
            <div class="pv-phone">
                <div class="pv-screen pv-ratio-9x16">
                <div class="pv-media">
                    ${
                    (!media.url || media.type !== 'video')
                        ? `<div class="text-muted small p-3">TikTok için video seçilmedi.</div>`
                        : `<video controls muted playsinline><source src="${escapeHtml(media.url)}"></video>`
                    }
                </div>

                <div class="pv-tt-left">
                    <div class="pv-tt-user">@sosyalmedyaplanlama</div>
                    <div class="pv-tt-caption">${escapeHtml(caption || '—')}</div>
                </div>

                <div class="pv-tt-right">
                    <div class="pv-tt-btn"></div>
                    <div class="pv-tt-btn"></div>
                    <div class="pv-tt-btn"></div>
                </div>
                </div>
            </div>
            </div>
        </div>

        <div style="flex:1 1 240px;">
            <div class="text-muted small mb-1">Caption</div>
            <div style="white-space:pre-wrap;">${escapeHtml(caption || '—')}</div>
            <div class="text-muted small mt-2">Audit için: Panel içinde TikTok ön izleme gösteriliyor ✅</div>
        </div>
        </div>
    `;

    html += buildPlatformCard('TikTok Ön İzleme', accounts, badges, body);
    }

    // YouTube
    if (platforms.youtube?.length) {
  const accounts = platforms.youtube.map(a => a.label).join(' • ');
  const badges = `
    <span class="pv-badge">YouTube</span>
    <span class="pv-badge">Privacy: ${escapeHtml(ytPriv)}</span>
  `;

  const ytFinalTitle = ytTitle || title || '—';

  const body = `
    <div style="max-width:620px;">
      <div class="pv-screen pv-ratio-16x9" style="background:#111; border-radius:16px;">
        <div class="pv-media">
          ${(!media.url) ? '' : (media.type === 'video'
            ? `<video controls muted playsinline><source src="${escapeHtml(media.url)}"></video>`
            : `<img src="${escapeHtml(media.url)}" alt="">`
          )}
        </div>
      </div>

      <div class="pv-yt-title">${escapeHtml(ytFinalTitle)}</div>
      <div class="pv-yt-meta">@burkaydoner • ${escapeHtml(ytPriv)}</div>

      <div class="text-muted small mt-2">Açıklama</div>
      <div style="white-space:pre-wrap; font-size:12px;">${escapeHtml(caption || '—')}</div>
    </div>
  `;

  html += buildPlatformCard('YouTube Ön İzleme', accounts, badges, body);
}

    previewWrap.innerHTML = html;
  }

  // events
  checks.forEach(ch => ch.addEventListener('change', render));
  inputTitle?.addEventListener('input', render);
  textareaBase?.addEventListener('input', render);
  selectPostType?.addEventListener('change', render);
  inputYtTitle?.addEventListener('input', render);
  selectYtPriv?.addEventListener('change', render);
  inputMedia?.addEventListener('change', render);

  // init
  render();
})();
</script>

<?= $this->endSection() ?>
