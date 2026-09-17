@forelse($dues as $d)
	@php
	  $c = $d->tenantContract;
	  $url = route('termination-dues.show', $d->id);
	  $promiseOverdue = $d->next_promise_date && $d->next_promise_date->isPast() && $d->balance > 0;
	  $statusClass = ['open' => 'btn-danger', 'partial' => 'btn-warning', 'settled' => 'btn-success', 'written_off' => 'btn-default'];
	@endphp
	<tr>
		<td><a class="no-link" href="{{ $url }}">{{ optional($c)->tenant_contract_no ?: '#' . $d->tenant_contract_id }}</a></td>
		<td><a class="no-link" href="{{ $url }}">{{ optional(optional($c)->tenant)->tenant_name }}<span class="tdue-sub">{{ optional(optional($c)->tenant)->tenant_contact_no }}</span></a></td>
		<td><a class="no-link" href="{{ $url }}">{{ optional(optional($c)->building)->building_name }}</a></td>
		<td><a class="no-link" href="{{ $url }}">{{ optional(optional($c)->unit)->unit_no ?: optional(optional($c)->unit)->unit_code }}</a></td>
		<td><a class="no-link" href="{{ $url }}">{{ $d->termination_date ? $d->termination_date->format('d/m/Y') : '-' }}@if($d->termination_date)<span class="tdue-sub">{{ $d->termination_date->diffInDays(now()) }} days ago</span>@endif</a></td>
		<td class="num">{{ numberFormat($d->total_owed) }}</td>
		<td class="num">{{ numberFormat($d->total_settled) }}</td>
		<td class="num"><b>{{ numberFormat($d->balance) }}</b></td>
		<td><span class="btn-circle {{ $statusClass[$d->status] ?? 'btn-default' }} btn-sm m-b-10 status"><b>{{ ucfirst(str_replace('_', ' ', $d->status)) }}</b></span></td>
		<td>{{ $d->last_followup_at ? $d->last_followup_at->format('d/m/Y') : '-' }}
			@if($d->next_promise_date)<span class="tdue-sub {{ $promiseOverdue ? 'tdue-overdue' : '' }}">promised {{ $d->next_promise_date->format('d/m/Y') }}</span>@endif</td>
		<td>
			<a title="View" href="{{ $url }}" class="btn btn-tbl-view btn-xs"><i class="fa fa-eye"></i></a>
			<a title="Print statement" href="{{ route('termination-dues.print', $d->id) }}" target="_blank" rel="noopener" class="btn btn-tbl-view btn-xs"><i class="fa fa-print"></i></a>
		</td>
	</tr>
@empty
	<tr>
		<td colspan="11" align="center">
			<p>No Record</p>
		</td>
	</tr>
@endforelse

@if(isset($request->ajax))
	<tr>
		<td colspan="11" id="pagination_ajax">
			{{ $dues->withPath($route)->appends(\Request::except(['page','ajax','_token']))->links() }}
			<div class="pagination_info">
				@include('includes.pagination_info',['paginator' => $dues])
			</div>
		</td>
	</tr>
@endif
