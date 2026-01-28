<?= $this->extend('layouts/panel') ?>
<?= $this->section('content') ?>

<style>
  /* dashboard sadece kendi içinde – layout’a dokunmuyoruz */
  .dash-grid{ display:grid; grid-template-columns: repeat(12, minmax(0, 1fr)); gap:14px; }

  .dash-card{
    background:#fff;
    border:1px solid rgba(0,0,0,.06);
    border-radius:18px;
    box-shadow: 0 14px 38px rgba(0,0,0,.05);
    overflow:hidden;
  }
  .dash-pad{ padding:14px; }

  /* soft tipografi */
  .dash-title{ font-weight:750; letter-spacing:-.2px; margin:0; color: rgba(17,24,39,.82); }
  .dash-muted{ color: rgba(17,24,39,.55); font-weight:500; }

  .dash-kpi{ font-size:30px; font-weight:800; letter-spacing:-.5px; color: rgba(17,24,39,.78); }

  .dash-chip{
    display:inline-flex; align-items:center; gap:8px;
    padding:7px 10px;
    border-radius:999px;
    border:1px solid rgba(124,58,237,.18);
    background: rgba(124,58,237,.07);
    color: rgba(124,58,237,.92);
    font-weight:650;
    font-size: 12px;
    white-space: nowrap;
  }

  .dash-list{ display:flex; flex-direction:column; gap:10px; margin-top:10px; }
  .dash-item{
    display:flex; align-items:flex-start; justify-content:space-between; gap:12px;
    padding:10px 12px;
    border-radius:14px;
    border:1px solid rgba(0,0,0,.06);
    background:#fff;
  }
  .dash-item b{ display:block; font-size:13px; font-weight:700; color: rgba(17,24,39,.80); }
  .dash-item small{ color: rgba(17,24,39,.55); }

  .pill{
    display:inline-flex; align-items:center; gap:6px;
    padding:6px 10px; border-radius:999px;
    font-size:12px; font-weight:650;
    border:1px solid rgba(0,0,0,.06);
    background: rgba(0,0,0,.03);
    white-space:nowrap;
    color: rgba(17,24,39,.72);
  }
  .pill.ok{ background: rgba(34,197,94,.10); border-color: rgba(34,197,94,.22); color: rgba(22,101,52,.95); }
  .pill.bad{ background: rgba(239,68,68,.10); border-color: rgba(239,68,68,.22); color: rgba(127,29,29,.95); }
  .pill.wait{ background: rgba(124,58,237,.10); border-color: rgba(124,58,237,.18); color: rgba(92,45,165,.95); }
  .pill.gray{ background: rgba(2,6,23,.04); border-color: rgba(2,6,23,.08); color: rgba(17,24,39,.72); }

  .btn-soft{
    border-radius: 999px;
    padding: 9px 12px;
    font-weight: 700;
    font-size: 13px;
    border: 1px solid rgba(0,0,0,.08);
    background: #fff;
    color: rgba(17,24,39,.78);
  }
  .btn-soft:hover{
    background: rgba(124,58,237,.06);
    border-color: rgba(124,58,237,.14);
    color: rgba(17,24,39,.86);
  }
  .btn-grad{
    border:0;
    border-radius:999px;
    padding: 9px 14px;
    font-weight: 800;
    font-size: 13px;
    color:#fff;
    background: linear-gradient(135deg, #7c3aed, #ec4899);
    box-shadow: 0 12px 26px rgba(124,58,237,.16);
  }

  .mini-cal{
    margin-top:12px;
    border-radius:16px;
    border:1px solid rgba(0,0,0,.06);
    overflow:hidden;
  }
  .mini-cal-head{
    display:flex; align-items:center; justify-content:space-between;
    padding:11px 12px;
    background: linear-gradient(135deg, rgba(124,58,237,.08), rgba(236,72,153,.06));
    font-weight:800;
    color: rgba(17,24,39,.72);
  }
  .mini-cal-grid{
    display:grid;
    grid-template-columns: repeat(7, 1fr);
    background:#fff;
  }
  .mini-cal-dow, .mini-cal-day{
    padding:10px 8px;
    border-top:1px solid rgba(0,0,0,.06);
    border-right:1px solid rgba(0,0,0,.06);
    min-height: 44px;
    position:relative;
    font-size:12px;
    color: rgba(17,24,39,.72);
  }
  .mini-cal-dow{ background:#fafafa; font-weight:800; text-align:center; min-height:auto; color: rgba(17,24,39,.60); }
  .mini-cal-day:last-child, .mini-cal-dow:last-child{ border-right:none; }
  .mini-cal-day.is-off{ background:#fcfcfc; color: rgba(17,24,39,.25); }

  .mini-cal-badge{
    position:absolute; top:8px; right:8px;
    min-width:20px; height:20px;
    padding:0 6px;
    border-radius:999px;
    display:inline-flex; align-items:center; justify-content:center;
    font-weight:800;
    font-size:11px;
    color: rgba(124,58,237,.95);
    background: rgba(124,58,237,.12);
    border:1px solid rgba(124,58,237,.18);
  }

  @media (max-width: 1200px){ .dash-grid{ grid-template-columns: repeat(6, minmax(0,1fr)); } }
  @media (max-width: 768px){ .dash-grid{ grid-template-columns: repeat(1, minmax(0,1fr)); } }

  .dash-item{
  transition: transform .12s ease, box-shadow .12s ease, border-color .12s ease;
    }
    .dash-item:hover{
    transform: translateY(-1px);
    box-shadow: 0 10px 22px rgba(0,0,0,.06);
    border-color: rgba(124,58,237,.18);
    }
</style>

<?php
  $showDebugIds = (ENVIRONMENT !== 'production');

  $plannedThisWeek = (int)($plannedThisWeek ?? 0);
  $accountsCount   = (int)($accountsCount ?? 0);
  $templatesCount  = (int)($templatesCount ?? 0);

  $upcoming  = $upcoming ?? [];
  $recent    = $recent ?? [];
  $accounts  = $accounts ?? [];
  $templates = $templates ?? [];   

  $platformLabel = function($p){
    $p = strtoupper((string)$p);
    return match($p){
      'INSTAGRAM' => 'Instagram',
      'FACEBOOK'  => 'Facebook',
      'TIKTOK'    => 'TikTok',
      'YOUTUBE'   => 'YouTube',
      default     => $p ?: '—',
    };
  };

  $scopeLabel = function($k){
    $k = (string)$k;
    return match($k){
      'instagram' => 'Instagram',
      'facebook'  => 'Facebook',
      'tiktok'    => 'TikTok',
      'youtube'   => 'YouTube',
      'universal' => 'Evrensel',
      default     => $k !== '' ? ucfirst($k) : 'Hepsi',
    };
  };

  $statusPill = function($s){
    $s = (string)$s;
    return match($s){
      'published' => ['Yayınlandı', 'ok'],
      'failed'    => ['Hata', 'bad'],
      'canceled'  => ['İptal', 'gray'],
      'queued'    => ['Sırada', 'wait'],
      'scheduled' => ['Planlı', 'wait'],
      default     => [$s ? ucfirst($s) : '—', 'gray'],
    };
  };

  // Template thumb: base_media_id üzerinden
  $thumbUrl = function($tpl){
    $baseMediaId = (int)($tpl['base_media_id'] ?? 0);
    return $baseMediaId > 0 ? site_url('media/' . $baseMediaId) : '';
  };

  // mini takvim
  $calNow = new \DateTime('now');
  $countsMap = $dayCounts ?? [];

  $monthNameTr = function(int $m){
    return [
      1=>'Ocak',2=>'Şubat',3=>'Mart',4=>'Nisan',5=>'Mayıs',6=>'Haziran',
      7=>'Temmuz',8=>'Ağustos',9=>'Eylül',10=>'Ekim',11=>'Kasım',12=>'Aralık'
    ][$m] ?? '';
  };

  $year = (int)$calNow->format('Y');
  $month = (int)$calNow->format('n');
  $first = new \DateTime("$year-$month-01");
  $daysInMonth = (int)$first->format('t');
  $firstDow = (int)$first->format('N');
  $padLeft = $firstDow - 1;

  $dowNames = ['Pzt','Sal','Çar','Per','Cum','Cmt','Paz'];
?>

<div class="dash-grid">

  <!-- KPI 1 -->
  <section class="dash-card" style="grid-column: span 4;">
    <div class="dash-pad d-flex align-items-start justify-content-between gap-2">
      <div>
        <div class="dash-muted" style="font-weight:750; letter-spacing:.14em; font-size:11px;">BU HAFTA PLANLI</div>
        <div class="dash-kpi"><?= $plannedThisWeek ?></div>
        <div class="dash-muted">Seçili haftadaki planlı gönderi sayısı.</div>
      </div>
      <span class="dash-chip"><i class="bi bi-calendar2-week"></i> Takvim</span>
    </div>

    <div class="dash-pad pt-0">
      <div class="dash-muted" style="font-weight:800; font-size:11px; letter-spacing:.12em;">YAKLAŞANLAR</div>

      <div class="dash-list">
        <?php if (empty($upcoming)): ?>
          <div class="dash-item">
            <div>
              <b>Planlı gönderi yok</b>
              <small>Yeni bir plan oluşturarak başlayabilirsin.</small>
            </div>
            <span class="pill gray"><i class="bi bi-info-circle"></i> Bilgi</span>
          </div>
        <?php else: ?>
          <?php foreach ($upcoming as $u): ?>
            <?php [$stLabel,$stClass] = $statusPill($u['status'] ?? ''); ?>
            <div class="dash-item">
              <div>
                <b><?= esc($u['content_title'] ?: ('#'.$u['id'])) ?></b>
                <small>
                  <?= esc($platformLabel($u['platform'] ?? '')) ?> •
                  <?= esc($u['sa_username'] ?: ($u['sa_name'] ?: '')) ?> •
                  <?= esc(date('d.m.Y H:i', strtotime((string)$u['schedule_at']))) ?>
                </small>
              </div>
              <span class="pill <?= esc($stClass) ?>"><?= esc($stLabel) ?></span>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <div class="d-flex mt-3" style="gap:10px;">
        <a href="<?= site_url('panel/publishes') ?>" class="btn-soft"><i class="bi bi-list-ul me-1"></i> Tüm planlar</a>
        <a href="<?= site_url('panel/planner') ?>" class="btn-grad"><i class="bi bi-plus-lg me-1"></i> Yeni gönderi planla</a>
      </div>
    </div>
  </section>

  <!-- KPI 2 -->
  <section class="dash-card" style="grid-column: span 4;">
    <div class="dash-pad d-flex align-items-start justify-content-between gap-2">
      <div>
        <div class="dash-muted" style="font-weight:750; letter-spacing:.14em; font-size:11px;">BAĞLI SOSYAL HESAP</div>
        <div class="dash-kpi"><?= $accountsCount ?></div>
        <div class="dash-muted">Panelde bağlı olan hesapların özeti.</div>
      </div>
      <span class="dash-chip"><i class="bi bi-share"></i> Hesaplar</span>
    </div>

    <div class="dash-pad pt-0">
      <div class="dash-list">
        <?php if (empty($accounts)): ?>
          <div class="dash-item">
            <div>
              <b>Hesap bağlı değil</b>
              <small>“Sosyal Hesaplar” menüsünden bağlayabilirsin.</small>
            </div>
            <span class="pill gray"><i class="bi bi-link-45deg"></i> Bağla</span>
          </div>
        <?php else: ?>
          <?php foreach ($accounts as $a): ?>
            <div class="dash-item">
              <div>
                <b><?= esc($platformLabel($a['platform'] ?? '')) ?></b>
                <small><?= esc($a['username'] ?: ($a['name'] ?: '')) ?></small>
              </div>
              
                <?php if ($showDebugIds): ?>
                    <span class="pill gray">ID: <?= (int)($a['id'] ?? 0) ?></span>
                <?php endif; ?>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <div class="mt-3">
        <a href="<?= site_url('panel/social-accounts') ?>" class="btn-soft"><i class="bi bi-gear me-1"></i> Hesapları yönet</a>
      </div>
    </div>
  </section>

  <!-- KPI 3 -->
  <section class="dash-card" style="grid-column: span 4;">
    <div class="dash-pad d-flex align-items-start justify-content-between gap-2">
      <div>
        <div class="dash-muted" style="font-weight:750; letter-spacing:.14em; font-size:11px;">AKTİF ŞABLON</div>
        <div class="dash-kpi"><?= $templatesCount ?></div>
        <div class="dash-muted">Kullanıma açık şablonların sayısı.</div>
      </div>
      <span class="dash-chip"><i class="bi bi-images"></i> Şablonlar</span>
    </div>

    <div class="dash-pad pt-0">
      <?php if (empty($templates)): ?>
        <div class="dash-item">
          <div>
            <b>Şablon yok</b>
            <small>Hazır şablon ekleyerek hızlı planlamayı güçlendirebilirsin.</small>
          </div>
          <span class="pill gray"><i class="bi bi-info-circle"></i> Bilgi</span>
        </div>
      <?php else: ?>
        <div class="dash-list">
          <?php foreach ($templates as $t): ?>
            <?php $thumb = $thumbUrl($t); ?>
            <div class="dash-item" style="align-items:center;">
              <div style="display:flex; gap:10px; align-items:center;">
                <div style="width:42px;height:42px;border-radius:12px;overflow:hidden;border:1px solid rgba(0,0,0,.06);background:linear-gradient(135deg, rgba(124,58,237,.10), rgba(236,72,153,.08));display:flex;align-items:center;justify-content:center;">
                  <?php if ($thumb !== ''): ?>
                    <img src="<?= esc($thumb) ?>" alt="" style="width:100%;height:100%;object-fit:cover;display:block;">
                  <?php else: ?>
                    <i class="bi bi-image" style="opacity:.55;"></i>
                  <?php endif; ?>
                </div>
                <div>
                  <b><?= esc($t['name'] ?? '') ?></b>
                  <small><?= esc($scopeLabel($t['platform_scope'] ?? '')) ?> • <?= esc($t['format_key'] ?? '') ?></small>
                </div>
              </div>
              <a class="pill gray" href="<?= site_url('panel/templates') ?>">Aç</a>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <div class="mt-3">
        <a href="<?= site_url('panel/templates') ?>" class="btn-soft"><i class="bi bi-grid me-1"></i> Şablonlara git</a>
      </div>
    </div>
  </section>

  <!-- Takvim özeti -->
  <section class="dash-card" style="grid-column: span 8;">
    <div class="dash-pad d-flex align-items-center justify-content-between">
      <div>
        <h4 class="dash-title" style="font-size:15px;">Takvim özeti</h4>
        <div class="dash-muted">Bu ayın planlı gönderilerini hızlıca gör.</div>
      </div>
      <a href="<?= site_url('panel/calendar') ?>" class="dash-chip"><i class="bi bi-lightning-charge"></i> Hızlı erişim</a>
    </div>

    <div class="dash-pad pt-0">
      <div class="mini-cal">
        <div class="mini-cal-head">
          <div><?= esc($monthNameTr($month)) ?> <?= $year ?></div>
          <div class="dash-muted" style="font-weight:750; font-size:11px;">Planlı günler rozetli</div>
        </div>

        <div class="mini-cal-grid">
          <?php foreach ($dowNames as $dn): ?>
            <div class="mini-cal-dow"><?= esc($dn) ?></div>
          <?php endforeach; ?>

          <?php for ($i=0; $i<$padLeft; $i++): ?>
            <div class="mini-cal-day is-off"></div>
          <?php endfor; ?>

          <?php for ($d=1; $d<=$daysInMonth; $d++): ?>
            <?php
              $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $d);
              $cnt = (int)($countsMap[$dateStr] ?? 0);
            ?>
            <div class="mini-cal-day">
              <a href="<?= site_url('panel/calendar?date=' . $dateStr) ?>" class="mini-cal-day" style="text-decoration:none;">
                <?= $d ?>
                <?php if ($cnt > 0): ?><span class="mini-cal-badge"><?= $cnt ?></span><?php endif; ?>
              </a>
            </div>
          <?php endfor; ?>
        </div>
      </div>

      <div class="mt-3 d-flex" style="gap:10px;">
        <a href="<?= site_url('panel/calendar') ?>" class="btn-soft"><i class="bi bi-calendar3 me-1"></i> Takvime git</a>
        <a href="<?= site_url('panel/templates') ?>" class="btn-soft"><i class="bi bi-easel2 me-1"></i> Şablonlardan oluştur</a>
      </div>
    </div>
  </section>

  <!-- Son işlemler -->
  <section class="dash-card" style="grid-column: span 4;">
    <div class="dash-pad d-flex align-items-center justify-content-between">
      <div>
        <h4 class="dash-title" style="font-size:15px;">Son işlemler</h4>
        <div class="dash-muted">Yayın / hata / iptal kayıtları.</div>
      </div>
      <a href="<?= site_url('panel/publishes') ?>" class="dash-chip"><i class="bi bi-clock-history"></i> Kayıtlar</a>
    </div>

    <div class="dash-pad pt-0">
      <div class="dash-list">
        <?php if (empty($recent)): ?>
          <div class="dash-item">
            <div>
              <b>Henüz kayıt yok</b>
              <small>Planlama yaptıkça burada son aksiyonlar görünecek.</small>
            </div>
            <span class="pill gray">Bilgi</span>
          </div>
        <?php else: ?>
          <?php foreach ($recent as $r): ?>
            <?php [$stLabel,$stClass] = $statusPill($r['status'] ?? ''); ?>
            <?php
              $dt = $r['published_at'] ?: ($r['updated_at'] ?: '');
              $dtShow = $dt ? date('d.m.Y H:i', strtotime((string)$dt)) : '';
            ?>
            <div class="dash-item">
              <div>
                <b><?= esc($r['content_title'] ?: ('#'.$r['id'])) ?></b>
                <small>
                  <?= esc($platformLabel($r['platform'] ?? '')) ?>
                  <?= ($dtShow ? ' • '.$dtShow : '') ?>
                </small>
              </div>
              <span class="pill <?= esc($stClass) ?>"><?= esc($stLabel) ?></span>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <div class="dash-muted mt-3">
        Hata alan gönderiler için buradan hızlı aksiyon alacağız (yeniden dene / detay gör).
      </div>
    </div>
  </section>

</div>

<?= $this->endSection() ?>
