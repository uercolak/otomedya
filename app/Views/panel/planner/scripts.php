<script>
(function(){
  const checks  = Array.from(document.querySelectorAll('.account-check'));

  const platformCard = document.getElementById('platformSettingsCard');
  const previewCard  = document.getElementById('previewCard');
  const previewWrap  = document.getElementById('previewWrap');

  const inputTitle   = document.querySelector('input[name="title"]');
  const textareaBase = document.querySelector('textarea[name="base_text"]');
  const inputMedia   = document.querySelector('input[name="media"]');

  const igPostType = document.getElementById('igPostType');
  const fbPostType = document.getElementById('fbPostType');
  const ttPrivacy  = document.getElementById('ttPrivacy');

  const ytTitle    = document.getElementById('ytTitle');
  const ytPrivacy  = document.getElementById('ytPrivacy');
  const ytKids     = document.getElementById('ytKids');

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

  function getSelectedPlatforms(){
    const selected = checks.filter(ch => ch.checked);
    const map = {};
    selected.forEach(ch => {
      const plat = (ch.dataset.platform || '').toLowerCase();
      const label = (ch.closest('label')?.querySelector('span')?.innerText || '').trim();
      if (!map[plat]) map[plat] = [];
      map[plat].push({ id: ch.value, label });
    });
    return map;
  }

  function getMediaSource(){
    if (PREFILL_MEDIA_URL && PREFILL_MEDIA_TYPE) {
      return { url: PREFILL_MEDIA_URL, type: PREFILL_MEDIA_TYPE };
    }

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

  function togglePlatformBoxes(platforms){
    const keys = Object.keys(platforms);

    // Platform Settings kartı
    if (platformCard) platformCard.style.display = keys.length ? 'block' : 'none';

    // Accordion itemları
    document.querySelectorAll('[data-plat-box]').forEach(el => {
      const p = el.getAttribute('data-plat-box');
      el.style.display = (platforms[p] && platforms[p].length) ? 'block' : 'none';
    });
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

  function render(){
    const platforms = getSelectedPlatforms();
    const keys = Object.keys(platforms);

    togglePlatformBoxes(platforms);

    if (!previewCard || !previewWrap) return;
    if (keys.length === 0) {
      previewCard.style.display = 'none';
      previewWrap.innerHTML = '';
      return;
    }
    previewCard.style.display = 'block';

    const title = (inputTitle?.value || '').trim();
    const caption = (textareaBase?.value || '').trim();

    const media = getMediaSource();

    const igTypeRaw = (igPostType?.value || 'auto');
    const fbTypeRaw = (fbPostType?.value || 'post');
    const ttPrivRaw = (ttPrivacy?.value || 'public');

    const ytTitleVal = (ytTitle?.value || '').trim();
    const ytPrivVal  = (ytPrivacy?.value || 'public');
    const ytKidsVal  = (ytKids?.value || 'no');

    let html = '';

    // Instagram preview
    if (platforms.instagram?.length) {
      const accounts = platforms.instagram.map(a => a.label).join(' • ');
      const igType = igTypeRaw.toUpperCase();

      const ratio = (igTypeRaw === 'story') ? 'pv-ratio-9x16'
                : (media.type === 'video') ? 'pv-ratio-9x16'
                : 'pv-ratio-4x5';

      const badges = `
        <span class="pv-badge">Instagram</span>
        <span class="pv-badge">${escapeHtml(igType)}</span>
      `;

      const igOverlay = `
        <div class="pv-ig-top">
          <span class="pv-ig-pill">@sosyalmedyaplanlama</span>
          <span class="pv-ig-pill">${escapeHtml(igType)}</span>
        </div>
      `;

      const body = `
        <div class="pv-row">
          <div style="flex:0 0 auto;">
            ${phoneMediaHtml(media, ratio, igOverlay)}
            <div class="mt-2">${buildCaptionBox('Caption', caption)}</div>
          </div>
        </div>
      `;

      html += buildPlatformCard('Instagram Ön İzleme', accounts, badges, body);
    }

    // Facebook preview
    if (platforms.facebook?.length) {
      const accounts = platforms.facebook.map(a => a.label).join(' • ');
      const badges = `
        <span class="pv-badge">Facebook</span>
        <span class="pv-badge">${escapeHtml(fbTypeRaw.toUpperCase())}</span>
      `;

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
              <div class="pv-feed-name">Sosyal Medya Planlama</div>
              <div class="pv-feed-sub">Az önce • ${escapeHtml('Herkese açık')}</div>
            </div>
          </div>
          <div class="pv-screen pv-ratio-4x5" style="border-radius:0;">
            <div class="pv-media">${mediaInner}</div>
          </div>
          <div class="pv-feed-body">${escapeHtml(caption || '—')}</div>
        </div>
      `;

      html += buildPlatformCard('Facebook Ön İzleme', accounts, badges, body);
    }

    // TikTok preview
    if (platforms.tiktok?.length) {
      const accounts = platforms.tiktok.map(a => a.label).join(' • ');
      const badges = `
        <span class="pv-badge">TikTok</span>
        <span class="pv-badge">${escapeHtml(ttPrivRaw.toUpperCase())}</span>
        <span class="pv-badge">Video</span>
      `;

      const ttOverlay = `
        <div class="pv-tt-left">
          <div class="pv-tt-user">@sosyalmedyaplanlama</div>
          <div class="pv-tt-caption">${escapeHtml(caption || '—')}</div>
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
            <div class="mt-2">
              ${buildCaptionBox('Caption - Açıklama', caption)}
            </div>
          </div>
        </div>
      `;

      html += buildPlatformCard('TikTok Ön İzleme', accounts, badges, body);
    }

    // YouTube preview
    if (platforms.youtube?.length) {
      const accounts = platforms.youtube.map(a => a.label).join(' • ');
      const badges = `
        <span class="pv-badge">YouTube</span>
        <span class="pv-badge">Gizlilik: ${escapeHtml(ytPrivVal)}</span>
        <span class="pv-badge">Kids: ${escapeHtml(ytKidsVal)}</span>
      `;

      const ytFinalTitle = ytTitleVal || title || '—';

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
          <div class="pv-yt-meta">@sosyalmedyaplanlama • ${escapeHtml(ytPrivVal)}</div>

          <div class="text-muted small mt-2">Açıklama</div>
          <div style="white-space:pre-wrap; font-size:12px;">${escapeHtml(caption || '—')}</div>
        </div>
      `;

      html += buildPlatformCard('YouTube Ön İzleme', accounts, badges, body);
    }

    previewWrap.innerHTML = html;
  }

  checks.forEach(ch => ch.addEventListener('change', render));
  inputTitle?.addEventListener('input', render);
  textareaBase?.addEventListener('input', render);
  inputMedia?.addEventListener('change', render);

  igPostType?.addEventListener('change', render);
  fbPostType?.addEventListener('change', render);
  ttPrivacy?.addEventListener('change', render);

  ytTitle?.addEventListener('input', render);
  ytPrivacy?.addEventListener('change', render);
  ytKids?.addEventListener('change', render);

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
