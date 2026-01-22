<?= $this->extend('layouts/panel') ?>
<?= $this->section('content') ?>

<!-- Google Fonts (edit ekranına özel; istersen layout'a taşıyabilirsin) -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Roboto:wght@300;400;500;700&family=Montserrat:wght@300;400;500;700&family=Poppins:wght@300;400;500;600;700&family=Oswald:wght@300;400;500;700&family=Playfair+Display:wght@400;500;600;700&family=Nunito:wght@300;400;500;700&family=Raleway:wght@300;400;500;700&display=swap" rel="stylesheet">

<style>
  /* Basit, bağımsız snackbar/toast (Bootstrap JS yoksa bile çalışır) */
  .om-toast-wrap{
    position:fixed; top:16px; right:16px; z-index:2000;
    display:flex; flex-direction:column; gap:10px;
    pointer-events:none;
  }
  .om-toast{
    pointer-events:auto;
    min-width:260px; max-width:360px;
    border-radius:14px;
    padding:12px 14px;
    box-shadow:0 10px 30px rgba(0,0,0,.12);
    background:#111827; color:#fff;
    display:flex; align-items:flex-start; gap:10px;
    transform:translateY(-8px);
    opacity:0;
    transition:all .18s ease;
    border:1px solid rgba(255,255,255,.10);
  }
  .om-toast.show{ transform:translateY(0); opacity:1; }
  .om-toast .t-title{ font-weight:600; font-size:13px; line-height:1.2; margin-bottom:2px; }
  .om-toast .t-msg{ font-size:13px; opacity:.92; line-height:1.35; }
  .om-toast .t-close{
    margin-left:auto; border:none; background:transparent; color:#fff;
    opacity:.8; cursor:pointer; padding:0 4px; line-height:1;
    font-size:18px;
  }
  .om-toast.success{ background:#0b1220; border-color:rgba(34,197,94,.35); }
  .om-toast.danger { background:#1a0b0b; border-color:rgba(239,68,68,.35); }
  .om-toast.warning{ background:#1a1408; border-color:rgba(245,158,11,.35); }
  .om-toast.info   { background:#0b1620; border-color:rgba(59,130,246,.35); }

  /* Sağ panel kutuları biraz daha modern */
  #textStyleBox, #cropBox { background:rgba(255,255,255,.7); }

   .om-autoplan-overlay{
    position: fixed;
    inset: 0;
    z-index: 3000;
    background: rgba(15, 23, 42, .55);
    backdrop-filter: blur(6px);
    display: none;
    align-items: center;
    justify-content: center;
    padding: 24px;
  }
  .om-autoplan-overlay.show{ display:flex; }

  .om-autoplan-box{
    width: min(420px, 92vw);
    background: rgba(255,255,255,.92);
    border: 1px solid rgba(0,0,0,.08);
    border-radius: 18px;
    box-shadow: 0 18px 60px rgba(0,0,0,.20);
    padding: 18px 18px 16px 18px;
    text-align: center;
  }
  .om-autoplan-title{
    font-weight: 700;
    letter-spacing: -.2px;
    margin: 10px 0 6px 0;
  }
  .om-autoplan-sub{
    color: rgba(0,0,0,.62);
    font-size: 13px;
    line-height: 1.35;
    margin: 0;
  }

  .om-spinner{
    width: 44px;
    height: 44px;
    border-radius: 50%;
    border: 4px solid rgba(124,58,237,.20);
    border-top-color: rgba(124,58,237,1);
    margin: 0 auto;
    animation: omspin .9s linear infinite;
  }
  @keyframes omspin { to { transform: rotate(360deg); } }
</style>

<?php
  $tplId = (int)($tpl['id'] ?? 0);
  $w = (int)($tpl['width'] ?? 1080);
  $h = (int)($tpl['height'] ?? 1080);

  $bgUrl = !empty($tpl['base_media_id'])
    ? site_url('media/' . (int)$tpl['base_media_id'])
    : (!empty($tpl['file_path']) ? base_url($tpl['file_path']) : '');

  $formatKey   = (string)($tpl['format_key'] ?? '');
  $savedState  = (string)($design['state_json'] ?? '');
  $designIdInt = (int)($design['id'] ?? 0);
?>

<div class="container-fluid py-3">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <div>
      <h3 class="mb-0">Şablonu Düzenle</h3>
      <div class="text-muted"><?= esc($tpl['name'] ?? '') ?> • <?= esc($formatKey) ?> • <?= $w ?>×<?= $h ?></div>
    </div>

    <div class="d-flex gap-2">
      <a href="<?= site_url('panel/templates') ?>" class="btn btn-outline-secondary">Geri</a>
      <button id="btnSave" class="btn btn-outline-primary">Kaydet</button>
      <button id="btnExport" class="btn btn-primary">Planla</button>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-lg-9">
      <div class="card p-3">
        <div id="canvasWrap" style="max-width:100%; overflow:auto;">
          <canvas id="c"
                  width="<?= $w ?>"
                  height="<?= $h ?>"
                  style="border-radius:14px; border:1px solid rgba(0,0,0,.08);"></canvas>
        </div>
      </div>
    </div>

    <div class="col-lg-3">
      <div class="card p-3">
        <div class="fw-semibold mb-2">Araçlar</div>

        <button id="btnAddText" class="btn btn-outline-secondary w-100 mb-2">
          <i class="bi bi-type me-1"></i> Yazı ekle
        </button>

        <label class="btn btn-outline-secondary w-100 mb-2">
          <i class="bi bi-image me-1"></i> Logo ekle
          <input id="logoFile" type="file" accept="image/*" hidden>
        </label>

        <button id="btnDelete" class="btn btn-outline-danger w-100 mb-2">
          <i class="bi bi-trash me-1"></i> Seçileni sil
        </button>

        <div class="d-flex gap-2 mb-2">
          <button id="btnCrop" class="btn btn-outline-secondary w-100" type="button" disabled>
            <i class="bi bi-crop me-1"></i> Kırp
          </button>
        </div>

        <div id="cropBox" class="border rounded p-2 mb-3" style="display:none;">
          <div class="small text-muted mb-2">Kırpma modu</div>
          <div class="d-flex gap-2">
            <button id="btnCropApply" class="btn btn-sm btn-primary w-100" type="button">Uygula</button>
            <button id="btnCropCancel" class="btn btn-sm btn-outline-secondary w-100" type="button">İptal</button>
          </div>
          <div class="small text-muted mt-2">Dikdörtgeni sürükleyip boyutlandır.</div>
        </div>

        <div id="textStyleBox" class="border rounded p-2 mb-3" style="display:none;">
          <div class="small text-muted mb-2">Yazı Ayarları</div>

          <label class="form-label small mb-1">Font</label>
          <select id="txtFont" class="form-select form-select-sm mb-2">
            <option value="Inter" selected>Inter (önerilen)</option>
            <option value="Poppins">Poppins</option>
            <option value="Montserrat">Montserrat</option>
            <option value="Roboto">Roboto</option>
            <option value="Oswald">Oswald</option>
            <option value="Playfair Display">Playfair Display</option>
            <option value="Nunito">Nunito</option>
            <option value="Raleway">Raleway</option>
            <option value="Arial">Arial (system)</option>
            <option value="Times New Roman">Times New Roman (system)</option>
          </select>

          <label class="form-label small mb-1">Boyut</label>
          <input id="txtSize" type="number" min="8" max="200" step="1"
                 class="form-control form-control-sm mb-2" value="48">

          <label class="form-label small mb-1">Renk</label>
          <input id="txtColor" type="color" class="form-control form-control-sm mb-2"
                 value="#111111" style="height:36px;">

          <div class="d-flex gap-2 mb-2">
            <button id="txtBold" type="button" class="btn btn-sm btn-outline-secondary w-100">B</button>
            <button id="txtItalic" type="button" class="btn btn-sm btn-outline-secondary w-100"><i>I</i></button>
          </div>

          <div class="d-flex gap-2">
            <button data-align="left"   type="button" class="btn btn-sm btn-outline-secondary w-100">Sol</button>
            <button data-align="center" type="button" class="btn btn-sm btn-outline-secondary w-100">Orta</button>
            <button data-align="right"  type="button" class="btn btn-sm btn-outline-secondary w-100">Sağ</button>
          </div>
        </div>

        <hr>

        <label class="form-label small text-muted">Instagram Paylaşım Tipi (meta_json)</label>
        <select id="postType" class="form-select mb-2">
          <option value="post" selected>Post</option>
          <option value="story">Story</option>
          <option value="reels">Reels (V1 image için planlama; video değil)</option>
        </select>

        <div class="small text-muted">
          V1’de export PNG üretir ve Planner’a içerik olarak taşır.
        </div>
      </div>
    </div>
  </div>
</div>

<div id="omAutoplanOverlay" class="om-autoplan-overlay" aria-live="polite" aria-busy="true">
  <div class="om-autoplan-box">
    <div class="om-spinner"></div>
    <div class="om-autoplan-title">Yönlendiriliyorsunuz…</div>
    <p class="om-autoplan-sub">
      Şablon kaydediliyor ve planlama ekranı hazırlanıyor.
    </p>
  </div>
</div>

<!-- Toast area (custom) -->
<div id="omToastWrap" class="om-toast-wrap" aria-live="polite" aria-atomic="true"></div>

<script src="https://cdn.jsdelivr.net/npm/fabric@5.3.0/dist/fabric.min.js"></script>

<script>
(function(){
  const W = <?= (int)$w ?>;
  const H = <?= (int)$h ?>;
  const BG = <?= json_encode((string)$bgUrl) ?>;
  const SAVED = <?= json_encode((string)$savedState) ?>;
  const AUTOPLAN = <?= json_encode((bool)($autoplan ?? false)) ?>;

  const CSRF = {
    name: <?= json_encode(csrf_token()) ?>,
    hash: <?= json_encode(csrf_hash()) ?>
  };

  const saveUrl   = <?= json_encode(site_url('panel/templates/'.$tplId.'/save')) ?>;
  const exportUrl = <?= json_encode(site_url('panel/templates/'.$tplId.'/export')) ?>;

  const toastWrap = document.getElementById('omToastWrap');

  function notify(message, type='info', title=null, ttl=2400){
    try{
      if(!toastWrap) return;
      const t = document.createElement('div');
      t.className = 'om-toast ' + (type || 'info');
      const safeTitle = title || (type==='success'?'Başarılı':type==='danger'?'Hata':type==='warning'?'Uyarı':'Bilgi');

      t.innerHTML = `
        <div>
          <div class="t-title">${escapeHtml(safeTitle)}</div>
          <div class="t-msg">${escapeHtml(String(message || ''))}</div>
        </div>
        <button class="t-close" type="button" aria-label="Kapat">×</button>
      `;

      toastWrap.appendChild(t);

      const close = () => {
        t.classList.remove('show');
        setTimeout(()=>{ try{ t.remove(); }catch(e){} }, 180);
      };

      t.querySelector('.t-close')?.addEventListener('click', close);

      requestAnimationFrame(()=> t.classList.add('show'));
      setTimeout(close, Math.max(800, ttl|0));
    }catch(e){
      console.log('notify error', e);
    }
  }

  function escapeHtml(str){
    return String(str)
      .replaceAll('&','&amp;')
      .replaceAll('<','&lt;')
      .replaceAll('>','&gt;')
      .replaceAll('"','&quot;')
      .replaceAll("'","&#039;");
  }

  // ---------- Fabric init ----------
  const canvas = new fabric.Canvas('c', {
    preserveObjectStacking: true,
    selection: true
  });

  fabric.Object.prototype.transparentCorners = false;
  fabric.Object.prototype.cornerStyle = 'circle';
  fabric.Object.prototype.cornerSize = 10;      
  fabric.Object.prototype.cornerColor = '#ffffff';
  fabric.Object.prototype.cornerStrokeColor = '#7c3aed';
  fabric.Object.prototype.borderColor = '#7c3aed';
  fabric.Object.prototype.borderDashArray = [6, 4];
  fabric.Object.prototype.padding = 4;

  function tuneTextControls(obj){
    if(!obj) return;
    obj.set({
      cornerSize: 9,
      padding: 2
    });
  }

  function setBusy(on){
    const b1 = document.getElementById('btnSave');
    const b2 = document.getElementById('btnExport');
    if (b1) b1.disabled = !!on;
    if (b2) b2.disabled = !!on;
  }

    const overlayEl = document.getElementById('omAutoplanOverlay');
    function showAutoplanOverlay(show){
    if(!overlayEl) return;
    overlayEl.classList.toggle('show', !!show);
    }

  function fitToWrap(){
    const wrap = document.getElementById('canvasWrap');
    if (!wrap) return;

    const maxW = Math.max(320, wrap.clientWidth - 6);
    const scale = Math.min(1, maxW / W);

    canvas.setZoom(scale);
    canvas.setWidth(Math.round(W * scale));
    canvas.setHeight(Math.round(H * scale));
    canvas.requestRenderAll();
  }

  async function setBackground(url){
    return new Promise((resolve) => {
      if (!url) return resolve(false);

      fabric.util.loadImage(url, (imgEl) => {
        if (!imgEl) return resolve(false);

        const img = new fabric.Image(imgEl, {
          selectable: false,
          evented: false
        });

        img.scaleToWidth(W);
        img.scaleToHeight(H);

        canvas.setBackgroundImage(img, () => {
          canvas.requestRenderAll();
          resolve(true);
        });
      }, null, 'anonymous');
    });
  }

  function getStateJson(){
    const json = canvas.toJSON(['selectable','evented']);
    delete json.backgroundImage; // DB şişmesin
    return JSON.stringify(json);
  }

  async function postForm(url, data){
    const form = new URLSearchParams();
    form.append(CSRF.name, CSRF.hash);
    Object.keys(data).forEach(k => form.append(k, data[k]));

    const res = await fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
      body: form.toString()
    });

    const json = await res.json().catch(() => null);

    // ✅ CSRF hash’i her response’ta güncelle
    if (json && json.csrfHash) {
      CSRF.hash = String(json.csrfHash);
    }

    if (!res.ok || !json || json.ok !== true) {
      throw new Error((json && json.message) ? json.message : 'İşlem başarısız');
    }

    return json;
  }


  const fontCache = new Set();

  async function ensureFontLoaded(fontFamily){
    const ff = String(fontFamily || '').trim();
    if(!ff || ff.toLowerCase()==='arial' || ff.toLowerCase()==='times new roman'){
      return true; // system font
    }
    if(fontCache.has(ff)) return true;

    if(!document.fonts || !document.fonts.load){
      // eski browser: en azından devam et
      fontCache.add(ff);
      return true;
    }

    try{
      // birkaç ağırlığı dene
      await document.fonts.load(`400 16px "${ff}"`);
      await document.fonts.load(`600 16px "${ff}"`);
      fontCache.add(ff);
      return true;
    }catch(e){
      console.warn('Font load failed', ff, e);
      return false;
    }
  }

  // ---------- UI: Add Text / Logo / Delete ----------
  document.getElementById('btnAddText').addEventListener('click', async () => {
    // Varsayılanı Inter yapalım
    await ensureFontLoaded('Inter');

    const t = new fabric.Textbox('Metin', {
      left: 80, top: 80,
      fontSize: 48,
      fill: '#111111',
      fontFamily: 'Inter',
      width: Math.min(820, W - 160)
    });

    tuneTextControls(t);
    canvas.add(t);
    canvas.setActiveObject(t);
    canvas.requestRenderAll();
  });

  document.getElementById('logoFile').addEventListener('change', (e) => {
    const file = e.target.files && e.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = () => {
      fabric.Image.fromURL(reader.result, (img) => {
        img.set({ left: 80, top: 160 });
        img.scaleToWidth(Math.min(320, W/3));
        canvas.add(img);
        canvas.setActiveObject(img);
        canvas.requestRenderAll();
      }, { crossOrigin: 'anonymous' });
    };
    reader.readAsDataURL(file);
    e.target.value = '';
  });

  document.getElementById('btnDelete').addEventListener('click', () => {
    const obj = canvas.getActiveObject();
    if (!obj) return;
    canvas.remove(obj);
    canvas.requestRenderAll();
  });

  // ---------- Text Style Panel ----------
  const textStyleBox = document.getElementById('textStyleBox');
  const txtFont  = document.getElementById('txtFont');
  const txtSize  = document.getElementById('txtSize');
  const txtColor = document.getElementById('txtColor');
  const txtBold  = document.getElementById('txtBold');
  const txtItalic= document.getElementById('txtItalic');

  function isText(o){
    return o && (o.type === 'textbox' || o.type === 'text' || o.type === 'i-text');
  }
  function isImage(o){
    return o && o.type === 'image';
  }

  function syncTextPanel(o){
    if (!isText(o)) return;
    txtFont.value = o.fontFamily || 'Inter';
    txtSize.value = Math.round(o.fontSize || 48);
    txtColor.value = (o.fill && typeof o.fill === 'string') ? o.fill : '#111111';
  }

  function applyToActiveText(patch){
    const o = canvas.getActiveObject();
    if (!isText(o)) return;
    o.set(patch);
    tuneTextControls(o);
    o.setCoords();
    canvas.requestRenderAll();
  }

  txtFont.addEventListener('change', async () => {
    const ff = txtFont.value;
    const ok = await ensureFontLoaded(ff);
    if(!ok){
      notify('Font yüklenemedi, sistem fontuna düşebilir.', 'warning');
    }
    applyToActiveText({ fontFamily: ff });
  });

  txtSize.addEventListener('change', () => {
    const n = parseInt(txtSize.value || '48', 10);
    applyToActiveText({ fontSize: isFinite(n) ? n : 48 });
  });

  txtColor.addEventListener('input', () => applyToActiveText({ fill: txtColor.value }));

  txtBold.addEventListener('click', () => {
    const o = canvas.getActiveObject();
    if (!isText(o)) return;
    const next = (o.fontWeight === 'bold') ? 'normal' : 'bold';
    applyToActiveText({ fontWeight: next });
  });

  txtItalic.addEventListener('click', () => {
    const o = canvas.getActiveObject();
    if (!isText(o)) return;
    const next = (o.fontStyle === 'italic') ? 'normal' : 'italic';
    applyToActiveText({ fontStyle: next });
  });

  textStyleBox.querySelectorAll('button[data-align]').forEach(btn => {
    btn.addEventListener('click', () => {
      const align = btn.getAttribute('data-align');
      applyToActiveText({ textAlign: align });
    });
  });

  // ---------- Crop (clipPath) ----------
  const btnCrop = document.getElementById('btnCrop');
  const cropBox = document.getElementById('cropBox');
  const btnCropApply = document.getElementById('btnCropApply');
  const btnCropCancel= document.getElementById('btnCropCancel');

  let cropTarget = null;
  let cropRect   = null;

  function enterCropMode(img){
    cropTarget = img;
    cropBox.style.display = 'block';

    const rW = Math.min(460, img.getScaledWidth() * 0.8);
    const rH = Math.min(460, img.getScaledHeight() * 0.8);

    cropRect = new fabric.Rect({
      left: img.left + 18,
      top: img.top + 18,
      width: rW,
      height: rH,
      fill: 'rgba(255,255,255,0.06)',
      stroke: '#7c3aed',
      strokeWidth: 2,
      strokeDashArray: [6,4],
      selectable: true,
      hasRotatingPoint: false,
      objectCaching: false,
      cornerStyle: 'circle',
      cornerSize: 10,
      transparentCorners: false
    });

    canvas.add(cropRect);
    canvas.setActiveObject(cropRect);
    canvas.requestRenderAll();
  }

  function exitCropMode(removeRect = true){
    cropBox.style.display = 'none';
    if (removeRect && cropRect) canvas.remove(cropRect);
    cropRect = null;
    cropTarget = null;
    canvas.discardActiveObject();
    canvas.requestRenderAll();
  }

  function applyCrop(){
    if (!cropTarget || !cropRect) return;

    cropRect.setCoords();
    cropTarget.setCoords();

    const inv = fabric.util.invertTransform(cropTarget.calcTransformMatrix());
    const rectTL = new fabric.Point(cropRect.left, cropRect.top);
    const rectBR = new fabric.Point(cropRect.left + cropRect.getScaledWidth(), cropRect.top + cropRect.getScaledHeight());

    const localTL = fabric.util.transformPoint(rectTL, inv);
    const localBR = fabric.util.transformPoint(rectBR, inv);

    const clipW = Math.max(1, localBR.x - localTL.x);
    const clipH = Math.max(1, localBR.y - localTL.y);

    cropTarget.clipPath = new fabric.Rect({
      left: localTL.x,
      top: localTL.y,
      width: clipW,
      height: clipH,
      absolutePositioned: false
    });

    cropTarget.dirty = true;
    exitCropMode(true);
    notify('Kırpma uygulandı ✅', 'success');
  }

  btnCrop.addEventListener('click', () => {
    const o = canvas.getActiveObject();
    if (!isImage(o)) return;
    enterCropMode(o);
  });

  btnCropApply.addEventListener('click', applyCrop);
  btnCropCancel.addEventListener('click', () => exitCropMode(true));

  // ---------- Selection UI ----------
  canvas.on('selection:created', refreshSelectionUI);
  canvas.on('selection:updated', refreshSelectionUI);
  canvas.on('selection:cleared', refreshSelectionUI);

  function refreshSelectionUI(){
    const o = canvas.getActiveObject();
    btnCrop.disabled = !isImage(o);

    if (isText(o)) {
      textStyleBox.style.display = 'block';
      syncTextPanel(o);
      tuneTextControls(o);
    } else {
      textStyleBox.style.display = 'none';
    }

    if (cropRect && o !== cropRect) {
      exitCropMode(true);
    }
  }

  // ---------- SAVE / EXPORT ----------
  let lastDesignId = <?= (int)$designIdInt ?>;

  document.getElementById('btnSave').addEventListener('click', async (e) => {
    e.preventDefault();
    setBusy(true);
    try {
      const state = getStateJson();
      const json = await postForm(saveUrl, {
        canvas_width: W,
        canvas_height: H,
        state_json: state
      });
      if (json.design_id) lastDesignId = parseInt(json.design_id, 10) || lastDesignId;
      notify('Kaydedildi ✅', 'success');
    } catch(err) {
      notify(err.message || 'Kaydetme hatası', 'danger');
    } finally {
      setBusy(false);
    }
  });

  document.getElementById('btnExport').addEventListener('click', async (e) => {
    e.preventDefault();
    setBusy(true);
    try {
      if (!lastDesignId) {
        const state = getStateJson();
        const json = await postForm(saveUrl, {
          canvas_width: W,
          canvas_height: H,
          state_json: state
        });
        lastDesignId = parseInt(json.design_id, 10) || lastDesignId;
      }

      const postType = document.getElementById('postType').value || 'post';

      // export PNG (background dahil)
      const dataUrl = canvas.toDataURL({ format:'png', quality: 1 });

      const out = await postForm(exportUrl, {
        design_id: String(lastDesignId),
        png_data: dataUrl,
        post_type: postType
      });

      notify('Şablon aktarıldı ✅', 'success');

      if (out.redirect) {
        window.location.href = out.redirect;
      }
    } catch(err) {
      notify(err.message || 'Export hatası', 'danger');
    } finally {
      setBusy(false);
    }
  });

  // ---------- Init ----------
  async function init(){
    const warm = ['Inter','Poppins','Montserrat','Roboto','Oswald','Playfair Display','Nunito','Raleway'];
    try{
      for(const f of warm){ await ensureFontLoaded(f); }
    }catch(e){}

    const okBg = BG ? await setBackground(BG) : true;
    if (!okBg) notify('Arkaplan yüklenemedi (BG URL erişilemedi)', 'warning');

    if (SAVED) {
      try {
        const json = JSON.parse(SAVED);
        canvas.loadFromJSON(json, async () => {
          if (BG) await setBackground(BG);
          canvas.getObjects().forEach(o => { if(isText(o)) tuneTextControls(o); });
          fitToWrap();
          canvas.requestRenderAll();
        });
      } catch(e) {
        fitToWrap();
      }
    } else {
      fitToWrap();
    }
  }

  async function runAutoPlan(){
    try{
        setBusy(true);
        showAutoplanOverlay(true);

        // önce save garanti olsun
        if (!lastDesignId) {
        const state = getStateJson();
        const json = await postForm(saveUrl, {
            canvas_width: W,
            canvas_height: H,
            state_json: state
        });
        if (json.design_id) lastDesignId = parseInt(json.design_id, 10) || lastDesignId;
        }

        const postType = document.getElementById('postType').value || 'post';
        const dataUrl = canvas.toDataURL({ format:'png', quality: 1 });

        const out = await postForm(exportUrl, {
        design_id: String(lastDesignId),
        png_data: dataUrl,
        post_type: postType
        });

        if (out.redirect) window.location.href = out.redirect;

    } catch(e){
        showAutoplanOverlay(false);
        notify((e && e.message) ? e.message : 'Hızlı planlama hatası', 'danger');
    } finally {
        setBusy(false);
    }
    }


  let resizeT = null;
  window.addEventListener('resize', () => {
    clearTimeout(resizeT);
    resizeT = setTimeout(() => fitToWrap(), 120);
  });

    (async () => {
    await init();
    if (AUTOPLAN) {
        showAutoplanOverlay(true); 
        setTimeout(() => runAutoPlan(), 250);
    }
    })();
})();

</script>

<?= $this->endSection() ?>
