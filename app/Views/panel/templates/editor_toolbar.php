<div class="card p-3">
  <div class="fw-semibold mb-2">Araçlar</div>

  <button id="btnAddText" class="btn btn-outline-secondary w-100 mb-2">
    <i class="bi bi-type me-1"></i> Yazı Ekle
  </button>

  <label class="btn btn-outline-secondary w-100 mb-2">
    <i class="bi bi-image me-1"></i> Görsel Ekle
    <input id="logoFile" type="file" accept="image/*" hidden>
  </label>

  <label id="btnReplaceImageWrap" class="btn btn-outline-secondary w-100 mb-2" style="display:none;">
    <i class="bi bi-arrow-repeat me-1"></i> Görsel Değiştir
    <input id="replaceImageFile" type="file" accept="image/*" hidden>
  </label>

  <button id="btnDelete" class="btn btn-outline-danger w-100 mb-2">
    <i class="bi bi-trash me-1"></i> Seçileni Sil
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
