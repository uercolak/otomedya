<div class="card mb-3" id="platformSettingsCard" style="display:none;">
  <div class="card-body">
    <div class="d-flex align-items-center justify-content-between mb-2">
      <h5 class="card-title mb-0">Platform Ayarları</h5>
      <span class="badge bg-light text-dark">Seçilen platformlara göre</span>
    </div>
    <div class="text-muted small mb-3">
      Seçtiğin platformlar için yayın türü / gizlilik / yorum gibi ayarları buradan yönetirsin.
    </div>

    <div class="accordion" id="platformAccordion">

      <!-- Instagram -->
      <div class="accordion-item" data-plat-box="instagram" style="display:none;">
        <h2 class="accordion-header" id="accIG">
          <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#accIGBody" aria-expanded="true">
            Instagram
          </button>
        </h2>
        <div id="accIGBody" class="accordion-collapse collapse show" data-bs-parent="#platformAccordion">
          <div class="accordion-body">
            <div class="mb-3">
              <label class="form-label">Paylaşım Tipi</label>
              <select name="settings[instagram][post_type]" class="form-select" id="igPostType">
                <option value="auto" selected>AUTO (video→reels, görsel→post)</option>
                <option value="post">Post</option>
                <option value="reels">Reels</option>
                <option value="story">Story</option>
              </select>
              <div class="form-text">Reels için video gerekir. Story için medya gerekir.</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Facebook -->
      <div class="accordion-item" data-plat-box="facebook" style="display:none;">
        <h2 class="accordion-header" id="accFB">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accFBBody" aria-expanded="false">
            Facebook
          </button>
        </h2>
        <div id="accFBBody" class="accordion-collapse collapse" data-bs-parent="#platformAccordion">
          <div class="accordion-body">
            <div class="mb-3">
              <label class="form-label">Paylaşım Tipi</label>
              <select name="settings[facebook][post_type]" class="form-select" id="fbPostType">
                <option value="post" selected>Post</option>
                <option value="reels">Reels (video)</option>
              </select>
              <div class="form-text">Reels seçersen video önerilir.</div>
            </div>

            <div class="mb-3">
              <label class="form-label">Görünürlük (basit)</label>
              <select name="settings[facebook][privacy]" class="form-select">
                <option value="public" selected>Public (herkese açık)</option>
                <option value="private">Private (özel)</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <!-- TikTok -->
      <div class="accordion-item" data-plat-box="tiktok" style="display:none;">
        <h2 class="accordion-header" id="accTT">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accTTBody" aria-expanded="false">
            TikTok
          </button>
        </h2>
        <div id="accTTBody" class="accordion-collapse collapse" data-bs-parent="#platformAccordion">
          <div class="accordion-body">
            <div class="mb-3">
              <label class="form-label">Gizlilik</label>
              <select name="settings[tiktok][privacy]" class="form-select" id="ttPrivacy">
                <option value="public" selected>Public</option>
                <option value="friends">Friends</option>
                <option value="private">Private</option>
              </select>
            </div>

            <div class="row g-2">
              <div class="col-12">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" value="1" id="ttAllowComment" name="settings[tiktok][allow_comment]" checked>
                  <label class="form-check-label" for="ttAllowComment">Yorumlara izin ver</label>
                </div>
              </div>

              <div class="col-12">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" value="1" id="ttAllowDuet" name="settings[tiktok][allow_duet]" checked>
                  <label class="form-check-label" for="ttAllowDuet">Duet’e izin ver</label>
                </div>
              </div>

              <div class="col-12">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" value="1" id="ttAllowStitch" name="settings[tiktok][allow_stitch]" checked>
                  <label class="form-check-label" for="ttAllowStitch">Stitch’e izin ver</label>
                </div>
              </div>
            </div>

            <div class="form-text mt-2">TikTok için video içeriği gerekir.</div>
          </div>
        </div>
      </div>

      <!-- YouTube -->
      <div class="accordion-item" data-plat-box="youtube" style="display:none;">
        <h2 class="accordion-header" id="accYT">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accYTBody" aria-expanded="false">
            YouTube
          </button>
        </h2>
        <div id="accYTBody" class="accordion-collapse collapse" data-bs-parent="#platformAccordion">
          <div class="accordion-body">
            <div class="mb-3">
              <label class="form-label">YouTube Başlık <span class="text-danger">*</span></label>
                <input type="text"
                    name="settings[youtube][title]"
                    class="form-control"
                    id="ytTitle"
                    value="<?= esc(old('settings.youtube.title')) ?>"
                    placeholder="YouTube video başlığı">
            <div class="form-text">YouTube için başlık zorunludur.</div>
            </div>

            <div class="mb-3">
              <label class="form-label">Gizlilik</label>
              <select name="settings[youtube][privacy]" class="form-select" id="ytPrivacy">
                <option value="public" selected>Public (herkese açık)</option>
                <option value="unlisted">Unlisted (liste dışı)</option>
                <option value="private">Private (özel)</option>
              </select>
            </div>

            <div class="mb-0">
              <label class="form-label">Çocuklara Özel</label>
              <select name="settings[youtube][made_for_kids]" class="form-select" id="ytKids">
                <option value="no" selected>Hayır</option>
                <option value="yes">Evet</option>
              </select>
            </div>

            <div class="form-text mt-2">Youtube için video içeriği gerekir.</div>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>
