@extends('layouts.plms-app')
@section('css')

<link href="{{asset('public/css/formlayout.css')}}" rel="stylesheet" type="text/css" />

@endsection 
@section('content')
  <!-- start widget -->
  <div class="page-bar">
      <div class="page-title-breadcrumb">
          <div class=" pull-left">
              <div class="page-title">Process Action Link</div>
          </div>
          <ol class="breadcrumb page-breadcrumb pull-right">
              <li><i class="fa fa-home"></i>&nbsp;<a class="parent-item" href="{{route('home')}}">Home</a>&nbsp;<i class="fa fa-angle-right"></i></li>
              <li>&nbsp;<a class="parent-item" href="{{route('processActionLink.index')}}"> Process Action Link</a>&nbsp;<i class="fa fa-angle-right"></i></li>
              <li class="active">{{!isset($processActionLink)?'Edit Process Action Link':'Create Process Action Link'}}</li>
          </ol>
      </div>
  </div>
<div class="row">
<div class="col">
<div class="card card-box salesSearchBox">
<form action="{{!isset($processActionLink)? route('processActionLink.store'): route('processActionLink.update',$processActionLink)}}" method="POST" id="form_sample_2" class="form-horizontal" enctype="multipart/form-data" data-toggle="validator">
{{csrf_field()}} @if(isset($processActionLink)){{method_field('PUT')}}@endif


<div class="dataSearchBox ">
    
        <div class="row">
         
         <div class="col-sm-6">
            <div class="form-group">
                <label for="work_flow_process_id">Process Action Link <small class="textRed">*</small></label>
              <div class="w-100">
                 <div class="p-relative">
                      <i class="fa fa-link icn-add" aria-hidden="true"></i>
                <select class="form-control" name="work_flow_process_id" required >
                  <option value="">Select Work Process</option>
                    <?php foreach ($workFlowProcess as $key => $process): ?>
                       <option {{ isset($processActionLink)? ((old('work_flow_process_id',$process->work_flow_processes_code) == $processActionLink->work_flow_processes_code )? 'selected' : '') :((old('work_flow_process_id') == $process->id )? 'selected' : '')}} value="{{$process->work_flow_processes_code}}">{{$process->work_flow_processes_name}}</option>
                    <?php endforeach ?> 
                   
                </select>
              </div>

            </div>
           </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="actions_id">Action Name<small class="textRed">*</small></label>
               <div class="w-100">
                       <div class="p-relative">
                      <i class="fa fa-level-up icn-add" aria-hidden="true"></i>
                <select class="form-control" name="actions_id" required >
                  <option value="">Select Action</option>
                    <?php foreach ($actions as $key => $action): ?>
                       <option {{ isset($processActionLink->actions_id)? ((old('work_flows_id',$action->id) == $processActionLink->actions_id )? 'selected' : '') :((old('actions_id') == $action->id )? 'selected' : '')}} value="{{$action->id}}">{{$action->action_name}}</option>
                    <?php endforeach ?> 
                </select>
              </div>
                     
              </div>
            </div> 
      </div>
      <div class="col-sm-6">
            <div class="form-group">
                <label for="next_process_id">Next Stage Name<small class="textRed">*</small></label>
              <div class="w-100">
                 <div class="p-relative">
                      <i class="fa fa-arrow-up icn-add" aria-hidden="true"></i>
                <select class="form-control" name="next_process_id" required >
                  <option value="">Select Next Work Process</option>
                    <?php foreach ($workFlowProcess as $key => $nxtProcess): ?>
                       <option {{ isset($processActionLink->next_processes_code)? ((old('next_process_id',$nxtProcess->work_flow_processes_code) == $processActionLink->next_processes_code )? 'selected' : '') :((old('next_process_id') == $nxtProcess->id )? 'selected' : '')}} value="{{$nxtProcess->work_flow_processes_code}}">{{$nxtProcess->work_flow_processes_name}}</option>
                    <?php endforeach ?> 
                   
                </select>
              </div>
            </div>
           </div>
        </div>
      <div class="w-100"></div>
        
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