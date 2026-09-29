<style>
  .mt { --mt-ink:#0f172a; --mt-muted:#475569; --mt-line:#e2e8f0; --mt-fill:#f1f5f9; --mt-primary:#2f4fd6; }
  .mt .mt-bar { display:flex; flex-wrap:wrap; gap:8px; align-items:flex-end; justify-content:space-between; margin-bottom:16px; }
  .mt .mt-filters { display:flex; flex-wrap:wrap; gap:12px; align-items:flex-end; }
  .mt label { display:block; font-size:13px; font-weight:600; color:var(--mt-ink); margin:0 0 6px; }
  .mt .mt-input, .mt select.mt-input, .mt textarea.mt-input { width:100%; height:40px; padding:0 12px; border:1px solid #cbd5e1; border-radius:6px;
      font-size:14px; color:var(--mt-ink); background:#fff; }
  .mt textarea.mt-input { height:auto; min-height:120px; padding:10px 12px; line-height:1.5; }
  .mt .mt-input:focus { outline:2px solid var(--mt-primary); outline-offset:1px; border-color:var(--mt-primary); }
  .mt .mt-input[readonly], .mt .mt-input[disabled] { background:var(--mt-fill); color:var(--mt-muted); }
  .mt .mt-btn { display:inline-flex; align-items:center; gap:8px; height:40px; padding:0 16px; border:1px solid #94a3b8; border-radius:6px;
      background:#fff; color:var(--mt-ink); font-size:14px; font-weight:600; text-decoration:none; cursor:pointer; white-space:nowrap; }
  .mt .mt-btn:hover { background:var(--mt-fill); color:var(--mt-ink); text-decoration:none; }
  .mt .mt-btn:focus-visible { outline:2px solid var(--mt-primary); outline-offset:2px; }
  .mt .mt-btn-primary { background:#16a34a; border-color:#16a34a; color:#fff; }
  .mt .mt-btn-primary:hover { background:#15803d; border-color:#15803d; color:#fff; }
  .mt .mt-btn-sm { height:32px; padding:0 10px; font-size:13px; }
  .mt .mt-btn-danger { color:#b91c1c; border-color:#fca5a5; }
  .mt .mt-btn-danger:hover { background:#fef2f2; color:#991b1b; }
  .mt table { width:100%; border-collapse:collapse; }
  .mt th, .mt td { padding:12px; border-bottom:1px solid var(--mt-line); font-size:14px; vertical-align:middle; }
  .mt th { font-size:12px; font-weight:600; color:var(--mt-muted); text-align:left; background:var(--mt-fill); text-transform:uppercase; letter-spacing:.03em; }
  .mt .mt-key { display:block; font-size:12px; color:var(--mt-muted); font-family:Consolas, monospace; }
  .mt .mt-badge { display:inline-block; padding:2px 8px; border-radius:10px; font-size:12px; font-weight:600; background:var(--mt-fill); color:var(--mt-muted); margin-right:4px; }
  .mt .mt-badge-system { background:#e0e7ff; color:#3730a3; }
  .mt .mt-badge-on { background:#dcfce7; color:#166534; }
  .mt .mt-actions { display:flex; flex-wrap:wrap; gap:6px; }
  .mt .mt-empty { padding:32px 16px; text-align:center; color:var(--mt-muted); }
  .mt .mt-empty strong { display:block; font-size:16px; color:var(--mt-ink); margin-bottom:4px; }
  .mt .mt-section { margin-bottom:24px; }
  .mt .mt-section h3 { font-size:16px; font-weight:600; color:var(--mt-ink); margin:0 0 4px; }
  .mt .mt-section .mt-help { font-size:13px; color:var(--mt-muted); margin:0 0 12px; }
  .mt .mt-grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:16px; }
  .mt .mt-field { margin-bottom:16px; }
  .mt .mt-row { display:flex; gap:8px; align-items:center; }
  .mt .mt-row .mt-input { flex:1; }
  .mt .mt-row select.mt-input { flex:0 0 200px; }
  .mt .mt-count { font-size:13px; color:var(--mt-muted); margin-top:6px; }
  .mt .mt-error { font-size:13px; color:#b91c1c; margin-top:6px; }
  .mt .mt-switch { display:inline-flex; align-items:center; gap:8px; font-size:14px; font-weight:600; cursor:pointer; }
  .mt .mt-switch input { width:18px; height:18px; }
  .mt .mt-form { max-width:960px; }
  .mt .mt-footer { display:flex; flex-wrap:wrap; gap:8px; border-top:1px solid var(--mt-line); padding-top:16px; }
  #mt-preview-modal .mt-pv-subject { font-weight:600; padding:8px 12px; background:#f1f5f9; border-radius:6px; margin-bottom:12px; }
  #mt-preview-modal .mt-pv-body { border:1px solid #e2e8f0; border-radius:6px; padding:16px; max-height:420px; overflow:auto; }
  #mt-preview-modal .mt-pv-sms { background:#dcfce7; border-radius:14px 14px 14px 4px; padding:10px 14px; max-width:340px; white-space:pre-wrap; word-wrap:break-word; }
  #mt-preview-modal .mt-pv-warn { background:#fef9c3; border:1px solid #fde047; color:#713f12; border-radius:6px; padding:8px 12px; margin-bottom:12px; display:none; }
  #mt-preview-modal .mt-pv-none { color:#475569; font-style:italic; }
  #mt-preview-modal .mt-pv-head { font-size:12px; font-weight:600; text-transform:uppercase; color:#475569; margin:16px 0 6px; }
  @media (max-width:600px) { .mt .mt-row { flex-wrap:wrap; } .mt .mt-row select.mt-input { flex:1 1 100%; }
      .mt .mt-hide-sm { display:none; } }
</style>
