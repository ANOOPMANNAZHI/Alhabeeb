@extends('layouts.plms-app')

@section('content')

  <div class="page-bar">
      <div class="page-title-breadcrumb">
          <div class=" pull-left">
              <div class="page-title">Mobile Devices</div>
          </div>
          {{ Breadcrumbs::render('mobile-devices.index') }}
      </div>
  </div>

  @include('includes.success')
  @include('includes.error')

<div class="row">
    <div class="col-md-12 col-sm-12">

<div class="card card-box">
    <div class="card-body ">
       <h4>
        <div id="pagination_info">
             @include('includes.pagination_info',['paginator' => $devices])
        </div>
           <div class="clr"></div>
        </h4>

      <div class="table-wrap">
			<div class="table-responsive">
				<table class="table display product-overview mb-30" id="dtBasicExample">
				  <thead>
					  <tr>
						  <th>User</th>
						  <th>Employee</th>
						  <th>Device</th>
						  <th>Platform</th>
						  <th>App</th>
						  <th>Registered</th>
						  <th>Last seen</th>
						  <th>Status</th>
						  <th width="14%">Action</th>
					  </tr>
				  </thead>
				  <tbody>
				  @forelse($devices as $d)
					<tr>
					  <td>{{ optional($d->user)->username }}</td>
					  <td>{{ optional(optional($d->user)->employee)->employee_name }}</td>
					  <td>{{ $d->device_name }} <small class="text-muted">{{ $d->device_uid }}</small></td>
					  <td>{{ $d->platform }}</td>
					  <td>{{ $d->app_version }}</td>
					  <td>{{ $d->registered_at?->format('d/m/Y H:i') }}</td>
					  <td>{{ $d->last_seen_at?->format('d/m/Y H:i') }}</td>
					  <td>@if($d->blocked_at)<span class="label label-danger">Blocked</span>@else<span class="label label-success">Active</span>@endif</td>
					  <td>
						<form method="post" action="{{ route('mobile-devices.clear', $d->user_id) }}" style="display:inline" onsubmit="return confirm('Clear this device? The user must log in again from the new phone.')">
						  @csrf<button type="submit" class="btn btn-xs btn-warning">Clear</button>
						</form>
						@if($d->blocked_at)
						  <form method="post" action="{{ route('mobile-devices.unblock', $d->user_id) }}" style="display:inline">
							@csrf<button type="submit" class="btn btn-xs btn-success">Unblock</button>
						  </form>
						@else
						  <form method="post" action="{{ route('mobile-devices.block', $d->user_id) }}" style="display:inline" onsubmit="return confirm('Block this device? The user will be signed out.')">
							@csrf<button type="submit" class="btn btn-xs btn-danger">Block</button>
						  </form>
						@endif
					  </td>
					</tr>
				  @empty
					<tr><td colspan="9" class="text-center">No devices registered yet.</td></tr>
				  @endforelse
				  </tbody>
				</table>
			</div>
		</div>
		   <div id="pagination">
				<div class="text-center">
				   {{ $devices->links() }}
			   </div>
		   </div>
    </div>
</div>

    </div>
</div>
@endsection
