<?= $this->extend('layouts/panel') ?>
<?= $this->section('content') ?>

<?php
$stats  = $stats  ?? ['tt_count' => 0];
?>

<style>
  .help-hero{
    border:1px solid rgba(0,0,0,.06);
    border-radius:16px;
    background: linear-gradient(180deg, rgba(124,58,237,.10), rgba(236,72,153,.06));
  }
  .help-hero h1{ letter-spacing:-.4px; font-weight:900; }
  .help-muted{ color: rgba(0,0,0,.62); }
  .help-badge{
    display:inline-flex; align-items:center; gap:8px;
    padding:7px 10px; border-radius:999px;
    background: rgba(255,255,255,.65);
    border: 1px solid rgba(0,0,0,.06);
    font-size:12px; font-weight:800;
    white-space:nowrap;
  }
  .help-card{
    border:1px solid rgba(0,0,0,.06);
    border-radius:16px;
  }
  .help-card .card-header{
    background:#fff;
    border-bottom:1px solid rgba(0,0,0,.06);
    border-top-left-radius:16px;
    border-top-right-radius:16px;
  }
  .help-acc .accordion-item{
    border:1px solid rgba(0,0,0,.06);
    border-radius:14px;
    overflow:hidden;
    margin-bottom:10px;
  }
  .help-acc .accordion-button{
    font-weight:900;
    background:#fff;
  }
  .help-acc .accordion-button:not(.collapsed){
    box-shadow:none;
    background: rgba(0,0,0,.02);
  }
  .help-kbd{
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
    font-size: .95em;
    padding: 2px 6px;
    border-radius: 8px;
    border: 1px solid rgba(0,0,0,.08);
    background: rgba(0,0,0,.03);
  }
  .btn-brand{
    border:0;
    color:#fff;
    background: linear-gradient(135deg, #7c3aed, #ec4899);
    box-shadow: 0 10px 24px rgba(124,58,237,.18);
    border-radius:999px;
    font-weight:900;
  }
  .btn-brand:hover{ filter: brightness(.98); transform: translateY(-1px); color:#fff; }
  .btn-soft{
    border:1px solid rgba(0,0,0,.10);
    background:#fff;
    color:#111;
    border-radius:999px;
    font-weight:900;
  }
  .btn-soft:hover{ background:#f8f9fa; }
</style>

<div class="d-flex align-items-start justify-content-between mb-3 flex-wrap gap-2">
  <div>
    <h1 class="h3 mb-1">Yardım / TikTok Bağlama Rehberi</h1>
    <div class="help-muted">
      TikTok hesabını bağlama ve Direct Post (video) paylaşımı için kısa, adım adım rehber.
    </div>
  </div>

  <a class="btn btn-soft" href="<?= site_url('panel/social-accounts') ?>">
    Sosyal Hesaplara Git
  </a>
</div>

<div class="help-hero p-3 p-lg-4 mb-3 d-flex align-items-start justify-content-between gap-3 flex-wrap">
  <div style="max-width:820px;">
    <div class="help-badge mb-2"><i class="bi bi-info-circle"></i> Kısa Özet</div>
    <div class="fw-semibold">
      TikTok paylaşımı için ilk adım <b>OAuth ile hesabı bağlamak</b>.
      Bağlantı sonrası <b>Post to TikTok</b> sayfasından video + açıklama + tarih seçerek planlayabilirsin.
    </div>
    <div class="help-muted small mt-2">
      İpucu: Bir şey takılırsa önce <span class="help-kbd">Sosyal Hesaplar → Bağlantıyı kaldır</span> yapıp tekrar bağlamak çoğu zaman en hızlı çözümdür.
    </div>
  </div>

  <span class="help-badge">
    <i class="bi bi-shield-check"></i>
    Güvenli bağlantı (OAuth)
  </span>
</div>

<div class="row g-3">
  <!-- LEFT: GUIDE -->
  <div class="col-lg-7">

    <div class="card help-card mb-3">
      <div class="card-header"><strong>Rehber</strong></div>
      <div class="card-body">

        <div class="accordion help-acc" id="accHelp">

          <!-- 1) TikTok connect -->
          <div class="accordion-item">
            <h2 class="accordion-header" id="hTikTok">
              <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#cTikTok" aria-expanded="true" aria-controls="cTikTok">
                1) TikTok — Hesap bağlama (OAuth)
              </button>
            </h2>
            <div id="cTikTok" class="accordion-collapse collapse show" aria-labelledby="hTikTok" data-bs-parent="#accHelp">
              <div class="accordion-body">
                <ol class="mb-0">
                  <li><strong>Panel → Sosyal Hesaplar</strong> sayfasına git.</li>
                  <li><strong>“TikTok’u Bağla”</strong> butonuna tıkla.</li>
                  <li>TikTok izin ekranında giriş yap ve izin ver.</li>
                  <li>Uygulamaya dönünce TikTok hesabın “Bağlı TikTok Hesapları” bölümünde görünür.</li>
                </ol>

                <div class="alert alert-light border mt-3 mb-0">
                  <div class="fw-semibold mb-1">Sık sorunlar</div>
                  <ul class="mb-0 small">
                    <li>“Hesap görünmüyor” → Bağlantıyı kaldırıp tekrar bağla.</li>
                    <li>Yanlış hesapla giriş → TikTok’ta çıkış yapıp doğru hesapla yeniden bağla.</li>
                    <li>Username alınamadı → Bazı hesaplarda TikTok username alanını paylaşmayabilir; bağlanma yine de geçerli olabilir.</li>
                  </ul>
                </div>
              </div>
            </div>
          </div>

          <!-- 2) Posting rules -->
          <div class="accordion-item">
            <h2 class="accordion-header" id="hPostRules">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#cPostRules" aria-expanded="false" aria-controls="cPostRules">
                2) Post to TikTok — Planlama kuralları
              </button>
            </h2>
            <div id="cPostRules" class="accordion-collapse collapse" aria-labelledby="hPostRules" data-bs-parent="#accHelp">
              <div class="accordion-body">
                <ul class="mb-0">
                  <li><strong>Video zorunlu</strong> (Direct Post).</li>
                  <li>Açıklama (caption) ve hashtag eklemek önerilir.</li>
                  <li>Tarih/Saat seçtikten sonra “Planla” ile kuyruklanır.</li>
                </ul>
              </div>
            </div>
          </div>

          <!-- 3) Troubleshooting -->
          <div class="accordion-item">
            <h2 class="accordion-header" id="hFix">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#cFix" aria-expanded="false" aria-controls="cFix">
                3) Sık görülen hatalar ve çözümler
              </button>
            </h2>
            <div id="cFix" class="accordion-collapse collapse" aria-labelledby="hFix" data-bs-parent="#accHelp">
              <div class="accordion-body">
                <div class="mb-3">
                  <div class="fw-semibold">❌ Bağlantı ekranı açılmıyor</div>
                  <div class="help-muted">Popup engelleyici kapalı olsun. Tarayıcıda yeni sekmeye izin ver.</div>
                </div>

                <div class="mb-3">
                  <div class="fw-semibold">❌ Video seçtim ama ön izleme boş</div>
                  <div class="help-muted">Bazı tarayıcılarda büyük videolarda ön izleme gecikebilir. Yine de yükleme sırasında sorun yoktur.</div>
                </div>

                <div class="mb-0">
                  <div class="fw-semibold">❌ Planladım ama yayınlanmadı</div>
                  <div class="help-muted">“Paylaşımlar” sayfasından durumunu kontrol et. Gerekirse aynı videoyu tekrar planla.</div>
                </div>

                <hr class="my-3">

                <div class="alert alert-warning mb-0">
                  <strong>En hızlı çözüm:</strong> Bağlantıyı kaldır → tekrar bağla.
                  <div class="small mt-1">Panel → Sosyal Hesaplar → Bağlı hesaplar → kaldır</div>
                </div>
              </div>
            </div>
          </div>

        </div><!-- /accordion -->

      </div>
    </div>

  </div>

  <!-- RIGHT: QUICK PANEL -->
  <div class="col-lg-5">

    <div class="card help-card mb-3">
      <div class="card-header d-flex align-items-center justify-content-between gap-2 flex-wrap">
        <strong>Hızlı Kontrol</strong>
        <span class="help-badge">
          Bağlı TikTok: <?= (int)($stats['tt_count'] ?? 0) ?>
        </span>
      </div>

      <div class="card-body">
        <div class="alert alert-light border">
          <div class="fw-semibold mb-1">1 dakikada kontrol</div>
          <ol class="mb-0 small">
            <li><strong>Sosyal Hesaplar</strong> sayfasında TikTok hesabın listeleniyor mu?</li>
            <li><strong>Post to TikTok</strong> sayfasında hesap seçebiliyor musun?</li>
            <li>Video seçince <strong>Ön İzleme</strong> geliyor mu?</li>
          </ol>
        </div>

        <div class="d-grid gap-2">
          <a href="<?= site_url('panel/social-accounts') ?>" class="btn btn-soft">
            TikTok Bağlantısına Git
          </a>
          <a href="<?= site_url('panel/planner') ?>" class="btn btn-brand">
            Post to TikTok
          </a>
        </div>

        <div class="small help-muted mt-3">
          Not: TikTok bağlantısı ve paylaşım planlama işlemleri için bu iki sayfa yeterlidir.
        </div>
      </div>
    </div>

  </div>
</div>

<?= $this->endSection() ?>
