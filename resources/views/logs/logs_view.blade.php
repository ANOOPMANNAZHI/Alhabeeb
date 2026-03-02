@extends('layouts.plms-app')
@section('content')
  <div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">System Log</div>
        </div>
         {{Breadcrumbs::render('log')}}                           
    </div>
  </div>
  
   <div class="mb-4">
      <div class="row">
       <div class="col-md-12 col-sm-12">
            <div class="card  card-box">
                
                <div class="card-body ">
                <h4>
             <div id="pagination_info">
                     @include('includes.pagination_info',['paginator' => $logs])         
                 </div>
                  <div class="clr"></div>
            </h4>
					 <div class="table-wrap">
				  <div class="table-responsive1">
                  <table class="table display product-overview mb-30" id="dtBasicExample">
                    <thead>
                        <tr>
                            <th>Sl No.</th>
                            <th>Log Name</th>
                            <th>Description</th>
                            <th>User</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $count = 1; @endphp  
                        @forelse ($logs as $log)
                        <tr>
                            <td>{{$logs ->perPage()*($logs->currentPage()-1)+$count}}</td>
                            <td>{{$log->log_name}}</td>
                            <td>{{$log->description}}</td>
							              <td>{{ucfirst($log->causer->username) ?? ''}}</td>
                            <td>{{$log->created_at->format('d-m-Y')}}</td>
                        </tr>  
                        @php $count++; @endphp
                        @empty
                        <tr>
                            <td colspan="5" align="center">
                            <p>No records</p>
                           </td>
                        </tr>
                        @endforelse
                    </tbody>
                  </table>
                      {{$logs->links()}} 
                </div>
                </div>
        </div>  
            </div>
        </div>  
    </div>
  </div>
@endsection
