<style>
  .ins { --ins-blue:#005baa; --ins-blue-50:#eef5fc; --ins-blue-100:#d6e7f7; --ins-blue-700:#00487f; --ins-accent:#00aee6; --ins-orange:#f26522;
    --ins-text:#172d45; --ins-muted:#5b6b7b; --ins-line:#e3e9ef; --ins-bg:#f4f7fa; --ins-ok:#15803d; --ins-ok-50:#ecfdf3;
    --ins-warn:#b45309; --ins-warn-50:#fff7e8; --ins-bad:#c62828; --ins-bad-50:#fdecec; color:var(--ins-text); font-size:14px; }
  .ins *, .ins *:before, .ins *:after { box-sizing:border-box; }
  /* the theme sets h1-h3 sizes with !important */
  .ins h1 { font-size:24px !important; font-weight:600; margin:0 0 4px; color:var(--ins-text); }
  .ins h2 { font-size:17px !important; font-weight:600; margin:0; color:var(--ins-text); }
  .ins h3 { font-size:15px !important; font-weight:600; margin:0; color:var(--ins-text); }
  .ins p { margin:0; }
  .ins .sub, .ins .muted { color:var(--ins-muted); }
  .ins small, .ins .small { font-size:12px; color:var(--ins-muted); }
  .ins-sr { position:absolute; width:1px; height:1px; overflow:hidden; clip:rect(0 0 0 0); white-space:nowrap; }

  .ins-tabs { display:flex; gap:4px; border-bottom:1px solid var(--ins-line); margin-bottom:24px; overflow-x:auto; }
  .ins-tabs a { padding:10px 16px; color:var(--ins-muted); font-weight:500; border-bottom:2px solid transparent; white-space:nowrap; text-decoration:none; }
  .ins-tabs a:hover { color:var(--ins-blue); text-decoration:none; }
  .ins-tabs a.active { color:var(--ins-blue); border-bottom-color:var(--ins-blue); }
  .ins-tabs .count { display:inline-block; min-width:20px; padding:0 6px; margin-left:6px; border-radius:10px; background:var(--ins-blue-100); color:var(--ins-blue-700); font-size:12px; text-align:center; }

  .ins-head { display:flex; justify-content:space-between; align-items:flex-start; gap:16px; flex-wrap:wrap; margin-bottom:24px; }
  .ins-tools { display:flex; gap:8px; flex-wrap:wrap; align-items:center; }

  .ins-btn { display:inline-flex; align-items:center; justify-content:center; gap:6px; height:40px; padding:0 16px; border-radius:8px; border:1px solid var(--ins-line);
    background:#fff; color:var(--ins-text); font-weight:500; cursor:pointer; text-decoration:none; white-space:nowrap; transition:background .15s, border-color .15s; }
  .ins-btn:hover { background:var(--ins-bg); text-decoration:none; color:var(--ins-text); }
  .ins-btn:focus-visible, .ins a:focus-visible, .ins-seg button:focus-visible, .ins input:focus-visible, .ins select:focus-visible, .ins textarea:focus-visible {
    outline:2px solid var(--ins-accent); outline-offset:2px; }
  .ins-btn.primary { background:var(--ins-blue); border-color:var(--ins-blue); color:#fff; }
  .ins-btn.primary:hover { background:var(--ins-blue-700); color:#fff; }
  .ins-btn.danger { color:var(--ins-bad); border-color:#f3c4c4; }
  .ins-btn.danger:hover { background:var(--ins-bad-50); }
  .ins-btn.sm { height:32px; padding:0 12px; font-size:13px; }
  .ins-btn[disabled] { opacity:.5; cursor:not-allowed; }
  .ins-link { background:none; border:0; padding:0; color:var(--ins-blue); font-weight:500; cursor:pointer; }

  .ins-card { background:#fff; border:1px solid var(--ins-line); border-radius:10px; margin-bottom:24px; }
  .ins-card-head { display:flex; justify-content:space-between; align-items:center; gap:12px; padding:16px 24px; border-bottom:1px solid var(--ins-line); flex-wrap:wrap; }
  .ins-card-body { padding:24px; }
  .ins-card-foot { display:flex; justify-content:space-between; align-items:center; gap:12px; padding:12px 24px; border-top:1px solid var(--ins-line); color:var(--ins-muted); font-size:13px; flex-wrap:wrap; }

  .ins-kpis { display:grid; grid-template-columns:repeat(4, minmax(0,1fr)); gap:16px; margin-bottom:24px; }
  .ins-kpi { background:#fff; border:1px solid var(--ins-line); border-radius:10px; padding:16px 20px; }
  .ins-kpi .k-label { font-size:13px; color:var(--ins-muted); font-weight:500; }
  .ins-kpi .value { font-size:32px; font-weight:600; line-height:1.2; margin:4px 0; color:var(--ins-text); }
  .ins-kpi .foot { font-size:12px; color:var(--ins-muted); }
  .ins-kpi.alert .value { color:var(--ins-orange); }
  a.ins-kpi { display:block; text-decoration:none; }
  a.ins-kpi:hover { border-color:var(--ins-blue-100); text-decoration:none; }

  .ins-grid2 { display:grid; grid-template-columns:minmax(0,1fr) minmax(0,1fr); gap:24px; }
  .ins-grid2 > .ins-card { margin-bottom:0; }

  .ins-table-wrap { overflow-x:auto; }
  .ins table.ins-table { width:100%; border-collapse:collapse; }
  .ins-table th { font-size:12px; font-weight:500; text-transform:uppercase; letter-spacing:.04em; color:var(--ins-muted); padding:10px 16px; border-bottom:1px solid var(--ins-line); text-align:left; white-space:nowrap; background:#fafcfd; }
  .ins-table td { padding:12px 16px; border-bottom:1px solid #eef2f6; vertical-align:top; }
  .ins-table tr:last-child td { border-bottom:0; }
  .ins-table .num { text-align:right; white-space:nowrap; }
  .ins-table tr.overdue td { background:#fffaf6; }
  .ins-table tr.rowlink { cursor:pointer; }
  .ins-table tr.rowlink:hover td { background:var(--ins-blue-50); }
  .ins-empty { padding:32px 24px; text-align:center; color:var(--ins-muted); }

  .ins-chip { display:inline-block; padding:2px 8px; border-radius:6px; font-size:12px; font-weight:500; background:#eef2f6; color:#41505f; white-space:nowrap; }
  .ins-chip.p1 { background:var(--ins-bad-50); color:var(--ins-bad); }
  .ins-chip.p2 { background:#fff0e6; color:#c2410c; }
  .ins-chip.p3 { background:var(--ins-warn-50); color:var(--ins-warn); }
  .ins-chip.p4 { background:#eef2f6; color:#41505f; }
  .ins-chip.s1 { background:var(--ins-blue-100); color:var(--ins-blue-700); }
  .ins-chip.s2 { background:#e0f4fb; color:#036b8f; }
  .ins-chip.s3 { background:var(--ins-warn-50); color:var(--ins-warn); }
  .ins-chip.s4 { background:var(--ins-ok-50); color:var(--ins-ok); }
  .ins-chip.v0 { background:var(--ins-blue-100); color:var(--ins-blue-700); }
  .ins-chip.v2 { background:var(--ins-ok-50); color:var(--ins-ok); }
  .ins-chip.v9 { background:#eef2f6; color:#6b7785; }
  .ins-chip.late { background:#fff0e6; color:#c2410c; }
  .ins .orange { color:#c2410c; }
  .ins .ins-nw { white-space:nowrap; }

  .ins-person { display:flex; align-items:center; gap:8px; }
  .ins-avatar { width:28px; height:28px; border-radius:50%; background:var(--ins-blue-100); color:var(--ins-blue-700); display:inline-flex; align-items:center; justify-content:center; font-size:11px; font-weight:600; flex:none; }
  .ins-progress { display:flex; align-items:center; gap:8px; min-width:140px; }
  .ins-progress .bar { flex:1; height:8px; background:#eef2f6; border-radius:4px; overflow:hidden; }
  .ins-progress .bar i { display:block; height:100%; background:var(--ins-blue); }

  .ins-filters { display:flex; flex-wrap:wrap; gap:12px; align-items:flex-end; padding:16px 24px; border-bottom:1px solid var(--ins-line); }
  .ins-filters label { display:flex; flex-direction:column; gap:4px; font-size:12px; font-weight:500; color:var(--ins-muted); margin:0; }
  .ins-filters select, .ins-filters input[type=search], .ins-filters input[type=text] { height:36px; min-width:150px; border:1px solid var(--ins-line); border-radius:8px; padding:0 10px; background:#fff; color:var(--ins-text); }
  .ins-filters .check { flex-direction:row; align-items:center; gap:6px; height:36px; }

  .ins-form { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
  .ins-form .full { grid-column:1 / -1; }
  .ins-field { display:flex; flex-direction:column; gap:6px; margin:0; }
  .ins-field > span, .ins-field > label { font-size:13px; font-weight:500; color:var(--ins-text); margin:0; }
  .ins-field input:not([type=checkbox]):not([type=radio]):not([type=file]), .ins-field select, .ins-field textarea {
    width:100%; min-height:40px; border:1px solid #cfd8e1; border-radius:8px; padding:8px 12px; background:#fff; color:var(--ins-text); }
  .ins-field textarea { min-height:84px; resize:vertical; }
  .ins-field input[readonly] { background:var(--ins-bg); }
  .ins-field .help, .ins .help { font-size:12px; color:var(--ins-muted); }
  .ins-field .req { color:var(--ins-bad); }
  .ins-field.has-error input, .ins-field.has-error select, .ins-field.has-error textarea { border-color:var(--ins-bad); }
  .ins-field .err { font-size:12px; color:var(--ins-bad); }

  .ins-alert { padding:12px 16px; border-radius:8px; margin-bottom:16px; font-size:13px; }
  .ins-alert.info { background:var(--ins-blue-50); color:var(--ins-blue-700); }
  .ins-alert.warn { background:var(--ins-warn-50); color:var(--ins-warn); }
  .ins-alert.bad { background:var(--ins-bad-50); color:var(--ins-bad); }

  .ins .modal-content { border-radius:12px; border:0; }
  .ins .modal-header { padding:16px 24px; border-bottom:1px solid var(--ins-line); }
  .ins .modal-body { padding:24px; }
  .ins .modal-footer { padding:12px 24px; border-top:1px solid var(--ins-line); gap:8px; }
  .ins .modal-title { font-size:17px !important; font-weight:600; }
  .ins-field .ins-seg { align-self:flex-start; }

  .ins-meta { display:grid; grid-template-columns:repeat(4, minmax(0,1fr)); gap:16px; }
  .ins-meta div small { display:block; text-transform:uppercase; letter-spacing:.04em; font-size:11px; margin-bottom:2px; }
  .ins-meta div b { font-weight:600; }

  /* jQuery UI autocomplete inside the module */
  .ui-autocomplete { max-height:280px; overflow-y:auto; overflow-x:hidden; z-index:2000 !important; font-size:14px; }

  @media (max-width: 991px) {
    .ins-kpis { grid-template-columns:repeat(2, minmax(0,1fr)); }
    .ins-grid2 { grid-template-columns:1fr; }
    .ins-meta { grid-template-columns:repeat(2, minmax(0,1fr)); }
  }
  @media (max-width: 575px) {
    .ins-form { grid-template-columns:1fr; }
    .ins-card-head, .ins-card-body, .ins-filters { padding-left:16px; padding-right:16px; }
    .ins-kpi .value { font-size:26px; }
    .ins-filters select, .ins-filters input[type=search] { min-width:0; width:100%; }
    .ins-filters label { flex:1 1 140px; }
  }
  @media (prefers-reduced-motion: reduce) { .ins * { transition:none !important; } }
</style>
