<?= $this->extend('layouts/panel') ?>
<?= $this->section('content') ?>

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
                  style="border-radius:12px; border:1px solid rgba(0,0,0,.08);"></canvas>
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
            <option value="Arial" selected>Arial</option>
            <option value="Roboto">Roboto</option>
            <option value="Montserrat">Montserrat</option>
            <option value="Poppins">Poppins</option>
            <option value="Times New Roman">Times New Roman</option>
          </select>

          <label class="form-label small mb-1">Boyut</label>
          <input id="txtSize" type="number" min="8" max="200" step="1" class="form-control form-control-sm mb-2" value="48">

          <label class="form-label small mb-1">Renk</label>
          <input id="txtColor" type="color" class="form-control form-control-sm mb-2" value="#111111" style="height:36px;">

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

<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1080;">
  <div id="appToast" class="toast align-items-center text-bg-dark border-0" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div id="appToastBody" class="toast-body">...</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Kapat"></button>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/fabric@5.3.0/dist/fabric.min.js"></script>

<!-- (İstersen Google Fonts da ekleyebilirsin; panel layout’ta tek sefer eklemek daha iyi)
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&family=Poppins:wght@400;700&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
-->

<script>
(function(){
  const W = <?= (int)$w ?>;
  const H = <?= (int)$h ?>;
  const BG = <?= json_encode((string)$bgUrl) ?>;
  const SAVED = <?= json_encode((string)$savedState) ?>;

  // ✅ CSRF state (auto refresh)
  const CSRF = {
    name: <?= json_encode(csrf_token()) ?>,
    hash: <?= json_encode(csrf_hash()) ?>
  };

  const saveUrl   = <?= json_encode(site_url('panel/templates/'.$tplId.'/save')) ?>;
  const exportUrl = <?= json_encode(site_url('panel/templates/'.$tplId.'/export')) ?>;

  const toastEl = document.getElementById('appToast');
  const toastBodyEl = document.getElementById('appToastBody');
  const toast = (toastEl && window.bootstrap) ? new bootstrap.Toast(toastEl, { delay: 2200 }) : null;

  function notify(message, type='dark'){
    if (!toastEl || !toast) { alert(message); return; }
    toastEl.className = 'toast align-items-center text-bg-' + type + ' border-0';
    toastBodyEl.textContent = message;
    toast.show();
  }

  const canvas = new fabric.Canvas('c', {
    preserveObjectStacking: true,
    selection: true
  });

  // ---------- Helpers ----------
  function setBusy(on){
    const b1 = document.getElementById('btnSave');
    const b2 = document.getElementById('btnExport');
    if (b1) b1.disabled = !!on;
    if (b2) b2.disabled = !!on;
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

      // fabric.util.loadImage ile daha stabil
      fabric.util.loadImage(url, (imgEl) => {
        if (!imgEl) return resolve(false);

        const img = new fabric.Image(imgEl, {
          selectable: false,
          evented: false
        });

        // cover gibi değil, tam W/H’e oturt
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
    // DB şişmesin diye background JSON’a yazmıyoruz
    const json = canvas.toJSON(['selectable','evented']);
    delete json.backgroundImage;
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

    // ✅ CSRF hash’i her response’ta güncelle (Planla hatasını bitirir)
    if (json && json.csrfHash) {
      CSRF.hash = String(json.csrfHash);
    }

    if (!res.ok || !json || json.ok !== true) {
      throw new Error((json && json.message) ? json.message : 'İşlem başarısız');
    }

    return json;
  }

  // ---------- UI: Add Text / Logo / Delete ----------
  document.getElementById('btnAddText').addEventListener('click', () => {
    const t = new fabric.Textbox('Metin', {
      left: 80, top: 80,
      fontSize: 48,
      fill: '#111111',
      fontFamily: 'Arial',
      width: Math.min(800, W - 160)
    });
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
        img.scaleToWidth(Math.min(280, W/3));
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

  function syncTextPanel(o){
    if (!isText(o)) return;
    txtFont.value = o.fontFamily || 'Arial';
    txtSize.value = Math.round(o.fontSize || 48);
    txtColor.value = (o.fill && typeof o.fill === 'string') ? o.fill : '#111111';
  }

  function applyToActiveText(patch){
    const o = canvas.getActiveObject();
    if (!isText(o)) return;
    o.set(patch);
    o.setCoords();
    canvas.requestRenderAll();
  }

  txtFont.addEventListener('change', () => applyToActiveText({ fontFamily: txtFont.value }));
  txtSize.addEventListener('change', () => applyToActiveText({ fontSize: parseInt(txtSize.value || '48', 10) }));
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

  let cropTarget = null;  // selected image
  let cropRect   = null;  // overlay rect

  function isImage(o){
    return o && o.type === 'image';
  }

  function enterCropMode(img){
    cropTarget = img;
    cropBox.style.display = 'block';

    // mevcut clip varsa üstüne düzenleme için approx bir rect çıkar
    const rW = Math.min(400, img.getScaledWidth() * 0.8);
    const rH = Math.min(400, img.getScaledHeight() * 0.8);

    cropRect = new fabric.Rect({
      left: img.left + 20,
      top: img.top + 20,
      width: rW,
      height: rH,
      fill: 'rgba(255,255,255,0.05)',
      stroke: '#0d6efd',
      strokeWidth: 2,
      selectable: true,
      hasRotatingPoint: false,
      objectCaching: false
    });

    canvas.add(cropRect);
    canvas.setActiveObject(cropRect);
    canvas.requestRenderAll();
  }

  function exitCropMode(removeRect = true){
    cropBox.style.display = 'none';
    if (removeRect && cropRect) {
      canvas.remove(cropRect);
    }
    cropRect = null;
    cropTarget = null;
    canvas.discardActiveObject();
    canvas.requestRenderAll();
  }

  function applyCrop(){
    if (!cropTarget || !cropRect) return;

    // cropRect'in koordinatlarını canvas üzerinde al
    cropRect.setCoords();
    cropTarget.setCoords();

    // cropRect'i hedef resmin lokal koordinatına çevirmek için:
    // - resmin transform matrisini kullanırız
    const inv = fabric.util.invertTransform(cropTarget.calcTransformMatrix());
    const rectTL = new fabric.Point(cropRect.left, cropRect.top);
    const rectBR = new fabric.Point(cropRect.left + cropRect.getScaledWidth(), cropRect.top + cropRect.getScaledHeight());

    const localTL = fabric.util.transformPoint(rectTL, inv);
    const localBR = fabric.util.transformPoint(rectBR, inv);

    const clipW = Math.max(1, localBR.x - localTL.x);
    const clipH = Math.max(1, localBR.y - localTL.y);

    const clip = new fabric.Rect({
      left: localTL.x,
      top: localTL.y,
      width: clipW,
      height: clipH,
      absolutePositioned: false
    });

    cropTarget.clipPath = clip;
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

  // ---------- Selection change: enable crop / show text panel ----------
  canvas.on('selection:created', refreshSelectionUI);
  canvas.on('selection:updated', refreshSelectionUI);
  canvas.on('selection:cleared', refreshSelectionUI);

  function refreshSelectionUI(){
    const o = canvas.getActiveObject();

    // crop butonu sadece image seçilince
    btnCrop.disabled = !isImage(o);

    // text panel sadece text seçilince
    if (isText(o)) {
      textStyleBox.style.display = 'block';
      syncTextPanel(o);
    } else {
      textStyleBox.style.display = 'none';
    }

    // crop modu açıkken başka şeye tıklanırsa iptal
    if (cropRect && o !== cropRect) {
      // crop rect harici bir seçim olursa crop moddan çık
      // (kullanıcı kafası karışmasın)
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
      // önce save garanti olsun
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
    const okBg = BG ? await setBackground(BG) : true;
    if (!okBg) notify('Arkaplan yüklenemedi (BG URL erişilemedi)', 'warning');

    if (SAVED) {
      try {
        const json = JSON.parse(SAVED);
        canvas.loadFromJSON(json, async () => {
          // loadFromJSON background'ı sıfırlayabilir → tekrar bas
          if (BG) await setBackground(BG);
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

  let resizeT = null;
  window.addEventListener('resize', () => {
    clearTimeout(resizeT);
    resizeT = setTimeout(() => fitToWrap(), 120);
  });

  init();
})();
</script>

<?= $this->endSection() ?>
