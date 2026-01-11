<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<?php
  helper('catalog');
  $fmt = new \App\Services\LogFormatter();
?>

<div class="page-header">
  <div>
    <h1 class="page-title">İşlem Kayıtları</h1>
    <p class="text-muted mb-4">Sistemde olan önemli işlemler ve sonuçları (başarılı / uyarı / hata)</p>
  </div>
</div>

<form method="get" class="card mb-3">
  <div class="card-body">
    <div class="row g-2">
      <div class="col-md-4">
        <label class="form-label small text-muted mb-1">Arama</label>
        <input name="q" value="<?= esc($filters['q']) ?>" class="form-control" placeholder="Mesaj veya detay içinde ara...">
      </div>

      <div class="col-md-2">
        <label class="form-label small text-muted mb-1">Önem Seviyesi</label>
        <select name="level" class="form-select">
          <option value="">Hepsi</option>
          <?php foreach ($levels as $lv): ?>
            <option value="<?= esc($lv) ?>" <?= ($filters['level'] === $lv) ? 'selected' : '' ?>>
              <?= esc(log_level_label($lv)) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-2">
        <label class="form-label small text-muted mb-1">Kaynak</label>
        <select name="channel" class="form-select">
          <option value="">Hepsi</option>
          <?php foreach (($channels ?? []) as $ch): ?>
            <option value="<?= esc($ch) ?>" <?= ($filters['channel'] === $ch) ? 'selected' : '' ?>>
              <?= esc(log_channel_label($ch)) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-2">
        <label class="form-label small text-muted mb-1">Kullanıcı ID</label>
        <input name="user_id" value="<?= esc($filters['user_id']) ?>" class="form-control" placeholder="Örn: 12">
      </div>

      <div class="col-md-2">
        <label class="form-label small text-muted mb-1">Kullanıcı (Ad/E-posta)</label>
        <input name="user" value="<?= esc($filters['user'] ?? '') ?>" class="form-control" placeholder="Örn: ahmet / ahmet@...">
      </div>

      <div class="col-md-2">
        <label class="form-label small text-muted mb-1">Başlangıç</label>
        <input type="date" name="date_from" value="<?= esc($filters['date_from']) ?>" class="form-control">
      </div>

      <div class="col-md-2">
        <label class="form-label small text-muted mb-1">Bitiş</label>
        <input type="date" name="date_to" value="<?= esc($filters['date_to']) ?>" class="form-control">
      </div>
    </div>

    <div class="d-flex gap-2 mt-3">
      <button class="btn btn-primary" type="submit">Filtrele</button>
      <a class="btn btn-outline-secondary" href="<?= site_url('admin/logs') ?>">Sıfırla</a>
    </div>
  </div>
</form>

<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0 align-middle">
        <thead>
          <tr>
            <th style="width:90px;">ID</th>
            <th style="width:150px;">Önem</th>
            <th style="width:170px;">Kaynak</th>
            <th>Ne oldu?</th>
            <th style="width:260px;">Kimin adına?</th>
            <th style="width:190px;">Tarih</th>
            <th style="width:40px;"></th>
          </tr>
        </thead>

        <tbody>
        <?php if (!$rows): ?>
          <tr><td colspan="7" class="text-center py-4 text-muted">Kayıt bulunamadı.</td></tr>
        <?php endif; ?>

        <?php foreach ($rows as $r): ?>
          <?php
            $level = (string)($r['level'] ?? '');
            $badge = match($level) {
              'error'   => 'badge-soft-danger',
              'warning' => 'badge-soft-warning',
              default   => 'badge-soft-info',
            };

            $ctx = (string)($r['context_json'] ?? '');

            $levelTr   = log_level_label($level);
            $channel   = (string)($r['channel'] ?? '');
            $channelTr = log_channel_label($channel);

            $userName  = (string)($r['user_name'] ?? '');
            $userEmail = (string)($r['user_email'] ?? '');
          ?>

          <tr class="log-row row-link"
              role="button"
              tabindex="0"
              data-bs-toggle="modal"
              data-bs-target="#logModal"
              data-id="<?= esc($r['id']) ?>"
              data-level="<?= esc($levelTr) ?>"
              data-level-raw="<?= esc($level) ?>"
              data-human="<?= esc($fmt->human($r)) ?>"
              data-title="<?= esc($fmt->title($r)) ?>"
              data-channel="<?= esc($channelTr) ?>"
              data-channel-raw="<?= esc($channel) ?>"
              data-message="<?= esc($fmt->title($r)) ?>"
              data-user-id="<?= esc((string)($r['user_id'] ?? '')) ?>"
              data-user-name="<?= esc($userName) ?>"
              data-user-email="<?= esc($userEmail) ?>"
              data-ip="<?= esc((string)($r['ip'] ?? '')) ?>"
              data-ua="<?= esc((string)($r['user_agent'] ?? '')) ?>"
              data-created="<?= esc((string)$r['created_at']) ?>"
              data-context="<?= esc($ctx) ?>"
          >
            <td class="text-muted"><?= esc($r['id']) ?></td>

            <td>
              <div class="d-flex flex-column">
                <span class="badge <?= $badge ?> align-self-start"><?= esc($levelTr) ?></span>
                <span class="text-muted small mt-1"><?= esc(log_level_hint($level)) ?></span>
              </div>
            </td>

            <td>
              <div class="d-flex flex-column">
                <span class="fw-semibold"><?= esc($channelTr) ?></span>
                <span class="text-muted small"><?= esc(log_channel_hint($channel)) ?></span>
              </div>
            </td>

            <td class="text-truncate" style="max-width:620px;">
              <?= esc($fmt->title($r)) ?>
            </td>

            <td>
              <?php if (!empty($r['user_id'])): ?>
                <div class="fw-semibold"><?= esc($userName ?: ('#' . $r['user_id'])) ?></div>
                <?php if ($userEmail): ?>
                  <div class="text-muted small"><?= esc($userEmail) ?></div>
                <?php endif; ?>
              <?php else: ?>
                <span class="text-muted">—</span>
              <?php endif; ?>
            </td>

            <td class="text-muted"><?= esc((string)$r['created_at']) ?></td>

            <td class="text-end pe-3">
              <span class="chev">›</span>
            </td>
          </tr>

        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="card-footer d-flex justify-content-between align-items-center">
    <div class="text-muted small">
      <?php $details = $pager ? $pager->getDetails('logs') : null; ?>
      <?= $details ? esc($details['total']) . ' kayıt' : '' ?>
    </div>
    <div>
      <?= $pager ? $pager->links('logs', 'bootstrap_full') : '' ?>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="logModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <h5 class="modal-title mb-0">Kayıt Detayı</h5>
          <div class="text-muted small">Bu kayıt ne zaman, kim için ve hangi kaynaktan üretildi?</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
      </div>

      <div class="modal-body">
        <div class="row g-2 mb-3">
          <div class="col-md-2"><strong>ID:</strong> <span id="m-id"></span></div>
          <div class="col-md-2"><strong>Önem:</strong> <span id="m-level"></span></div>
          <div class="col-md-3"><strong>Kaynak:</strong> <span id="m-channel"></span></div>
          <div class="col-md-3"><strong>Kullanıcı:</strong> <span id="m-user"></span></div>
          <div class="col-md-2"><strong>Tarih:</strong> <span id="m-created"></span></div>

          <div class="col-md-12 mt-1" id="m-job-wrap" style="display:none;">
            <strong>İlgili İş:</strong>
            <a href="#" id="m-job-link" class="btn btn-sm btn-outline-primary ms-2">İş #</a>
          </div>
        </div>

        <div class="mb-3">
          <strong>Açıklama</strong>
          <div class="border rounded p-2 bg-light" id="m-message"></div>
        </div>

        <div class="mb-3">
          <strong>İstek Bilgisi</strong>
          <div class="small text-muted">IP: <span id="m-ip"></span></div>
          <div class="small text-muted">Tarayıcı: <span id="m-ua"></span></div>
        </div>

        <div class="alert alert-light border mb-3">
          <div class="fw-semibold mb-1">Bu kayıt ne anlama gelir?</div>
          <div class="text-muted small" id="m-human"></div>
        </div>

        <div class="accordion" id="techAcc">
          <div class="accordion-item">
            <h2 class="accordion-header" id="techHead">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#techBody" aria-expanded="false" aria-controls="techBody">
                Teknik Detaylar (sistem yöneticisi için)
              </button>
            </h2>
            <div id="techBody" class="accordion-collapse collapse" aria-labelledby="techHead" data-bs-parent="#techAcc">
              <div class="accordion-body">
                <div class="d-flex justify-content-between align-items-center">
                  <strong>Detay (JSON)</strong>
                  <button type="button" class="btn btn-sm btn-outline-secondary" id="btnCopyJson">Kopyala</button>
                </div>

                <div class="mt-1 text-muted small" id="m-worker"></div>

                <pre class="border rounded p-2 bg-light mb-0 mt-2" id="m-context" style="white-space:pre-wrap;"></pre>
              </div>
            </div>
          </div>
        </div>

      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
      </div>
    </div>
  </div>
</div>

<style>
  .row-link { cursor:pointer; }
  .chev { color:#9ca3af; font-size:18px; transition:.15s; }
  tr.row-link:hover .chev { transform: translateX(4px); color:#6b7280; }

  .badge-soft-info{background:rgba(59,130,246,.12);color:#1d4ed8;border:1px solid rgba(59,130,246,.18);font-weight:600;}
  .badge-soft-warning{background:rgba(245,158,11,.14);color:#92400e;border:1px solid rgba(245,158,11,.22);font-weight:600;}
  .badge-soft-danger{background:rgba(239,68,68,.12);color:#b91c1c;border:1px solid rgba(239,68,68,.20);font-weight:600;}
</style>

<script>
(function () {
  const modal = document.getElementById('logModal');
  if (!modal) return;

  const mId      = document.getElementById('m-id');
  const mLevel   = document.getElementById('m-level');
  const mChannel = document.getElementById('m-channel');
  const mMessage = document.getElementById('m-message');
  const mUser    = document.getElementById('m-user');
  const mIp      = document.getElementById('m-ip');
  const mUa      = document.getElementById('m-ua');
  const mCreated = document.getElementById('m-created');
  const mContext = document.getElementById('m-context');
  const mWorker  = document.getElementById('m-worker');
  const mHuman   = document.getElementById('m-human');

  const mJobWrap = document.getElementById('m-job-wrap');
  const mJobLink = document.getElementById('m-job-link');

  modal.addEventListener('show.bs.modal', function (event) {
    const el = event.relatedTarget;

    const id         = el.getAttribute('data-id') || '';
    const levelTr    = el.getAttribute('data-level') || '';
    const levelRaw   = el.getAttribute('data-level-raw') || '';
    const channelTr  = el.getAttribute('data-channel') || '';
    const channelRaw = el.getAttribute('data-channel-raw') || '';
    const title      = el.getAttribute('data-title') || '';
    const human      = el.getAttribute('data-human') || '';

    const userId    = el.getAttribute('data-user-id') || '';
    const userName  = el.getAttribute('data-user-name') || '';
    const userEmail = el.getAttribute('data-user-email') || '';

    const ip         = el.getAttribute('data-ip') || '';
    const ua         = el.getAttribute('data-ua') || '';
    const created    = el.getAttribute('data-created') || '';
    const contextRaw = el.getAttribute('data-context') || '';

    let userText = '—';
    if (userName || userEmail) {
      userText = userName ? userName : ('#' + userId);
      if (userEmail) userText += ' (' + userEmail + ')';
    } else if (userId) {
      userText = '#' + userId;
    }

    mId.textContent = id;
    mLevel.textContent = levelTr;
    mChannel.textContent = channelTr;
    mMessage.textContent = title;
    mHuman.textContent = human;

    mUser.textContent = userText;
    mIp.textContent = ip || '—';
    mUa.textContent = ua || '—';
    mCreated.textContent = created;

    let pretty = contextRaw;
    let workerLine = '';

    if (mJobWrap) mJobWrap.style.display = 'none';
    if (mJobLink) { mJobLink.textContent = 'İş #'; mJobLink.href = '#'; }

    try {
      if (contextRaw) {
        const obj = JSON.parse(contextRaw);
        pretty = JSON.stringify(obj, null, 2);

        if (obj.worker || obj.pid) {
          workerLine = 'Çalıştıran servis: ' + (obj.worker ?? '-') + (obj.pid ? (' | PID: ' + obj.pid) : '');
        }

        const jobId = obj.job_id ?? obj.jobId ?? null;
        if (jobId && mJobWrap && mJobLink) {
          mJobWrap.style.display = '';
          mJobLink.textContent = 'İş #' + jobId;
          mJobLink.href = "<?= site_url('admin/jobs/') ?>" + jobId;
        }
      }
    } catch (e) {}

    mContext.textContent = pretty || '—';
    mWorker.textContent = workerLine;
  });

  document.addEventListener('keydown', function (e) {
    const row = e.target.closest('.log-row');
    if (!row) return;

    if (e.key === 'Enter' || e.key === ' ') {
      e.preventDefault();
      row.click();
    }
  });

  if (mJobLink) {
    mJobLink.addEventListener('click', function(e){
      if (mJobLink.getAttribute('href') === '#') e.preventDefault();
    });
  }

  const btnCopy = document.getElementById('btnCopyJson');
  if (btnCopy) {
    btnCopy.addEventListener('click', async function () {
      const text = mContext?.textContent || '';
      if (!text || text === '—') return;

      try {
        await navigator.clipboard.writeText(text);
        btnCopy.textContent = 'Kopyalandı';
        setTimeout(() => btnCopy.textContent = 'Kopyala', 1200);
      } catch (e) {
        const ta = document.createElement('textarea');
        ta.value = text;
        document.body.appendChild(ta);
        ta.select();
        document.execCommand('copy');
        document.body.removeChild(ta);

        btnCopy.textContent = 'Kopyalandı';
        setTimeout(() => btnCopy.textContent = 'Kopyala', 1200);
      }
    });
  }
})();
</script>

<?= $this->endSection() ?>
