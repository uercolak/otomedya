<?= $this->extend('layouts/panel') ?>
<?= $this->section('content') ?>

<?php
  $tplId = (int)($tpl['id'] ?? 0);
  $w = (int)($tpl['width'] ?? 1080);
  $h = (int)($tpl['height'] ?? 1080);
  $bgUrl = !empty($tpl['file_path']) ? base_url($tpl['file_path']) : '';
  $formatKey = (string)($tpl['format_key'] ?? '');
  $savedState = $design['state_json'] ?? '';
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
        <div style="max-width:100%; overflow:auto;">
          <canvas id="c" width="<?= $w ?>" height="<?= $h ?>" style="border-radius:12px; border:1px solid rgba(0,0,0,.08);"></canvas>
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

<script src="https://cdn.jsdelivr.net/npm/fabric@5.3.0/dist/fabric.min.js"></script>
<script>
(function(){
  const W = <?= (int)$w ?>;
  const H = <?= (int)$h ?>;
  const BG = <?= json_encode((string)$bgUrl) ?>;
  const SAVED = <?= json_encode((string)$savedState) ?>;

  const csrfName = <?= json_encode(csrf_token()) ?>;
  const csrfHash = <?= json_encode(csrf_hash()) ?>;

  const saveUrl   = <?= json_encode(site_url('panel/templates/'.$tplId.'/save')) ?>;
  const exportUrl = <?= json_encode(site_url('panel/templates/'.$tplId.'/export')) ?>;

  const canvas = new fabric.Canvas('c', {
    preserveObjectStacking: true,
    selection: true
  });

  // Background (template image)
  function setBackground(url){
    return new Promise((resolve) => {
      fabric.Image.fromURL(url, (img) => {
        img.set({ selectable:false, evented:false });
        img.scaleToWidth(W);
        img.scaleToHeight(H);
        canvas.setBackgroundImage(img, canvas.renderAll.bind(canvas));
        resolve();
      }, { crossOrigin: 'anonymous' });
    });
  }

  // Load saved state (if any)
  async function init(){
    if (BG) await setBackground(BG);

    if (SAVED) {
      try {
        const json = JSON.parse(SAVED);
        // load WITHOUT background (we already set it)
        canvas.loadFromJSON(json, () => {
          // background tekrar seçilebilir olmasın
          canvas.getObjects().forEach(o => {
            // nothing
          });
          canvas.renderAll();
        });
      } catch(e) {}
    }
  }

  function getStateJson(){
    // BackgroundImage JSON’a girmesin diye: export sadece objects
    // Fabric loadFromJSON ile yine çizilecek.
    const json = canvas.toJSON(['selectable','evented']);
    // backgroundImage kaldır
    delete json.backgroundImage;
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
    canvas.renderAll();
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
        canvas.renderAll();
      });
    };
    reader.readAsDataURL(file);
    e.target.value = '';
  });

  document.getElementById('btnDelete').addEventListener('click', () => {
    const obj = canvas.getActiveObject();
    if (!obj) return;
    canvas.remove(obj);
    canvas.renderAll();
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
      alert('Kaydedildi ✅');
    } catch(err) {
      alert(err.message || 'Kaydetme hatası');
    }
  });

  // EXPORT -> content oluştur -> planner’a git
  document.getElementById('btnExport').addEventListener('click', async () => {
    try {
      // önce save zorunlu olsun
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

      // export PNG (background dahil)
      const dataUrl = canvas.toDataURL({ format:'png', quality: 1 });

      const out = await postForm(exportUrl, {
        design_id: String(lastDesignId),
        png_data: dataUrl,
        post_type: postType
      });

      if (out.redirect) {
        window.location.href = out.redirect;
      } else {
        alert('Export tamam ama yönlendirme yok.');
      }
    } catch(err) {
      alert(err.message || 'Export hatası');
    }
  });

  init();
})();
</script>

<?= $this->endSection() ?>
