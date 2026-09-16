{{--
  Vacating Unit Inspection Details — shared read-only block.
  Expects: $inspection (Termination row holding the readings/totals, or null),
           $tenantContract, $groupedWork (terminationChecklist grouped by work_id),
           $terminationDocument (inspection images/docs).
  Included by the inspection page (after save), approval view and terminated-contract view.
--}}
<style>
  .idet { --d-blue:#2563eb; --d-blue-soft:#eff6ff; --d-ink:#1f2937; --d-muted:#6b7280; --d-line:#e5e7eb; --d-bg:#f9fafb; --d-radius:8px; color:var(--d-ink); }
  .idet .d-head { display:flex; flex-wrap:wrap; align-items:center; gap:10px 16px; padding:14px 18px; border-bottom:1px solid var(--d-line); }
  .idet .d-head h4 { margin:0; font-size:17px; font-weight:800; }
  .idet .d-head .d-when { font-size:12px; color:var(--d-muted); }
  .idet .d-badge { display:inline-flex; align-items:center; gap:6px; padding:4px 10px; border-radius:999px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; }
  .idet .d-badge-ok { background:#dcfce7; color:#166534; } .idet .d-badge-no { background:#fef3c7; color:#92400e; }
  .idet .d-body { padding:16px 18px 6px; }

  /* Totals strip */
  .idet .d-tiles { display:grid; grid-template-columns:repeat(auto-fit, minmax(150px, 1fr)); gap:12px; margin-bottom:18px; }
  .idet .d-tile { border:1px solid var(--d-line); border-radius:var(--d-radius); padding:12px 14px; background:#fff; }
  .idet .d-tile .d-lbl { display:block; font-size:11px; text-transform:uppercase; letter-spacing:.05em; color:var(--d-muted); margin-bottom:4px; }
  .idet .d-tile .d-val { font-size:18px; font-weight:700; font-variant-numeric:tabular-nums; white-space:nowrap; }
  .idet .d-tile.d-net { background:var(--d-blue-soft); border-color:#bfdbfe; }
  .idet .d-tile.d-net .d-val { color:var(--d-blue); font-size:22px; font-weight:800; }
  .idet .d-tile .d-sub { display:block; font-size:11px; color:var(--d-muted); margin-top:2px; }

  /* Sections */
  .idet .d-section { display:flex; align-items:center; gap:10px; font-size:14px; font-weight:700; border-left:4px solid var(--d-blue); padding:6px 12px; margin:18px 0 10px; background:var(--d-blue-soft); border-radius:6px; }
  .idet .d-section .d-hint { margin-left:auto; font-size:12px; font-weight:500; color:var(--d-muted); font-variant-numeric:tabular-nums; }
  .idet .d-meters { display:grid; grid-template-columns:repeat(auto-fit, minmax(260px, 1fr)); gap:12px; }
  .idet .d-meter { border:1px solid var(--d-line); border-radius:var(--d-radius); overflow:hidden; }
  .idet .d-meter-head { display:flex; align-items:center; gap:8px; padding:8px 12px; background:var(--d-bg); font-weight:700; font-size:13px; }
  .idet .d-meter-head .d-amt { margin-left:auto; font-variant-numeric:tabular-nums; }
  .idet .d-kv { display:grid; grid-template-columns:1fr 1fr; gap:8px 12px; padding:10px 12px; margin:0; font-size:13px; }
  .idet .d-kv dt { color:var(--d-muted); font-weight:500; }
  .idet .d-kv dd { margin:0; font-weight:600; text-align:right; font-variant-numeric:tabular-nums; }

  .idet .d-table-wrap { border:1px solid var(--d-line); border-radius:var(--d-radius); overflow:hidden; }
  .idet table.d-table { margin:0; }
  .idet table.d-table th { font-size:12px; text-transform:uppercase; letter-spacing:.03em; color:var(--d-muted); background:var(--d-bg); border-top:0; border-bottom:2px solid var(--d-line); padding:8px 12px; }
  .idet table.d-table td { padding:8px 12px; vertical-align:middle; }
  .idet table.d-table tr.d-cat td { background:#f3f6fb; font-weight:700; font-size:12px; text-transform:uppercase; letter-spacing:.04em; color:var(--d-blue); padding:6px 12px; }
  .idet table.d-table tr.d-cat td .d-cat-n { float:right; font-weight:600; color:var(--d-muted); text-transform:none; letter-spacing:0; font-variant-numeric:tabular-nums; }
  .idet table.d-table .num { text-align:right; font-variant-numeric:tabular-nums; white-space:nowrap; }
  .idet table.d-table tfoot td { font-weight:700; background:var(--d-bg); }
  .idet .d-empty { padding:14px; color:var(--d-muted); font-size:13px; border:1px dashed var(--d-line); border-radius:var(--d-radius); text-align:center; }

  .idet .d-notes { border:1px solid var(--d-line); border-left:4px solid #f59e0b; border-radius:var(--d-radius); padding:12px 14px; background:#fffbeb; font-size:14px; white-space:pre-line; }
  .idet .d-photos { display:flex; flex-wrap:wrap; gap:12px; margin-bottom:14px; }
  .idet .d-photo { width:150px; border:1px solid var(--d-line); border-radius:var(--d-radius); overflow:hidden; background:#fff; }
  .idet .d-photo img { width:100%; height:100px; object-fit:cover; display:block; background:var(--d-bg); }
  .idet .d-photo .d-photo-meta { padding:6px 8px; font-size:12px; }
  .idet .d-photo .d-photo-meta .d-tag { display:block; color:var(--d-muted); }
  .idet .d-photo .d-photo-meta a { word-break:break-all; }
  .idet .d-none { padding:28px 16px; text-align:center; color:var(--d-muted); }
  .idet .d-none i { font-size:28px; display:block; margin-bottom:8px; color:#cbd5e1; }
</style>

@php
  $n3 = function ($v) { return isset($v) ? number_format((float) $v, 3) : '0.000'; };
  $worksTotal = 0.0; $worksN = 0;
  foreach ($groupedWork as $g) { foreach ($g as $r) { $worksTotal += (float) $r->termination_amount; $worksN++; } }
  $othersRows = $tenantContract->terminationChecklistOther;
  $othersTotal = 0.0; foreach ($othersRows as $o) { $othersTotal += (float) $o->termination_amount; }
@endphp
<div class="col-sm-12">
  <div class="card card-box idet">
    <div class="d-head">
      <h4><i class="fa fa-check-square-o" aria-hidden="true"></i> Vacating Unit Inspection</h4>
      @if($inspection)
        @if($inspection->termination_main_key_status == 1)
          <span class="d-badge d-badge-ok"><i class="fa fa-key" aria-hidden="true"></i> Main door key received</span>
        @else
          <span class="d-badge d-badge-no"><i class="fa fa-key" aria-hidden="true"></i> Main door key not received</span>
        @endif
        <span class="d-when">
          Taken over {{ $inspection->termination_takenover_date ? \Carbon\Carbon::parse($inspection->termination_takenover_date)->format('d/m/Y') : '—' }}
          · recorded {{ $inspection->created_at ? \Carbon\Carbon::parse($inspection->created_at)->format('d/m/Y H:i') : '—' }}
        </span>
      @endif
    </div>

    @if(!$inspection)
      <div class="d-none"><i class="fa fa-clipboard" aria-hidden="true"></i>Inspection has not been recorded for this termination yet.</div>
    @else
    <div class="d-body">

      {{-- Totals --}}
      <div class="d-tiles">
        <div class="d-tile"><span class="d-lbl">Checklist</span><span class="d-val">{{ $n3($inspection->termination_total_amount) }}</span><span class="d-sub">{{ $worksN }} item{{ $worksN == 1 ? '' : 's' }}</span></div>
        <div class="d-tile"><span class="d-lbl">Other charges</span><span class="d-val">{{ $n3($othersTotal) }}</span><span class="d-sub">{{ count($othersRows) }} line{{ count($othersRows) == 1 ? '' : 's' }}</span></div>
        <div class="d-tile"><span class="d-lbl">Electricity + water</span><span class="d-val">{{ $n3($inspection->termination_total_elec_water_amount) }}</span></div>
        <div class="d-tile"><span class="d-lbl">Discount</span><span class="d-val">{{ $n3($inspection->termination_discount_maintenance_due) }}</span></div>
        <div class="d-tile d-net"><span class="d-lbl">Net amount (OMR)</span><span class="d-val">{{ $n3($inspection->termination_net_amount) }}</span></div>
      </div>

      {{-- Meter readings --}}
      <div class="d-section">Meter readings</div>
      <div class="d-meters">
        <div class="d-meter">
          <div class="d-meter-head"><i class="fa fa-bolt" aria-hidden="true"></i> Electricity <span class="d-amt">{{ $n3($inspection->termination_electricity_amount) }} OMR</span></div>
          <dl class="d-kv">
            <dt>Account No</dt><dd>{{ $inspection->termination_electricity_acc_no ?: '—' }}</dd>
            <dt>Closing reading</dt><dd>{{ $inspection->termination_electricity_close_reading ?: '—' }}</dd>
          </dl>
        </div>
        <div class="d-meter">
          <div class="d-meter-head"><i class="fa fa-tint" aria-hidden="true"></i> Water <span class="d-amt">{{ $n3($inspection->termination_water_amount) }} OMR</span></div>
          <dl class="d-kv">
            <dt>Account No</dt><dd>{{ $inspection->termination_water_acc_no ?: '—' }}</dd>
            <dt>Closing reading</dt><dd>{{ $inspection->termination_water_close_reading ?: '—' }}</dd>
          </dl>
        </div>
      </div>

      {{-- Checklist --}}
      <div class="d-section">Damage &amp; repair checklist <span class="d-hint">{{ $worksN }} item{{ $worksN == 1 ? '' : 's' }} · {{ $n3($worksTotal) }} OMR</span></div>
      @if($worksN > 0)
      <div class="d-table-wrap table-responsive">
        <table class="table d-table">
          <thead><tr><th>Item</th><th class="num" style="width:110px">Qty</th><th class="num" style="width:150px">Amount (OMR)</th></tr></thead>
          <tbody>
          @foreach($groupedWork as $checklist)
            @php $catSum = 0.0; foreach ($checklist as $r) { $catSum += (float) $r->termination_amount; } @endphp
            <tr class="d-cat"><td colspan="3">{{ optional($checklist->first()->work)->works_code ?: 'Uncategorised' }} <span class="d-cat-n">{{ count($checklist) }} · {{ $n3($catSum) }}</span></td></tr>
            @foreach($checklist as $subWork)
            <tr>
              <td>{{ optional($subWork->subWorks)->sub_work ?: '—' }}</td>
              <td class="num">{{ ($subWork->termination_quantity !== null && $subWork->termination_quantity !== '') ? $subWork->termination_quantity : '—' }}</td>
              <td class="num">{{ $n3($subWork->termination_amount) }}</td>
            </tr>
            @endforeach
          @endforeach
          </tbody>
          <tfoot><tr><td colspan="2" class="text-right">Checklist total</td><td class="num">{{ $n3($worksTotal) }}</td></tr></tfoot>
        </table>
      </div>
      @else
      <div class="d-empty">No checklist items were charged.</div>
      @endif

      {{-- Other charges --}}
      <div class="d-section">Other charges <span class="d-hint">{{ $n3($othersTotal) }} OMR</span></div>
      @if(count($othersRows))
      <div class="d-table-wrap table-responsive">
        <table class="table d-table">
          <thead><tr><th>Charge</th><th class="num" style="width:110px">Qty</th><th class="num" style="width:150px">Amount (OMR)</th></tr></thead>
          <tbody>
          @foreach($othersRows as $other)
            <tr>
              <td>{{ $other->termination_other_work === 'Muncipal Tax' ? 'Municipal Tax' : $other->termination_other_work }}</td>
              <td class="num">{{ ($other->termination_quantity !== null && $other->termination_quantity !== '') ? $other->termination_quantity : '—' }}</td>
              <td class="num">{{ $n3($other->termination_amount) }}</td>
            </tr>
          @endforeach
          </tbody>
          <tfoot><tr><td colspan="2" class="text-right">Other charges total</td><td class="num">{{ $n3($othersTotal) }}</td></tr></tfoot>
        </table>
      </div>
      @else
      <div class="d-empty">No other charges.</div>
      @endif

      {{-- Notes --}}
      <div class="d-section">Inspection notes</div>
      @if($inspection->termination_notes)
        <div class="d-notes">{{ $inspection->termination_notes }}</div>
      @else
        <div class="d-empty">No notes.</div>
      @endif

      {{-- Photos --}}
      <div class="d-section">Inspection photos <span class="d-hint">{{ count($terminationDocument) }}</span></div>
      @if(count($terminationDocument))
      <div class="d-photos">
        @foreach($terminationDocument as $document)
        <div class="d-photo">
          <a href="{{ asset('storage/app/' . $document->termination_doc) }}" target="_blank">
            <img src="{{ asset('storage/app/' . ($document->image_path_thumbnail ?: $document->termination_doc)) }}" alt="{{ $document->termination_doc_type }} photo" loading="lazy">
          </a>
          <div class="d-photo-meta">
            <span class="d-tag">{{ $document->termination_doc_type }}</span>
            <a href="{{ asset('storage/app/' . $document->termination_doc) }}" target="_blank">{{ $document->termination_doc_name }}</a>
          </div>
        </div>
        @endforeach
      </div>
      @else
      <div class="d-empty" style="margin-bottom:14px">No photos uploaded.</div>
      @endif

    </div>
    @endif
  </div>
</div>
