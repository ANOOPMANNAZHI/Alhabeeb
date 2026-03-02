@extends('layouts.plms-app')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">


@section('content')
    <!-- start widget -->
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Process Assign</div>
        </div>
        <ol class="breadcrumb page-breadcrumb pull-right">
            <li><i class="fa fa-home"></i>&nbsp;<a class="parent-item" href="{{route('home')}}">Home</a>&nbsp;<i class="fa fa-angle-right"></i></li>
            <li>&nbsp;<a class="parent-item" href="{{route('processAssign.index')}}">Process Assign </a>&nbsp;<i class="fa fa-angle-right"></i></li>
            <li class="active">View </li>
        </ol>
    </div>
</div>

<div class="row">
    <div class="col-sm-12">
        <div class="card-box">
          
            <form action="#" id="form_sample_2" class="form-horizontal">
            <div class="card-body row">


    <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b> Work-Flow Name:  </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$processAssigns->work_process->workflow->work_flows_name}}</span></div>
                </div>
              </div>

    <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b> Process Name:  </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$processAssigns->work_process->work_flow_processes_name}}</span></div>
                </div>
              </div>


    <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Location:  </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>
                      @if($processAssigns->location_id){{$processAssigns->location->locations_name}} @endif </span></div>
                </div>
              </div>

    <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Range:  </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>
                      @if($processAssigns->price_range_id)
                      {{$processAssigns->price->price_ranges_name}}
                     @endif 
                  </span></div>
                </div>
              </div>
 @foreach ($processAssigns->processAssignUserRole->pluck('role_id')->unique() as $key=>$val)
    <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Role {{ $loop->iteration }}:  </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>
                      @foreach ($processAssigns->processAssignRoles as $role)

                      @if($role->role_id== $val)
                          {{ucwords(str_replace('_', ' ',$role->name))}}
                          </br>
                      @endif
                      @endforeach 
                    </span></div>
                </div>
              </div>


    <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Users {{ $loop->iteration }}:  </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>
                       @php 
                          $userId  = $processAssigns->processAssignUserRole->where('role_id',$val)->pluck('user_id')->all(); 
                       @endphp 
                       @foreach(\App\User::role($val)->get() as $users)
                              
                              @if(in_array($users->id, $userId))
                                 
                                 {{$users->username}}   </br>      
             
                              @endif

                      @endforeach 
                     
                    </span></div>
                </div>
              </div>
                @endforeach 
                   
                
               
            </div>

            </form>
        </div>
    </div>
</div> 


@endsection