@include('backoffice::MessageTemplate._styles')
<style>
  /* Mail -> Send screens. Scoped under .bs (always together with .mt for the shared tokens). */
  .bs { --bs-ok-bg:#dcfce7; --bs-ok:#166534; --bs-bad-bg:#fee2e2; --bs-bad:#991b1b; --bs-warn-bg:#fef9c3; --bs-warn-line:#fde047; --bs-warn:#713f12;
        --bs-info-bg:#e0e7ff; --bs-info:#1e3a8a; --bs-radius:8px; color:var(--mt-ink); }
  .bs *, .bs *::before, .bs *::after { box-sizing:border-box; }
  .bs [hidden] { display:none !important; }
  .bs .bs-sr { position:absolute !important; width:1px; height:1px; padding:0; margin:-1px; overflow:hidden; clip:rect(0,0,0,0); white-space:nowrap; border:0; }
  .bs h2 { font-size:18px; font-weight:600; margin:0 0 4px; color:var(--mt-ink); }
  .bs h3 { font-size:15px; font-weight:600; margin:0 0 8px; color:var(--mt-ink); }
  .bs .mt-help { font-size:13px; color:var(--mt-muted); }
  .bs .mt-btn { min-height:40px; height:auto; }
  .bs .mt-btn-sm { min-height:36px; height:auto; padding:0 12px; }
  /* white on #15803d is 5:1 (the lighter green is below 4.5:1 for 14px text) */
  .bs .mt-btn-primary { background:#15803d; border-color:#15803d; }
  .bs .mt-btn-primary:hover, .bs .mt-btn-primary:focus { background:#166534; border-color:#166534; color:#fff; }
  .bs .mt-btn[disabled], .bs .mt-btn[aria-disabled="true"] { opacity:.6; cursor:not-allowed; }
  .bs a:focus-visible, .bs button:focus-visible, .bs input:focus-visible, .bs select:focus-visible, .bs textarea:focus-visible {
      outline:2px solid var(--mt-primary); outline-offset:2px; }
  .bs .bs-link { background:none; border:0; padding:4px 2px; color:var(--mt-primary); font-size:13px; font-weight:600; cursor:pointer; text-decoration:underline; min-height:32px; }
  .bs .bs-link:hover { color:#1e3a8a; }

  /* page head */
  .bs .bs-head { display:flex; flex-wrap:wrap; gap:12px; align-items:center; justify-content:space-between; margin-bottom:16px; }
  .bs .bs-head p { margin:0; }

  /* cards */
  .bs .bs-card { background:#fff; border:1px solid var(--mt-line); border-radius:var(--bs-radius); padding:20px; margin-bottom:16px; }
  .bs .bs-card > h2 { display:flex; align-items:center; gap:10px; margin-bottom:12px; }
  .bs .bs-step-no { display:inline-flex; align-items:center; justify-content:center; width:28px; height:28px; border-radius:50%;
      background:var(--mt-primary); color:#fff; font-size:14px; font-weight:700; flex:0 0 28px; }

  /* chips + badges (always icon + text) */
  .bs .bs-chip { display:inline-flex; align-items:center; gap:5px; padding:2px 10px; border-radius:999px; font-size:12px; font-weight:600;
      background:var(--mt-fill); color:var(--mt-muted); border:1px solid var(--mt-line); white-space:nowrap; }
  .bs .bs-chips { display:flex; flex-wrap:wrap; gap:6px; }
  .bs .bs-badge { display:inline-flex; align-items:center; gap:6px; padding:3px 10px; border-radius:999px; font-size:12px; font-weight:700; white-space:nowrap; }
  .bs .bs-badge-running { background:var(--bs-info-bg); color:var(--bs-info); }
  .bs .bs-badge-paused { background:var(--bs-warn-bg); color:var(--bs-warn); }
  .bs .bs-badge-cancelled, .bs .bs-badge-skipped, .bs .bs-badge-pending { background:var(--mt-fill); color:var(--mt-muted); }
  .bs .bs-badge-done, .bs .bs-badge-sent { background:var(--bs-ok-bg); color:var(--bs-ok); }
  .bs .bs-badge-failed { background:var(--bs-bad-bg); color:var(--bs-bad); }
  .bs .bs-badge-none { background:#fff; color:var(--mt-muted); border:1px dashed #94a3b8; font-weight:600; }

  /* warning / error lines */
  .bs .bs-warn { display:flex; gap:8px; align-items:flex-start; background:var(--bs-warn-bg); border:1px solid var(--bs-warn-line); color:var(--bs-warn);
      border-radius:6px; padding:8px 12px; font-size:13px; margin-top:10px; }
  .bs .bs-errors { background:var(--bs-bad-bg); border:1px solid #fca5a5; color:var(--bs-bad); border-radius:6px; padding:12px 16px; margin-bottom:16px; font-size:14px; }
  .bs .bs-errors ul { margin:6px 0 0; padding-left:20px; }
  .bs .bs-errors a { color:var(--bs-bad); text-decoration:underline; font-weight:600; }
  .bs .bs-sec-error { display:flex; gap:8px; align-items:flex-start; color:var(--bs-bad); font-size:13px; font-weight:600; margin:0 0 12px; }

  /* progress */
  .bs .bs-progress { display:flex; height:8px; border-radius:999px; background:var(--mt-line); overflow:hidden; }
  .bs .bs-progress span { display:block; height:100%; transition:width .25s ease; }
  .bs .bs-progress .bs-p-sent { background:#15803d; }
  .bs .bs-progress .bs-p-failed { background:#b91c1c; }
  .bs .bs-progress .bs-p-skipped { background:#94a3b8; }
  .bs .bs-progress-text { font-size:12px; color:var(--mt-muted); margin-top:4px; }
  .bs .bs-counts { display:flex; flex-wrap:wrap; gap:4px 14px; font-size:13px; color:var(--mt-muted); margin-top:6px; }
  .bs .bs-counts span { display:inline-flex; align-items:center; gap:5px; white-space:nowrap; }
  .bs .bs-counts .bs-c-sent i { color:#15803d; }
  .bs .bs-counts .bs-c-failed i { color:#b91c1c; }

  /* sends list */
  .bs .bs-list { list-style:none; margin:0; padding:0; display:flex; flex-direction:column; gap:12px; }
  .bs .bs-send { display:grid; grid-template-columns:minmax(0, 1fr) minmax(220px, 300px) auto; gap:16px; align-items:start;
      border:1px solid var(--mt-line); border-radius:var(--bs-radius); padding:16px; background:#fff; }
  .bs .bs-send-title { display:flex; flex-wrap:wrap; align-items:center; gap:8px; font-size:15px; font-weight:600; margin:0 0 2px; }
  .bs .bs-send-title a { color:var(--mt-ink); }
  .bs .bs-send-title a:hover { color:var(--mt-primary); }
  .bs .bs-send-id { color:var(--mt-muted); font-weight:600; }
  .bs .bs-subject { font-size:13px; color:var(--mt-muted); margin:0 0 8px; overflow-wrap:anywhere; }
  .bs .bs-meta { font-size:12px; color:var(--mt-muted); margin-top:8px; }
  .bs .bs-send .mt-actions { justify-content:flex-end; }
  .bs .bs-send .mt-actions form { margin:0; }
  .bs .bs-refresh { font-size:12px; color:var(--mt-muted); margin:0; display:inline-flex; align-items:center; gap:6px; }
  .bs .bs-empty { text-align:center; padding:48px 16px; color:var(--mt-muted); }
  .bs .bs-empty .fa { font-size:36px; color:#94a3b8; margin-bottom:12px; display:block; }
  .bs .bs-empty strong { display:block; font-size:16px; color:var(--mt-ink); margin-bottom:12px; }

  /* tiles */
  .bs .bs-tiles { display:grid; grid-template-columns:repeat(auto-fit, minmax(130px, 1fr)); gap:12px; margin:16px 0; }
  .bs .bs-tile { border:1px solid var(--mt-line); border-radius:var(--bs-radius); padding:12px 14px; background:#fff; }
  .bs .bs-tile-label { display:flex; align-items:center; gap:6px; font-size:12px; font-weight:600; color:var(--mt-muted); text-transform:uppercase; letter-spacing:.03em; }
  .bs .bs-tile-value { font-size:24px; font-weight:700; color:var(--mt-ink); line-height:1.3; font-variant-numeric:tabular-nums; }
  .bs .bs-tile-note { font-size:12px; color:var(--mt-muted); }

  /* tabs (links or buttons) */
  .bs .bs-tabs { display:flex; gap:4px; border-bottom:1px solid var(--mt-line); margin:0 0 12px; overflow-x:auto; }
  .bs .bs-tab { display:inline-flex; align-items:center; gap:6px; min-height:40px; padding:0 14px; border:0; border-bottom:3px solid transparent;
      background:none; color:var(--mt-muted); font-size:14px; font-weight:600; text-decoration:none; white-space:nowrap; cursor:pointer; }
  .bs .bs-tab:hover { color:var(--mt-ink); text-decoration:none; }
  .bs .bs-tab[aria-current="page"], .bs .bs-tab[aria-pressed="true"] { color:var(--mt-primary); border-bottom-color:var(--mt-primary); }
  .bs .bs-tab-n { display:inline-block; min-width:22px; padding:0 6px; border-radius:999px; background:var(--mt-fill); color:var(--mt-ink); font-size:12px; text-align:center; }

  /* tables */
  .bs .bs-table-wrap { overflow-x:auto; border:1px solid var(--mt-line); border-radius:var(--bs-radius); }
  .bs .bs-table-wrap table { margin:0; min-width:560px; }
  .bs .bs-table-wrap th, .bs .bs-table-wrap td { padding:10px 12px; }
  .bs tr.bs-row-failed td { background:#fff7f7; }
  .bs .bs-note-failed { display:flex; gap:6px; color:var(--bs-bad); font-size:13px; }
  .bs .bs-pager { display:flex; flex-wrap:wrap; align-items:center; gap:8px; margin-top:12px; font-size:13px; color:var(--mt-muted); }

  /* stepper */
  .bs .bs-stepper { list-style:none; display:flex; flex-wrap:wrap; gap:8px 24px; margin:0 0 16px; padding:0; counter-reset:none; }
  .bs .bs-stepper li { display:flex; align-items:center; gap:8px; font-size:14px; font-weight:600; color:var(--mt-muted); }
  .bs .bs-stepper .bs-dot { display:inline-flex; align-items:center; justify-content:center; width:28px; height:28px; border-radius:50%;
      border:2px solid #94a3b8; font-size:13px; font-weight:700; background:#fff; color:var(--mt-muted); }
  .bs .bs-stepper li[aria-current="step"] { color:var(--mt-primary); }
  .bs .bs-stepper li[aria-current="step"] .bs-dot { border-color:var(--mt-primary); background:var(--mt-primary); color:#fff; }
  .bs .bs-stepper li.is-done { color:var(--mt-ink); }
  .bs .bs-stepper li.is-done .bs-dot { border-color:#15803d; background:#15803d; color:#fff; }
  .bs .bs-stepper a { color:inherit; text-decoration:none; display:inline-flex; align-items:center; gap:8px; }
  .bs .bs-stepper a:hover span:last-child { text-decoration:underline; }

  /* compose layout */
  .bs .bs-compose { display:grid; grid-template-columns:minmax(0, 1fr) 300px; gap:16px; align-items:start; }
  .bs .bs-side { position:sticky; top:72px; }
  .bs fieldset { border:0; margin:0 0 16px; padding:0; min-width:0; }
  .bs legend { font-size:13px; font-weight:600; color:var(--mt-ink); margin:0 0 8px; padding:0; border:0; width:auto; }
  .bs .bs-choices { display:grid; grid-template-columns:repeat(auto-fill, minmax(220px, 1fr)); gap:10px; }
  .bs .bs-choice { position:relative; display:block; margin:0; cursor:pointer; font-weight:400; }
  .bs .bs-choice input { position:absolute; opacity:0; width:1px; height:1px; }
  .bs .bs-choice-body { display:flex; flex-direction:column; gap:6px; height:100%; min-height:88px; padding:12px 14px 12px 40px; border:1px solid #cbd5e1;
      border-radius:var(--bs-radius); background:#fff; position:relative; transition:border-color .15s ease, background-color .15s ease; }
  .bs .bs-choice-body::before { content:""; position:absolute; left:14px; top:14px; width:16px; height:16px; border-radius:50%; border:2px solid #64748b; background:#fff; }
  .bs .bs-choice input:checked + .bs-choice-body { border-color:var(--mt-primary); background:#eef2ff; box-shadow:inset 0 0 0 1px var(--mt-primary); }
  .bs .bs-choice input:checked + .bs-choice-body::before { border-color:var(--mt-primary); background:radial-gradient(circle, var(--mt-primary) 45%, #fff 50%); }
  .bs .bs-choice input:focus-visible + .bs-choice-body { outline:2px solid var(--mt-primary); outline-offset:2px; }
  .bs .bs-choice:hover .bs-choice-body { border-color:#94a3b8; }
  .bs .bs-choice-title { font-size:14px; font-weight:600; color:var(--mt-ink); overflow-wrap:anywhere; }
  .bs .bs-choice-note { display:inline-flex; gap:5px; align-items:center; font-size:12px; color:var(--bs-warn); }
  .bs .bs-choice.is-unfit .bs-choice-body { background:var(--mt-fill); }
  .bs .bs-choice.is-unfit .bs-choice-title { color:var(--mt-muted); }
  .bs .bs-choice-new .bs-choice-body { border-style:dashed; }
  .bs .bs-group-n { font-size:22px; font-weight:700; color:var(--mt-ink); font-variant-numeric:tabular-nums; }
  .bs .bs-group-n-label { font-size:12px; color:var(--mt-muted); }

  .bs .bs-oneoff { border-left:3px solid var(--mt-line); padding:4px 0 4px 16px; margin:0 0 16px; }

  .bs .bs-toggles { display:flex; flex-wrap:wrap; gap:10px; }
  .bs .bs-toggle { position:relative; margin:0; cursor:pointer; }
  .bs .bs-toggle input { position:absolute; opacity:0; width:1px; height:1px; }
  .bs .bs-toggle span { display:inline-flex; align-items:center; gap:8px; min-height:40px; padding:0 14px; border:1px solid #94a3b8; border-radius:6px;
      background:#fff; font-size:14px; font-weight:600; color:var(--mt-ink); transition:background-color .15s ease, border-color .15s ease; }
  .bs .bs-toggle span .bs-tick { width:16px; height:16px; border:2px solid #64748b; border-radius:3px; display:inline-flex; align-items:center; justify-content:center; font-size:11px; color:transparent; }
  .bs .bs-toggle input:checked + span { background:#eef2ff; border-color:var(--mt-primary); color:var(--mt-primary); }
  .bs .bs-toggle input:checked + span .bs-tick { background:var(--mt-primary); border-color:var(--mt-primary); color:#fff; }
  .bs .bs-toggle input:focus-visible + span { outline:2px solid var(--mt-primary); outline-offset:2px; }
  .bs .bs-toggle input:disabled + span { background:var(--mt-fill); color:var(--mt-muted); border-style:dashed; cursor:not-allowed; }

  /* searchable checkbox lists */
  .bs .bs-pick { border:1px solid var(--mt-line); border-radius:var(--bs-radius); margin-bottom:16px; }
  .bs .bs-pick-head { display:flex; flex-wrap:wrap; gap:8px 12px; align-items:flex-end; padding:12px; border-bottom:1px solid var(--mt-line); background:var(--mt-fill); border-radius:var(--bs-radius) var(--bs-radius) 0 0; }
  .bs .bs-pick-head > div:first-child { flex:1 1 200px; }
  .bs .bs-pick-head label { margin-bottom:4px; }
  .bs .bs-pick-tools { display:flex; flex-wrap:wrap; align-items:center; gap:8px; font-size:13px; color:var(--mt-muted); }
  .bs .bs-pick-list { max-height:260px; overflow-y:auto; padding:6px; display:grid; grid-template-columns:repeat(auto-fill, minmax(220px, 1fr)); gap:2px 8px; }
  .bs .bs-pick-item { display:flex; align-items:center; gap:10px; min-height:36px; padding:4px 8px; margin:0; border-radius:6px; font-size:14px; font-weight:400; cursor:pointer; }
  .bs .bs-pick-item:hover { background:var(--mt-fill); }
  .bs .bs-pick-item input { width:18px; height:18px; flex:0 0 18px; margin:0; }
  .bs .bs-pick-item .bs-pick-name { flex:1; min-width:0; overflow-wrap:anywhere; }
  .bs .bs-pick-n { font-size:12px; color:var(--mt-muted); font-variant-numeric:tabular-nums; }
  .bs .bs-pick-empty { padding:12px; color:var(--mt-muted); font-size:13px; }
  .bs .bs-roles { display:flex; flex-wrap:wrap; gap:8px; }
  .bs .bs-role { position:relative; margin:0; cursor:pointer; font-weight:400; }
  .bs .bs-role input { position:absolute; opacity:0; width:1px; height:1px; }
  .bs .bs-role span { display:inline-flex; align-items:center; gap:6px; min-height:36px; padding:0 12px; border:1px solid #94a3b8; border-radius:999px; font-size:13px; background:#fff; }
  .bs .bs-role input:checked + span { background:#eef2ff; border-color:var(--mt-primary); color:var(--mt-primary); font-weight:600; }
  .bs .bs-role input:checked + span::before { content:"\f00c"; font-family:FontAwesome; font-size:11px; }
  .bs .bs-role input:focus-visible + span { outline:2px solid var(--mt-primary); outline-offset:2px; }

  /* summary */
  .bs .bs-summary dl { margin:0 0 16px; }
  .bs .bs-summary dl > div { display:flex; justify-content:space-between; gap:12px; padding:8px 0; border-bottom:1px solid var(--mt-line); font-size:14px; }
  .bs .bs-summary dt { font-weight:400; color:var(--mt-muted); display:inline-flex; gap:6px; align-items:center; }
  .bs .bs-summary dd { margin:0; font-weight:700; font-variant-numeric:tabular-nums; }
  .bs .bs-summary .bs-summary-total dd { font-size:20px; }
  .bs .bs-btn-block { width:100%; justify-content:center; }
  .bs .bs-summary .mt-btn + .mt-btn { margin-top:8px; }

  /* review */
  .bs .bs-preview { display:grid; grid-template-columns:minmax(0, 1fr) minmax(0, 320px); gap:16px; }
  .bs .bs-mail { border:1px solid var(--mt-line); border-radius:var(--bs-radius); overflow:hidden; }
  .bs .bs-mail-head { background:var(--mt-fill); padding:10px 14px; font-size:13px; border-bottom:1px solid var(--mt-line); }
  .bs .bs-mail-head div + div { margin-top:2px; }
  .bs .bs-mail-head strong { color:var(--mt-ink); }
  .bs .bs-mail iframe { display:block; width:100%; height:320px; border:0; background:#fff; }
  .bs .bs-sms-bubble { background:var(--bs-ok-bg); color:var(--mt-ink); border-radius:14px 14px 14px 4px; padding:10px 14px; white-space:pre-wrap; overflow-wrap:anywhere; font-size:14px; }
  .bs .bs-people-tools { display:flex; flex-wrap:wrap; gap:12px; align-items:flex-end; margin-bottom:12px; }
  .bs .bs-people-tools > div { flex:1 1 220px; }
  .bs .bs-check { display:inline-flex; align-items:center; gap:8px; font-size:14px; font-weight:600; min-height:40px; margin:0; cursor:pointer; }
  .bs .bs-check input { width:18px; height:18px; margin:0; }
  .bs td .bs-row-check { width:18px; height:18px; margin:0; }
  .bs tr.bs-row-off td { color:var(--mt-muted); }
  .bs tr.bs-row-off td:nth-child(2) { text-decoration:line-through; }
  .bs .bs-add-results { display:flex; flex-wrap:wrap; gap:6px; margin-top:8px; }
  .bs .bs-actionbar { position:sticky; bottom:0; z-index:5; display:flex; flex-wrap:wrap; gap:8px 12px; align-items:center; justify-content:space-between;
      background:#fff; border:1px solid var(--mt-line); border-radius:var(--bs-radius); box-shadow:0 -4px 12px rgba(15,23,42,.06); padding:12px 16px; margin:16px 0 0; }
  .bs .bs-actionbar p { margin:0; font-size:14px; }
  .bs .bs-actionbar .bs-actionbar-btns { display:flex; gap:8px; flex-wrap:wrap; }

  /* in-page dialog */
  .bs-dialog-backdrop { position:fixed; inset:0; top:0; right:0; bottom:0; left:0; background:rgba(15,23,42,.5); z-index:10050; display:flex; align-items:center; justify-content:center; padding:16px; }
  .bs-dialog-backdrop[hidden] { display:none; }
  .bs-dialog { background:#fff; color:#0f172a; border-radius:10px; max-width:460px; width:100%; padding:20px; box-shadow:0 20px 40px rgba(15,23,42,.25); }
  .bs-dialog:focus { outline:none; }
  .bs-dialog h2 { font-size:18px; font-weight:600; margin:0 0 12px; }
  .bs-dialog ul { list-style:none; margin:0 0 12px; padding:0; }
  .bs-dialog li { display:flex; justify-content:space-between; gap:12px; padding:6px 0; border-bottom:1px solid #e2e8f0; font-size:14px; }
  .bs-dialog li strong { font-variant-numeric:tabular-nums; }
  .bs-dialog p { font-size:14px; margin:0 0 12px; }
  .bs-dialog .bs-dialog-warn { display:flex; gap:8px; background:#fef9c3; border:1px solid #fde047; color:#713f12; border-radius:6px; padding:8px 12px; font-size:13px; }
  .bs-dialog .bs-dialog-actions { display:flex; flex-wrap:wrap; justify-content:flex-end; gap:8px; margin-top:16px; }
  body.bs-dialog-open { overflow:hidden; }

  @media (max-width:991px) {
    .bs .bs-compose { grid-template-columns:minmax(0, 1fr); }
    .bs .bs-side { position:static; }
    .bs .bs-send { grid-template-columns:minmax(0, 1fr); }
    .bs .bs-send .mt-actions { justify-content:flex-start; }
    .bs .bs-preview { grid-template-columns:minmax(0, 1fr); }
  }
  @media (max-width:600px) {
    .bs .bs-card { padding:14px; }
    .bs .bs-actionbar .bs-actionbar-btns { width:100%; }
    .bs .bs-actionbar .bs-actionbar-btns .mt-btn { flex:1; justify-content:center; }
    .bs .bs-choices, .bs .bs-pick-list { grid-template-columns:minmax(0, 1fr); }
    .bs .bs-tiles { grid-template-columns:repeat(2, minmax(0, 1fr)); }
  }
  @media (prefers-reduced-motion:reduce) { .bs *, .bs-dialog { transition:none !important; } }
</style>
