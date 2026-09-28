{{-- Shared by the bank-sheet posting result / day / history pages (inside content: the layout does not yield 'css'). --}}
<style>
  .bsr td.num, .bsr th.num { text-align:right; white-space:nowrap; }
  .bsr .card-box { margin-bottom:20px; }
  .bsr .bsr-sub { display:block; font-size:12px; color:#6b7280; }
  .bsr .bsr-head { display:flex; align-items:center; gap:10px; flex-wrap:wrap; margin:0 0 10px; }
  .bsr .bsr-head h4 { margin:0; font-size:16px; }
  .bsr .bsr-head .bsr-hint { color:#6b7280; font-size:12px; }
  .bsr .bsr-head .bsr-right { margin-left:auto; }
  .bsr dl { margin:0; }
  .bsr dt { font-size:12px; color:#6b7280; font-weight:normal; }
  .bsr dd { font-weight:600; margin:0 0 8px; }
  .bsr-pill { display:inline-block; padding:3px 10px; border-radius:12px; font-size:12px; font-weight:600; margin-right:6px; line-height:18px; }
  .bsr-pill i { margin-right:4px; }
  .bsr-pill-ok   { background:#dcfce7; color:#166534; }
  .bsr-pill-warn { background:#fef3c7; color:#92400e; }
  .bsr-pill-bad  { background:#fee2e2; color:#991b1b; }
  .bsr-pill-off  { background:#e5e7eb; color:#374151; }
  .bsr tr.bsr-total td { font-weight:700; background:#f7fdfb; }

  /* Action toolbar: its own row under the summary, navigation left, Download right */
  .bsr .bsr-toolbar { display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:8px;
                      margin-top:16px; padding-top:16px; border-top:1px solid #e2e8f0; }
  .bsr .bsr-toolbar-nav { display:flex; flex-wrap:wrap; gap:8px; }
  .bsr .bsr-btn { display:inline-flex; align-items:center; gap:8px; height:40px; padding:0 16px;
                  border:1px solid #94a3b8; border-radius:6px; background:#fff; color:#0f172a;
                  font-size:14px; font-weight:600; line-height:1; text-decoration:none; white-space:nowrap; }
  .bsr .bsr-btn:hover { background:#f1f5f9; border-color:#475569; color:#0f172a; text-decoration:none; }
  .bsr .bsr-btn:focus-visible { outline:2px solid #2f4fd6; outline-offset:2px; }
  .bsr .bsr-btn-primary { background:#16a34a; border-color:#16a34a; color:#fff; }
  .bsr .bsr-btn-primary:hover { background:#15803d; border-color:#15803d; color:#fff; }
  @media (max-width: 575px) {
    .bsr .bsr-toolbar, .bsr .bsr-toolbar-nav { flex-direction:column; align-items:stretch; }
    .bsr .bsr-btn { justify-content:center; }
  }
</style>
