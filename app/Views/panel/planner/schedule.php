<div class="card mb-3" id="platformSettingsCard" style="display:none;">
  <div class="card-body">
    <h5 class="card-title mb-3">Platform Ayarları</h5>

    <!-- Instagram -->
    <div class="border rounded p-3 mb-3" id="igSettings" style="display:none;">
      <div class="fw-semibold mb-2">Instagram</div>

      <div class="mb-2">
        <label class="form-label">Paylaşım Türü</label>
        <select name="ig_post_type" class="form-select">
          <option value="auto" selected>Otomatik (görsel→Gönderi, video→Reels)</option>
          <option value="post">Gönderi</option>
          <option value="reels">Reels</option>
          <option value="story">Hikaye</option>
        </select>
        <div class="form-text">Seçimine göre paylaşım uygun formatta hazırlanır.</div>
      </div>
    </div>

    <!-- Facebook -->
    <div class="border rounded p-3 mb-3" id="fbSettings" style="display:none;">
      <div class="fw-semibold mb-2">Facebook</div>

      <div class="mb-2">
        <label class="form-label">Görünürlük</label>
        <select name="fb_privacy" class="form-select">
          <option value="public" selected>Herkese Açık</option>
          <option value="page">Sayfa Takipçileri</option>
        </select>
        <div class="form-text">Bu ayar, paylaşımın görünürlüğünü belirler.</div>
      </div>

      <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" name="fb_allow_comments" value="1" id="fbAllowComments" checked>
        <label class="form-check-label" for="fbAllowComments">Yorumlara izin ver</label>
      </div>
    </div>

    <!-- TikTok -->
    <div class="border rounded p-3 mb-3" id="ttSettings" style="display:none;">
      <div class="fw-semibold mb-2">TikTok</div>

      <div class="mb-2">
        <label class="form-label">Gizlilik</label>
        <select name="tt_privacy" class="form-select">
          <option value="public" selected>Herkese Açık</option>
          <option value="private">Sadece Ben</option>
        </select>
      </div>

      <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" name="tt_allow_comments" value="1" id="ttAllowComments" checked>
        <label class="form-check-label" for="ttAllowComments">Yorumlara izin ver</label>
      </div>

      <div class="form-check form-switch mt-2">
        <input class="form-check-input" type="checkbox" name="tt_allow_duet" value="1" id="ttAllowDuet" checked>
        <label class="form-check-label" for="ttAllowDuet">Duet’e izin ver</label>
      </div>

      <div class="form-check form-switch mt-2">
        <input class="form-check-input" type="checkbox" name="tt_allow_stitch" value="1" id="ttAllowStitch" checked>
        <label class="form-check-label" for="ttAllowStitch">Stitch’e izin ver</label>
      </div>

      <div class="form-text mt-2">TikTok için video içeriği gerekir.</div>
    </div>

    <!-- YouTube -->
    <div class="border rounded p-3" id="ytSettings" style="display:none;">
      <div class="fw-semibold mb-2">YouTube</div>

      <div class="mb-2">
        <label class="form-label">Video Başlığı <span class="text-danger">*</span></label>
        <input type="text" name="youtube_title" class="form-control" placeholder="YouTube video başlığı">
        <div class="form-text">YouTube’da video başlığı zorunludur.</div>
      </div>

      <div class="mb-2">
        <label class="form-label">Gizlilik</label>
        <select name="youtube_privacy" class="form-select">
          <option value="public" selected>Herkese Açık</option>
          <option value="unlisted">Liste Dışı</option>
          <option value="private">Özel</option>
        </select>
      </div>

      <div class="form-text mt-2">YouTube için video içeriği gerekir.</div>
    </div>

  </div>
</div>
