@extends('layouts.plms-app')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">


@section('content')
    <!-- start widget -->
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Employee View</div>
        </div>
        <ol class="breadcrumb page-breadcrumb pull-right">
            <li><i class="fa fa-home"></i>&nbsp;<a class="parent-item" href="{{route('home')}}">Home</a>&nbsp;<i class="fa fa-angle-right"></i></li>
            <li>&nbsp;<a class="parent-item" href="{{route('employee.index')}}">Employee </a>&nbsp;<i class="fa fa-angle-right"></i></li>
            <li class="active">View </li>
        </ol>
    </div>
</div>

<div class="row">
	<div class="col">
		 <div class="card card-box salesSearchBox">
			 <div class="row">
            <div class="col-sm-12">
                @can('edit_employee')
					<a href="{{route('employee.edit',$employee->id)}}" class="btn btn-circle btn-primary  align-right">Edit</a> 
                @endcan
           </div>
       </div>
			  <div class="dataSearchBox">
				<div class="card-body row">

					<div class="col-lg-6 p-t-20"> 
						<div class = "txt-full-width">
							<h5 class="details"><b>Username :  </b><span>{{$employee->user->username ?? 'NA'}}</span></h5>
						</div>
					</div>
					<div class="col-lg-6 p-t-20"> 
						<div class = "txt-full-width">
							<h5 class="details"><b>Code  :  </b><span>{{$employee->employee_code ?? 'NA'}}</span></h5>
						</div>
					</div>
				</div>
			</div>
			<div class="sub-head">Contact Details</div>
			<div class="dataSearchBox">    
				<div class="card-body row">

				<div class="col-lg-6 p-t-20"> 
					<div class = "txt-full-width">
						<h5 class="details"><b>Name  :  </b><span>{{$employee->employee_name ?? 'NA'}}</span></h5>
					</div>
				</div>
				<div class="col-lg-6 p-t-20"> 
					<div class = "txt-full-width">
						<h5 class="details"><b>Email :  </b><span>
							<a href="mailto:{{$employee->user->email}}" target="_top">
								<span> {{$employee->user->email ?? 'NA'}} </span>
							</a>
						</span></h5>
					</div>
				</div>
				<div class="col-lg-6 p-t-20"> 
					<div class = "txt-full-width">
						<h5 class="details"><b> Contact No :  </b><span>{{$employee->employee_contact_no ?? 'NA'}}</span></h5>
					</div>
				</div>
				<div class="col-lg-6 p-t-20"> 
					<div class = "txt-full-width">
						<h5 class="details"><b>Secondary Contact No  :  </b><span>{{$employee->employee_secondary_no ?? 'NA'}}</span></h5>
					</div>
				</div>
				<div class="col-lg-6 p-t-20"> 
					<div class = "txt-full-width">
						<h5 class="details"><b> Contact Address :  </b><span>{{$employee->employee_contact_address ?? 'NA'}}</span></h5>
					</div>
				</div>
				<div class="col-lg-6 p-t-20"> 
					<div class = "txt-full-width">
						<h5 class="details"><b>Secondary Address :  </b><span>{{$employee->employee_secondary_address ?? 'NA'}}</span></h5>
					</div>
				</div>
			</div>
			</div>
			<div class="sub-head">Job Details</div>
			<div class="dataSearchBox">    
				<div class="card-body row">
				@if(isset($employee->employee_dob))
					<div class="col-lg-6 p-t-20"> 
						<div class = "txt-full-width">
							<h5 class="details"><b> Date of Birth :  </b><span>{{$employee->employee_dob->format('d/m/Y')}}</span></h5>
						</div>
					</div>
				@endif
				<div class="col-lg-6 p-t-20"> 
					<div class = "txt-full-width">
						<h5 class="details"><b>Designation :  </b><span>{{$employee->designations->designation_name ?? 'NA'}}</span></h5>
					</div>
				</div>
				<div class="col-lg-6 p-t-20"> 
					<div class = "txt-full-width">
						<h5 class="details"><b>Role :  </b><span>{{ucwords(str_replace('_', ' ',$employee->user->getRoleNames()->implode(', ') ))}}</span></h5>
					</div>
				</div>
				<div class="col-lg-6 p-t-20"> 
					<div class = "txt-full-width">
						<h5 class="details"><b>Head Role  :  </b><span> 
							@if(!empty($employee->head_role))                    
								{{ucwords(str_replace('_', ' ',$employee->headRole->name)) }}
							@endif 
						</span></h5>
					</div>
				</div>
				<div class="col-lg-6 p-t-20"> 
					<div class = "txt-full-width">
						<h5 class="details"><b>Head User  :  </b><span> 
							@if(!empty($employee->head_user))
								{{$employee->headUser->employee->employee_name}}
							@endif   
						</span></h5>
					</div>
				</div>
				 @if(isset($employee->job->job_category_name))
				<div class="col-lg-6 p-t-20"> 
					<div class = "txt-full-width">
						<h5 class="details"><b>Job :  </b><span> 
							                  
								{{$employee->job->job_category_name}}
						
						</span></h5>
					</div>
				</div>
				@endif 
				<div class="col-lg-6 p-t-20"> 
					<div class = "txt-full-width">
						<h5 class="details"><b>Status :  </b><span> 
							@if($employee->employee_status==1){{ 'Active' }}@else{{ 'Inactive' }}@endif  
						</span></h5>
					</div>
				</div>
				</div>
			</div>
				<div class="sub-head">Image</div>
					<div class="dataSearchBox">    
						<div class="card-body row">
							<div class="col-lg-6 p-t-20"> 
							<div class = "txt-full-width">
								<h5 class="details"><b>Image :  </b><span> 
									@if($employee->employee_picture)
                    
										<img src="{{asset("storage/app/".$employee->employee_picture)}}"  width="100" height="100">

									@endif							
								</span></h5>
							</div>
						</div>
						</div>
					</div>
			</div>
		</div>	
   
</div> 


@endsection
