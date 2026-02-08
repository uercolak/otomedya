<?= $this->extend('layouts/panel') ?>
<?= $this->section('content') ?>

<style>
  .dash-grid{ display:grid; grid-template-columns: repeat(12, minmax(0, 1fr)); gap:14px; }

  .dash-card{
    background:#fff;
    border:1px solid rgba(0,0,0,.06);
    border-radius:18px;
    box-shadow: 0 14px 38px rgba(0,0,0,.05);
    overflow:hidden;
  }
  .dash-pad{ padding:14px; }
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

  .mini-cal-marks{
    position:absolute;
    left:8px;
    right:8px;
    bottom:7px;
    display:flex;
    gap:6px;
    align-items:center;
    opacity:.95;
  }
  .mk{
    width:8px; height:8px; border-radius:999px;
    border:1px solid rgba(0,0,0,.10);
    background: rgba(0,0,0,.10);
  }
  .mk.scheduled{ background: rgba(124,58,237,.85); border-color: rgba(124,58,237,.25); }
  .mk.published{ background: rgba(34,197,94,.90); border-color: rgba(34,197,94,.25); }

  .mini-legend{
    display:flex; gap:12px; align-items:center; justify-content:flex-end;
    font-weight:650; font-size:12px;
    color: rgba(17,24,39,.62);
  }
  .mini-legend span{ display:inline-flex; gap:6px; align-items:center; }

  @media (max-width: 1200px){ .dash-grid{ grid-template-columns: repeat(6, minmax(0,1fr)); } }
  @media (max-width: 768px){ .dash-grid{ grid-template-columns: repeat(1, minmax(0,1fr)); } }

  .wiz-wrap{ display:flex; flex-direction:column; gap:12px; }
  .wiz-head{ display:flex; align-items:flex-start; justify-content:space-between; gap:12px; }
  .wiz-progress{
    height:10px; border-radius:999px; overflow:hidden;
    background: rgba(0,0,0,.06);
    border:1px solid rgba(0,0,0,.06);
  }
  .wiz-progress > span{
    display:block; height:100%;
    width:0%;
    background: linear-gradient(135deg, #7c3aed, #ec4899);
  }
  .wiz-steps{ display:flex; flex-direction:column; gap:10px; }
  .wiz-step{
    display:flex; align-items:flex-start; justify-content:space-between; gap:12px;
    padding:12px 12px;
    border-radius:16px;
    border:1px solid rgba(0,0,0,.06);
    background:#fff;
  }
  .wiz-left{ display:flex; gap:10px; align-items:flex-start; }
  .wiz-ico{
    width:36px; height:36px; border-radius:12px;
    display:flex; align-items:center; justify-content:center;
    border:1px solid rgba(0,0,0,.06);
    background: linear-gradient(135deg, rgba(124,58,237,.10), rgba(236,72,153,.08));
    color: rgba(124,58,237,.95);
    flex: 0 0 auto;
  }
  .wiz-step b{ display:block; font-size:13px; font-weight:800; color: rgba(17,24,39,.82); }
  .wiz-step small{ display:block; color: rgba(17,24,39,.55); margin-top:2px; }
  .wiz-actions{ display:flex; flex-direction:column; align-items:flex-end; gap:8px; }
  .wiz-actions .pill{ font-weight:800; }
</style>

<?php
  $plannedThisWeek = (int)($plannedThisWeek ?? 0);
  $accountsCount   = (int)($accountsCount ?? 0);
  $templatesCount  = (int)($templatesCount ?? 0);

  $upcoming  = $upcoming ?? [];
  $recent    = $recent ?? [];
  $accounts  = $accounts ?? [];
  $templates = $templates ?? [];

  $dayCountsScheduled = $dayCountsScheduled ?? [];
  $dayCountsPublished = $dayCountsPublished ?? [];
  $wizardSteps     = $wizardSteps ?? [];
  $wizardPercent   = (int)($wizardPercent ?? 0);
  $wizardDoneCount = (int)($wizardDoneCount ?? 0);

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
      default     => $k !== '' ? ucfirst($k) : 'Hepsi',
    };
  };

  $statusPill = function($s){
    $s = (string)$s;
    return match($s){
      'published' => ['Yayınlandı', 'ok'],
      'failed'    => ['Dikkat Gerekiyor', 'bad'],
      'canceled'  => ['İptal', 'gray'],
      'queued'    => ['Sırada', 'wait'],
      'scheduled' => ['Planlandı', 'wait'],
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
<?php
$formatLabel = static function (?string $key, array $formats = []): string {
  $key = trim((string)$key);
  if ($key === '') return '';

  // Controller'dan $formats geliyorsa label kullan
  if (!empty($formats[$key]['label'])) return (string)$formats[$key]['label'];

  // Fallback (en azından çirkin görünmesin)
  $map = [
    'ig_post_1_1'   => 'Instagram Post (1:1)',
    'ig_post_4_5'   => 'Instagram Post (4:5)',
    'ig_story_9_16' => 'Instagram Story (9:16)',
    'ig_reels_9_16' => 'Instagram Reels (9:16)',
    'fb_post_1_1'   => 'Facebook Post (1:1)',
    'fb_story_9_16' => 'Facebook Story (9:16)',
    'tt_video_9_16' => 'TikTok (9:16)',
    'yt_thumb_16_9' => 'YouTube Thumbnail (16:9)',
    'yt_short_9_16' => 'YouTube Shorts (9:16)',
  ];

  return $map[$key] ?? $key;
};
?>
<div class="dash-grid">

    <section class="dash-card" style="grid-column: span 12;">
        <div class="dash-pad wiz-wrap">
        <div class="wiz-head">
            <div>
            <div class="dash-muted" style="font-weight:800; letter-spacing:.14em; font-size:11px;">KURULUM SİHİRBAZI</div>
            <h4 class="dash-title" style="font-size:15px; margin-top:4px;">3 adımda yayına hazırsın</h4>
            <div class="dash-muted">Eksik adımı tamamla, ilk planın takvime düşsün.</div>
            </div>

            <span class="dash-chip">
            <i class="bi bi-check2-circle"></i>
            <?= $wizardDoneCount ?>/3 • <?= $wizardPercent ?>%
            </span>
        </div>

        <div class="wiz-progress" aria-label="Kurulum ilerlemesi">
            <span style="width: <?= $wizardPercent ?>%;"></span>
        </div>

        <div class="wiz-steps">
            <?php if (empty($wizardSteps)): ?>
            <div class="dash-item">
                <div>
                <b>Sihirbaz yüklenemedi</b>
                <small>Hata Oluştu Lütfen Bildiriniz.</small>
                </div>
                <span class="pill gray">Bilgi</span>
            </div>
            <?php else: ?>
            <?php foreach ($wizardSteps as $st): ?>
                <?php
                $done = !empty($st['done']);
                $pillText  = $done ? 'Tamam' : 'Eksik';
                $pillClass = $done ? 'ok' : 'bad';
                ?>
                <div class="wiz-step">
                <div class="wiz-left">
                    <div class="wiz-ico"><i class="bi <?= esc($st['icon'] ?? 'bi-flag') ?>"></i></div>
                    <div>
                    <b><?= esc($st['title'] ?? '') ?></b>
                    <small><?= esc($st['desc'] ?? '') ?></small>
                    </div>
                </div>

                <div class="wiz-actions">
                    <span class="pill <?= $pillClass ?>"><?= $pillText ?></span>
                    <a href="<?= esc($st['url'] ?? '#') ?>" class="<?= $done ? 'btn-soft' : 'btn-grad' ?>">
                    <?= esc($st['cta'] ?? 'Devam et') ?>
                    <i class="bi bi-arrow-right-short"></i>
                    </a>
                </div>
                </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
        </div>
    </section>

  <!-- KPI 1 -->
  <section class="dash-card" style="grid-column: span 4;">
    <div class="dash-pad d-flex align-items-start justify-content-between gap-2">
      <div>
        <div class="dash-muted" style="font-weight:750; letter-spacing:.14em; font-size:11px;">BU HAFTA PLANLI</div>
        <div class="dash-kpi"><?= $plannedThisWeek ?></div>
        <div class="dash-muted">Bu hafta için planlanan paylaşım adedi.</div>
      </div>
      <span class="dash-chip"><i class="bi bi-calendar2-week"></i> Takvim</span>
    </div>

    <div class="dash-pad pt-0">
      <div class="dash-muted" style="font-weight:800; font-size:11px; letter-spacing:.12em;">YAKLAŞAN PAYLAŞIMLAR</div>

      <div class="dash-list">
        <?php if (empty($upcoming)): ?>
          <div class="dash-item">
            <div>
              <b>Henüz planlı paylaşım yok</b>
              <small>Takvimden yeni bir paylaşım planlayarak başlayabilirsiniz.</small>
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
        <a href="<?= site_url('panel/publishes') ?>" class="btn-soft"><i class="bi bi-list-ul me-1"></i> Tüm paylaşımlar</a>
        <a href="<?= site_url('panel/planner') ?>" class="btn-grad"><i class="bi bi-plus-lg me-1"></i> Yeni paylaşım planla</a>
      </div>
    </div>
  </section>

  <!-- KPI 2 -->
  <section class="dash-card" style="grid-column: span 4;">
    <div class="dash-pad d-flex align-items-start justify-content-between gap-2">
      <div>
        <div class="dash-muted" style="font-weight:750; letter-spacing:.14em; font-size:11px;">BAĞLI SOSYAL HESAP</div>
        <div class="dash-kpi"><?= $accountsCount ?></div>
        <div class="dash-muted">Bağlı hesaplarınızın kısa özeti.</div>
      </div>
      <span class="dash-chip"><i class="bi bi-share"></i> Hesaplar</span>
    </div>

    <div class="dash-pad pt-0">
      <div class="dash-list">
        <?php if (empty($accounts)): ?>
          <div class="dash-item">
            <div>
              <b>Henüz hesap bağlı değil</b>
              <small>Sosyal hesaplarınızı bağlayarak paylaşım planlamaya başlayın.</small>
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
              <span class="pill gray">ID: <?= (int)($a['id'] ?? 0) ?></span>
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
        <div class="dash-muted">Kullanıma açık şablon sayısı.</div>
      </div>
      <span class="dash-chip"><i class="bi bi-images"></i> Şablonlar</span>
    </div>

    <div class="dash-pad pt-0">
      <?php if (empty($templates)): ?>
        <div class="dash-item">
          <div>
            <b>Henüz şablon yok</b>
            <small>Şablonlar ile paylaşımlarınızı çok daha hızlı hazırlayabilirsiniz.</small>
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
                  <small>
                    <?= esc($scopeLabel($t['platform_scope'] ?? '')) ?>
                    •
                    <?= esc($formatLabel($t['format_key'] ?? '', $formats ?? [])) ?>
                    </small>
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
        <div class="dash-muted">Bu ayın planlı ve yayınlanan paylaşımlarını hızlıca görün.</div>
      </div>
      <div class="mini-legend">
        <span><i class="mk scheduled"></i> Planlı</span>
        <span><i class="mk published"></i> Yayınlandı</span>
        <a href="<?= site_url('panel/calendar') ?>" class="dash-chip"><i class="bi bi-lightning-charge"></i> Hızlı erişim</a>
      </div>
    </div>

    <div class="dash-pad pt-0">
      <div class="mini-cal">
        <div class="mini-cal-head">
          <div><?= esc($monthNameTr($month)) ?> <?= $year ?></div>
          <div class="dash-muted" style="font-weight:750; font-size:11px;">Günlerdeki toplam aktivite rozetlenir</div>
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

              $cntSch = (int)($dayCountsScheduled[$dateStr] ?? 0);
              $cntPub = (int)($dayCountsPublished[$dateStr] ?? 0);
              $total  = $cntSch + $cntPub;

              $title = [];
              if ($cntSch > 0) $title[] = "Planlı: {$cntSch}";
              if ($cntPub > 0) $title[] = "Yayınlanan: {$cntPub}";
              $titleAttr = !empty($title) ? implode(' • ', $title) : '';
            ?>
            <a class="mini-cal-day" href="<?= site_url('panel/calendar?date='.$dateStr) ?>" style="text-decoration:none; color:inherit;" title="<?= esc($titleAttr) ?>">
              <?= $d ?>

              <?php if ($total > 0): ?>
                <span class="mini-cal-badge"><?= $total ?></span>
                <div class="mini-cal-marks">
                  <?php if ($cntSch > 0): ?><span class="mk scheduled"></span><?php endif; ?>
                  <?php if ($cntPub > 0): ?><span class="mk published"></span><?php endif; ?>
                </div>
              <?php endif; ?>
            </a>
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
        <h4 class="dash-title" style="font-size:15px;">Son aktiviteler</h4>
        <div class="dash-muted">Son yayınlar ve işlem geçmişi.</div>
      </div>
      <a href="<?= site_url('panel/publishes') ?>" class="dash-chip"><i class="bi bi-clock-history"></i> Kayıtlar</a>
    </div>

    <div class="dash-pad pt-0">
      <div class="dash-list">
        <?php if (empty($recent)): ?>
          <div class="dash-item">
            <div>
              <b>Henüz aktivite yok</b>
              <small>Paylaşım planladıkça burada son durumlar görüntülenir.</small>
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

      <div class="mt-3">
        <a href="<?= site_url('panel/publishes') ?>" class="btn-soft"><i class="bi bi-search me-1"></i> Tüm kayıtları görüntüle</a>
      </div>
    </div>
  </section>

</div>

<?= $this->endSection() ?>
