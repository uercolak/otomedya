<div class="card">
  <div class="card-body">
    <h5 class="card-title mb-3">İçerik</h5>

    <div class="mb-3">
      <label class="form-label">Başlık</label>
      <input type="text" name="title" class="form-control" value="<?= esc($prefill['title'] ?? '') ?>" placeholder="Örn: Yeni ürün duyurusu / Kampanya / Etkinlik">
      <div class="form-text">Kısa ve net bir başlık, içeriklerini daha kolay takip etmeni sağlar.</div>
    </div>

    <div class="mb-3">
      <div class="d-flex align-items-center justify-content-between">
        <label class="form-label mb-0">Açıklama (Caption)</label>

        <div class="dropdown">
          <button class="btn btn-sm btn-soft dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Emoji ekle">
            😊 Emoji
          </button>

          <div class="dropdown-menu dropdown-menu-end p-2 emoji-menu" style="width: 320px;">
            <div class="small text-muted mb-2">Sık kullanılanlar</div>
            <div class="d-flex flex-wrap gap-1 mb-2" id="emojiRecent"></div>

            <div class="small text-muted mb-2">Emojiler</div>
            <div class="d-flex flex-wrap gap-1" id="emojiGrid">
              <?php
                $emojis = ['😀','😁','😂','🤣','😊','😍','😘','😎','🤩','😇','😅','😉','😌','🙂','🙃','🤗',
                           '🔥','✨','💯','✅','🎉','📌','📣','💬','📸','🎬','🎥','🎵','🧡','❤️','💙','💚',
                           '🙏','👏','🤝','🚀','⭐','⚡','📍','🕒','🗓️','🔗','🛒','🎁','🍀','🌸','☀️','🌙'];
                foreach ($emojis as $e):
              ?>
                <button type="button" class="btn btn-emoji" data-emoji="<?= esc($e) ?>"><?= esc($e) ?></button>
              <?php endforeach; ?>
            </div>

            <div class="mt-2 pt-2 border-top d-flex justify-content-between align-items-center">
              <div class="small text-muted">Seçtiğin emoji otomatik eklenir</div>
              <button type="button" class="btn btn-sm btn-link text-decoration-none" id="emojiClearRecent">Temizle</button>
            </div>
          </div>
        </div>
      </div>

      <textarea
        name="base_text"
        class="form-control"
        rows="6"
        id="captionText"
        placeholder="Gönderinin açıklamasını yaz...
#hashtag #örnek #kampanya"
      ><?= esc($prefill['base_text'] ?? '') ?></textarea>

      <div class="form-text">
        Bu açıklama seçtiğin platformlara göre kullanılır. (YouTube vb. için platform ayarlarında ekstra alanlar açılır.)
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label">Medya</label>

      <?php if (!empty($prefill) && !empty($prefill['media_path'])): ?>
        <div class="border rounded p-2 bg-light">
          <div class="small text-muted mb-2">Şablondan üretilen medya:</div>

          <?php if (($prefill['media_type'] ?? '') === 'image'): ?>
            <img src="<?= base_url($prefill['media_path']) ?>" style="max-width: 100%; height: auto; border-radius: 10px; display:block;">
          <?php elseif (($prefill['media_type'] ?? '') === 'video'): ?>
            <video controls style="max-width:100%; border-radius:10px; display:block;">
              <source src="<?= base_url($prefill['media_path']) ?>">
            </video>
          <?php else: ?>
            <div class="text-danger small">Medya tipi bulunamadı.</div>
          <?php endif; ?>

          <div class="small text-muted mt-2">
            Bu içerik hazır. Yeni dosya seçmek istersen aşağıdan değiştirebilirsin.
          </div>

          <div class="mt-2">
            <input type="file" name="media" class="form-control" accept="image/*,video/*">
          </div>
        </div>
      <?php else: ?>
        <input type="file" name="media" class="form-control" accept="image/*,video/*">
        <div class="form-text">
          Bazı platformlarda paylaşım için görsel veya video gerekir.
        </div>
      <?php endif; ?>
    </div>

  </div>
</div>
