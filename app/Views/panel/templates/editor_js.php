<script>
(function(){
  const CFG = window.OM_EDITOR_CONFIG || {};
  const W  = parseInt(CFG.W || 1080, 10);
  const H  = parseInt(CFG.H || 1080, 10);
  const BG = String(CFG.BG || '');

  const SAVED = String(CFG.SAVED || '');
  const TEMPLATE_STATE = String(CFG.TEMPLATE_STATE || '');
  const AUTOPLAN = !!CFG.AUTOPLAN;

  const CSRF = {
    name: (CFG.CSRF && CFG.CSRF.name) ? String(CFG.CSRF.name) : '',
    hash: (CFG.CSRF && CFG.CSRF.hash) ? String(CFG.CSRF.hash) : ''
  };

  const saveUrl   = String(CFG.saveUrl || '');
  const exportUrl = String(CFG.exportUrl || '');

  let lastDesignId = parseInt(CFG.designId || 0, 10) || 0;

  const toastWrap = document.getElementById('omToastWrap');

  function escapeHtml(str){
    return String(str)
      .replaceAll('&','&amp;')
      .replaceAll('<','&lt;')
      .replaceAll('>','&gt;')
      .replaceAll('"','&quot;')
      .replaceAll("'","&#039;");
  }

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
    obj.set({ cornerSize: 9, padding: 2 });
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

        const img = new fabric.Image(imgEl, { selectable: false, evented: false });
        img.scaleToWidth(W);
        img.scaleToHeight(H);

        canvas.setBackgroundImage(img, () => {
          canvas.requestRenderAll();
          resolve(true);
        });
      }, null, 'anonymous');
    });
  }

  // ✅ ÖNCE JSON/FONKSİYONLAR (History bunları kullanacak)
  function getStateJson(){
    const json = canvas.toJSON([
      'selectable','evented',
      'omRole','omLocked','placeholderId'
    ]);
    delete json.backgroundImage;
    return JSON.stringify(json);
  }

  function applyTemplateLocksIfNeeded(o){
    if (!o) return;
    if (o.omLocked) {
      o.set({
        selectable: false,
        evented: false,
        hasControls: false,
        lockMovementX: true,
        lockMovementY: true,
        lockScalingX: true,
        lockScalingY: true,
        lockRotation: true,
      });
    }
  }

  function isText(o){ return o && (o.type === 'textbox' || o.type === 'text' || o.type === 'i-text'); }
  function isImage(o){ return o && o.type === 'image'; }

  async function loadStateAny(jsonStr){
    const json = JSON.parse(jsonStr);
    return new Promise((resolve) => {
      canvas.loadFromJSON(json, async () => {
        if (BG) await setBackground(BG);

        canvas.getObjects().forEach(o => {
          if (isText(o)) tuneTextControls(o);
          applyTemplateLocksIfNeeded(o);
        });

        fitToWrap();
        canvas.requestRenderAll();
        resolve(true);
      });
    });
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
    if (json && json.csrfHash) CSRF.hash = String(json.csrfHash);

    if (!res.ok || !json || json.ok !== true) {
      throw new Error((json && json.message) ? json.message : 'İşlem başarısız');
    }
    return json;
  }

  const fontCache = new Set();
  async function ensureFontLoaded(fontFamily){
    const ff = String(fontFamily || '').trim();
    if(!ff || ff.toLowerCase()==='arial' || ff.toLowerCase()==='times new roman') return true;
    if(fontCache.has(ff)) return true;

    if(!document.fonts || !document.fonts.load){ fontCache.add(ff); return true; }

    try{
      await document.fonts.load(`400 16px "${ff}"`);
      await document.fonts.load(`600 16px "${ff}"`);
      fontCache.add(ff);
      return true;
    }catch(e){
      console.warn('Font load failed', ff, e);
      return false;
    }
  }

  // ---------- Placeholder helpers ----------
  function isPlaceholderTarget(o){
    if (!o) return false;
    if (o.omRole === 'placeholderImage') return true; // eski rect
    if (o.omRole === 'placeholderGroup') return true; // yeni group
    return false;
  }

  // ---------- HISTORY + SHORTCUTS (DÜZELTİLMİŞ) ----------
  let __history = [];
  let __historyIndex = -1;
  let __isRestoring = false;
  let __historyTimer = null;
  const HISTORY_LIMIT = 80;

  function pushHistoryNow(){
    if (__isRestoring) return;

    try{
      const json = getStateJson();
      const last = __history[__historyIndex];
      if (last && last === json) return;

      if (__historyIndex < __history.length - 1) {
        __history = __history.slice(0, __historyIndex + 1);
      }

      __history.push(json);

      if (__history.length > HISTORY_LIMIT) {
        __history.shift();
      }

      __historyIndex = __history.length - 1;
    }catch(e){
      console.warn('pushHistoryNow error', e);
    }
  }

  function pushHistory(){
    if (__isRestoring) return;
    clearTimeout(__historyTimer);
    __historyTimer = setTimeout(pushHistoryNow, 180);
  }

  async function restoreFromHistory(index){
    if (index < 0 || index >= __history.length) return;
    __isRestoring = true;

    try{
      const src = __history[index];
      await loadStateAny(src);
      __historyIndex = index;
    }catch(e){
      console.warn('restoreFromHistory error', e);
    }finally{
      __isRestoring = false;
    }
  }

  function undo(){
    if (__historyIndex <= 0) return;
    restoreFromHistory(__historyIndex - 1);
  }

  function redo(){
    if (__historyIndex >= __history.length - 1) return;
    restoreFromHistory(__historyIndex + 1);
  }

  // history tetikleyicileri
  canvas.on('object:added',    () => pushHistory());
  canvas.on('object:modified', () => pushHistory());
  canvas.on('object:removed',  () => pushHistory());
  canvas.on('text:changed',    () => pushHistory());

  // DELETE
  function deleteActive(){
    const obj = canvas.getActiveObject();
    if (!obj) return;

    // ActiveSelection ise içindekileri sil
    if (obj.type === 'activeSelection') {
      const items = obj.getObjects();
      canvas.discardActiveObject();
      items.forEach(o => {
        if (!o || o.omLocked) return;
        canvas.remove(o);
      });
      canvas.requestRenderAll();
      return;
    }

    if (obj.omLocked) return;
    canvas.remove(obj);
    canvas.requestRenderAll();
  }

  // COPY/PASTE (OBJ)
  let __clipboard = null;

  function copyActive(){
    const obj = canvas.getActiveObject();
    if (!obj) return;
    if (obj.omLocked) return;

    obj.clone((cloned) => {
      __clipboard = cloned;
    }, [
      'selectable','evented',
      'omRole','omLocked','placeholderId'
    ]);
  }

  function pasteClipboard(){
    if (!__clipboard) return;

    __clipboard.clone((clonedObj) => {
      canvas.discardActiveObject();

      clonedObj.set({
        left: (clonedObj.left || 0) + 12,
        top:  (clonedObj.top  || 0) + 12,
        evented: true
      });

      if (clonedObj.type === 'activeSelection') {
        clonedObj.canvas = canvas;
        clonedObj.forEachObject((o) => {
          if (o.omLocked) return;
          canvas.add(o);
        });
        clonedObj.setCoords();
      } else {
        canvas.add(clonedObj);
      }

      canvas.setActiveObject(clonedObj);
      canvas.requestRenderAll();
    }, [
      'selectable','evented',
      'omRole','omLocked','placeholderId'
    ]);
  }

  // Ctrl+A = tüm objeleri seç (text edit modunda ASLA çalışmasın)
  function selectAll(){
    const objects = canvas.getObjects().filter(o => o && o.selectable !== false && !o.omLocked);
    if (!objects.length) return;

    canvas.discardActiveObject();
    const sel = new fabric.ActiveSelection(objects, { canvas });
    canvas.setActiveObject(sel);
    canvas.requestRenderAll();
  }

  // Text edit modunu tespit et (kritik!)
  function isEditingText(){
    const o = canvas.getActiveObject();
    return !!(o && o.isEditing === true);
  }

  document.addEventListener('keydown', (e) => {
    const ctrl = e.ctrlKey || e.metaKey;
    const key  = (e.key || '').toLowerCase();

    // ✅ Text edit modundayken: KESİNLİKLE kısayollara karışma
    // (Ctrl+A, Ctrl+C, Backspace vb. normal textarea davranışı olsun)
    if (isEditingText()) {
      return;
    }

    // Delete / Backspace (objeyi sil)
    if (!ctrl && (e.key === 'Delete' || e.key === 'Backspace')) {
      e.preventDefault();
      deleteActive();
      return;
    }

    // ESC = seçim kaldır
    if (e.key === 'Escape') {
      canvas.discardActiveObject();
      canvas.requestRenderAll();
      return;
    }

    // Undo / Redo
    if (ctrl && key === 'z') {
      e.preventDefault();
      if (e.shiftKey) redo(); else undo();
      return;
    }
    if (ctrl && key === 'y') {
      e.preventDefault();
      redo();
      return;
    }

    // Copy / Paste (objeler)
    if (ctrl && key === 'c') {
      e.preventDefault();
      copyActive();
      return;
    }
    if (ctrl && key === 'v') {
      e.preventDefault();
      pasteClipboard();
      return;
    }

    // Select all objects
    if (ctrl && key === 'a') {
      e.preventDefault();
      selectAll();
      return;
    }
  }, { passive:false });

  // ---------- UI: Add Text / Logo / Delete ----------
  document.getElementById('btnAddText')?.addEventListener('click', async () => {
    await ensureFontLoaded('Inter');

    const t = new fabric.Textbox('Metin', {
      left: 80, top: 80,
      fontSize: 48,
      fill: '#111111',
      fontFamily: 'Inter',
      width: Math.min(820, W - 160),
      selectable: true,
      evented: true
    });

    tuneTextControls(t);
    canvas.add(t);
    canvas.setActiveObject(t);
    canvas.requestRenderAll();
  });

  document.getElementById('logoFile')?.addEventListener('change', (e) => {
    const file = e.target.files && e.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = () => {
      fabric.Image.fromURL(reader.result, (img) => {
        img.set({ left: 80, top: 160, selectable: true, evented: true });
        img.scaleToWidth(Math.min(320, W/3));
        canvas.add(img);
        canvas.setActiveObject(img);
        canvas.requestRenderAll();
      }, { crossOrigin: 'anonymous' });
    };
    reader.readAsDataURL(file);
    e.target.value = '';
  });

  document.getElementById('btnDelete')?.addEventListener('click', () => {
    const obj = canvas.getActiveObject();
    if (!obj) return;

    if (obj.omLocked) {
      notify('Bu katman kilitli, silinemez.', 'warning');
      return;
    }

    canvas.remove(obj);
    canvas.requestRenderAll();
  });

  // ---------- Placeholder Replace (GROUP FIX) ----------
  const btnReplaceWrap = document.getElementById('btnReplaceImageWrap');
  const replaceFile = document.getElementById('replaceImageFile');

  function toggleReplaceUI(){
    const o = canvas.getActiveObject();
    if (btnReplaceWrap) btnReplaceWrap.style.display = isPlaceholderTarget(o) ? 'block' : 'none';
  }

  function buildPlaceholderGroupFromRect(phRect, img){
    const center = phRect.getCenterPoint();

    const baseW = (phRect.width || 0) * (phRect.scaleX || 1);
    const baseH = (phRect.height || 0) * (phRect.scaleY || 1);

    const scale = Math.max(baseW / img.width, baseH / img.height);
    img.scale(scale);

    img.set({
      originX: 'center',
      originY: 'center',
      left: 0,
      top: 0,
      selectable: true,
      evented: true,
      omRole: 'userImage',
      placeholderId: phRect.placeholderId || null,
    });

    const frame = new fabric.Rect({
      originX: 'center',
      originY: 'center',
      left: 0,
      top: 0,
      width: baseW,
      height: baseH,
      rx: phRect.rx || 0,
      ry: phRect.ry || 0,
      fill: 'transparent',
      stroke: phRect.stroke || 'rgba(0,0,0,.25)',
      strokeWidth: phRect.strokeWidth || 2,
      selectable: false,
      evented: false,
      omLocked: true
    });

    const clip = new fabric.Rect({
      originX: 'center',
      originY: 'center',
      left: 0,
      top: 0,
      width: baseW,
      height: baseH,
      rx: phRect.rx || 0,
      ry: phRect.ry || 0,
      absolutePositioned: false
    });

    const group = new fabric.Group([img, frame], {
      originX: 'center',
      originY: 'center',
      left: center.x,
      top: center.y,
      subTargetCheck: true,
      selectable: true,
      evented: true,
      omRole: 'placeholderGroup',
      placeholderId: phRect.placeholderId || null,
    });

    group.clipPath = clip;
    return group;
  }

  function removeOldGroupForPlaceholder(placeholderId){
    const pid = String(placeholderId || '');
    canvas.getObjects().slice().forEach(o => {
      if (o && o.omRole === 'placeholderGroup' && String(o.placeholderId || '') === pid) {
        canvas.remove(o);
      }
    });
  }

  replaceFile?.addEventListener('change', (e) => {
    const file = e.target.files && e.target.files[0];
    if (!file) return;

    const active = canvas.getActiveObject();
    if (!active) { e.target.value=''; return; }

    const reader = new FileReader();
    reader.onload = () => {
      fabric.Image.fromURL(reader.result, (img) => {

        // CASE A: rect placeholder
        if (active.omRole === 'placeholderImage') {
          const phRect = active;

          removeOldGroupForPlaceholder(phRect.placeholderId);
          canvas.remove(phRect);

          const group = buildPlaceholderGroupFromRect(phRect, img);
          canvas.add(group);
          canvas.setActiveObject(group);
          canvas.requestRenderAll();
          toggleReplaceUI();
        }

        // CASE B: group placeholder -> replace image
        else if (active.omRole === 'placeholderGroup') {
          const group = active;
          const pid = group.placeholderId || null;

          const frame = group._objects?.find(x => x && x.type === 'rect' && x.omLocked);
          if (!frame) return;

          const baseW = frame.width;
          const baseH = frame.height;

          const scale = Math.max(baseW / img.width, baseH / img.height);
          img.scale(scale);

          img.set({
            originX: 'center',
            originY: 'center',
            left: 0,
            top: 0,
            selectable: true,
            evented: true,
            omRole: 'userImage',
            placeholderId: pid,
          });

          group._objects = group._objects.filter(x => !(x && x.omRole === 'userImage'));
          group._objects.unshift(img);

          group.addWithUpdate();
          canvas.requestRenderAll();
          toggleReplaceUI();
        }

      }, { crossOrigin: 'anonymous' });
    };

    reader.readAsDataURL(file);
    e.target.value = '';
  });

  // ---------- Text Style Panel ----------
  const textStyleBox = document.getElementById('textStyleBox');
  const txtFont  = document.getElementById('txtFont');
  const txtSize  = document.getElementById('txtSize');
  const txtColor = document.getElementById('txtColor');
  const txtBold  = document.getElementById('txtBold');
  const txtItalic= document.getElementById('txtItalic');

  function syncTextPanel(o){
    if (!isText(o)) return;
    txtFont.value = o.fontFamily || 'Inter';
    txtSize.value = Math.round(o.fontSize || 48);
    txtColor.value = (o.fill && typeof o.fill === 'string') ? o.fill : '#111111';
  }

  function applyToActiveText(patch){
    const o = canvas.getActiveObject();
    if (!isText(o)) return;
    if (o.omLocked) return;

    o.set(patch);
    tuneTextControls(o);
    o.setCoords();
    canvas.requestRenderAll();
  }

  txtFont?.addEventListener('change', async () => {
    const ff = txtFont.value;
    const ok = await ensureFontLoaded(ff);
    if(!ok) notify('Font yüklenemedi, sistem fontuna düşebilir.', 'warning');
    applyToActiveText({ fontFamily: ff });
  });

  txtSize?.addEventListener('change', () => {
    const n = parseInt(txtSize.value || '48', 10);
    applyToActiveText({ fontSize: isFinite(n) ? n : 48 });
  });

  txtColor?.addEventListener('input', () => applyToActiveText({ fill: txtColor.value }));

  txtBold?.addEventListener('click', () => {
    const o = canvas.getActiveObject();
    if (!isText(o) || o.omLocked) return;
    const next = (o.fontWeight === 'bold') ? 'normal' : 'bold';
    applyToActiveText({ fontWeight: next });
  });

  txtItalic?.addEventListener('click', () => {
    const o = canvas.getActiveObject();
    if (!isText(o) || o.omLocked) return;
    const next = (o.fontStyle === 'italic') ? 'normal' : 'italic';
    applyToActiveText({ fontStyle: next });
  });

  textStyleBox?.querySelectorAll('button[data-align]')?.forEach(btn => {
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

  btnCrop?.addEventListener('click', () => {
    const o = canvas.getActiveObject();
    if (!isImage(o)) return;
    enterCropMode(o);
  });

  btnCropApply?.addEventListener('click', applyCrop);
  btnCropCancel?.addEventListener('click', () => exitCropMode(true));

  // ---------- Selection UI ----------
  canvas.on('selection:created', refreshSelectionUI);
  canvas.on('selection:updated', refreshSelectionUI);
  canvas.on('selection:cleared', refreshSelectionUI);

  function refreshSelectionUI(){
    const o = canvas.getActiveObject();
    if (btnCrop) btnCrop.disabled = !isImage(o);

    toggleReplaceUI();

    if (isText(o) && !o.omLocked) {
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

  // ---------- Double click text edit ----------
  canvas.on('mouse:dblclick', function(opt){
    const t = opt?.target;
    if (!t) return;
    if (t.omLocked) return;

    if (t.type === 'i-text' || t.type === 'textbox' || t.type === 'text') {
      canvas.setActiveObject(t);
      if (t.enterEditing) {
        t.enterEditing();
        if (t.hiddenTextarea) t.hiddenTextarea.focus();
      }
    }
  });

  // ---------- SAVE / EXPORT ----------
  document.getElementById('btnSave')?.addEventListener('click', async (e) => {
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

  document.getElementById('btnExport')?.addEventListener('click', async (e) => {
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

      const postType = document.getElementById('postType')?.value || 'post';
      const dataUrl = canvas.toDataURL({ format:'png', quality: 1 });

      const out = await postForm(exportUrl, {
        design_id: String(lastDesignId),
        png_data: dataUrl,
        post_type: postType
      });

      notify('Şablon aktarıldı ✅', 'success');
      if (out.redirect) window.location.href = out.redirect;
    } catch(err) {
      notify(err.message || 'Export hatası', 'danger');
    } finally {
      setBusy(false);
    }
  });

  async function runAutoPlan(){
    try{
      setBusy(true);
      showAutoplanOverlay(true);

      if (!lastDesignId) {
        const state = getStateJson();
        const json = await postForm(saveUrl, {
          canvas_width: W,
          canvas_height: H,
          state_json: state
        });
        if (json.design_id) lastDesignId = parseInt(json.design_id, 10) || lastDesignId;
      }

      const postType = document.getElementById('postType')?.value || 'post';
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

  // ---------- Init ----------
  async function init(){
    const warm = ['Inter','Poppins','Montserrat','Roboto','Oswald','Playfair Display','Nunito','Raleway'];
    try{ for(const f of warm){ await ensureFontLoaded(f); } } catch(e){}

    const okBg = BG ? await setBackground(BG) : true;
    if (!okBg) notify('Arkaplan yüklenemedi (BG URL erişilemedi)', 'warning');

    const src = (SAVED && SAVED.trim() !== '')
      ? SAVED
      : ((TEMPLATE_STATE && TEMPLATE_STATE.trim() !== '') ? TEMPLATE_STATE : '');

    if (src) {
      try { await loadStateAny(src); }
      catch(e){ fitToWrap(); canvas.requestRenderAll(); }
    } else {
      fitToWrap();
      canvas.requestRenderAll();
    }

    toggleReplaceUI();

    // ✅ init bittikten sonra ilk state’i history’ye baseline olarak koy
    setTimeout(() => pushHistoryNow(), 120);
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
