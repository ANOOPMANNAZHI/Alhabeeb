@extends('layouts.plms-app')
@section('css')
<link href="{{ asset('public/css/custom.css') }}" rel="stylesheet">
<style>
  .obi-list tr.inactive td { color: #94a3b8; }
  .obi-list .handle { cursor: move; color: #94a3b8; }
  .obi-list .tag { display:inline-block; padding:2px 8px; border-radius:999px; font-size:11px; font-weight:600; background:#f1f5f9; color:#475569; margin-right:4px; }
  .obi-list .tag-req { background:#fee2e2; color:#991b1b; }
  .obi-list .tag-photo { background:#dbeafe; color:#1e40af; }
</style>
@endsection

@section('content')
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Onboarding Checklist Items</div>
        </div>
        {{ Breadcrumbs::render('onboarding-items.index') }}
    </div>
</div>

@include('includes.success')
@include('includes.error')

<div class="row">
  <div class="col-md-12 col-sm-12">
    <div class="card card-box obi-list">
      <div class="card-body">
        <h4>
          <span class="text-muted" style="font-size:14px">The move-in checklist the ARE fills on the phone. Drag rows to reorder; deactivate instead of deleting.</span>
          <a href="{{ route('onboarding-items.create') }}" class="btn btn-circle btn-primary align-right">Add</a>
          <div class="clr"></div>
        </h4>
        <div class="table-responsive">
          <table class="table display product-overview mb-30">
            <thead>
              <tr>
                <th style="width:32px"></th>
                <th>Group</th>
                <th>Item</th>
                <th>Input</th>
                <th>Flags</th>
                <th>Order</th>
                <th>Status</th>
                <th style="width:12%">Action</th>
              </tr>
            </thead>
            <tbody id="obi-rows">
            @forelse($items as $item)
              <tr data-id="{{ $item->id }}" class="{{ $item->is_active ? '' : 'inactive' }}">
                <td class="handle"><i class="fa fa-bars"></i></td>
                <td>{{ $item->group_name }}</td>
                <td><a class="no-link" href="{{ route('onboarding-items.edit', $item) }}">{{ $item->title }}</a></td>
                <td>{{ $item->input_type }}</td>
                <td>
                  @if($item->required)<span class="tag tag-req">required</span>@endif
                  @if($item->needs_photo)<span class="tag tag-photo">photo</span>@endif
                </td>
                <td>{{ $item->sort_order }}</td>
                <td>@if($item->is_active)<span class="btn-circle btn-success btn-sm m-b-10 status"><b>Active</b></span>@else<span class="btn-circle btn-danger btn-sm m-b-10 status"><b>Inactive</b></span>@endif</td>
                <td>
                  <a title="Edit" href="{{ route('onboarding-items.edit', $item) }}" class="btn btn-tbl-edit btn-xs"><i class="fa fa-pencil"></i></a>
                  <form method="post" action="{{ route('onboarding-items.destroy', $item) }}" style="display:inline">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-tbl-delete btn-xs" title="{{ $item->is_active ? 'Deactivate' : 'Activate' }}"><i class="fa {{ $item->is_active ? 'fa-eye-slash' : 'fa-eye' }}"></i></button>
                  </form>
                </td>
              </tr>
            @empty
              <tr><td colspan="8" align="center"><p>No items yet. Run the seeder or add the first item.</p></td></tr>
            @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
$(function () {
  if (!$.fn.sortable) { return; }
  $('#obi-rows').sortable({
    handle: '.handle',
    update: function () {
      var ids = $('#obi-rows tr').map(function () { return $(this).data('id'); }).get();
      $.post('{{ route('onboarding-items.reorder') }}', { _token: '{{ csrf_token() }}', ids: ids });
    }
  });
});
</script>
@endsection
