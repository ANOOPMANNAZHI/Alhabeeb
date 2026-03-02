@extends('layouts.plms-app')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">


@section('content')
    <!-- start widget -->
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Workflow</div>
        </div>
        <ol class="breadcrumb page-breadcrumb pull-right">
            <li><i class="fa fa-home"></i>&nbsp;<a class="parent-item" href="{{route('home')}}">Home</a>&nbsp;<i class="fa fa-angle-right"></i></li>
            <li>&nbsp;<a class="parent-item" href="{{route('workFlowProcess.index')}}">Work-Flow Stage </a>&nbsp;<i class="fa fa-angle-right"></i></li>
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
                  <div class="col-md-5"><b>Workflow Stage Name </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$workFlowProcess->work_flow_processes_name}}</span></div>
                </div>
              </div>

    <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Workflow Process Code</b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$workFlowProcess->work_flow_processes_code}}</span></div>
                </div>
              </div>

    <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Workflow Process Name  </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$workFlowProcess->workflow->work_flows_name}}</span></div>
                </div>
              </div>

    <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Status :  </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>@if($workFlowProcess->work_flow_processes_status==1){{ 'Active' }}@else{{ 'Inactive' }}@endif</span></div>
                </div>
              </div>
            </div>
            </form>
        </div>
    </div>
</div> 


@endsection
