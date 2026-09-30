@include('backoffice::MessageTemplate._styles')
<style>
  /* Mail -> Send screens. Scoped under .bs (always together with .mt for the shared tokens).
     One blue (the app's sidebar blue, darkened so white text is 5.4:1) for primary buttons, active tab/step and focus.
     Green only for success, red only for cancel/failed, amber for warnings/skipped. */
  .mt.bs, .bs { --mt-primary:#136fae; --bs-primary-dark:#0f5c91; --bs-primary-bg:#e8f2fb;
        --bs-ok-bg:#dcfce7; --bs-ok:#166534; --bs-bad-bg:#fee2e2; --bs-bad:#991b1b; --bs-warn-bg:#fef9c3; --bs-warn-line:#fde047; --bs-warn:#713f12;
        --bs-info-bg:#e8f2fb; --bs-info:#0f5c91; --bs-radius:8px; color:var(--mt-ink); }
  .bs *, .bs *::before, .bs *::after { box-sizing:border-box; }
  .bs [hidden] { display:none !important; }
  .bs .bs-sr { position:absolute !important; width:1px; height:1px; padding:0; margin:-1px; overflow:hidden; clip:rect(0,0,0,0); white-space:nowrap; border:0; }
  /* the app sets h2/h3 to 30px/23px !important; inside these screens a heading is a quiet 16px section title */
  .bs h2, .bs h3 { font-family:inherit; font-size:16px !important; font-weight:600; line-height:1.4; margin:0; color:var(--mt-ink); }
  .bs .mt-help { font-size:13px; color:var(--mt-muted); }
  .bs .mt-btn { min-height:36px; height:auto; }
  .bs .mt-btn-sm { min-height:36px; height:auto; padding:0 12px; }
  .bs .mt-btn-primary, .bs-dialog .mt-btn-primary { background:var(--mt-primary); border-color:var(--mt-primary); color:#fff; }
  .bs .mt-btn-primary:hover, .bs .mt-btn-primary:focus { background:var(--bs-primary-dark); border-color:var(--bs-primary-dark); color:#fff; }
  .bs .mt-btn[disabled], .bs .mt-btn[aria-disabled="true"] { opacity:.6; cursor:not-allowed; }
  .bs a:focus-visible, .bs button:focus-visible, .bs input:focus-visible, .bs select:focus-visible, .bs textarea:focus-visible {
      outline:2px solid var(--mt-primary); outline-offset:2px; }
  .bs .mt-input:focus { outline-color:var(--mt-primary); border-color:var(--mt-primary); }
  .bs .bs-link { background:none; border:0; padding:4px 2px; color:var(--mt-primary); font-size:13px; font-weight:600; cursor:pointer; text-decoration:none; min-height:32px; }
  .bs .bs-link:hover { color:var(--bs-primary-dark); text-decoration:underline; }
  .bs .bs-muted { color:var(--mt-muted); }

  /* page head (top row inside the panel) */
  .bs .bs-head { display:flex; flex-wrap:wrap; gap:12px; align-items:center; justify-content:space-between; margin-bottom:16px; }
  .bs .bs-head p { margin:0; }
  .bs .bs-head-end { justify-content:flex-end; }

  /* flat sections: title row with a thin divider, 24px between sections */
  .bs .bs-sec + .bs-sec { margin-top:24px; }
  .bs .bs-sec-head { display:flex; flex-wrap:wrap; align-items:baseline; gap:4px 12px; padding-bottom:8px; margin-bottom:16px; border-bottom:1px solid var(--mt-line); }
  .bs .bs-sec-head .mt-help { font-weight:400; }
  .bs h2[tabindex="-1"]:focus { outline:none; }

  /* chips: audience + channel only */
  .bs .bs-chip { display:inline-flex; align-items:center; padding:1px 8px; border-radius:4px; font-size:12px; font-weight:600; line-height:18px;
      background:var(--mt-fill); color:var(--mt-muted); white-space:nowrap; }
  .bs .bs-chips { display:inline-flex; flex-wrap:wrap; gap:4px; }

  /* status badge: small icon + text */
  .bs .bs-badge { display:inline-flex; align-items:center; gap:4px; padding:1px 6px; border-radius:4px; font-size:12px; font-weight:600; line-height:18px; white-space:nowrap; }
  .bs .bs-badge .fa { font-size:11px; }
  .bs .bs-badge-running { background:var(--bs-info-bg); color:var(--bs-info); }
  .bs .bs-badge-paused, .bs .bs-badge-skipped { background:var(--bs-warn-bg); color:var(--bs-warn); }
  .bs .bs-badge-cancelled, .bs .bs-badge-pending { background:var(--mt-fill); color:var(--mt-muted); }
  .bs .bs-badge-done, .bs .bs-badge-sent { background:var(--bs-ok-bg); color:var(--bs-ok); }
  .bs .bs-badge-failed { background:var(--bs-bad-bg); color:var(--bs-bad); }
  .bs .bs-badge-none { background:none; padding:0; color:var(--mt-muted); font-weight:400; font-style:italic; font-size:13px; }
  .bs .bs-badge-none .fa { display:none; }

  /* warning / error lines */
  .bs .bs-warn { display:flex; gap:6px; align-items:flex-start; color:var(--bs-warn); font-size:12px; margin:4px 0 0; }
  .bs .bs-errors { background:var(--bs-bad-bg); border:1px solid #fca5a5; color:var(--bs-bad); border-radius:6px; padding:12px 16px; margin-bottom:16px; font-size:14px; }
  .bs .bs-errors ul { margin:6px 0 0; padding-left:20px; }
  .bs .bs-errors a { color:var(--bs-bad); text-decoration:underline; font-weight:600; }
  .bs .bs-sec-error { display:flex; gap:8px; align-items:flex-start; color:var(--bs-bad); font-size:13px; font-weight:600; margin:0 0 12px; }

  /* progress: bar + one counts line */
  .bs .bs-progress { display:flex; height:6px; border-radius:999px; background:var(--mt-line); overflow:hidden; }
  .bs .bs-progress span { display:block; height:100%; transition:width .25s ease; }
  .bs .bs-progress .bs-p-sent { background:#15803d; }
  .bs .bs-progress .bs-p-failed { background:#b91c1c; }
  .bs .bs-progress .bs-p-skipped { background:#d97706; }
  .bs .bs-progress-text { font-size:12px; color:var(--mt-muted); margin-top:4px; white-space:nowrap; font-variant-numeric:tabular-nums; }
  .bs .bs-progress-text .bs-c-failed { color:var(--bs-bad); }
  .bs .bs-progress-text .bs-c-skipped { color:var(--bs-warn); }

  /* sends list table */
  .bs .bs-sends { min-width:980px; }
  .bs .bs-sends th, .bs .bs-sends td { vertical-align:top; }
  .bs .bs-sends .bs-col-id { width:56px; color:var(--mt-muted); font-variant-numeric:tabular-nums; white-space:nowrap; }
  .bs .bs-sends .bs-col-status { width:110px; }
  .bs .bs-sends .bs-col-progress { width:176px; }
  .bs .bs-sends .bs-col-progress .bs-progress { width:150px; margin-top:7px; }
  .bs .bs-sends .bs-col-started { width:130px; font-size:12px; color:var(--mt-muted); }
  .bs .bs-sends .bs-col-actions { width:1%; white-space:nowrap; }
  .bs .bs-send-name { display:block; font-size:14px; font-weight:600; color:var(--mt-ink); overflow-wrap:anywhere; }
  .bs a.bs-send-name:hover { color:var(--mt-primary); }
  .bs .bs-sub { display:block; font-size:12px; color:var(--mt-muted); margin-top:2px; overflow-wrap:anywhere; }
  .bs .bs-subject { font-size:13px; color:var(--mt-muted); margin:0; overflow-wrap:anywhere; }
  .bs .bs-meta { font-size:12px; color:var(--mt-muted); }
  .bs .mt-actions { flex-wrap:nowrap; }
  .bs .mt-actions form { margin:0; }
  .bs .bs-refresh { font-size:12px; color:var(--mt-muted); margin:0; display:inline-flex; align-items:center; gap:6px; }
  .bs .bs-empty { text-align:center; padding:48px 16px; color:var(--mt-muted); }
  .bs .bs-empty .fa { font-size:36px; color:#94a3b8; margin-bottom:12px; display:block; }
  .bs .bs-empty strong { display:block; font-size:16px; color:var(--mt-ink); margin-bottom:12px; }

  /* send detail summary */
  .bs .bs-summary-line { display:flex; flex-wrap:wrap; align-items:center; gap:6px 10px; }
  .bs .bs-show-progress { max-width:520px; margin:12px 0 24px; }

  /* quiet stat row (review) */
  .bs .bs-tiles { display:flex; flex-wrap:wrap; gap:12px 0; margin:0 0 24px; }
  .bs .bs-tile { padding:0 24px; border-left:1px solid var(--mt-line); min-width:110px; }
  .bs .bs-tile:first-child { padding-left:0; border-left:0; }
  .bs .bs-tile-label { font-size:13px; color:var(--mt-muted); }
  .bs .bs-tile-value { font-size:20px; font-weight:600; color:var(--mt-ink); line-height:1.3; font-variant-numeric:tabular-nums; }
  .bs .bs-tile-note { font-size:12px; color:var(--mt-muted); }

  /* tabs (links or buttons) */
  .bs .bs-tabs { display:flex; gap:4px; border-bottom:1px solid var(--mt-line); margin:0 0 12px; overflow-x:auto; }
  .bs .bs-tab { display:inline-flex; align-items:center; gap:6px; min-height:40px; padding:0 12px; border:0; border-bottom:2px solid transparent;
      background:none; color:var(--mt-muted); font-size:14px; font-weight:600; text-decoration:none; white-space:nowrap; cursor:pointer; }
  .bs .bs-tab:hover { color:var(--mt-ink); text-decoration:none; }
  .bs .bs-tab[aria-current="page"], .bs .bs-tab[aria-pressed="true"] { color:var(--mt-primary); border-bottom-color:var(--mt-primary); }
  .bs .bs-tab-n { display:inline-block; min-width:20px; padding:0 5px; border-radius:4px; background:var(--mt-fill); color:var(--mt-muted); font-size:12px; text-align:center; font-variant-numeric:tabular-nums; }

  /* tables: flat */
  .bs .bs-table-wrap { overflow-x:auto; }
  .bs .bs-table-wrap table { margin:0; min-width:560px; }
  .bs .bs-table-wrap th, .bs .bs-table-wrap td { padding:10px 12px; }
  .bs .bs-table-wrap th { background:none; border-bottom:1px solid #cbd5e1; }
  .bs tr.bs-row-failed td { background:#fff7f7; }
  .bs .bs-note-failed { display:flex; gap:6px; color:var(--bs-bad); font-size:13px; }
  .bs .bs-pager { display:flex; flex-wrap:wrap; align-items:center; gap:8px; margin-top:12px; font-size:13px; color:var(--mt-muted); }

  /* stepper */
  .bs .bs-stepper { list-style:none; display:flex; flex-wrap:wrap; gap:8px 20px; margin:0 0 20px; padding:0; }
  .bs .bs-stepper li { display:flex; align-items:center; gap:8px; font-size:13px; font-weight:600; color:var(--mt-muted); }
  .bs .bs-stepper .bs-dot { display:inline-flex; align-items:center; justify-content:center; width:24px; height:24px; border-radius:50%;
      border:1px solid #94a3b8; font-size:12px; font-weight:600; background:#fff; color:var(--mt-muted); }
  .bs .bs-stepper .bs-dot .fa { font-size:11px; }
  .bs .bs-stepper li[aria-current="step"] { color:var(--mt-primary); }
  .bs .bs-stepper li[aria-current="step"] .bs-dot { border-color:var(--mt-primary); background:var(--mt-primary); color:#fff; }
  .bs .bs-stepper li.is-done { color:var(--mt-ink); }
  .bs .bs-stepper li.is-done .bs-dot { border-color:var(--mt-primary); background:var(--bs-primary-bg); color:var(--mt-primary); }
  .bs .bs-stepper a { color:inherit; text-decoration:none; display:inline-flex; align-items:center; gap:8px; min-height:32px; }
  .bs .bs-stepper a:hover span:last-child { text-decoration:underline; }

  /* compose layout */
  .bs .bs-compose { display:grid; grid-template-columns:minmax(0, 1fr) 280px; gap:32px; align-items:start; }
  .bs .bs-side { position:sticky; top:72px; }
  .bs fieldset { border:0; margin:0 0 20px; padding:0; min-width:0; }
  .bs legend { font-size:14px; font-weight:600; color:var(--mt-ink); margin:0 0 8px; padding:0; border:0; width:auto; }

  /* one-choice lists (template, group): one row each */
  .bs .bs-choices { border-top:1px solid var(--mt-line); }
  .bs .bs-choice { position:relative; display:block; margin:0; cursor:pointer; font-weight:400; border-bottom:1px solid var(--mt-line); }
  .bs .bs-choice input { position:absolute; opacity:0; width:1px; height:1px; }
  .bs .bs-choice-body { display:flex; flex-wrap:wrap; align-items:baseline; gap:2px 10px; min-height:44px; padding:11px 12px 11px 40px; position:relative;
      background:#fff; transition:background-color .15s ease; }
  .bs .bs-choice-body::before { content:""; position:absolute; left:12px; top:13px; width:16px; height:16px; border-radius:50%; border:2px solid #64748b; background:#fff; }
  .bs .bs-choice:hover .bs-choice-body { background:var(--mt-fill); }
  .bs .bs-choice input:checked + .bs-choice-body { background:var(--bs-primary-bg); }
  .bs .bs-choice input:checked + .bs-choice-body::before { border-color:var(--mt-primary); background:radial-gradient(circle, var(--mt-primary) 40%, #fff 46%); }
  .bs .bs-choice input:focus-visible + .bs-choice-body { outline:2px solid var(--mt-primary); outline-offset:-2px; }
  .bs .bs-choice-title { font-size:14px; font-weight:600; color:var(--mt-ink); overflow-wrap:anywhere; }
  .bs .bs-choice-meta { font-size:13px; color:var(--mt-muted); }
  .bs .bs-choice-note { font-size:13px; color:var(--mt-muted); }
  .bs .bs-choice.is-unfit .bs-choice-title { color:var(--mt-muted); }
  .bs .bs-group-n { font-size:13px; color:var(--mt-muted); font-variant-numeric:tabular-nums; }

  .bs .bs-oneoff { border-left:2px solid var(--mt-line); padding:4px 0 4px 16px; margin:0 0 20px; }

  /* Email / SMS switches: the native checkbox stays in the DOM (keyboard + label); the track is drawn next to it */
  .bs .bs-toggles { display:flex; flex-wrap:wrap; gap:8px 28px; }
  .bs .bs-toggle { position:relative; display:inline-flex; align-items:center; gap:10px; min-height:36px; margin:0; cursor:pointer; font-size:14px; font-weight:600; color:var(--mt-ink); }
  .bs .bs-toggle input { position:absolute; opacity:0; width:1px; height:1px; margin:0; }
  .bs .bs-track { position:relative; flex:0 0 36px; width:36px; height:20px; border-radius:999px; background:#94a3b8; transition:background-color .15s ease; }
  .bs .bs-track::after { content:""; position:absolute; top:2px; left:2px; width:16px; height:16px; border-radius:50%; background:#fff;
      box-shadow:0 1px 2px rgba(15,23,42,.3); transition:transform .15s ease; }
  .bs .bs-toggle input:checked + .bs-track { background:var(--mt-primary); }
  .bs .bs-toggle input:checked + .bs-track::after { transform:translateX(16px); }
  .bs .bs-toggle input:focus-visible + .bs-track { outline:2px solid var(--mt-primary); outline-offset:2px; }
  .bs .bs-toggle input:disabled + .bs-track { background:#e2e8f0; }
  .bs .bs-toggle input:disabled + .bs-track::after { box-shadow:none; background:#f8fafc; }
  .bs .bs-toggle input:disabled ~ .bs-toggle-text { color:var(--mt-muted); }
  .bs .bs-toggle.is-disabled, .bs .bs-toggle input:disabled { cursor:not-allowed; }

  /* searchable checkbox lists (locations, buildings, roles) */
  .bs .bs-pick { margin-bottom:20px; }
  .bs .bs-pick-head { display:flex; flex-wrap:wrap; gap:8px 16px; align-items:center; margin-bottom:8px; }
  .bs .bs-pick-head .mt-input { width:280px; max-width:100%; height:36px; }
  .bs .bs-pick-tools { display:flex; flex-wrap:wrap; align-items:center; gap:4px 6px; font-size:13px; color:var(--mt-muted); }
  .bs .bs-pick-sep { color:#94a3b8; }
  .bs .bs-pick-status { margin-left:6px; }
  .bs .bs-pick-list { max-height:260px; overflow-y:auto; padding:4px 0; display:grid; grid-template-columns:repeat(auto-fill, minmax(220px, 1fr)); gap:0 16px;
      border-top:1px solid var(--mt-line); border-bottom:1px solid var(--mt-line); }
  .bs .bs-pick-item { display:flex; align-items:center; gap:8px; min-height:36px; padding:2px 6px; margin:0; border-radius:4px; font-size:14px; font-weight:400; color:var(--mt-ink); cursor:pointer; }
  .bs .bs-pick-item:hover { background:var(--mt-fill); }
  .bs .bs-pick-item input { width:16px; height:16px; flex:0 0 16px; margin:0; accent-color:var(--mt-primary); }
  .bs .bs-pick-item .bs-pick-name { min-width:0; overflow-wrap:anywhere; }
  .bs .bs-pick-n { font-size:13px; color:var(--mt-muted); font-variant-numeric:tabular-nums; }
  .bs .bs-pick-empty { padding:8px 6px; margin:0; color:var(--mt-muted); font-size:13px; }
  .bs .bs-roles { max-height:none; }

  /* summary (compose): one light grey box */
  .bs .bs-summary { background:var(--mt-fill); border-radius:var(--bs-radius); padding:16px; }
  .bs .bs-summary h2 { margin-bottom:8px; }
  .bs .bs-summary dl { margin:0 0 12px; }
  .bs .bs-summary dl > div { display:flex; justify-content:space-between; gap:12px; padding:7px 0; border-bottom:1px solid #e2e8f0; font-size:14px; }
  .bs .bs-summary dt { font-weight:400; color:var(--mt-muted); }
  .bs .bs-summary dd { margin:0; font-weight:600; font-variant-numeric:tabular-nums; }
  .bs .bs-summary .bs-summary-total dd { font-size:16px; }
  .bs .bs-btn-block { width:100%; justify-content:center; }
  .bs .bs-summary .mt-btn + .mt-btn { margin-top:8px; }

  /* review */
  .bs .bs-review-head { display:flex; flex-wrap:wrap; align-items:center; gap:6px 10px; margin-bottom:16px; }
  .bs .bs-preview { display:grid; grid-template-columns:minmax(0, 1fr) minmax(0, 300px); gap:24px; }
  .bs .bs-mail { border:1px solid var(--mt-line); border-radius:6px; overflow:hidden; }
  .bs .bs-mail-head { padding:8px 12px; font-size:13px; border-bottom:1px solid var(--mt-line); color:var(--mt-muted); }
  .bs .bs-mail-head div + div { margin-top:2px; }
  .bs .bs-mail-head strong { color:var(--mt-ink); font-weight:600; }
  .bs .bs-mail iframe { display:block; width:100%; height:300px; border:0; background:#fff; }
  .bs .bs-sms-label { font-size:13px !important;font-weight:600; color:var(--mt-muted); margin:0 0 6px; }
  .bs .bs-sms-bubble { background:var(--mt-fill); color:var(--mt-ink); border-radius:14px 14px 14px 4px; padding:10px 14px; white-space:pre-wrap; overflow-wrap:anywhere; font-size:14px; }
  .bs .bs-people-tools { display:flex; flex-wrap:wrap; gap:12px 16px; align-items:flex-start; margin-bottom:12px; }
  .bs .bs-people-tools > div { flex:1 1 220px; }
  .bs .bs-people-tools .mt-input { height:36px; }
  .bs .bs-check { display:inline-flex; align-items:center; gap:8px; font-size:13px; font-weight:600; min-height:36px; margin:0; cursor:pointer; }
  .bs .bs-check input, .bs td .bs-row-check { width:16px; height:16px; margin:0; accent-color:var(--mt-primary); }
  .bs tr.bs-row-off td { color:var(--mt-muted); }
  .bs tr.bs-row-off td:nth-child(2) { text-decoration:line-through; }
  .bs .bs-add-results { display:flex; flex-wrap:wrap; gap:6px; margin-top:8px; }
  .bs .bs-actionbar { position:sticky; bottom:0; z-index:5; display:flex; flex-wrap:wrap; gap:8px 12px; align-items:center; justify-content:space-between;
      background:#fff; border-top:1px solid var(--mt-line); box-shadow:0 -6px 12px -8px rgba(15,23,42,.15); padding:12px 0; margin:24px 0 0; }
  .bs .bs-actionbar p { margin:0; font-size:14px; color:var(--mt-muted); }
  .bs .bs-actionbar .bs-actionbar-btns { display:flex; gap:8px; flex-wrap:wrap; }

  /* in-page dialog */
  .bs-dialog-backdrop { position:fixed; inset:0; top:0; right:0; bottom:0; left:0; background:rgba(15,23,42,.5); z-index:10050; display:flex; align-items:center; justify-content:center; padding:16px; }
  .bs-dialog-backdrop[hidden] { display:none; }
  .bs-dialog { background:#fff; color:#0f172a; border-radius:10px; max-width:460px; width:100%; padding:20px; box-shadow:0 20px 40px rgba(15,23,42,.25); }
  .bs-dialog:focus { outline:none; }
  .bs-dialog h2 { font-size:18px !important; font-weight:600; margin:0 0 12px; }
  .bs-dialog ul { list-style:none; margin:0 0 12px; padding:0; }
  .bs-dialog li { display:flex; justify-content:space-between; gap:12px; padding:6px 0; border-bottom:1px solid #e2e8f0; font-size:14px; }
  .bs-dialog li strong { font-variant-numeric:tabular-nums; }
  .bs-dialog p { font-size:14px; margin:0 0 12px; }
  .bs-dialog .bs-dialog-warn { display:flex; gap:8px; background:#fef9c3; border:1px solid #fde047; color:#713f12; border-radius:6px; padding:8px 12px; font-size:13px; }
  .bs-dialog .bs-dialog-actions { display:flex; flex-wrap:wrap; justify-content:flex-end; gap:8px; margin-top:16px; }
  body.bs-dialog-open { overflow:hidden; }

  @media (max-width:991px) {
    .bs .bs-compose { grid-template-columns:minmax(0, 1fr); gap:24px; }
    .bs .bs-side { position:static; }
    .bs .bs-preview { grid-template-columns:minmax(0, 1fr); }
  }
  @media (max-width:600px) {
    .bs .bs-actionbar .bs-actionbar-btns { width:100%; }
    .bs .bs-actionbar .bs-actionbar-btns .mt-btn { flex:1; justify-content:center; }
    .bs .bs-pick-list { grid-template-columns:minmax(0, 1fr); }
    .bs .bs-tiles { display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:12px 16px; }
    .bs .bs-tile, .bs .bs-tile:first-child { padding:0; border-left:0; min-width:0; }
    .bs .bs-head-end { justify-content:space-between; }
  }
  @media (prefers-reduced-motion:reduce) { .bs *, .bs *::after, .bs-dialog { transition:none !important; } }
</style>
