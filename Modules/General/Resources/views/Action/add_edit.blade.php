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
              <div class="page-title">Action</div>
          </div>
          <ol class="breadcrumb page-breadcrumb pull-right">
              <li><i class="fa fa-home"></i>&nbsp;<a class="parent-item" href="{{route('home')}}">Home</a>&nbsp;<i class="fa fa-angle-right"></i></li>
              <li>&nbsp;<a class="parent-item" href="{{route('action.index')}}">Action</a>&nbsp;<i class="fa fa-angle-right"></i></li>
              <li class="active">Create Action</li>
          </ol>
      </div>
  </div>
<div class="row">
<div class="col">
<div class="card card-box salesSearchBox">
<form action="{{ !isset($action)? route('action.store'): route('action.update',$action->id)}}" method="POST" id="form_sample_2" class="form-horizontal" enctype="multipart/form-data" data-toggle="validator">
{{csrf_field()}} @if(isset($action)){{method_field('PUT')}}@endif


<div class="dataSearchBox ">
    
        <div class="row">
         
         <div class="col-sm-6">
            <div class="form-group">
                <label for="action_name">Action Name</label>
                 <div class="p-relative">
                      <i class="fa fa-id-badge icn-add" aria-hidden="true"></i>
                <input type="text" class="form-control" id="action_name"  placeholder="Enter Action Name" name="action_name" value="{{ isset($action)?  old('action_name',$action->action_name): old('action_name')}}" maxlength="25" required>
              </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="action_key">Action Key</label>
              <div class="p-relative">
                      <i class="fa fa-key icn-add" aria-hidden="true"></i>
                <input type="text" class="form-control" id="action_key"  placeholder="Enter Action Key" name="action_key" value="{{ isset($action)?  old('action_key',$action->action_key): old('action_key')}}" maxlength="25" required>
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