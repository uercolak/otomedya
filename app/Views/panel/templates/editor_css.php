<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Roboto:wght@300;400;500;700&family=Montserrat:wght@300;400;500;700&family=Poppins:wght@300;400;500;600;700&family=Oswald:wght@300;400;500;700&family=Playfair+Display:wght@400;500;600;700&family=Nunito:wght@300;400;500;700&family=Raleway:wght@300;400;500;700&display=swap" rel="stylesheet">

<style>
  .om-toast-wrap{
    position:fixed; top:16px; right:16px; z-index:2000;
    display:flex; flex-direction:column; gap:10px;
    pointer-events:none;
  }
  .om-toast{
    pointer-events:auto;
    min-width:260px; max-width:360px;
    border-radius:14px;
    padding:12px 14px;
    box-shadow:0 10px 30px rgba(0,0,0,.12);
    background:#111827; color:#fff;
    display:flex; align-items:flex-start; gap:10px;
    transform:translateY(-8px);
    opacity:0;
    transition:all .18s ease;
    border:1px solid rgba(255,255,255,.10);
  }
  .om-toast.show{ transform:translateY(0); opacity:1; }
  .om-toast .t-title{ font-weight:600; font-size:13px; line-height:1.2; margin-bottom:2px; }
  .om-toast .t-msg{ font-size:13px; opacity:.92; line-height:1.35; }
  .om-toast .t-close{
    margin-left:auto; border:none; background:transparent; color:#fff;
    opacity:.8; cursor:pointer; padding:0 4px; line-height:1;
    font-size:18px;
  }
  .om-toast.success{ background:#0b1220; border-color:rgba(34,197,94,.35); }
  .om-toast.danger { background:#1a0b0b; border-color:rgba(239,68,68,.35); }
  .om-toast.warning{ background:#1a1408; border-color:rgba(245,158,11,.35); }
  .om-toast.info   { background:#0b1620; border-color:rgba(59,130,246,.35); }

  #textStyleBox, #cropBox { background:rgba(255,255,255,.7); }

  .om-autoplan-overlay{
    position: fixed;
    inset: 0;
    z-index: 3000;
    background: rgba(15, 23, 42, .55);
    backdrop-filter: blur(6px);
    display: none;
    align-items: center;
    justify-content: center;
    padding: 24px;
  }
  .om-autoplan-overlay.show{ display:flex; }

  .om-autoplan-box{
    width: min(420px, 92vw);
    background: rgba(255,255,255,.92);
    border: 1px solid rgba(0,0,0,.08);
    border-radius: 18px;
    box-shadow: 0 18px 60px rgba(0,0,0,.20);
    padding: 18px 18px 16px 18px;
    text-align: center;
  }
  .om-autoplan-title{
    font-weight: 700;
    letter-spacing: -.2px;
    margin: 10px 0 6px 0;
  }
  .om-autoplan-sub{
    color: rgba(0,0,0,.62);
    font-size: 13px;
    line-height: 1.35;
    margin: 0;
  }

  .om-spinner{
    width: 44px;
    height: 44px;
    border-radius: 50%;
    border: 4px solid rgba(124,58,237,.20);
    border-top-color: rgba(124,58,237,1);
    margin: 0 auto;
    animation: omspin .9s linear infinite;
  }
  @keyframes omspin { to { transform: rotate(360deg); } }
</style>
