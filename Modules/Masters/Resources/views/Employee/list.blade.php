@extends('layouts.plms-app')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">
<!-- data tables -->
<link rel="stylesheet" href="{{ asset('public/css/datatables.min.css')}}">

@section('content')
    <!-- start widget -->
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Employee</div>
        </div>
        <ol class="breadcrumb page-breadcrumb pull-right">
            <li><i class="fa fa-home"></i>&nbsp;<a class="parent-item" href="{{route('home')}}">Home</a>&nbsp;<i class="fa fa-angle-right"></i></li>
            <li class="active">Employee</li>
        </ol>
    </div>
</div>
<a  class=" align-right advSearch" href="#" id="enquiry_div" data-toggle="collapse" data-target="#show">
    <i class="fa fa-search" aria-hidden="true"></i>  <span id="enquirySearch"> Advance Search <i class="fa fa-caret-down" aria-hidden="true"></i></span>
</a>
<div class="clearfix"></div>
<div class="row collapse @if(old('fieldName')) show @endif" id="show">
    <div class="col-md-12 col-sm-12 dashboardtab">
        <div class="panel tab-border card-box">

            <div class="panel-body">
                <div class="dataSearchBox">
                    <form action=" " method="GET" id="leade_search" class="form-horizontal" enctype="multipart/form-data" data-toggle="validator">
                        {{csrf_field()}}
                        <div class="row">
                            <div class="col-sm-5">
                                <div class="form-group">
                                    <label for="fieldName">Field </label>
                                    <select class="form-control" required name="fieldName">
                                          <option value="">Select </option>
                                          @foreach($employee_fields as $key=>$val)
                                            <option {{ (old('fieldName') == $key)? 'selected': '' }} value="{{$key}}">{{$val}}</option>
                                          @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-5">
                                <div class="form-group">
                                    <label for="fieldValue"> Value</label>
                                    <input required type="text" class="form-control" name="fieldValue" id="fieldValue" placeholder="Enter Value" value="{{old('fieldValue')}}">
                                </div>
                            </div>
                            <div class="col-sm-1">
                                <div class="dataSearchLabel w-100 margin"></div>
                                <button type="submit" class="btn btn-primary">Search</button>

                            </div>
                            <div class="col-sm-1">
                                    <div class="dataSearchLabel w-100 margin"></div>
                                    <a href="{{url()->current()}}" class="btn btn-primary">Reset</a>
                                </div>
                        </div>
                    </form>
                </div>
            </div>           
        </div>
    </div>
</div> 
 <div class="row">
   <div class="col-md-12 col-sm-12">
        <div class="card  card-box">
            
            <div class="card-body ">
            <h4>
             <div id="pagination_info">
                     @include('includes.pagination_info',['paginator' => $employees])         
                 </div>
             @can('add_employee')

                 <a href="{{route('employee.create')}}" class="btn btn-circle btn-primary  align-right"  >Add</a>
                 <div class="clr"></div>
                </h4>
              @endcan
             <div class="table-responsive1">
			 <table class="table display product-overview mb-30" id="dtBasicExample">
                <thead>
                    <tr>
                        <th>Sl No.</th>
                        <th>Employee Code</th>
                        <th>Employee Name</th>
                        <th>Employee Designation</th>
                        <th>Employee mob</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
					@php $count = 1; @endphp
                    @forelse ($employees as $employee)
                    <tr>
                        <td>
							<a @can('edit_employee') href="{{route('employee.edit',$employee->id)}}" title="Edit" @elsecan('view_employee') href="{{route('employee.show',$employee->id)}}" title="View"  @endcan class="no-link" >
								{{$employees->perPage()*($employees->currentPage()-1)+$count}}
							</a>
						</td>
                        <td>
							<a @can('edit_employee') href="{{route('employee.edit',$employee->id)}}" title="Edit" @elsecan('view_employee') href="{{route('employee.show',$employee->id)}}" title="View"  @endcan class="no-link" >
								{{$employee->employee_code}}
							</a>
						</td>
                        <td><a @can('edit_employee') href="{{route('employee.edit',$employee->id)}}" title="Edit" @elsecan('view_employee') href="{{route('employee.show',$employee->id)}}" title="View"  @endcan class="no-link" >
								{{$employee->employee_name}}
							</a>
						</td>
                        <td>
							<a @can('edit_employee') href="{{route('employee.edit',$employee->id)}}" title="Edit" @elsecan('view_employee') href="{{route('employee.show',$employee->id)}}" title="View"  @endcan class="no-link" >
								{{$employee->designations->designation_name}}
							</a>
						</td>
                        <td>
							<a @can('edit_employee') href="{{route('employee.edit',$employee->id)}}" title="Edit" @elsecan('view_employee') href="{{route('employee.show',$employee->id)}}" title="View"  @endcan class="no-link" >
								{{$employee->employee_contact_no}}
							</a>
						</td>
                        <td >
                          @can('reset_password_employee')  
                            <a class="change_status" title="Change Status" class="change_status" href="{{route('employee.changeStatus',$employee->id)}}">
                          @endcan
                                @if($employee->employee_status==1)
                                        @can('reset_password_employee')  
                                        <button type="button" class="btn btn-circle btn-success btn-sm m-b-10">
                                        @endcan
                                        Active
                                        @can('reset_password_employee') 
                                        </button>
                                        @endcan
                                @else 
                                        @can('reset_password_employee') 
                                        <button type="button" class="btn btn-circle btn-danger btn-sm m-b-10">
                                        @endcan
                                             Inactive
                                        @can('reset_password_employee') 
                                        </button> 
                                        @endcan
                                 @endif
                           @can('reset_password_employee')   
                                </a>
                           @endcan
                        </button>
                            <form id="status-form" action="" method="POST">
                                 {{csrf_field()}}
                                 <input type="hidden" name="status" value="{{$employee->employee_status}}">
                                <input style="display: none;" type="submit">
                            </form>
                        </td>      
                        <td>
                        <a href="{{route('employee.show',$employee->id)}}" title="View" class="btn btn-tbl-view btn-xs">
                            <i class="fa fa-eye "></i>
                        </a> 
                        @can('edit_employee')
                        <a  title="Edit" href="{{route('employee.edit',$employee->id)}}" class="btn btn-tbl-edit btn-xs" title="Edit">
                            <i class="fa fa-pencil"></i>
                        </a>      
                        @endcan                                           
                        @can('delete_employee')
                        <!--<a href="{{route('employee.destroy',$employee->id)}}" title="Delete" class="btn btn-tbl-delete btn-xs delete_type">
                            <i class="fa fa-trash-o "></i>
                        </a> --> 
                        @endcan
                        @can('reset_password_employee')
                        <a href="{{route('employee.resetPassword',$employee->id)}}" title="Reset Password" class="btn btn-tbl-view btn-xs" id="reset_pass">   
                          
                             <i class="fa fa-key" aria-hidden="true"></i>
                        </a>  
                        @endcan
                        </td>
                    </tr>  
                    @php $count++; @endphp
                    @empty
                    <tr class="no-record">
                        <td colspan="7">
                        <p>No Record</p>
                       </td>
                    </tr>
                    @endforelse
                </tbody>
              </table> 
			  </div>
               {{$employees->links()}} 
            </div>
        </div>
    </div>
</div>
<form id="delete-form" action="" method="POST">
    {{ method_field('DELETE') }}  {{csrf_field()}}
    <input value="delete" style="display: none;" type="submit">
</form>
@endsection
@section('scripts')    
 
<script>


 jQuery(document).ready(function() {

	$("#show").on("hide.bs.collapse", function(){
        $("#enquirySearch").html('Advance Search <i class="fa fa-caret-down" aria-hidden="true"></i>');
    });
       $("#show").on("show.bs.collapse", function(){
        $("#enquirySearch").html('Advance Search <i class="fa fa-caret-up" aria-hidden="true"></i>');
    });
  jQuery('#reset_pass').click(function (event) {
        var action = $(this).attr("href");
      
        if (confirm('Do you want to reset password?')) {
            jQuery("#change-pass-form").attr('action', action);
            jQuery("#change-pass-form").submit();
        } else {
            return false;
        }
  });

  jQuery('.dataTables_length').addClass('bs-select');
  
            jQuery('.delete_type').click(function (event) {
                var action = $(this).attr("href");                
                event.preventDefault();
                if (confirm('Do you want to delete this employee?')) {
					jQuery("#delete-form").attr('action', action);
					jQuery("#delete-form").submit();
				} else {
					return false;
				}
            });
            jQuery('.change_status').click(function (event) {
                var action = $(this).attr("href");
                event.preventDefault();
                if (confirm('Do you want to Change Status?')) {
                    jQuery("#status-form").attr('action', action);
                    jQuery("#status-form").submit();
                } else {
                    return false;
                }
            })
        });

</script> 


@endsection
