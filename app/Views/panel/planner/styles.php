<style>
  .btn-brand{
    border:0;
    color:#fff;
    background: linear-gradient(135deg, #7c3aed, #ec4899);
    box-shadow: 0 10px 24px rgba(124,58,237,.18);
  }
  .btn-brand:hover{ filter: brightness(.98); transform: translateY(-1px); }
  .btn-brand:active{ transform: translateY(0); }
  .btn-soft{
    border:1px solid rgba(0,0,0,.10);
    background:#fff;
    color:#111;
  }
  .btn-soft:hover{ background:#f8f9fa; }

  /* Emoji */
  .btn-emoji{
    width:36px; height:36px; padding:0;
    display:flex; align-items:center; justify-content:center;
    border-radius:10px;
    border:1px solid rgba(0,0,0,.08);
    background:#fff;
  }
  .btn-emoji:hover{ background:#f8f9fa; }
  .emoji-menu{
    border-radius:14px;
    border:1px solid rgba(0,0,0,.10);
    box-shadow:0 18px 50px rgba(0,0,0,.12);
  }

  /* Preview UI */
  .pv-platform { border:1px solid rgba(0,0,0,.08); border-radius:16px; padding:14px; background:#fff; }
  .pv-head { display:flex; align-items:flex-start; justify-content:space-between; gap:10px; margin-bottom:10px; }
  .pv-title { font-weight:600; }
  .pv-sub { color:#6c757d; font-size:12px; margin-top:2px; }
  .pv-badges { display:flex; gap:6px; flex-wrap:wrap; justify-content:flex-end; }
  .pv-badge { font-size:11px; padding:4px 8px; border-radius:999px; border:1px solid rgba(0,0,0,.08); background:#f8f9fa; color:#111; }
  .pv-row { display:flex; gap:14px; flex-wrap:wrap; align-items:flex-start; }

  .pv-phone{
    width: 300px;
    max-width: 100%;
    border-radius: 28px;
    background: #0b0b0c;
    padding: 10px;
    border: 1px solid rgba(0,0,0,.18);
    box-shadow: 0 18px 40px rgba(0,0,0,.12);
    position: relative;
  }
  .pv-phone:before{
    content:"";
    position:absolute;
    top:10px;
    left:50%;
    transform:translateX(-50%);
    width: 92px;
    height: 18px;
    border-radius: 999px;
    background: rgba(255,255,255,.06);
    border: 1px solid rgba(255,255,255,.05);
    pointer-events:none;
  }

  .pv-screen { background:#111; border-radius: 20px; overflow:hidden; position:relative; }
  .pv-ratio-9x16 { aspect-ratio: 9/16; }
  .pv-ratio-4x5  { aspect-ratio: 4/5; }
  .pv-ratio-1x1  { aspect-ratio: 1/1; }
  .pv-ratio-16x9 { aspect-ratio: 16/9; }
  .pv-media, .pv-media video, .pv-media img { width:100%; height:100%; object-fit: cover; display:block; background:#000; }

  .pv-ig-top{
    position:absolute; top:10px; left:10px; right:10px;
    display:flex; align-items:center; justify-content:space-between;
    color:#fff; font-size:12px;
    text-shadow:0 1px 10px rgba(0,0,0,.55);
    pointer-events:none;
  }
  .pv-ig-pill{
    background:rgba(0,0,0,.40);
    border:1px solid rgba(255,255,255,.14);
    padding:4px 8px;
    border-radius:999px;
  }

  .pv-tt-left{
    position:absolute; left:10px; bottom:14px; right:68px;
    color:#fff; font-size:12px;
    text-shadow:0 1px 12px rgba(0,0,0,.75);
    pointer-events:none;
  }
  .pv-tt-user{ font-weight:700; margin-bottom:6px; }
  .pv-tt-caption{
    opacity:.95;
    white-space:pre-wrap;
    max-height:76px;
    overflow:hidden;
  }
  .pv-tt-right{
    position:absolute; right:10px; bottom:16px;
    display:flex; flex-direction:column; gap:10px;
    pointer-events:none;
  }
  .pv-tt-avatar{
    width:40px; height:40px; border-radius:999px;
    background:rgba(255,255,255,.18);
    border:1px solid rgba(255,255,255,.28);
    display:flex; align-items:center; justify-content:center;
    box-shadow:0 8px 18px rgba(0,0,0,.25);
  }
  .pv-tt-avatar::after{ content:"S"; font-weight:800; font-size:14px; color:#fff; }
  .pv-tt-action{
    width:38px; height:38px; border-radius:999px;
    background:rgba(255,255,255,.14);
    border:1px solid rgba(255,255,255,.22);
    display:flex; align-items:center; justify-content:center;
    box-shadow:0 8px 18px rgba(0,0,0,.22);
  }
  .pv-tt-action::after{ color:#fff; font-size:16px; line-height:1; opacity:.95; }
  .pv-tt-like::after{ content:"♥"; }
  .pv-tt-comment::after{ content:"💬"; }
  .pv-tt-share::after{ content:"↗"; }

  .pv-caption-box{
    border:1px solid rgba(0,0,0,.08);
    border-radius:12px;
    padding:10px 12px;
    background:#fff;
    max-width: 300px;
  }
  .pv-caption-label{ color:#6c757d; font-size:12px; margin-bottom:6px; }
  .pv-caption-text{ font-size:12px; white-space:pre-wrap; }
  .pv-disclaimer{ color:#6c757d; font-size:11px; margin-top:8px; }

  .pv-feed{
    border:1px solid rgba(0,0,0,.08);
    border-radius:14px;
    overflow:hidden;
    background:#fff;
    max-width: 520px;
  }
  .pv-feed-head{
    padding:10px 12px;
    display:flex;
    align-items:center;
    gap:10px;
  }
  .pv-avatar{ width:28px; height:28px; border-radius:999px; background:#e9ecef; }
  .pv-feed-name{ font-size:12px; font-weight:600; line-height:1.1; }
  .pv-feed-sub{ font-size:11px; color:#6c757d; }
  .pv-feed-body{ padding:10px 12px; font-size:12px; white-space:pre-wrap; }
  .pv-yt-wrap{ max-width: 620px; }
  .pv-yt-title{ font-weight:700; font-size:13px; margin-top:10px; }
  .pv-yt-meta{ color:#6c757d; font-size:11px; margin-top:2px; }
</style>
