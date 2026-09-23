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
</style>
