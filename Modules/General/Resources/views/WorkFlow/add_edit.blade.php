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
              <div class="page-title">Workflow</div>
          </div>
          <ol class="breadcrumb page-breadcrumb pull-right">
              <li><i class="fa fa-home"></i>&nbsp;<a class="parent-item" href="{{route('home')}}">Home</a>&nbsp;<i class="fa fa-angle-right"></i></li>
              <li>&nbsp;<a class="parent-item" href="{{route('workFlow.index')}}">Work-Flow</a>&nbsp;<i class="fa fa-angle-right"></i></li>
              <li class="active">Create Work-Flow</li>
          </ol>
      </div>
  </div>
<div class="row">
<div class="col">
<div class="card card-box salesSearchBox">
<form action="{{ !isset($workFlow)? route('workFlow.store'): route('workFlow.update',$workFlow->id)}}" method="POST" id="form_sample_2" class="form-horizontal" enctype="multipart/form-data" data-toggle="validator">
{{csrf_field()}} @if(isset($workFlow)){{method_field('PUT')}}@endif


<div class="dataSearchBox ">
    
        <div class="row">
         
         <div class="col-sm-6">
            <div class="form-group">
                <label for="simpleFormEmail">Workflow Name</label> 
                 <div class="p-relative">
                      <i class="fa fa-tag icn-add" aria-hidden="true"></i>            
                <input type="text" class="form-control" id="simpleFormEmail"  placeholder="Enter Work-Flow Name" name="work_flows_name" value="{{ isset($workFlow)?  old('work_flows_name',$workFlow->work_flows_name): old('work_flows_name')}}" maxlength="25" required>
              </div>
            </div>
          </div>

        <div class="col-sm-6">
            <div class="form-group">
                <label for="simpleFormEmail">Default Role</label>
                <div class="p-relative">
                      <i class="fa fa-user-circle-o icn-add" aria-hidden="true"></i>
                 <select id="role" required name="role" class="form-control">
                   <option value="">Select Role</option>
                   @foreach($roles as $role)
                   <option {{ (old('role', isset($workFlow)? $workFlow->default_role : '' ) == $role->id) ? 'selected' : '' }}  value="{{$role->id}}">{{ucwords(str_replace('_', ' ',$role->name))}}</option>
                   @endforeach
                 </select>  
                 </div>             
            </div>
          </div>

          <div class="col-sm-6">
            <div class="form-group">
                <label for="simpleFormEmail">Default User</label>
                <div class="p-relative">
                      <i class="fa fa-user-circle-o icn-add" aria-hidden="true"></i>
                 <select id="user_id" name="user_id" class="form-control">
                   @isset($usersList)
                   <option value="">Select User</option>
                   @foreach($usersList as $user)
                   <option {{ (old('user_id', isset($workFlow)? $workFlow->default_user_id : '' ) == $user->id) ? 'selected' : '' }}  value="{{$user->id}}">{{$user->username}}</option>
                   @endforeach
                   @else
                    <option value="">Select Role</option>
                   @endisset
                 </select>
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

      $('#role').change(function() {         
          var role = $(this).val();             
               $.ajax({
                url: "{{url('/processAssign/usersByRoleId')}}",
                data: {"id":role,"_token": "{{ csrf_token() }}"},
                dataType: "json",
                method: 'POST',
                success: function(data){   
                     if(data.length > 0){
                          $('#user_id').empty();
                           $('#user_id').append('<option value="">Select User</option>');
                            $.each(data, function(key, value) {
                                $('#user_id').append('<option value="'+ value['id'] +'">'+ value['username'] +'</option>');
                          });
                        }else
                        $('#user_id').empty();
                 }
               });
      });

  })
 
</script>
@endsection 
