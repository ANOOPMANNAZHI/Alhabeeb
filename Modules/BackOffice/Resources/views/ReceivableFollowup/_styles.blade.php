<style>
  /* Receivables Follow-up. Same palette as Mail -> Send: one blue for primary/focus (5.4:1 with white text),
     green / red / indigo only for promise status, always with a text label (never colour alone).
     Figures use tabular digits so columns line up. */
  .rf, .rf-dialog { --rf-ink:#0f172a; --rf-muted:#475569; --rf-line:#e2e8f0; --rf-fill:#f8fafc; --rf-primary:#136fae; --rf-primary-dark:#0f5c91;
        --rf-ok-bg:#dcfce7; --rf-ok:#166534; --rf-bad-bg:#fee2e2; --rf-bad:#991b1b; --rf-info-bg:#e0e7ff; --rf-info:#3730a3; color:var(--rf-ink); }
  .rf *, .rf-dialog * { box-sizing:border-box; }
  .rf [hidden], .rf-dialog [hidden] { display:none !important; }
  .rf .rf-sr, .rf-dialog .rf-sr { position:absolute !important; width:1px; height:1px; margin:-1px; overflow:hidden; clip:rect(0,0,0,0); white-space:nowrap; border:0; padding:0; }
  .rf a:focus-visible, .rf button:focus-visible, .rf input:focus-visible, .rf select:focus-visible,
  .rf-dialog button:focus-visible, .rf-dialog input:focus-visible, .rf-dialog textarea:focus-visible { outline:2px solid var(--rf-primary); outline-offset:2px; }

  /* filter bar */
  .rf .rf-filters { display:grid; grid-template-columns:repeat(auto-fit, minmax(160px, 1fr)); gap:12px 16px; align-items:start; margin:0 0 20px; }
  .rf .rf-filters .rf-grow { grid-column:span 2; }
  .rf .rf-filters label { display:block; font-size:13px; font-weight:600; margin:0 0 6px; color:var(--rf-ink); }
  .rf .rf-filters .form-control { font-size:14px; }

  /* toolbar: search left, Filters button right; the filter card opens underneath */
  .rf .rf-toolbar { display:flex; flex-wrap:wrap; gap:12px; align-items:center; justify-content:space-between; margin:0 0 12px; }
  .rf .rf-search { position:relative; flex:1 1 280px; max-width:520px; }
  .rf .rf-search .fa { position:absolute; left:2px; top:50%; transform:translateY(-50%); color:var(--rf-muted); }
  .rf .rf-search .form-control { padding-left:24px; font-size:14px; }
  .rf .rf-filter-btn { display:inline-flex; align-items:center; gap:8px; min-height:40px; padding:6px 16px; }
  .rf .rf-filter-btn.is-open { border-color:var(--rf-primary); color:var(--rf-primary); background:#e8f2fb; }
  .rf .rf-filter-count { display:inline-flex; align-items:center; justify-content:center; min-width:20px; height:20px; padding:0 6px; border-radius:10px;
        background:var(--rf-primary); color:#fff; font-size:12px; font-weight:700; }
  .rf .rf-filter-card { margin:0 0 12px; padding:16px; border:1px solid var(--rf-line); border-radius:8px; background:#fff;
        box-shadow:0 4px 12px rgba(15, 23, 42, .08); }
  .rf .rf-filter-card .rf-filters { margin:0; }
  .rf .rf-card-foot { display:flex; justify-content:flex-end; gap:8px; margin-top:16px; }
  .rf .rf-card-foot .rf-btn { min-height:36px; padding:6px 16px; }
  .rf .rf-card-foot .rf-btn + .rf-btn { margin-top:0; }
  .rf .rf-applied { margin:0 0 12px; font-size:13px; font-weight:600; color:var(--rf-muted); }

  /* summary strip */
  .rf .rf-strip { display:flex; flex-wrap:wrap; justify-content:space-between; align-items:center; gap:8px 24px; padding:12px 16px; margin:0 0 12px;
        background:var(--rf-fill); border:1px solid var(--rf-line); border-radius:8px; font-size:13px; color:var(--rf-muted); }
  .rf .rf-strip strong { color:var(--rf-ink); font-size:15px; font-variant-numeric:tabular-nums; }
  .rf .rf-strip .rf-stats { display:flex; flex-wrap:wrap; gap:4px 24px; }
  .rf .rf-link { background:none; border:0; padding:6px 4px; min-height:32px; color:var(--rf-primary); font-weight:600; font-size:13px; cursor:pointer; }
  .rf .rf-link:hover { color:var(--rf-primary-dark); text-decoration:underline; }
  .rf .rf-link[disabled] { opacity:.6; cursor:progress; }

  /* table */
  .rf .rf-table { width:100%; border-collapse:collapse; font-size:13px; }
  .rf .rf-table th { position:sticky; top:0; background:#fff; z-index:1; font-weight:600; color:var(--rf-muted); text-align:left; white-space:nowrap;
        padding:10px 8px; border-bottom:2px solid var(--rf-line); }
  .rf .rf-table td { padding:10px 8px; border-bottom:1px solid var(--rf-line); vertical-align:top; line-height:1.45; }
  .rf .rf-table tbody tr:hover td { background:var(--rf-fill); }
  .rf .rf-table tbody tr.rf-row { cursor:pointer; }
  .rf .rf-table tbody tr.rf-row:focus-visible { outline:2px solid var(--rf-primary); outline-offset:-2px; }
  .rf .rf-table tbody tr.rf-row:focus-visible td { background:#e8f2fb; }
  .rf .rf-table .num { text-align:right; white-space:nowrap; font-variant-numeric:tabular-nums; }
  .rf .rf-table .rf-bal { font-weight:700; }
  .rf .rf-sub { display:block; font-size:12px; color:var(--rf-muted); margin-top:2px; }
  .rf .rf-sort { background:none; border:0; padding:0; font:inherit; color:inherit; cursor:pointer; white-space:nowrap; }
  .rf .rf-sort:hover { color:var(--rf-ink); text-decoration:underline; }
  .rf .rf-table th[aria-sort="ascending"] .rf-sort, .rf .rf-table th[aria-sort="descending"] .rf-sort { color:var(--rf-ink); }
  .rf .rf-overdue { color:var(--rf-bad); font-weight:600; }
  .rf .rf-flag { display:inline-block; margin-left:4px; padding:0 6px; border-radius:10px; background:var(--rf-bad-bg); color:var(--rf-bad); font-size:11px; font-weight:700; }
  .rf .rf-totals td { font-weight:700; border-top:2px solid var(--rf-line); border-bottom:0; background:var(--rf-fill); }
  .rf .rf-actions { white-space:nowrap; }
  .rf .rf-actions .rf-btn { display:block; width:100%; }
  .rf .rf-btn { min-height:32px; padding:4px 12px; border-radius:6px; font-size:13px; font-weight:600; border:1px solid #cbd5e1; background:#fff; color:var(--rf-ink); cursor:pointer; }
  .rf .rf-btn + .rf-btn { margin-top:6px; }
  .rf .rf-btn:hover { border-color:var(--rf-primary); color:var(--rf-primary); }
  .rf .rf-btn-primary, .rf-dialog .rf-btn-primary { background:var(--rf-primary); border-color:var(--rf-primary); color:#fff; }
  .rf .rf-btn-primary:hover, .rf-dialog .rf-btn-primary:hover { background:var(--rf-primary-dark); border-color:var(--rf-primary-dark); color:#fff; }
  .rf .rf-row-saved td { animation:rf-flash 1.2s ease-out; }
  @keyframes rf-flash { from { background:var(--rf-ok-bg); } to { background:transparent; } }
  .rf .rf-empty { text-align:center; padding:40px 16px; color:var(--rf-muted); }
  .rf .rf-empty strong { display:block; color:var(--rf-ink); font-size:15px; margin-bottom:4px; }

  /* loading placeholders */
  .rf .rf-skel { height:12px; border-radius:6px; background:linear-gradient(90deg, #eef2f7 25%, #e2e8f0 37%, #eef2f7 63%); background-size:400% 100%; animation:rf-shimmer 1.4s ease infinite; }
  @keyframes rf-shimmer { 0% { background-position:100% 50%; } 100% { background-position:0 50%; } }
  .rf .rf-busy { opacity:.55; pointer-events:none; transition:opacity .15s ease-out; }
  .rf .rf-calc { display:flex; gap:8px; align-items:center; padding:10px 16px; margin:0 0 12px; border-radius:8px; background:var(--rf-info-bg); color:var(--rf-info); font-size:13px; font-weight:600; }

  /* modals */
  .rf-dialog .modal-content { border:0; border-radius:10px; }
  .rf-dialog .modal-header { border-bottom:1px solid var(--rf-line); padding:16px 20px; }
  .rf-dialog .modal-title { font-size:17px; font-weight:600; margin:0; }
  .rf-dialog .modal-body { padding:20px; }
  .rf-dialog .rf-head { display:grid; grid-template-columns:repeat(auto-fit, minmax(140px, 1fr)); gap:12px 16px; padding:12px 16px; margin:0 0 20px;
        background:var(--rf-fill); border:1px solid var(--rf-line); border-radius:8px; font-size:13px; }
  .rf-dialog .rf-head dt { font-weight:600; color:var(--rf-muted); font-size:12px; margin:0; }
  .rf-dialog .rf-head dd { margin:2px 0 0; color:var(--rf-ink); font-variant-numeric:tabular-nums; }
  .rf-dialog .rf-head .rf-bal { font-size:16px; font-weight:700; }
  .rf-dialog label { display:block; font-size:13px; font-weight:600; margin:0 0 6px; }
  .rf-dialog .rf-req { color:var(--rf-bad); }
  .rf-dialog .rf-help { display:block; font-size:12px; color:var(--rf-muted); margin-top:4px; }
  .rf-dialog .rf-field-error { display:block; font-size:12px; color:var(--rf-bad); font-weight:600; margin-top:4px; }
  .rf-dialog .is-invalid { border-color:var(--rf-bad); }
  .rf-dialog .rf-count { float:right; font-size:12px; color:var(--rf-muted); font-weight:400; }
  .rf-dialog .rf-foot { display:flex; justify-content:flex-end; gap:8px; margin-top:20px; }
  .rf-dialog .rf-btn { min-height:40px; padding:8px 16px; border-radius:6px; font-size:14px; font-weight:600; border:1px solid #cbd5e1; background:#fff; color:var(--rf-ink); cursor:pointer; }
  .rf-dialog .rf-btn[disabled] { opacity:.6; cursor:progress; }
  .rf-dialog .rf-btn.rf-btn-primary { background:var(--rf-primary); border-color:var(--rf-primary); color:#fff; }
  .rf-dialog .rf-btn.rf-btn-primary:hover { background:var(--rf-primary-dark); border-color:var(--rf-primary-dark); color:#fff; }

  /* recent follow-ups above the form */
  .rf-dialog .rf-recent { margin:0 0 20px; padding:12px 16px; border-left:3px solid var(--rf-primary); background:#f8fafc; border-radius:0 8px 8px 0; }
  .rf-dialog .rf-recent h5 { font-size:13px; font-weight:700; margin:0 0 8px; color:var(--rf-ink); }
  .rf-dialog .rf-recent ol { list-style:none; margin:0; padding:0; }
  .rf-dialog .rf-recent li + li { margin-top:10px; padding-top:10px; border-top:1px solid var(--rf-line); }
  .rf-dialog .rf-recent .rf-when { font-size:12px; color:var(--rf-muted); }
  .rf-dialog .rf-recent .rf-when strong { color:var(--rf-ink); }
  .rf-dialog .rf-recent .rf-comment { margin:2px 0 0; font-size:13px; line-height:1.5; white-space:pre-wrap; word-break:break-word; }

  /* timeline */
  .rf-dialog .rf-tl { list-style:none; margin:0; padding:0 0 0 24px; position:relative; }
  .rf-dialog .rf-tl:before { content:''; position:absolute; left:7px; top:6px; bottom:6px; width:2px; background:var(--rf-line); }
  .rf-dialog .rf-tl li { position:relative; padding:0 0 20px; }
  .rf-dialog .rf-tl li:last-child { padding-bottom:0; }
  .rf-dialog .rf-tl li:before { content:''; position:absolute; left:-22px; top:5px; width:12px; height:12px; border-radius:50%; background:#fff; border:3px solid var(--rf-primary); }
  .rf-dialog .rf-tl .rf-when { font-size:12px; color:var(--rf-muted); }
  .rf-dialog .rf-tl .rf-when strong { color:var(--rf-ink); }
  .rf-dialog .rf-tl .rf-comment { margin:4px 0; white-space:pre-wrap; word-break:break-word; font-size:14px; line-height:1.5; }
  .rf-dialog .rf-tl .rf-promise { font-size:13px; }
  .rf-badge { display:inline-block; margin-left:6px; padding:1px 8px; border-radius:10px; font-size:12px; font-weight:700; }
  .rf-badge.kept { background:var(--rf-ok-bg); color:var(--rf-ok); }
  .rf-badge.broken { background:var(--rf-bad-bg); color:var(--rf-bad); }
  .rf-badge.upcoming { background:var(--rf-info-bg); color:var(--rf-info); }
  .rf-dialog .rf-empty-tl { padding:24px; text-align:center; color:var(--rf-muted); border:1px dashed var(--rf-line); border-radius:8px; }

  @media (max-width:600px) { .rf .rf-filters .rf-grow { grid-column:auto; } }
  @media (prefers-reduced-motion:reduce) { .rf *, .rf-dialog * { animation:none !important; transition:none !important; } }
</style>
