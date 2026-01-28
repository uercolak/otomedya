<?= $this->extend('layouts/panel') ?>
<?= $this->section('content') ?>

<?php
// -------------------- Helpers --------------------
if (!function_exists('ui_dt_tr')) {
  function ui_dt_tr($val): string {
    if (empty($val)) return '—';
    try {
      $dt = new DateTime(is_string($val) ? $val : (string)$val);
      $months = [1=>'Ocak',2=>'Şubat',3=>'Mart',4=>'Nisan',5=>'Mayıs',6=>'Haziran',7=>'Temmuz',8=>'Ağustos',9=>'Eylül',10=>'Ekim',11=>'Kasım',12=>'Aralık'];
      $m = (int)$dt->format('n');
      $monthName = $months[$m] ?? $dt->format('m');
      return $dt->format('j ') . $monthName . $dt->format(' Y') . ' • ' . $dt->format('H:i');
    } catch (Throwable $e) {
      return esc((string)$val);
    }
  }
}

if (!function_exists('is_url')) {
  function is_url(string $s): bool { return preg_match('~^https?://~i', $s) === 1; }
}

if (!function_exists('guess_media_type')) {
  function guess_media_type(string $url): string {
    $u = strtolower(parse_url($url, PHP_URL_PATH) ?: $url);
    if (preg_match('~\.(mp4|mov|m4v|webm|ogg)$~i', $u)) return 'video';
    if (preg_match('~\.(jpg|jpeg|png|gif|webp)$~i', $u)) return 'image';
    // youtube tespit (linkten)
    if (preg_match('~(youtube\.com|youtu\.be)~i', $url)) return 'youtube';
    return 'unknown';
  }
}

// -------------------- Vars --------------------
$platform = strtoupper((string)($row['platform'] ?? ''));

if (!empty($row['sa_username'])) $accLabel = '@' . $row['sa_username'];
elseif (!empty($row['sa_name'])) $accLabel = (string)$row['sa_name'];
else $accLabel = 'Hesap #' . (int)($row['account_id'] ?? 0);

$contentTitle = trim((string)($row['content_title'] ?? ''));
$contentId    = (int)($row['content_id'] ?? 0);
$contentLabel = $contentTitle !== '' ? $contentTitle : ('İçerik #' . $contentId);

$status   = (string)($row['status'] ?? '');
$remoteId = (string)($row['remote_id'] ?? '');
$isUrl    = is_url($remoteId);

$scheduleAt  = (string)($row['schedule_at'] ?? '');
$publishedAt = (string)($row['published_at'] ?? '');
if ($publishedAt === '' && $status === 'published') {
  $publishedAt = (string)($row['updated_at'] ?? '');
}

// -------------------- Media extraction (robust) --------------------
$mediaUrl  = '';
$thumbUrl  = '';
$permalink = '';

$meta = [];
if (!empty($row['meta_json'])) {
  $tmp = json_decode((string)$row['meta_json'], true);
  if (is_array($tmp)) $meta = $tmp;
}

// 1) Direct columns (varsa)
foreach (['content_media_url','media_url','content_media','media','file_url','asset_url'] as $k) {
  $v = trim((string)($row[$k] ?? ''));
  if ($v !== '' && is_url($v)) { $mediaUrl = $v; break; }
}

// 2) JSON alanları (varsa)
if ($mediaUrl === '') {
  foreach (['content_media_json','media_json','content_json'] as $k) {
    $raw = $row[$k] ?? '';
    if (!$raw) continue;
    $arr = json_decode((string)$raw, true);
    if (!is_array($arr)) continue;

    // olası anahtarlar
    foreach (['media_url','url','src','file','path'] as $mk) {
      $v = trim((string)($arr[$mk] ?? ''));
      if ($v !== '' && is_url($v)) { $mediaUrl = $v; break 2; }
    }
    // dizi ise ilk eleman
    if (isset($arr[0]) && is_array($arr[0])) {
      foreach (['media_url','url','src','file','path'] as $mk) {
        $v = trim((string)($arr[0][$mk] ?? ''));
        if ($v !== '' && is_url($v)) { $mediaUrl = $v; break 2; }
      }
    }
  }
}

// 3) meta_json içinden olası alanlar
if ($permalink === '' && !empty($meta['meta']['permalink'])) {
  $permalink = (string)$meta['meta']['permalink'];
}
if ($mediaUrl === '' && !empty($meta['meta']['media_url'])) {
  $mediaUrl = (string)$meta['meta']['media_url'];
}
if ($thumbUrl === '' && !empty($meta['meta']['thumbnail_url'])) {
  $thumbUrl = (string)$meta['meta']['thumbnail_url'];
}

// 4) Published ise remoteId URL olabilir (permalink gibi)
if ($permalink === '' && $isUrl) $permalink = $remoteId;

// 5) thumb yoksa media url ile aynı olabilir (image ise)
if ($thumbUrl === '' && $mediaUrl !== '' && guess_media_type($mediaUrl) === 'image') {
  $thumbUrl = $mediaUrl;
}

$mediaType = $mediaUrl !== '' ? guess_media_type($mediaUrl) : '';
?>

<style>
  .btn-brand{
    background: linear-gradient(90deg, #6a5cff, #ff4fd8);
    border: 0;
    color: #fff;
    font-weight: 600;
  }
  .btn-brand:hover{ filter: brightness(0.97); color:#fff; }

  .btn-brand-outline{
    border: 1px solid rgba(106,92,255,.45);
    color: #6a5cff;
    background: #fff;
    font-weight: 600;
  }
  .btn-brand-outline:hover{
    background: linear-gradient(90deg, rgba(106,92,255,.12), rgba(255,79,216,.12));
    color:#4a3cff;
  }

  .pill{
    display:inline-flex; align-items:center; gap:8px;
    padding:6px 10px; border-radius:999px;
    background: rgba(0,0,0,.04);
    border: 1px solid rgba(0,0,0,.06);
    font-size: 12px;
  }

  .media-card{
    border-radius: 16px;
    overflow: hidden;
    border: 1px solid rgba(0,0,0,.06);
    background: #fff;
  }
  .media-placeholder{
    min-height: 280px;
    display:flex; align-items:center; justify-content:center;
    background: linear-gradient(135deg, rgba(106,92,255,.06), rgba(255,79,216,.06));
    color: rgba(0,0,0,.55);
    text-align:center;
    padding: 30px;
  }
  .media-meta{
    padding: 14px 16px;
    border-top: 1px solid rgba(0,0,0,.06);
    background: #fff;
  }

  .content-box{
    background: rgba(0,0,0,.03);
    border: 1px solid rgba(0,0,0,.06);
    border-radius: 14px;
    padding: 14px;
    white-space: pre-wrap;
  }
</style>

<!-- Header -->
<div class="d-flex justify-content-between align-items-start mb-3">
  <div>
    <div class="fw-semibold" style="font-size:18px;">
      Paylaşım #<?= (int)$row['id'] ?>
    </div>
    <div class="text-muted small">
      <?= esc($platform) ?> · <?= esc($accLabel) ?> · <?= esc($contentLabel) ?>
    </div>

    <div class="d-flex gap-2 flex-wrap mt-2">
      <span class="pill">Platform: <strong><?= esc($platform) ?></strong></span>
      <span class="pill">Hesap: <strong><?= esc($accLabel) ?></strong></span>
      <span class="pill">Durum: <?= view('partials/status_badge', ['status' => $status]) ?></span>
    </div>
  </div>

  <div class="d-flex gap-2">
    <?php if ($status === 'published' && $permalink !== ''): ?>
      <a class="btn btn-brand-outline" href="<?= esc($permalink) ?>" target="_blank" rel="noopener">
        Platformda görüntüle
      </a>
    <?php endif; ?>
    <a class="btn btn-outline-secondary" href="<?= site_url('panel/publishes') ?>">Geri</a>
  </div>
</div>

<div class="row g-3">
  <!-- LEFT: Media Preview -->
  <div class="col-lg-7">
    <div class="media-card">
      <?php if ($mediaUrl !== '' && $mediaType === 'video'): ?>
        <div class="ratio ratio-16x9">
          <video src="<?= esc($mediaUrl) ?>" controls playsinline style="width:100%; height:100%; object-fit:cover;"></video>
        </div>

      <?php elseif ($mediaUrl !== '' && $mediaType === 'image'): ?>
        <div class="ratio ratio-16x9">
          <img src="<?= esc($mediaUrl) ?>" alt="Ön izleme" style="width:100%; height:100%; object-fit:cover;">
        </div>

      <?php elseif ($mediaUrl !== '' && $mediaType === 'youtube'): ?>
        <?php
          // youtube embed dönüşümü (basit)
          $embed = $mediaUrl;
          // youtu.be/ID
          if (preg_match('~youtu\.be/([a-zA-Z0-9_-]{6,})~', $mediaUrl, $m)) {
            $embed = 'https://www.youtube.com/embed/' . $m[1];
          }
          // youtube.com/watch?v=ID
          if (preg_match('~v=([a-zA-Z0-9_-]{6,})~', $mediaUrl, $m)) {
            $embed = 'https://www.youtube.com/embed/' . $m[1];
          }
        ?>
        <div class="ratio ratio-16x9">
          <iframe src="<?= esc($embed) ?>" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
        </div>

      <?php else: ?>
        <div class="media-placeholder">
          <div>
            <div class="fw-semibold mb-1">Ön izleme bulunamadı</div>
            <div class="small">
              Bu paylaşımın görsel/video bağlantısı kayıtlı değilse burada gösterilemeyebilir.<br>
              <?php if ($status === 'published' && $permalink !== ''): ?>
                İsterseniz “Platformda görüntüle” ile doğrudan açabilirsiniz.
              <?php else: ?>
                Paylaşım yayınlandığında platform bağlantısı oluşacaktır.
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endif; ?>

      <div class="media-meta d-flex justify-content-between align-items-center">
        <div>
          <div class="fw-semibold"><?= esc($contentLabel) ?></div>
          <div class="text-muted small">
            Planlanan: <?= esc(ui_dt_tr($scheduleAt)) ?> · Yayınlanan: <?= esc(ui_dt_tr($publishedAt)) ?>
          </div>
        </div>

        <?php if ($thumbUrl !== '' && $mediaUrl === '' ): ?>
          <a class="btn btn-brand-outline btn-sm" href="<?= esc($thumbUrl) ?>" target="_blank" rel="noopener">Görseli aç</a>
        <?php endif; ?>
      </div>
    </div>

    <?php if (!empty($row['error'])): ?>
      <div class="alert alert-danger mt-3 mb-0">
        <strong>Sorun:</strong> <?= esc((string)$row['error']) ?>
      </div>
    <?php endif; ?>
  </div>

  <!-- RIGHT: Details -->
  <div class="col-lg-5">
    <div class="card mb-3">
      <div class="card-body">
        <div class="row g-3">
          <div class="col-6">
            <div class="text-muted small">Durum</div>
            <div><?= view('partials/status_badge', ['status' => $status]) ?></div>
          </div>
          <div class="col-6">
            <div class="text-muted small">Platform</div>
            <div class="fw-semibold"><?= esc($platform) ?></div>
          </div>
          <div class="col-12">
            <div class="text-muted small">Hesap</div>
            <div class="fw-semibold"><?= esc($accLabel) ?></div>
          </div>

          <div class="col-12">
            <div class="text-muted small">Paylaşım bağlantısı / Kimlik</div>

            <?php if ($remoteId === ''): ?>
              <div class="text-muted">—</div>
            <?php elseif ($isUrl): ?>
              <a href="<?= esc($remoteId) ?>" target="_blank" rel="noopener" class="fw-semibold text-decoration-none">
                Bağlantıyı aç
              </a>
              <div class="text-muted small mt-1 text-break"><?= esc($remoteId) ?></div>
            <?php else: ?>
              <div class="fw-semibold text-break"><?= esc($remoteId) ?></div>
            <?php endif; ?>
          </div>

          <div class="col-6">
            <div class="text-muted small">Planlanan</div>
            <div class="fw-semibold"><?= esc(ui_dt_tr($scheduleAt)) ?></div>
          </div>
          <div class="col-6">
            <div class="text-muted small">Yayınlanan</div>
            <div class="fw-semibold"><?= esc(ui_dt_tr($publishedAt)) ?></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Hızlı işlemler -->
    <div class="card">
      <div class="card-body">
        <div class="fw-semibold mb-2">Hızlı işlemler</div>

        <div class="d-grid gap-2">
          <?php if ($status === 'published' && $permalink !== ''): ?>
            <a class="btn btn-brand-outline" href="<?= esc($permalink) ?>" target="_blank" rel="noopener">
              Platformda görüntüle
            </a>
          <?php endif; ?>

          <?php if (!empty($row['content_text'])): ?>
            <button class="btn btn-brand" type="button" id="btnCopyText">
              Açıklamayı kopyala
            </button>
          <?php endif; ?>

          <a class="btn btn-outline-secondary" href="<?= site_url('panel/publishes') ?>">
            Listeye dön
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Content Text -->
<div class="card mt-3">
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-2">
      <h5 class="mb-0">İçerik</h5>
      <?php if (!empty($row['content_text'])): ?>
        <button class="btn btn-outline-secondary btn-sm" type="button" id="btnCopyText2">Kopyala</button>
      <?php endif; ?>
    </div>

    <div class="text-muted small mb-2">
      <?= $contentTitle !== '' ? esc($contentTitle) : esc('Bu paylaşım için içerik bilgisi') ?>
      <?php if ($contentId): ?>
        <span class="text-muted"> · İçerik ID: #<?= (int)$contentId ?></span>
      <?php endif; ?>
    </div>

    <?php if (!empty($row['content_text'])): ?>
      <div class="content-box" id="contentText"><?= esc((string)$row['content_text']) ?></div>
    <?php else: ?>
      <div class="text-muted">Bu içerik için metin bulunamadı.</div>
    <?php endif; ?>
  </div>
</div>

<script>
  (function(){
    function copyText(){
      var el = document.getElementById('contentText');
      if(!el) return;
      var text = el.innerText || el.textContent || '';
      if(!text.trim()) return;

      if(navigator.clipboard && window.isSecureContext){
        navigator.clipboard.writeText(text).then(function(){
          // küçük feedback
          toastInfo('Açıklama kopyalandı.');
        }).catch(function(){ fallbackCopy(text); });
      }else{
        fallbackCopy(text);
      }
    }

    function fallbackCopy(text){
      var ta = document.createElement('textarea');
      ta.value = text;
      ta.style.position = 'fixed';
      ta.style.left = '-9999px';
      document.body.appendChild(ta);
      ta.select();
      try { document.execCommand('copy'); toastInfo('Açıklama kopyalandı.'); } catch(e){}
      document.body.removeChild(ta);
    }

    function toastInfo(msg){
      // Eğer projede hazır toast altyapın varsa burayı ona bağlarız.
      // Şimdilik minik bootstrap alert gibi davranalım:
      var div = document.createElement('div');
      div.className = 'alert alert-success';
      div.style.position = 'fixed';
      div.style.right = '16px';
      div.style.bottom = '16px';
      div.style.zIndex = 9999;
      div.style.minWidth = '220px';
      div.innerHTML = msg;
      document.body.appendChild(div);
      setTimeout(function(){ div.remove(); }, 1600);
    }

    var b1 = document.getElementById('btnCopyText');
    var b2 = document.getElementById('btnCopyText2');
    if(b1) b1.addEventListener('click', copyText);
    if(b2) b2.addEventListener('click', copyText);
  })();
</script>

<?= $this->endSection() ?>
