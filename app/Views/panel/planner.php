<?= $this->extend('layouts/panel') ?>
<?= $this->section('content') ?>

<style>
  /* ---------- Brand Buttons ---------- */
  .btn-brand{
    border:0;
    color:#fff;
    background: linear-gradient(135deg, #7c3aed, #ec4899);
    box-shadow: 0 10px 24px rgba(124,58,237,.18);
    border-radius:999px;
    font-weight:800;
  }
  .btn-brand:hover{ filter: brightness(.98); transform: translateY(-1px); color:#fff; }
  .btn-brand:active{ transform: translateY(0); }
  .btn-soft{
    border:1px solid rgba(0,0,0,.10);
    background:#fff;
    color:#111;
    border-radius:999px;
    font-weight:800;
  }
  .btn-soft:hover{ background:#f8f9fa; }

  /* Emoji menu style */
  .btn-emoji{
    width:36px; height:36px; padding:0;
    display:flex; align-items:center; justify-content:center;
    border-radius:10px;
    border:1px solid rgba(0,0,0,.08);
    background:#fff;
  }
  .btn-emoji:hover{ background:#f8f9fa; }
  .emoji-menu{
    border-radius:14px;
    border:1px solid rgba(0,0,0,.10);
    box-shadow:0 18px 50px rgba(0,0,0,.12);
  }

  /* --- TikTok Preview (Premium) --- */
  .pv-platform{
    border:1px solid rgba(0,0,0,.08);
    border-radius:16px;
    padding:14px;
    background:#fff;
  }
  .pv-head{
    display:flex; align-items:flex-start; justify-content:space-between; gap:10px; margin-bottom:10px;
  }
  .pv-title{ font-weight:900; letter-spacing:-.3px; }
  .pv-sub{ color:#6c757d; font-size:12px; margin-top:2px; }

  .pv-badges{ display:flex; gap:6px; flex-wrap:wrap; justify-content:flex-end; }
  .pv-badge{
    font-size:11px; padding:4px 8px; border-radius:999px;
    border:1px solid rgba(0,0,0,.08); background:#f8f9fa; color:#111; font-weight:800;
  }

  .pv-row{ display:flex; gap:14px; flex-wrap:wrap; align-items:flex-start; }

  .pv-phone{
    width: 320px;
    max-width: 100%;
    border-radius: 28px;
    background: #0b0b0c;
    padding: 10px;
    border: 1px solid rgba(0,0,0,.18);
    box-shadow: 0 18px 40px rgba(0,0,0,.12);
    position: relative;
  }
  .pv-phone:before{
    content:"";
    position:absolute;
    top:10px;
    left:50%;
    transform:translateX(-50%);
    width: 92px;
    height: 18px;
    border-radius: 999px;
    background: rgba(255,255,255,.06);
    border: 1px solid rgba(255,255,255,.05);
    pointer-events:none;
  }

  .pv-screen{
    background:#111;
    border-radius: 20px;
    overflow:hidden;
    position:relative;
    aspect-ratio: 9/16;
  }

  .pv-media, .pv-media video, .pv-media img{
    width:100%;
    height:100%;
    object-fit: cover;
    display:block;
    background:#000;
  }

  /* TikTok overlay */
  .pv-tt-left{
    position:absolute; left:10px; bottom:14px; right:68px;
    color:#fff; font-size:12px;
    text-shadow:0 1px 12px rgba(0,0,0,.75);
    pointer-events:none;
  }
  .pv-tt-user{ font-weight:900; margin-bottom:6px; }
  .pv-tt-caption{
    opacity:.95;
    white-space:pre-wrap;
    max-height:86px;
    overflow:hidden;
  }
  .pv-tt-right{
    position:absolute; right:10px; bottom:16px;
    display:flex; flex-direction:column; gap:10px;
    pointer-events:none;
  }
  .pv-tt-avatar{
    width:40px; height:40px; border-radius:999px;
    background:rgba(255,255,255,.18);
    border:1px solid rgba(255,255,255,.28);
    display:flex; align-items:center; justify-content:center;
    box-shadow:0 8px 18px rgba(0,0,0,.25);
  }
  .pv-tt-avatar::after{
    content:"S";
    font-weight:900;
    font-size:14px;
    color:#fff;
  }
  .pv-tt-action{
    width:38px; height:38px; border-radius:999px;
    background:rgba(255,255,255,.14);
    border:1px solid rgba(255,255,255,.22);
    display:flex; align-items:center; justify-content:center;
    box-shadow:0 8px 18px rgba(0,0,0,.22);
  }
  .pv-tt-action::after{
    color:#fff;
    font-size:16px;
    line-height:1;
    opacity:.95;
  }
  .pv-tt-like::after{ content:"♥"; }
  .pv-tt-comment::after{ content:"💬"; }
  .pv-tt-share::after{ content:"↗"; }

  .pv-caption-box{
    border:1px solid rgba(0,0,0,.08);
    border-radius:12px;
    padding:10px 12px;
    background:#fff;
    max-width: 320px;
  }
  .pv-caption-label{ color:#6c757d; font-size:12px; margin-bottom:6px; }
  .pv-caption-text{ font-size:12px; white-space:pre-wrap; }

  .pv-disclaimer{ color:#6c757d; font-size:11px; margin-top:8px; }

  .page-title{
    font-size:28px;
    font-weight:900;
    letter-spacing:-.5px;
    margin-bottom:2px;
  }
  .page-sub{ color: rgba(0,0,0,.55); }

  .hint-card{
    border:1px solid rgba(0,0,0,.06);
    border-radius:18px;
    background:#fff;
  }
</style>

<div class="container-fluid py-3">
  <div class="d-flex align-items-start justify-content-between mb-3 flex-wrap gap-2">
    <div>
      <div class="page-title">Post to TikTok</div>
      <div class="page-sub">Tek ekrandan video yükle, TikTok hesabını seç, tarih/saat belirle ve paylaşımı planla.</div>
    </div>
    <div class="d-flex gap-2">
      <a href="<?= site_url('panel/calendar') ?>" class="btn btn-soft">Takvime Dön</a>
      <a href="<?= site_url('panel/templates') ?>" class="btn btn-brand">Şablondan Oluştur</a>
    </div>
  </div>

  <?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
  <?php endif; ?>
  <?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
  <?php endif; ?>

  <?php
    // TikTok-only hesaplar
    $tiktokAccounts = [];
    foreach (($accounts ?? []) as $a) {
      if (strtolower((string)($a['platform'] ?? '')) === 'tiktok') $tiktokAccounts[] = $a;
    }
    $hasTiktok = !empty($tiktokAccounts);
  ?>

  <?php if (!$hasTiktok): ?>
    <div class="hint-card p-3 mb-3">
      <div class="fw-bold mb-1">Önce TikTok hesabını bağlamalısın</div>
      <div class="text-muted small mb-2">
        TikTok Direct Post paylaşımı yapabilmek için hesabını yetkilendirmen gerekir.
      </div>
      <a href="<?= site_url('panel/social-accounts') ?>" class="btn btn-brand btn-sm">
        <i class="bi bi-music-note-beamed me-1"></i> TikTok Bağlantısı
      </a>
    </div>
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
            <label class="form-label">Başlık</label>
            <input type="text" name="title" class="form-control" value="<?= esc($prefill['title'] ?? '') ?>" placeholder="Örn: Yeni ürün duyurusu / Kampanya / Etkinlik">
            <div class="form-text">Başlık içeriklerini panelde daha kolay takip etmeni sağlar.</div>
          </div>

          <!-- Caption + Emoji -->
          <div class="mb-3">
            <div class="d-flex align-items-center justify-content-between">
              <label class="form-label mb-0">Açıklama (Caption)</label>

              <div class="dropdown">
                <button
                  class="btn btn-sm btn-soft dropdown-toggle"
                  type="button"
                  data-bs-toggle="dropdown"
                  aria-expanded="false"
                  title="Emoji ekle"
                >
                  😊 Emoji
                </button>

                <div class="dropdown-menu dropdown-menu-end p-2 emoji-menu" style="width: 320px;">
                  <div class="small text-muted mb-2">Sık kullanılanlar</div>
                  <div class="d-flex flex-wrap gap-1 mb-2" id="emojiRecent"></div>

                  <div class="small text-muted mb-2">Emojiler</div>
                  <div class="d-flex flex-wrap gap-1" id="emojiGrid">
                    <?php
                      $emojis = ['😀','😁','😂','🤣','😊','😍','😘','😎','🤩','😇','😅','😉','😌','🙂','🙃','🤗',
                                 '🔥','✨','💯','✅','🎉','📌','📣','💬','📸','🎬','🎥','🎵','🧡','❤️','💙','💚',
                                 '🙏','👏','🤝','🚀','⭐','⚡','📍','🕒','🗓️','🔗','🛒','🎁','🍀','🌸','☀️','🌙'];
                      foreach ($emojis as $e):
                    ?>
                      <button type="button" class="btn btn-emoji" data-emoji="<?= esc($e) ?>"><?= esc($e) ?></button>
                    <?php endforeach; ?>
                  </div>

                  <div class="mt-2 pt-2 border-top d-flex justify-content-between align-items-center">
                    <div class="small text-muted">Seçtiğin emoji otomatik eklenir</div>
                    <button type="button" class="btn btn-sm btn-link text-decoration-none" id="emojiClearRecent">Temizle</button>
                  </div>
                </div>
              </div>
            </div>

            <textarea
              name="base_text"
              class="form-control"
              rows="6"
              id="captionText"
              placeholder="Açıklamanı yaz...
#hashtag #örnek #kampanya"
            ><?= esc($prefill['base_text'] ?? '') ?></textarea>

            <div class="form-text">
              Bu metin TikTok gönderi açıklaması olarak kullanılır.
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Video</label>

            <?php if (!empty($prefill) && !empty($prefill['media_path'])): ?>
              <div class="border rounded p-2 bg-light">
                <div class="small text-muted mb-2">Şablondan üretilen video:</div>

                <?php if (($prefill['media_type'] ?? '') === 'video'): ?>
                  <video controls style="max-width:100%; border-radius:10px; display:block;">
                    <source src="<?= base_url($prefill['media_path']) ?>">
                  </video>
                <?php else: ?>
                  <div class="text-danger small">
                    TikTok için video gerekir. Şablondan gelen medya video değil.
                  </div>
                <?php endif; ?>

                <div class="small text-muted mt-2">
                  Bu içerik hazır. Yeni dosya yükleme alanı gizlendi.
                </div>
              </div>
            <?php else: ?>
              <input type="file" name="media" class="form-control" accept="video/*" required>
              <div class="form-text">
                TikTok Direct Post için video zorunludur.
              </div>
            <?php endif; ?>
          </div>

        </div>
      </div>
    </div>

    <div class="col-lg-5">
      <div class="card mb-3">
        <div class="card-body">
          <h5 class="card-title mb-3">Hedef Hesap</h5>

          <?php if (!$hasTiktok): ?>
            <div class="alert alert-warning mb-0">
              Henüz TikTok hesabı yok. Önce <a href="<?= site_url('panel/social-accounts') ?>">TikTok Bağlantısı</a> yap.
            </div>
          <?php else: ?>
            <div class="vstack gap-2">
              <?php foreach ($tiktokAccounts as $a): ?>
                <?php
                  $label = 'TikTok — ';
                  if (!empty($a['username'])) $label .= '@' . $a['username'];
                  elseif (!empty($a['name'])) $label .= $a['name'];
                  else $label .= 'Hesap #' . (int)$a['id'];
                ?>
                <label class="border rounded p-2 d-flex align-items-center justify-content-between">
                  <span><?= esc($label) ?> <span class="text-muted">(ID: <?= (int)$a['id'] ?>)</span></span>
                  <input
                    class="form-check-input account-check"
                    type="radio"
                    name="account_ids[]"
                    value="<?= (int)$a['id'] ?>"
                    data-platform="tiktok"
                    data-username="<?= esc($a['username'] ?? '') ?>"
                    data-name="<?= esc($a['name'] ?? '') ?>"
                    required
                    >
                </label>
              <?php endforeach; ?>
            </div>
            <div class="form-text mt-2">TikTok için tek hesap seçilir.</div>
          <?php endif; ?>
        </div>
      </div>

      <div class="card">
        <div class="card-body">
          <h5 class="card-title mb-3">Zamanlama</h5>

          <div class="mb-3">
            <label class="form-label">Tiktok Paylaşım Tipi</label>
            <select name="post_type" class="form-select" required>
              <option value="auto" selected>AUTO</option>
            </select>
            <!-- TikTok Audit için zorunlu UX alanları -->
<div class="border rounded-3 p-3 mb-3" style="border-color: rgba(0,0,0,.08) !important;">
  <div class="fw-bold mb-2">TikTok Gönderi Ayarları <span class="text-danger">*</span></div>
  <div class="text-muted small mb-3">
    Bu alanlar TikTok Direct Post yönergeleri için gereklidir. Varsayılan olarak kapalıdır.
  </div>

  <!-- Privacy (Zorunlu, default seçili OLMAMALI) -->
  <div class="mb-3">
    <label class="form-label">Gizlilik</label>
    <select name="tiktok_privacy" class="form-select" required>
      <option value="" selected disabled>Seçiniz</option>
      <option value="PUBLIC">Herkese açık</option>
      <option value="FRIENDS">Arkadaşlar</option>
      <option value="PRIVATE">Sadece ben</option>
    </select>
    <div class="form-text">Gönderi gizlilik seçimi zorunludur.</div>
  </div>

  <!-- Interaction toggles (Default OFF) -->
  <div class="mb-3">
    <label class="form-label d-block">Etkileşim İzinleri (varsayılan kapalı)</label>

    <div class="form-check">
      <input class="form-check-input" type="checkbox" name="allow_comment" id="allow_comment" value="1">
      <label class="form-check-label" for="allow_comment">Yorumlara izin ver</label>
    </div>

    <div class="form-check">
      <input class="form-check-input" type="checkbox" name="allow_duet" id="allow_duet" value="1">
      <label class="form-check-label" for="allow_duet">Duet’e izin ver</label>
    </div>

    <div class="form-check">
      <input class="form-check-input" type="checkbox" name="allow_stitch" id="allow_stitch" value="1">
      <label class="form-check-label" for="allow_stitch">Stitch’e izin ver</label>
    </div>

    <div class="form-text">Audit için bu ayarların kullanıcı tarafından seçilebilir olması gerekir.</div>
  </div>

  <!-- Music / Content rights confirmation (Zorunlu onay checkbox) -->
  <div class="mb-3">
    <div class="form-check">
      <input class="form-check-input" type="checkbox" name="music_confirm" id="music_confirm" value="1" required>
      <label class="form-check-label" for="music_confirm">
        Bu içerikte kullanılan müzik/medya haklarının paylaşım için uygun olduğunu onaylıyorum.
      </label>
    </div>
    <div class="form-text">Bu onay işaretlenmeden paylaşım planlanamaz.</div>
  </div>

  <!-- Commercial content disclosure -->
  <div class="mb-2">
    <div class="form-check">
      <input class="form-check-input" type="checkbox" name="is_commercial" id="is_commercial" value="1">
      <label class="form-check-label" for="is_commercial">
        Ticari içerik / reklam (disclosure)
      </label>
    </div>
  </div>

  <div id="commercialBox" class="mt-2" style="display:none;">
    <label class="form-label">Marka / Ürün (opsiyonel)</label>
    <input type="text" name="commercial_brand" class="form-control" placeholder="Örn: Marka adı">
    <div class="form-text">Ticari içerik ise açıklama eklemek audit açısından daha nettir.</div>
  </div>
</div>
          </div>

          <div class="mb-3">
            <label class="form-label">Tarih/Saat</label>
            <input type="datetime-local" name="schedule_at" class="form-control" required>
            <div class="form-text">Kaydetmeden önce otomatik olarak Y-m-d H:i:s formatına çevrilir.</div>
          </div>

          <button type="submit" class="btn btn-brand w-100">Planla</button>
        </div>
      </div>
    </div>

    <!-- Ön İzleme -->
    <div class="col-12">
      <div class="card mb-3" id="previewCard" style="display:none;">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <h5 class="card-title mb-0">Ön İzleme</h5>
            <span class="badge bg-light text-dark">TikTok</span>
          </div>

          <div class="text-muted small mb-3">
            Videonun TikTok’ta yaklaşık nasıl görüneceğini burada görebilirsin.
            <span class="pv-disclaimer d-block mt-1">
              Not: Bu bir ön izleme görünümüdür. TikTok arayüzüne göre küçük farklılıklar olabilir.
            </span>
          </div>

          <div id="previewWrap" class="vstack gap-3"></div>
        </div>
      </div>
    </div>

  </form>
</div>

<script>
(function(){
  const checks  = Array.from(document.querySelectorAll('.account-check'));
  const previewCard = document.getElementById('previewCard');
  const previewWrap = document.getElementById('previewWrap');

  const inputTitle     = document.querySelector('input[name="title"]');
  const textareaBase   = document.querySelector('textarea[name="base_text"]');
  const inputMedia     = document.querySelector('input[name="media"]');

  // Prefill medya varsa
  const PREFILL_MEDIA_URL  = <?= !empty($prefill['media_path']) ? json_encode(base_url($prefill['media_path'])) : 'null' ?>;
  const PREFILL_MEDIA_TYPE = <?= !empty($prefill['media_type']) ? json_encode($prefill['media_type']) : 'null' ?>;

  let lastObjectUrl = null;

  function escapeHtml(s){
    return (s ?? '').toString()
      .replace(/&/g,'&amp;')
      .replace(/</g,'&lt;')
      .replace(/>/g,'&gt;')
      .replace(/"/g,'&quot;')
      .replace(/'/g,'&#039;');
  }

  function getMediaSource(){
    if (PREFILL_MEDIA_URL && PREFILL_MEDIA_TYPE) {
      return { url: PREFILL_MEDIA_URL, type: PREFILL_MEDIA_TYPE };
    }

    if (inputMedia && inputMedia.files && inputMedia.files[0]) {
      const f = inputMedia.files[0];
      const mime = (f.type || '').toLowerCase();
      const type = mime.startsWith('video/') ? 'video' : null;

      if (lastObjectUrl) {
        try { URL.revokeObjectURL(lastObjectUrl); } catch(e){}
        lastObjectUrl = null;
      }

      lastObjectUrl = URL.createObjectURL(f);
      return { url: lastObjectUrl, type };
    }

    if (lastObjectUrl) {
      try { URL.revokeObjectURL(lastObjectUrl); } catch(e){}
      lastObjectUrl = null;
    }

    return { url: null, type: null };
  }

  function phoneMediaHtml(media, overlayHtml){
    const inner = (!media.url || media.type !== 'video')
      ? `<div class="text-muted small p-3">Video seçilmedi.</div>`
      : `<video controls muted playsinline><source src="${escapeHtml(media.url)}"></video>`;

    return `
      <div class="pv-phone">
        <div class="pv-screen">
          <div class="pv-media">${inner}</div>
          ${overlayHtml || ''}
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

  function buildCaptionBox(label, caption){
    return `
      <div class="pv-caption-box">
        <div class="pv-caption-label">${escapeHtml(label)}</div>
        <div class="pv-caption-text">${escapeHtml(caption || '—')}</div>
      </div>
    `;
  }

  function render(){
    if (!previewCard || !previewWrap) return;

    const hasSelected = checks.some(ch => ch.checked);
    if (!hasSelected){
      previewCard.style.display = 'none';
      previewWrap.innerHTML = '';
      return;
    }

    previewCard.style.display = 'block';

    const caption = (textareaBase?.value || '').trim();
    const selected = checks.find(ch => ch.checked);
    let uname = (selected?.dataset?.username || '').trim();
    let nm    = (selected?.dataset?.name || '').trim();
    let shownUser = '@tiktok_user';
    if (uname) shownUser = '@' + uname;
    else if (nm) shownUser = nm;
    const media = getMediaSource();

    const badges = `
      <span class="pv-badge">TikTok</span>
      <span class="pv-badge">Direct Post</span>
    `;

    const ttOverlay = `
      <div class="pv-tt-left">
        <div class="pv-tt-user">${escapeHtml(shownUser)}</div>
        <div class="pv-tt-caption">${escapeHtml(caption || '—')}</div>
      </div>
      <div class="pv-tt-right">
        <div class="pv-tt-avatar"></div>
        <div class="pv-tt-action pv-tt-like"></div>
        <div class="pv-tt-action pv-tt-comment"></div>
        <div class="pv-tt-action pv-tt-share"></div>
      </div>
    `;

    const body = `
      <div class="pv-row">
        <div style="flex:0 0 auto;">
          ${phoneMediaHtml(media, ttOverlay)}
          <div class="mt-2">
            ${buildCaptionBox('Caption - Açıklama', caption)}
          </div>
        </div>
      </div>
    `;

    previewWrap.innerHTML = buildPlatformCard('TikTok Ön İzleme', 'Seçtiğin TikTok hesabında yayınlanır.', badges, body);
  }

  checks.forEach(ch => ch.addEventListener('change', render));
  inputTitle?.addEventListener('input', render);
  textareaBase?.addEventListener('input', render);
  inputMedia?.addEventListener('change', render);

  render();
})();
</script>

<!-- Emoji insert script -->
<script>
(function(){
  const ta = document.getElementById('captionText');
  if (!ta) return;

  const recentKey = 'smp_recent_emojis_v1';
  const recentWrap = document.getElementById('emojiRecent');
  const clearBtn = document.getElementById('emojiClearRecent');

  function loadRecent(){
    try{
      const arr = JSON.parse(localStorage.getItem(recentKey) || '[]');
      return Array.isArray(arr) ? arr : [];
    }catch(e){ return []; }
  }
  function saveRecent(arr){
    try{ localStorage.setItem(recentKey, JSON.stringify(arr.slice(0, 16))); }catch(e){}
  }
  function renderRecent(){
    if (!recentWrap) return;
    const arr = loadRecent();
    recentWrap.innerHTML = arr.length
      ? arr.map(e => `<button type="button" class="btn btn-emoji" data-emoji="${e}">${e}</button>`).join('')
      : `<div class="small text-muted">Henüz yok</div>`;
  }

  function insertAtCursor(textarea, text){
    const start = textarea.selectionStart ?? textarea.value.length;
    const end   = textarea.selectionEnd ?? textarea.value.length;
    const before = textarea.value.substring(0, start);
    const after  = textarea.value.substring(end);
    textarea.value = before + text + after;
    const pos = start + text.length;
    textarea.setSelectionRange(pos, pos);
    textarea.focus();
    textarea.dispatchEvent(new Event('input', { bubbles:true }));
  }

  function handleEmojiClick(e){
    const btn = e.target.closest('[data-emoji]');
    if (!btn) return;
    const emoji = btn.getAttribute('data-emoji');
    if (!emoji) return;

    insertAtCursor(ta, emoji);

    const arr = loadRecent().filter(x => x !== emoji);
    arr.unshift(emoji);
    saveRecent(arr);
    renderRecent();
  }

  document.addEventListener('click', function(e){
    if (e.target.closest('#emojiGrid')) handleEmojiClick(e);
    if (e.target.closest('#emojiRecent')) handleEmojiClick(e);
  });

  clearBtn?.addEventListener('click', function(){
    try{ localStorage.removeItem(recentKey); }catch(e){}
    renderRecent();
  });

  renderRecent();
})();
</script>
<script>
(function(){
  const cb = document.getElementById('is_commercial');
  const box = document.getElementById('commercialBox');
  if(!cb || !box) return;
  function sync(){ box.style.display = cb.checked ? 'block' : 'none'; }
  cb.addEventListener('change', sync);
  sync();
})();
</script>
<?= $this->endSection() ?>
