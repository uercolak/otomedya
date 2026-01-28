<?= $this->extend('layouts/panel') ?>
<?= $this->section('content') ?>

<style>
  /* =========================
     Premium Planner UI
  ========================== */
  :root{
    --pv-border: rgba(17, 24, 39, .08);
    --pv-muted: #6b7280;
    --pv-ink: #111827;
    --pv-card: #ffffff;
    --pv-soft: rgba(99,102,241,.08);
    --pv-soft2: rgba(236,72,153,.08);
  }

  .planner-wrap{ padding: 14px 0 22px; }
  .planner-topbar{
    display:flex; align-items:flex-end; justify-content:space-between; gap:12px;
    padding: 10px 12px; border:1px solid var(--pv-border);
    background: linear-gradient(135deg, var(--pv-soft), var(--pv-soft2));
    border-radius: 18px;
  }
  .planner-title{ margin:0; font-weight:800; letter-spacing:-.02em; color:var(--pv-ink); }
  .planner-sub{ margin-top:4px; color: var(--pv-muted); font-size: 13px; }
  .planner-actions .btn{ border-radius: 12px; }

  /* Section card */
  .p-card{
    background: var(--pv-card);
    border: 1px solid var(--pv-border);
    border-radius: 18px;
    overflow: hidden;
    box-shadow: 0 18px 40px rgba(17,24,39,.04);
  }
  .p-card-head{
    padding: 14px 16px;
    display:flex; align-items:center; justify-content:space-between; gap:10px;
    border-bottom: 1px solid rgba(0,0,0,.06);
    background: rgba(249,250,251,.6);
  }
  .p-card-head h5{ margin:0; font-weight:800; letter-spacing:-.01em; }
  .p-pill{
    display:inline-flex; align-items:center; gap:6px;
    padding: 6px 10px;
    border-radius: 999px;
    border:1px solid rgba(0,0,0,.08);
    background:#fff;
    font-size: 12px;
    color:#111;
    white-space: nowrap;
  }
  .p-card-body{ padding: 16px; }

  /* Form elements polish */
  .form-label{ font-weight:700; color:#111827; }
  .form-text{ color: #6b7280; }
  .form-control, .form-select{
    border-radius: 14px;
    border-color: rgba(0,0,0,.12);
    padding: 10px 12px;
  }
  .form-control:focus, .form-select:focus{
    box-shadow: 0 0 0 .2rem rgba(99,102,241,.12);
    border-color: rgba(99,102,241,.45);
  }

  /* Media dropzone look */
  .media-drop{
    border: 1.5px dashed rgba(99,102,241,.35);
    background: rgba(99,102,241,.06);
    border-radius: 16px;
    padding: 14px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:12px;
  }
  .media-drop strong{ color:#111827; }
  .media-hint{ font-size: 12px; color: var(--pv-muted); margin:0; }
  .media-drop .btn{ border-radius: 12px; }

  /* Accounts list */
  .acc-list{ display:flex; flex-direction:column; gap:10px; }
  .acc-item{
    border:1px solid rgba(0,0,0,.10);
    border-radius: 14px;
    padding: 10px 12px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:10px;
    background:#fff;
  }
  .acc-left{ display:flex; align-items:center; gap:10px; min-width: 0; }
  .acc-plat{
    font-size: 11px; font-weight: 800; letter-spacing:.02em;
    padding: 5px 10px; border-radius: 999px;
    border:1px solid rgba(0,0,0,.08);
    white-space: nowrap;
  }
  .acc-plat.instagram{ background: rgba(225,48,108,.10); border-color: rgba(225,48,108,.22); }
  .acc-plat.facebook { background: rgba(24,119,242,.10); border-color: rgba(24,119,242,.22); }
  .acc-plat.tiktok   { background: rgba(0,242,234,.10); border-color: rgba(0,242,234,.22); }
  .acc-plat.youtube  { background: rgba(255,0,0,.08);   border-color: rgba(255,0,0,.18); }

  .acc-meta{ min-width:0; }
  .acc-name{
    font-weight: 800; font-size: 13px; color:#111827;
    overflow:hidden; text-overflow:ellipsis; white-space:nowrap;
  }
  .acc-sub{ font-size: 12px; color: var(--pv-muted); }

  /* Sticky sidebar */
  @media (min-width: 992px){
    .planner-sticky{ position: sticky; top: 14px; }
  }

  /* CTA button */
  .btn-primary{
    border-radius: 14px;
    padding: 11px 14px;
    font-weight: 800;
  }

  /* Preview section */
  .preview-note{
    color: var(--pv-muted);
    font-size: 12px;
    margin: 0;
  }

  /* --- Preview UI (Premium) --- */
  .pv-platform { border:1px solid rgba(0,0,0,.08); border-radius:16px; padding:14px; background:#fff; }
  .pv-head { display:flex; align-items:flex-start; justify-content:space-between; gap:10px; margin-bottom:10px; }
  .pv-title { font-weight:800; }
  .pv-sub { color:#6c757d; font-size:12px; margin-top:2px; }

  .pv-badges { display:flex; gap:6px; flex-wrap:wrap; justify-content:flex-end; }
  .pv-badge { font-size:11px; padding:4px 8px; border-radius:999px; border:1px solid rgba(0,0,0,.08); background:#f8f9fa; color:#111; }

  .pv-row { display:flex; gap:14px; flex-wrap:wrap; align-items:flex-start; }

  /* --- Phone mock (IG/TikTok) --- */
  .pv-phone{
    width: 300px;
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

  .pv-screen {
    background:#111;
    border-radius: 20px;
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
    background:#000;
  }

  /* --- IG overlay (minimal) --- */
  .pv-ig-top{
    position:absolute; top:10px; left:10px; right:10px;
    display:flex; align-items:center; justify-content:space-between;
    color:#fff; font-size:12px;
    text-shadow:0 1px 10px rgba(0,0,0,.55);
    pointer-events:none;
  }
  .pv-ig-pill{
    background:rgba(0,0,0,.40);
    border:1px solid rgba(255,255,255,.14);
    padding:4px 8px;
    border-radius:999px;
  }

  /* --- TikTok overlay --- */
  .pv-tt-left{
    position:absolute; left:10px; bottom:14px; right:68px;
    color:#fff; font-size:12px;
    text-shadow:0 1px 12px rgba(0,0,0,.75);
    pointer-events:none;
  }
  .pv-tt-user{ font-weight:800; margin-bottom:6px; }
  .pv-tt-caption{
    opacity:.95;
    white-space:pre-wrap;
    max-height:76px;
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
  .pv-tt-action::after{ color:#fff; font-size:16px; line-height:1; opacity:.95; }
  .pv-tt-like::after{ content:"♥"; }
  .pv-tt-comment::after{ content:"💬"; }
  .pv-tt-share::after{ content:"↗"; }

  /* --- Caption box (under phone) --- */
  .pv-caption-box{
    border:1px solid rgba(0,0,0,.08);
    border-radius:12px;
    padding:10px 12px;
    background:#fff;
    max-width: 300px;
  }
  .pv-caption-label{ color:#6c757d; font-size:12px; margin-bottom:6px; }
  .pv-caption-text{ font-size:12px; white-space:pre-wrap; }

  /* --- Facebook feed card --- */
  .pv-feed{
    border:1px solid rgba(0,0,0,.08);
    border-radius:14px;
    overflow:hidden;
    background:#fff;
    max-width: 520px;
  }
  .pv-feed-head{
    padding:10px 12px;
    display:flex;
    align-items:center;
    gap:10px;
  }
  .pv-avatar{ width:28px; height:28px; border-radius:999px; background:#e9ecef; }
  .pv-feed-name{ font-size:12px; font-weight:800; line-height:1.1; }
  .pv-feed-sub{ font-size:11px; color:#6c757d; }
  .pv-feed-body{ padding:10px 12px; font-size:12px; white-space:pre-wrap; }

  /* --- YouTube --- */
  .pv-yt-wrap{ max-width: 620px; }
  .pv-yt-title{ font-weight:900; font-size:13px; margin-top:10px; }
  .pv-yt-meta{ color:#6c757d; font-size:11px; margin-top:2px; }

  /* small helpers */
  .char-count{ font-size:12px; color: var(--pv-muted); }
  .mini-tip{
    font-size:12px; color: var(--pv-muted);
    background: rgba(17,24,39,.03);
    border:1px solid rgba(17,24,39,.06);
    border-radius: 14px;
    padding: 10px 12px;
  }
</style>

<div class="container-fluid planner-wrap">
  <div class="planner-topbar mb-3">
    <div>
      <h3 class="planner-title">Yeni Gönderi Planla</h3>
      <div class="planner-sub">Tek ekrandan içerik oluştur, hesap seç, tarih belirle ve paylaşımı planla.</div>
    </div>
    <div class="planner-actions d-flex gap-2">
      <a href="<?= site_url('panel/calendar') ?>" class="btn btn-outline-secondary">
        <i class="bi bi-calendar3 me-1"></i> Takvime Dön
      </a>
      <a href="<?= site_url('panel/templates') ?>" class="btn btn-outline-primary">
        <i class="bi bi-grid-3x3-gap me-1"></i> Şablondan Oluştur
      </a>
    </div>
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

    <!-- LEFT -->
    <div class="col-lg-7">
      <!-- CONTENT -->
      <div class="p-card mb-3">
        <div class="p-card-head">
          <h5><i class="bi bi-pencil-square me-2"></i>İçerik</h5>
          <span class="p-pill"><i class="bi bi-1-circle me-1"></i> Adım 1</span>
        </div>
        <div class="p-card-body">
          <div class="mb-3">
            <label class="form-label">Başlık</label>
            <input type="text" name="title" class="form-control" value="<?= esc($prefill['title'] ?? '') ?>" placeholder="Örn: Yeni ürün duyurusu / Kampanya / Etkinlik">
            <div class="form-text">Kısa ve net bir başlık, içeriklerini daha kolay takip etmeni sağlar.</div>
          </div>

          <div class="mb-2 d-flex align-items-center justify-content-between">
            <label class="form-label mb-0">Açıklama (Caption)</label>
            <div class="char-count" id="captionCount">0 karakter</div>
          </div>
          <textarea name="base_text" class="form-control" rows="7" placeholder="Gönderinin açıklamasını yaz..."><?= esc($prefill['base_text'] ?? '') ?></textarea>
          <div class="form-text mt-2">
            Bu açıklama seçtiğin platformlara göre kullanılır. (İstersen YouTube için ayrıca başlık belirleyebilirsin.)
          </div>

          <div class="mini-tip mt-3">
            <i class="bi bi-lightbulb me-1"></i>
            İpucu: Kısa paragraf + 3–5 hashtag genelde daha okunaklı olur.
          </div>
        </div>
      </div>

      <!-- MEDIA -->
      <div class="p-card mb-3">
        <div class="p-card-head">
          <h5><i class="bi bi-image me-2"></i>Medya</h5>
          <span class="p-pill"><i class="bi bi-2-circle me-1"></i> Adım 2</span>
        </div>
        <div class="p-card-body">

          <?php if (!empty($prefill) && !empty($prefill['media_path'])): ?>
            <div class="border rounded-4 p-3 bg-light">
              <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                <div class="fw-bold">Şablondan oluşturulan medya</div>
                <span class="badge bg-white text-dark border">Hazır</span>
              </div>

              <?php if (($prefill['media_type'] ?? '') === 'image'): ?>
                <img src="<?= base_url($prefill['media_path']) ?>"
                     style="max-width: 100%; height: auto; border-radius: 14px; display:block;">
              <?php elseif (($prefill['media_type'] ?? '') === 'video'): ?>
                <video controls style="max-width:100%; border-radius:14px; display:block;">
                  <source src="<?= base_url($prefill['media_path']) ?>">
                </video>
              <?php else: ?>
                <div class="text-danger small">Medya tipi bulunamadı.</div>
              <?php endif; ?>

              <div class="small text-muted mt-2">
                Bu içerik hazır. Yeni dosya yükleme alanı kapalı.
              </div>
            </div>
          <?php else: ?>
            <div class="media-drop">
              <div>
                <strong>Görsel / Video yükle</strong>
                <p class="media-hint">Instagram & Facebook için görsel/video, YouTube için video önerilir.</p>
              </div>
              <div class="text-end">
                <label class="btn btn-outline-primary mb-0">
                  <i class="bi bi-upload me-1"></i> Dosya Seç
                  <input type="file" name="media" class="d-none" id="mediaInput" accept="image/*,video/*">
                </label>
              </div>
            </div>

            <div class="form-text mt-2" id="mediaInfo">
              Seçim yaptığında ön izleme otomatik güncellenir.
            </div>
          <?php endif; ?>

          <div class="mini-tip mt-3" id="tiktokHint" style="display:none;">
            <i class="bi bi-camera-video me-1"></i>
            TikTok için <b>video</b> seçmelisin. Görsel seçilirse TikTok ön izleme boş görünebilir.
          </div>
        </div>
      </div>

      <!-- YOUTUBE SETTINGS -->
      <div class="p-card mb-3" id="ytSettings" style="display:none;">
        <div class="p-card-head">
          <h5><i class="bi bi-youtube me-2"></i>YouTube Ayarları</h5>
          <span class="p-pill"><i class="bi bi-3-circle me-1"></i> Opsiyonel</span>
        </div>
        <div class="p-card-body">
          <div class="mb-3">
            <label class="form-label">YouTube Başlık <span class="text-danger">*</span></label>
            <input type="text" name="youtube_title" class="form-control" placeholder="YouTube video başlığı">
            <div class="form-text">YouTube seçiliyse başlık girmeni isteyeceğiz.</div>
          </div>

          <div class="mb-0">
            <label class="form-label">Gizlilik</label>
            <select name="youtube_privacy" class="form-select">
              <option value="public" selected>Herkese Açık</option>
              <option value="unlisted">Liste Dışı</option>
              <option value="private">Özel</option>
            </select>
          </div>
        </div>
      </div>

      <!-- PREVIEW -->
      <div class="p-card" id="previewCard" style="display:none;">
        <div class="p-card-head">
          <h5><i class="bi bi-eye me-2"></i>Ön İzleme</h5>
          <span class="p-pill"><i class="bi bi-stars me-1"></i> Canlı</span>
        </div>
        <div class="p-card-body">
          <p class="preview-note mb-3">
            Seçtiğin platformlara göre gönderinin yaklaşık görünümünü burada görebilirsin.
            <span class="d-block mt-1">Not: Platformlarda küçük farklılıklar olabilir.</span>
          </p>
          <div id="previewWrap" class="vstack gap-3"></div>
        </div>
      </div>
    </div>

    <!-- RIGHT -->
    <div class="col-lg-5">
      <div class="planner-sticky">
        <!-- ACCOUNTS -->
        <div class="p-card mb-3">
          <div class="p-card-head">
            <h5><i class="bi bi-people me-2"></i>Hedef Hesaplar</h5>
            <span class="p-pill"><i class="bi bi-check2-square me-1"></i> Seç</span>
          </div>
          <div class="p-card-body">
            <?php if (empty($accounts)): ?>
              <div class="alert alert-warning mb-0">
                Henüz sosyal hesap eklenmemiş.
                <a href="<?= site_url('panel/social-accounts') ?>">Sosyal Hesaplar</a> bölümünden ekleyebilirsin.
              </div>
            <?php else: ?>
              <div class="acc-list">
                <?php foreach ($accounts as $a): ?>
                  <?php
                    $plat = strtolower((string)$a['platform']);
                    $platUp = strtoupper((string)$a['platform']);

                    // kullanıcıya “ID” göstermiyoruz:
                    $display = '';
                    if (!empty($a['username'])) $display = '@' . $a['username'];
                    elseif (!empty($a['name'])) $display = $a['name'];
                    else $display = 'Bağlı hesap';
                  ?>
                  <label class="acc-item">
                    <div class="acc-left">
                      <span class="acc-plat <?= esc($plat) ?>"><?= esc($platUp) ?></span>
                      <div class="acc-meta">
                        <div class="acc-name"><?= esc($display) ?></div>
                        <div class="acc-sub">Bu içerik bu hesaba planlanır</div>
                      </div>
                    </div>

                    <input
                      class="form-check-input account-check"
                      type="checkbox"
                      name="account_ids[]"
                      value="<?= (int)$a['id'] ?>"
                      data-platform="<?= esc($plat) ?>"
                    >
                  </label>
                <?php endforeach; ?>
              </div>

              <div class="form-text mt-2">
                Birden fazla hesap seçersen aynı içerik seçilen tüm hesaplarda planlanır.
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- SCHEDULING -->
        <div class="p-card">
          <div class="p-card-head">
            <h5><i class="bi bi-clock-history me-2"></i>Zamanlama</h5>
            <span class="p-pill"><i class="bi bi-calendar-check me-1"></i> Planla</span>
          </div>
          <div class="p-card-body">
            <div class="mb-3">
              <label class="form-label">Instagram Paylaşım Tipi</label>
              <select name="post_type" class="form-select" required>
                <option value="auto" selected>Otomatik (video → reels, görsel → post)</option>
                <option value="post">Gönderi (Post)</option>
                <option value="reels">Reels</option>
                <option value="story">Hikâye (Story)</option>
              </select>
              <div class="form-text">Otomatik seçeneği, çoğu içerik için en pratik yöntemdir.</div>
            </div>

            <div class="mb-3">
              <label class="form-label">Tarih & Saat</label>
              <input type="datetime-local" name="schedule_at" class="form-control" required>
              <div class="form-text">Dilersen takvimden de sürükleyerek tarihi değiştirebilirsin.</div>
            </div>

            <button type="submit" class="btn btn-primary w-100">
              <i class="bi bi-send me-1"></i> Planla
            </button>

            <div class="mini-tip mt-3">
              <i class="bi bi-shield-check me-1"></i>
              Planlanan gönderiler “Paylaşımlar” bölümünde durumuyla birlikte görünür.
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- hidden inputs for prefill media (already in your JS below) -->
    <?php if (!empty($prefill['id'])): ?>
      <!-- no-op -->
    <?php endif; ?>
  </form>
</div>

<script>
(function(){
  const checks  = Array.from(document.querySelectorAll('.account-check'));
  const ytBox   = document.getElementById('ytSettings');

  const previewCard = document.getElementById('previewCard');
  const previewWrap = document.getElementById('previewWrap');

  const inputTitle     = document.querySelector('input[name="title"]');
  const textareaBase   = document.querySelector('textarea[name="base_text"]');

  // Bu view'de file input id değişti (dropzone görünümü için):
  const inputMedia     = document.getElementById('mediaInput') || document.querySelector('input[name="media"]');

  const selectPostType = document.querySelector('select[name="post_type"]');

  const inputYtTitle   = document.querySelector('input[name="youtube_title"]');
  const selectYtPriv   = document.querySelector('select[name="youtube_privacy"]');

  const captionCount = document.getElementById('captionCount');
  const tiktokHint = document.getElementById('tiktokHint');

  // Prefill medya varsa (şablondan geldiyse)
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

  function updateCaptionCount(){
    const n = (textareaBase?.value || '').length;
    if (captionCount) captionCount.textContent = n + ' karakter';
  }

  function getSelectedPlatforms(){
    const selected = checks.filter(ch => ch.checked);
    const map = {}; // platform => [{id,label}]
    selected.forEach(ch => {
      const plat = (ch.dataset.platform || '').toLowerCase();
      const label = (ch.closest('label')?.querySelector('.acc-name')?.innerText || '').trim();
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

  function phoneMediaHtml(media, ratioClass, overlayHtml){
    const inner = (!media.url || !media.type)
      ? `<div class="text-muted small p-3">Medya seçilmedi.</div>`
      : (media.type === 'image')
        ? `<img src="${escapeHtml(media.url)}" alt="">`
        : `<video controls muted playsinline><source src="${escapeHtml(media.url)}"></video>`;

    return `
      <div class="pv-phone">
        <div class="pv-screen ${ratioClass}">
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

  function truncate(s, n){
    s = (s || '').trim();
    if (!s) return '';
    return s.length > n ? s.slice(0, n) + '…' : s;
  }

  function render(){
    updateCaptionCount();

    // YouTube ayarlarını aç/kapa
    const hasYT = checks.some(ch => ch.checked && (ch.dataset.platform === 'youtube'));
    if (ytBox) ytBox.style.display = hasYT ? 'block' : 'none';

    const platforms = getSelectedPlatforms();
    const keys = Object.keys(platforms);

    if (!previewCard || !previewWrap) return;
    if (keys.length === 0) {
      previewCard.style.display = 'none';
      previewWrap.innerHTML = '';
      if (tiktokHint) tiktokHint.style.display = 'none';
      return;
    }
    previewCard.style.display = 'block';

    const title = (inputTitle?.value || '').trim();
    const caption = (textareaBase?.value || '').trim();
    const postTypeRaw = (selectPostType?.value || 'auto');
    const postType = postTypeRaw.toUpperCase();

    const ytTitle = (inputYtTitle?.value || '').trim();
    const ytPriv  = (selectYtPriv?.value || 'public').trim();

    const media = getMediaSource();

    // TikTok seçiliyse video öner
    const hasTiktok = !!platforms.tiktok?.length;
    if (tiktokHint) {
      tiktokHint.style.display = (hasTiktok && media.type === 'image') ? 'block' : 'none';
    }

    let html = '';

    // Instagram
    if (platforms.instagram?.length) {
      const accounts = platforms.instagram.map(a => a.label).join(' • ');

      const ratio = (postTypeRaw === 'story') ? 'pv-ratio-9x16'
                  : (media.type === 'video') ? 'pv-ratio-9x16'
                  : 'pv-ratio-4x5';

      const badges = `
        <span class="pv-badge">Instagram</span>
        <span class="pv-badge">${escapeHtml(postType)}</span>
      `;

      const igOverlay = `
        <div class="pv-ig-top">
          <span class="pv-ig-pill">Ön İzleme</span>
          <span class="pv-ig-pill">${escapeHtml(postType)}</span>
        </div>
      `;

      const body = `
        <div class="pv-row">
          <div style="flex:0 0 auto;">
            ${phoneMediaHtml(media, ratio, igOverlay)}
            <div class="mt-2">${buildCaptionBox('Açıklama', caption)}</div>
          </div>
        </div>
      `;

      html += buildPlatformCard('Instagram', accounts, badges, body);
    }

    // Facebook
    if (platforms.facebook?.length) {
      const accounts = platforms.facebook.map(a => a.label).join(' • ');
      const badges = `<span class="pv-badge">Facebook</span>`;

      const mediaInner = (!media.url || !media.type)
        ? `<div class="text-muted small p-3">Medya seçilmedi.</div>`
        : (media.type === 'image')
          ? `<img src="${escapeHtml(media.url)}" alt="">`
          : `<video controls muted playsinline><source src="${escapeHtml(media.url)}"></video>`;

      const body = `
        <div class="pv-feed">
          <div class="pv-feed-head">
            <div class="pv-avatar"></div>
            <div>
              <div class="pv-feed-name">Gönderi Ön İzleme</div>
              <div class="pv-feed-sub">Herkese açık</div>
            </div>
          </div>
          <div class="pv-screen pv-ratio-4x5" style="border-radius:0;">
            <div class="pv-media">${mediaInner}</div>
          </div>
          <div class="pv-feed-body">${escapeHtml(caption || '—')}</div>
        </div>
      `;

      html += buildPlatformCard('Facebook', accounts, badges, body);
    }

    // TikTok
    if (platforms.tiktok?.length) {
      const accounts = platforms.tiktok.map(a => a.label).join(' • ');
      const badges = `
        <span class="pv-badge">TikTok</span>
        <span class="pv-badge">Video</span>
      `;

      const ttOverlay = `
        <div class="pv-tt-left">
          <div class="pv-tt-user">@sosyalmedyaplanlama</div>
          <div class="pv-tt-caption">${escapeHtml(truncate(caption || '—', 140))}</div>
        </div>
        <div class="pv-tt-right">
          <div class="pv-tt-avatar"></div>
          <div class="pv-tt-action pv-tt-like"></div>
          <div class="pv-tt-action pv-tt-comment"></div>
          <div class="pv-tt-action pv-tt-share"></div>
        </div>
      `;

      const ttMedia = (media.type === 'video') ? media : { url: null, type: null };

      const body = `
        <div class="pv-row">
          <div style="flex:0 0 auto;">
            ${phoneMediaHtml(ttMedia, 'pv-ratio-9x16', ttOverlay)}
            <div class="mt-2">${buildCaptionBox('Açıklama', caption)}</div>
          </div>
        </div>
      `;

      html += buildPlatformCard('TikTok', accounts, badges, body);
    }

    // YouTube
    if (platforms.youtube?.length) {
      const accounts = platforms.youtube.map(a => a.label).join(' • ');
      const badges = `
        <span class="pv-badge">YouTube</span>
        <span class="pv-badge">${escapeHtml(ytPriv)}</span>
      `;

      const ytFinalTitle = ytTitle || title || '—';

      const ytInner = (!media.url || !media.type)
        ? `<div class="text-muted small p-3">Medya seçilmedi.</div>`
        : (media.type === 'video')
          ? `<video controls muted playsinline><source src="${escapeHtml(media.url)}"></video>`
          : `<img src="${escapeHtml(media.url)}" alt="">`;

      const body = `
        <div class="pv-yt-wrap">
          <div class="pv-screen pv-ratio-16x9" style="background:#111; border-radius:16px;">
            <div class="pv-media">${ytInner}</div>
          </div>

          <div class="pv-yt-title">${escapeHtml(ytFinalTitle)}</div>
          <div class="pv-yt-meta">Gizlilik: ${escapeHtml(ytPriv)}</div>

          <div class="text-muted small mt-2">Açıklama</div>
          <div style="white-space:pre-wrap; font-size:12px;">${escapeHtml(caption || '—')}</div>
        </div>
      `;

      html += buildPlatformCard('YouTube', accounts, badges, body);
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
