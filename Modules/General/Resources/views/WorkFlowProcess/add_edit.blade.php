@extends('layouts.plms-app')
@section('css')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">
<!-- data tables -->
<link href="{{asset('public/css/formlayout.css')}}" rel="stylesheet" type="text/css" />
@endsection 
@section('content')
  <!-- start widget -->
  <div class="page-bar">
      <div class="page-title-breadcrumb">
          <div class=" pull-left">
              <div class="page-title">Workflow Stage</div>
          </div>
          <ol class="breadcrumb page-breadcrumb pull-right">
              <li><i class="fa fa-home"></i>&nbsp;<a class="parent-item" href="{{route('home')}}">Home</a>&nbsp;<i class="fa fa-angle-right"></i></li>
              <li>&nbsp;<a class="parent-item" href="{{route('workFlowProcess.index')}}">Create Work-Flow Stage</a>&nbsp;<i class="fa fa-angle-right"></i></li>
              <li class="active">Create Work-Flow Stage</li>
          </ol>
      </div>
  </div>
<div class="row">
<div class="col">
<div class="card card-box salesSearchBox">
<form action="{{ !isset($workFlowProcess)? route('workFlowProcess.store'): route('workFlowProcess.update',$workFlowProcess->id)}}" method="POST" id="form_sample_2" class="form-horizontal" enctype="multipart/form-data" data-toggle="validator">
{{csrf_field()}} @if(isset($workFlowProcess)){{method_field('PUT')}}@endif


<div class="dataSearchBox ">
    
        <div class="row">
         
         <div class="col-sm-6">
            <div class="form-group">
                <label for="work_flow_processes_name">Workflow Stage Name</label>
             <div class="p-relative">
                      <i class="fa fa-tag icn-add" aria-hidden="true"></i>
                <input type="text" class="form-control" id="work_flow_processes_name"  placeholder="Enter Work-Flow Stage Name" name="work_flow_processes_name" value="{{ isset($workFlowProcess)?  old('work_flow_processes_name',$workFlowProcess->work_flow_processes_name): old('work_flow_processes_name')}}" maxlength="50" required>
              </div>
            </div>
        </div>
         <div class="col-sm-6">
            <div class="form-group">
                <label for="work_flow_processes_code">Workflow Code</label>
             <div class="p-relative">
                      <i class="fa fa-terminal icn-add" aria-hidden="true"></i>
                <input type="text" class="form-control" id="work_flow_processes_code"  placeholder="Enter work-flow process code" name="work_flow_processes_code" value="{{ isset($workFlowProcess)?  old('work_flow_processes_code',$workFlowProcess->work_flow_processes_code): old('work_flow_processes_code')}}" maxlength="50" required>
              </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="work_flows_id">Workflow Name<small class="textRed">*</small></label>
               <div class="w-100">
                      <div class="p-relative">
                      <i class="fa fa-user-circle icn-add" aria-hidden="true"></i>
                <select class="form-control" name="work_flows_id" required>
                  <option>Select Work Process</option>
                    <?php foreach ($workFlowList as $key => $flow): ?>
                       <option {{ isset($workFlowProcess->work_flows_id)? ((old('work_flows_id',$flow->id) == $workFlowProcess->work_flows_id )? 'selected' : '') :((old('work_flows_id') == $flow->id )? 'selected' : '')}} value="{{$flow->id}}">{{$flow->work_flows_name}}</option>
                    <?php endforeach ?> 
                   
                </select>
              </div>
            </div> 
        </div>
       </div>
       <div class="col-sm-6">
            <div class="form-group">
                <label for="process_assign_level">Workflow Status<small class="textRed">*</small></label>
               <div class="w-100">
                     <div class="p-relative">
                      <i class="fa fa-anchor icn-add" aria-hidden="true"></i>
                <select class="form-control" name="process_assign_level" >
                      <option value="1" {{ isset($workFlowProcess)?(($workFlowProcess->process_assign_level==1)?'selected':''):''}}>In-Progress</option>
                      <option value="2" {{ isset($workFlowProcess)?(($workFlowProcess->process_assign_level==2)?'selected':''):''}}>Completed</option>
                      <option value="3" {{ isset($workFlowProcess)?(($workFlowProcess->process_assign_level==3)?'selected':''):''}}>Reopen</option>
                </select>
                </div> 
            </div> 
        </div>
       </div> 
       <div class="w-100">
         
       </div>
        
        <div class="col">
          <div class="w-100"></div>
              <button type="submit" class="btn btn-primary">SAVE</button>
        </div>
           
      </div>
    
</div>
<div class="clearfix"></div>
</form>
    
</div>
</div>
</div>
@endsection
@section('scripts')
<script type="text/javascript">
  $(document).ready(function(){ 

      $("#form_sample_2").validate()

  })
 
</script>
@endsection 
