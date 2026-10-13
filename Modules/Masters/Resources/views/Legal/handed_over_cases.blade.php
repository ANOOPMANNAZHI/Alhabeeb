@extends('layouts.plms-app')
@section('css')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">
@endsection

@section('content')
@php $LC = \Modules\Masters\Services\LegalCase::class; @endphp
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Handed-over Legal Cases</div>
        </div>
       {{ Breadcrumbs::render('handedOverLegalCases') }}
    </div>
</div>
<div class="row">
  <div class="col-md-12 col-sm-12">
    <div class="card card-box">
      <div class="card-body">
        <form method="GET" action="{{ route('handedOverLegalCases') }}" class="row" style="margin-bottom:12px;">
          <div class="col-sm-3">
            <select name="handled_by" class="form-control" aria-label="Handed over to">
              <option value="">All</option>
              @foreach([2, 3, 4] as $k)
                <option value="{{ $k }}" @if((int) $request->handled_by === $k) selected @endif>{{ $LC::HANDLED_BY[$k] }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-sm-5">
            <input type="text" name="search" class="form-control" value="{{ $request->search }}" placeholder="Agreement no, tenant, building, handed to or reference" aria-label="Search">
          </div>
          <div class="col-sm-4">
            <button type="submit" class="btn btn-primary">Search</button>
            <a href="{{ route('handedOverLegalCases') }}" class="btn btn-default">Reset</a>
          </div>
        </form>
        <div class="table-responsive">
          <table class="table table-striped table-hover">
            <thead>
              <tr>
                <th>Agreement No</th>
                <th>Tenant</th>
                <th>Building</th>
                <th>Unit</th>
                <th>Handed to</th>
                <th>Name</th>
                <th>Reference</th>
                <th>Handover date</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              @forelse($cases as $case)
              <tr>
                <td>{{ $case->tenantContract->tenant_contract_no ?? '' }}</td>
                <td>{{ $case->tenantContract->tenant->tenant_name ?? '' }}</td>
                <td>{{ $case->building->building_name ?? '' }}</td>
                <td>{{ $case->unit->unit_code ?? '' }}</td>
                <td>{{ $LC::HANDLED_BY[$case->handled_by] ?? '' }}</td>
                <td>{{ $case->handed_to_name }}</td>
                <td>{{ $case->handover_ref }}</td>
                <td>{{ $case->handover_date ? \Carbon\Carbon::parse($case->handover_date)->format('d/m/Y') : '' }}</td>
                <td>
                  <a href="{{ route('plmsApprovalShow', $case->id) }}" title="View" class="btn btn-tbl-view btn-xs"><i class="fa fa-eye"></i></a>
                </td>
              </tr>
              @empty
              <tr><td colspan="9" align="center"><p>No handed-over cases.</p></td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
        {{ $cases->links() }}
      </div>
    </div>
  </div>
</div>
@endsection
