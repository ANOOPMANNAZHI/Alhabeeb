<style>
  .br { --br-ink:#0f172a; --br-muted:#475569; --br-line:#e2e8f0; --br-fill:#f1f5f9; --br-primary:#2f4fd6; }
  .br .br-bar { display:flex; flex-wrap:wrap; gap:8px; align-items:center; justify-content:space-between; margin-bottom:16px; }
  .br .br-hint { color:var(--br-muted); font-size:13px; max-width:80ch; margin:0; }
  .br .br-btn { display:inline-flex; align-items:center; gap:8px; height:40px; padding:0 16px; border:1px solid #94a3b8; border-radius:6px;
      background:#fff; color:var(--br-ink); font-size:14px; font-weight:600; text-decoration:none; cursor:pointer; white-space:nowrap; }
  .br .br-btn:hover { background:var(--br-fill); color:var(--br-ink); text-decoration:none; }
  .br .br-btn:focus-visible { outline:2px solid var(--br-primary); outline-offset:2px; }
  .br .br-btn-primary { background:#16a34a; border-color:#16a34a; color:#fff; }
  .br .br-btn-primary:hover { background:#15803d; border-color:#15803d; color:#fff; }
  .br .br-btn-sm { height:32px; padding:0 10px; font-size:13px; }
  .br table { width:100%; border-collapse:collapse; }
  .br th, .br td { padding:12px; border-bottom:1px solid var(--br-line); font-size:14px; vertical-align:middle; }
  .br th { font-size:12px; font-weight:600; color:var(--br-muted); text-align:left; background:var(--br-fill); text-transform:uppercase; letter-spacing:.03em; }
  .br .num { text-align:right; font-variant-numeric:tabular-nums; }
  .br .br-words { font-size:13px; color:var(--br-muted); }
  .br .br-badge { display:inline-block; padding:2px 8px; border-radius:10px; font-size:12px; font-weight:600; background:var(--br-fill); color:var(--br-muted); }
  .br .br-badge-on { background:#dcfce7; color:#166534; }
  .br .br-badge-fb { background:#e0e7ff; color:#3730a3; margin-left:4px; }
  .br .br-form { max-width:720px; }
  .br label { display:block; font-size:13px; font-weight:600; color:var(--br-ink); margin:0 0 6px; }
  .br .br-input { width:100%; height:40px; padding:0 12px; border:1px solid #cbd5e1; border-radius:6px; font-size:14px; color:var(--br-ink); background:#fff; }
  .br textarea.br-input { height:auto; min-height:80px; padding:10px 12px; line-height:1.5; }
  .br .br-input:focus { outline:2px solid var(--br-primary); outline-offset:1px; border-color:var(--br-primary); }
  .br .br-field { margin-bottom:20px; }
  .br .br-help { font-size:12px; color:var(--br-muted); margin-top:6px; }
  .br .br-error { font-size:13px; color:#b91c1c; margin-top:6px; }
  .br .br-switch { display:flex; align-items:flex-start; gap:8px; font-size:14px; font-weight:600; cursor:pointer; margin-bottom:12px; }
  .br .br-switch input { width:18px; height:18px; margin-top:1px; }
  .br .br-switch span { font-weight:400; color:var(--br-muted); display:block; font-size:12px; }
  .br .br-footer { display:flex; flex-wrap:wrap; gap:8px; border-top:1px solid var(--br-line); padding-top:16px; }
</style>
