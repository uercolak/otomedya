<?= $this->extend('layouts/panel') ?>
<?= $this->section('content') ?>

<?php
  $tplId = (int)($tpl['id'] ?? 0);
  $w = (int)($tpl['width'] ?? 1080);
  $h = (int)($tpl['height'] ?? 1080);

  // base_media_id varsa /media/{id}; yoksa eski file_path fallback
  $bgUrl = !empty($tpl['base_media_id'])
    ? site_url('media/' . (int)$tpl['base_media_id'])
    : (!empty($tpl['file_path']) ? base_url($tpl['file_path']) : '');

  $formatKey  = (string)($tpl['format_key'] ?? '');
  $savedState = (string)($design['state_json'] ?? '');
?>

<style>
  /* ✅ Canvas “ekranda” fit olacak ama gerçek çözünürlük bozulmayacak */
  #canvasWrap {
    width: 100%;
    overflow: auto;
  }
  #canvasStage {
    /* stage içine canvas’ı koyuyoruz; burası ölçeklenecek */
    transform-origin: top left;
    display: inline-block;
  }
  /* Canvas elementleri inline-block kalsın */
  canvas { display:block; }
</style>

<div class="container-fluid py-3">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <div>
      <h3 class="mb-0">Şablonu Düzenle</h3>
      <div class="text-muted"><?= esc($tpl['name'] ?? '') ?> • <?= esc($formatKey) ?> • <?= $w ?>×<?= $h ?></div>
    </div>

    <div class="d-flex gap-2">
      <a href="<?= site_url('panel/templates') ?>" class="btn btn-outline-secondary">Geri</a>
      <button id="btnSave" class="btn btn-outline-primary" type="button">Kaydet</button>
      <button id="btnExport" class="btn btn-primary" type="button">Planla</button>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-lg-9">
      <div class="card p-3">

        <?php if ($bgUrl): ?>
          <div class="small text-muted mb-2">BG: <?= esc($bgUrl) ?></div>
        <?php else: ?>
          <div class="small text-danger mb-2">BG yok (base_media_id/file_path boş)</div>
        <?php endif; ?>

        <div id="canvasWrap">
          <!-- ✅ Stage: sadece görüntü için scale uygulanacak -->
          <div id="canvasStage">
            <canvas id="c"
              width="<?= $w ?>"
              height="<?= $h ?>"
              style="border-radius:12px; border:1px solid rgba(0,0,0,.08);"></canvas>
          </div>
        </div>

      </div>
    </div>

    <div class="col-lg-3">
      <div class="card p-3">
        <div class="fw-semibold mb-2">Araçlar</div>

        <button id="btnAddText" class="btn btn-outline-secondary w-100 mb-2" type="button">
          <i class="bi bi-type me-1"></i> Yazı ekle
        </button>

        <label class="btn btn-outline-secondary w-100 mb-2">
          <i class="bi bi-image me-1"></i> Logo ekle
          <input id="logoFile" type="file" accept="image/*" hidden>
        </label>

        <button id="btnDelete" class="btn btn-outline-danger w-100 mb-2" type="button">
          <i class="bi bi-trash me-1"></i> Seçileni sil
        </button>

        <hr>

        <label class="form-label small text-muted">Instagram Paylaşım Tipi (meta_json)</label>
        <select id="postType" class="form-select mb-2">
          <option value="post" selected>Post</option>
          <option value="story">Story</option>
          <option value="reels">Reels (V1 image için planlama; video değil)</option>
        </select>

        <div class="small text-muted">V1’de export PNG üretir ve Planner’a içerik olarak taşır.</div>
      </div>
    </div>
  </div>
</div>

<!-- Toast (Bootstrap varsa kullanır, yoksa alert fallback) -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1080;">
  <div id="appToast" class="toast align-items-center text-bg-dark border-0" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div id="appToastBody" class="toast-body">...</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Kapat"></button>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/fabric@5.3.0/dist/fabric.min.js"></script>
<script>
(() => {
  const W = <?= (int)$w ?>;
  const H = <?= (int)$h ?>;
  const BG = <?= json_encode((string)$bgUrl) ?>;
  const SAVED = <?= json_encode((string)$savedState) ?>;

  const csrfName = <?= json_encode(csrf_token()) ?>;
  const csrfHash = <?= json_encode(csrf_hash()) ?>;

  const saveUrl   = <?= json_encode(site_url('panel/templates/'.$tplId.'/save')) ?>;
  const exportUrl = <?= json_encode(site_url('panel/templates/'.$tplId.'/export')) ?>;

  const toastEl = document.getElementById('appToast');
  const toastBodyEl = document.getElementById('appToastBody');

  function notify(message, type='dark'){
    // type: success | danger | warning | dark
    try {
      if (window.bootstrap && toastEl && toastBodyEl) {
        toastEl.className = 'toast align-items-center text-bg-' + type + ' border-0';
        toastBodyEl.textContent = message;
        new bootstrap.Toast(toastEl, { delay: 2200 }).show();
        return;
      }
    } catch (e) {}
    alert(message);
  }

  const canvas = new fabric.Canvas('c', {
    preserveObjectStacking: true,
    selection: true
  });

  // ✅ Display fit: canvas gerçek boyutu sabit, sadece stage CSS scale
  function fitToWrap(){
    const wrap = document.getElementById('canvasWrap');
    const stage = document.getElementById('canvasStage');
    if (!wrap || !stage) return;

    const availableW = Math.max(320, wrap.clientWidth - 6);
    const scale = Math.min(1, availableW / W);

    stage.style.transform = `scale(${scale})`;
    stage.style.width = (W * scale) + 'px';
    stage.style.height = (H * scale) + 'px';
  }

  // ✅ Background yükle (Fabric uyumlu, CORS anonymous)
  async function loadBackground(){
    if (!BG) return false;

    return new Promise((resolve) => {
      fabric.Image.fromURL(BG, (img) => {
        if (!img) {
          notify('Arkaplan yüklenemedi: ' + BG, 'danger');
          return resolve(false);
        }
        img.set({ selectable:false, evented:false });
        img.scaleToWidth(W);
        img.scaleToHeight(H);

        canvas.setBackgroundImage(img, () => {
          canvas.requestRenderAll();
          resolve(true);
        });
      }, { crossOrigin: 'anonymous' });
    });
  }

  function getStateJson(){
    const json = canvas.toJSON(['selectable','evented']);
    delete json.backgroundImage; // DB şişmesin
    return JSON.stringify(json);
  }

  async function postForm(url, data){
    const form = new URLSearchParams();
    form.append(csrfName, csrfHash);
    Object.keys(data).forEach(k => form.append(k, data[k]));

    const res = await fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
      body: form.toString()
    });

    const json = await res.json().catch(() => null);
    if (!res.ok || !json || json.ok !== true) {
      throw new Error((json && json.message) ? json.message : 'İşlem başarısız');
    }
    return json;
  }

  // ✅ Init: background -> state -> background guarantee -> fit
  async function init(){
    await loadBackground();

    if (SAVED) {
      try {
        const parsed = JSON.parse(SAVED);
        canvas.loadFromJSON(parsed, async () => {
          // loadFromJSON background’ı silebilir → garanti tekrar
          await loadBackground();
          canvas.requestRenderAll();
          fitToWrap();
        });
      } catch (e) {
        fitToWrap();
      }
    } else {
      fitToWrap();
    }
  }

  // UI actions
  document.getElementById('btnAddText').addEventListener('click', () => {
    const t = new fabric.Textbox('Metin', {
      left: 80, top: 80,
      fontSize: 48,
      fill: '#111',
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
        if (!img) return;
        img.set({ left: 80, top: 160 });
        img.scaleToWidth(Math.min(280, W/3));
        canvas.add(img);
        canvas.setActiveObject(img);
        canvas.requestRenderAll();
      });
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

  // SAVE
  let lastDesignId = <?= (int)($design['id'] ?? 0) ?>;

  document.getElementById('btnSave').addEventListener('click', async () => {
    try {
      const state = getStateJson();
      const json = await postForm(saveUrl, {
        canvas_width: W,
        canvas_height: H,
        state_json: state
      });
      lastDesignId = json.design_id || lastDesignId;
      notify('Kaydedildi ✅', 'success');
    } catch(err) {
      notify(err.message || 'Kaydetme hatası', 'danger');
    }
  });

  // EXPORT (full-res garanti)
  document.getElementById('btnExport').addEventListener('click', async () => {
    try {
      if (!lastDesignId) {
        const state = getStateJson();
        const json = await postForm(saveUrl, {
          canvas_width: W,
          canvas_height: H,
          state_json: state
        });
        lastDesignId = json.design_id;
      }

      const postType = document.getElementById('postType').value || 'post';

      // ✅ Export her zaman gerçek canvas boyutundan çıkar (W×H)
      const dataUrl = canvas.toDataURL({
        format: 'png',
        multiplier: 1,
        quality: 1
      });

      const out = await postForm(exportUrl, {
        design_id: String(lastDesignId),
        png_data: dataUrl,
        post_type: postType
      });

      notify('Şablon aktarıldı ✅', 'success');
      if (out.redirect) window.location.href = out.redirect;

    } catch(err) {
      notify(err.message || 'Export hatası', 'danger');
    }
  });

  // ✅ ResizeObserver: daha stabil (sidebar aç/kapa vs)
  try {
    const wrap = document.getElementById('canvasWrap');
    if (wrap && window.ResizeObserver) {
      const ro = new ResizeObserver(() => fitToWrap());
      ro.observe(wrap);
    } else {
      window.addEventListener('resize', () => fitToWrap());
    }
  } catch(e) {
    window.addEventListener('resize', () => fitToWrap());
  }

  init();
})();
</script>

<?= $this->endSection() ?>
