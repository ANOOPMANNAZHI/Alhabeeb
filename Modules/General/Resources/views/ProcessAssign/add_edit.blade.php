@extends('layouts.plms-app')
@section('css')

<link href="{{asset('public/css/formlayout.css')}}" rel="stylesheet" type="text/css" />

@endsection 
@section('content')
  <!-- start widget -->
  <div class="page-bar">
      <div class="page-title-breadcrumb">
          <div class=" pull-left">
              <div class="page-title">Process Assign</div>
          </div>
          <ol class="breadcrumb page-breadcrumb pull-right">
              <li><i class="fa fa-home"></i>&nbsp;<a class="parent-item" href="{{route('home')}}">Home</a>&nbsp;<i class="fa fa-angle-right"></i></li>
              <li>&nbsp;<a class="parent-item" href="{{route('processAssign.index')}}"> Process Assign</a>&nbsp;<i class="fa fa-angle-right"></i></li>
              <li class="active">{{!isset($processAssign)?'Edit Process Assign':'Create Process Assign'}}</li>
          </ol>
      </div>
  </div>
<div class="row">
<div class="col">
<div class="card card-box salesSearchBox">
<form action="{{!isset($processAssign)? route('processAssign.store'): route('processAssign.update',$processAssign)}}" method="POST" id="form_sample_2" class="form-horizontal" enctype="multipart/form-data" data-toggle="validator">
{{csrf_field()}} @if(isset($processAssign)){{method_field('PUT')}}@endif


<div class="dataSearchBox ">
    
        <div class="row">
         <div class="col-sm-6">
            <div class="form-group">
                <label for="work_flows_name">Work-Flow Name<small class="textRed">*</small></label>
              <div class="w-100">
                <div class="p-relative">
                      <i class="fa fa-user-circle icn-add" aria-hidden="true"></i>
                <select class="form-control" name="work_flows_name" required id="work_flows_name">
                  <option value="">Select Work-Flow</option>
                    <?php foreach ($workFlow as $key => $flow): ?>
                       <option {{ isset($processAssign)? ((old('work_flows_name',$processAssign->work_process->workflow->id) == $flow->id )? 'selected' : '') :((old('work_flows_name') == $flow->id )? 'selected' : '')}} value="{{$flow->id}}">{{$flow->work_flows_name}}</option>
                    <?php endforeach ?> 
                   
                </select>
              </div>

            </div>
           </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="work_flow_process_id">Work Flow Stage<small class="textRed">*</small></label>
              <div class="w-100">
                <div class="p-relative">
                      <i class="fa fa-level-up icn-add" aria-hidden="true"></i>
                <select class="form-control" name="work_flow_process_id" required id="work_flow_process_id">
                  @if(isset($processAssign)):
                  workFlowProcess
                  @foreach ($workFlowProcess as $process)
                    <option value="{{$process->work_flow_processes_code}}" {{ ($processAssign->work_process->work_flow_processes_code == $process->work_flow_processes_code)?'selected' : ''}}  >{{$process->work_flow_processes_name}}
                    </option>
                  @endforeach
                  @else
                     <option value="">select process
                    </option>
                  @endif
                </select>
              </div>
            </div>
           </div>
        </div>
      </div>
      <div class="row Sales" >
        <div class="w-100"></div>
         <div class="col-sm-6">
            <div class="form-group">
                <label for="work_flow_process_id">Location</label>
              <div class="w-100">
                 <div class="p-relative">
                      <i class="fa fa-map-marker icn-add" aria-hidden="true"></i>
                <select class="form-control" name="location_id" >
                  <option value="">Select Location</option>
                    <?php foreach ($location as $key => $loc): ?>
                       <option {{ isset($processAssign->location_id)? ((old('location_id',$loc->id) == $processAssign->location_id )? 'selected' : '') :((old('location_id') == $loc->id )? 'selected' : '')}} value="{{$loc->id}}">{{$loc->locations_name.'( '.$loc->locations_code.' )'}}</option>
                    <?php endforeach ?> 
                </select>
                </div>
            </div>
           </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="price_range_id">Price Range</label>
              <div class="w-100">
                <div class="p-relative">
                      <i class="fa fa-money icn-add" aria-hidden="true"></i>
                <select class="form-control" name="price_range_id" >
                  <option value="">Select Price Range </option>
                    <?php foreach ($priceRange as $key => $price): ?>
                       <option {{ isset($processAssign->price_range_id)? ((old('price_range_id',$price->id) == $processAssign->price_range_id )? 'selected' : '') :((old('price_range_id') == $price->id )? 'selected' : '')}} value="{{$price->id}}">{{$price->price_ranges_name}}</option>
                    <?php endforeach ?> 
                   
                </select>
                </div>
            </div>
           </div>
        </div>
        </div>

        <div class="row Maintenance" >
          <div class="col-sm-6">
            <div class="form-group">
                <label for="work_flow_process_id">Status</label>
              <div class="w-100">
                 <div class="p-relative">
                      <i class="fa fa-map-marker icn-add" aria-hidden="true"></i>
                <select class="form-control" name="status" >
                    <option value="">Select Status</option>                    
                    <option {{ isset($processAssign->tenant_status_id)? ((old('status',1) == $processAssign->tenant_status_id )? 'selected' : '') :((old('status') == 1 )? 'selected' : '')}} value="1">{{'On Hold'}}</option>
                    <option {{ isset($processAssign->tenant_status_id)? ((old('status',2) == $processAssign->tenant_status_id )? 'selected' : '') :((old('status') == 2 )? 'selected' : '')}} value="2">{{'No Maintenance'}}</option>
                    <option {{ isset($processAssign->tenant_status_id)? ((old('status',3) == $processAssign->tenant_status_id )? 'selected' : '') :((old('status') == 3 )? 'selected' : '')}} value="3">{{'Maintenance By Landlord'}}</option>
                    <option {{ isset($processAssign->tenant_status_id)? ((old('status',4) == $processAssign->tenant_status_id )? 'selected' : '') :((old('status') == 4 )? 'selected' : '')}} value="4">{{'VIP'}}</option>
                    <option {{ isset($processAssign->tenant_status_id)? ((old('status',5) == $processAssign->tenant_status_id )? 'selected' : '') :((old('status') == 5 )? 'selected' : '')}} value="5">{{'Blacklisted'}}</option>
                    <option {{ isset($processAssign->tenant_status_id)? ((old('status',6) == $processAssign->tenant_status_id )? 'selected' : '') :((old('status') == 6 )? 'selected' : '')}} value="6">{{'Legal'}}</option>
                   
                </select>
                </div>
            </div>
           </div>
        </div>
        </div>
        <div class="row"> 
        <div class="col-sm-12 align-right" >
            <div class="form-group">
                <label for="work_flow_process_id"></label>
              <div class="w-100">
                <a href="#" id="" class="add btn btn-circle btn-primary align-right move-button"  > Add Row  </a>

            </div>
           </div>
        </div>
        <div class="col-sm-12 btm-mrgn" id="appendhtml" >
              {{--dd($processAssign->processAssignUserRole()->pluck('role_id'))--}}
              {{--dd($processAssign->processAssignUserRole->where('role_id',1))--}}
       @if(isset($processAssign))

       @foreach ($processAssign->processAssignUserRole->pluck('role_id')->unique() as $key=>$val)
        
        <div class="row rowcount" >
            <div class="col-sm-5">
                <div class="form-group">
                    <label for="work_flow_process_id">Roles<small class="textRed">*</small></label>
                  <div class="w-100">
                    <select class="form-control rolecls" name="role_id{{ $loop->iteration }}"  id="role_{{ $loop->iteration }}" required >
                      <option value="">Select Roles</option>
                        <?php foreach ($roleList as $k=> $role): ?>
                          @if($key===0)
                           <option {{ isset($processAssign)? ((old('role_id',$val) == $role->id)? 'selected' : '') :((old('role_id') == $loc->id )? 'selected' : '')}} value="{{$role->id}}">{{ucwords(str_replace('_', ' ',$role->name))}}

                           </option>
                         @else
                            @if($role->id == $val)
                            <option {{ isset($processAssign)? ((old('role_id',$val) == $role->id)? 'selected' : '') :((old('role_id') == $loc->id )? 'selected' : '')}} value="{{$role->id}}">{{ucwords(str_replace('_', ' ',$role->name))}}
                        
                            </option>
                            @endif

                         @endif
                        <?php endforeach ?> 
                       
                      </select>
                    </div>
                  </div>
          </div>
          <div class="col-sm-5" >
                <div class="form-group">
                    <label for="user_id">Users</label>
                  <div class="w-100">
                    @php 
                          $userId  = $processAssign->processAssignUserRole->where('role_id',$val)->pluck('user_id')->all(); 
                

                    @endphp 
                    <select multiple name="user_id{{ $loop->iteration }}[]" class="form-control usercls" id="user_id_role_{{ $loop->iteration }}"  >
                      @if(isset($processAssign))

                        @foreach (\App\User::role($val)->get() as $users)
                                      
                            <option {{ isset($processAssign)? ((in_array($users->id, $userId))? 'selected' : ''):'' }}  value="{{$users->id}}">{{$users->username}}</option>
                      
                        @endforeach 
                                   
                      @else
                          <option value="">No Data</option>
                      @endif
                      
                     
                    </select> 

                </div>
               </div>
          </div>
          <div class="col-sm-2" >
                  <div class="form-group"> 
                    <div style="margin-top: 75px;">
                     <!--  <a href="#" id="@if(isset($processAssign)){{ $loop->iteration }}@else 1 @endif" class="add"  > Add Row  </a> -->
                      @if($key>0)
                       <a href="javascript:void(0);" class="remove"  onclick="removeRoleRow({{ $loop->iteration }})"> Remove</a> 
                      
                      @endif
                    </div>
                  </div>
          </div>
      </div>
      @endforeach
      @else

      <div class="row rowcount" >
            <div class="col-sm-5">
                <div class="form-group">
                    <label for="work_flow_process_id">Roles<small class="textRed">*</small></label>
                  <div class="w-100">
                    <select class="form-control rolecls" name="role_id1"  id="role_1" required >
                      <option value="">Select Roles</option>
                        <?php foreach ($roleList as $k=> $role): ?>
                          <option {{ isset($processAssign)? ((old('role_id',$val) == $role->id)? 'selected' : '') :((old('role_id') == $loc->id )? 'selected' : '')}} value="{{$role->id}}">{{ucwords(str_replace('_', ' ',$role->name))}}

                           </option>
                      
                        <?php endforeach ?> 
                       
                      </select>
                    </div>
                  </div>
          </div>
          <div class="col-sm-5" >
                <div class="form-group">
                    <label for="user_id">Users</label>
                  <div class="w-100">
                    <select multiple name="user_id1[]" class="form-control usercls" id="user_id_role_1"  >
                      @if(isset($processAssign))
                        
                        @foreach (\App\User::role($val)->get() as $users)
                             
                            <option {{ isset($processAssign)? ((old('user_id',$val) == $users->id)? 'selected' : '') :((old('user_id') == $user->id )? 'selected' : '')}}  value="{{$users->id}}">{{$users->username}}</option>
                      
                        @endforeach 
                                   
                      @else
                          <option value="">No Data</option>
                      @endif
                      
                     
                    </select> 

                </div>
               </div>
          </div>
          <div class="col-sm-2" >
                  <div class="form-group"> 
                    <div style="margin-top: 65px;">
                      <!-- <a href="#" id="1" class="add"  > Add Row  </a> -->
                      @if($key>0)
                       <a class="btn btn-secondary" href="javascript:void(0);" class="remove"  onclick="removeRoleRow(1)"> Remove</a> 
                      
                      @endif
                    </div>
                  </div>
          </div>
      </div>
      @endif
      </div>
      <div class="w-100"></div>
        
      <div class="col">
        <div class="w-100"></div>
            <input type="hidden" name="row_count" id="row_count" value="@if(isset($processAssign)){{ count($processAssign->processAssignUserRole->pluck('role_id')->unique()) }}@else 1 @endif">
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

      $("#work_flows_name").change(function()
      {
          var id  = $(this).val();
          if(id){
              $.ajax
                  ({
                      type: "POST",
                      url: "{{url('/processAssign/ajaxStages')}}",
                      data: {"id":id,"_token": "{{ csrf_token() }}"},
                      cache: false,
                      dataType: "json",
                      success: function(data)
                      {
                        if(data.length > 0){
                          $('#work_flow_process_id').empty();
                            $.each(data, function(key, value) {
                                $('#work_flow_process_id').append('<option value="'+ value['work_flow_processes_code'] +'">'+ value['work_flow_processes_name'] +'</option>');
                          });
                        }
                        else{
                            $('#work_flow_process_id').html('<option value="0" >No Data</option>');
                        }
                      } 
                  });

                }
          
          else{
              $('#work_flow_process_id').empty();
          }
        });

      // Users By RoleId
      $(document).on('change',".rolecls", function()
      {
     
          var id_attr  = $(this).attr("id"); 
          var id       = $('#'+id_attr).val();
          
          if(id){
              $.ajax
                  ({
                      type: "POST",
                      url: "{{url('/processAssign/usersByRoleId')}}",
                      data: {"id":id,"_token": "{{ csrf_token() }}"},
                      cache: false,
                      dataType: "json",
                      success: function(data)
                      {
                        if(data.length > 0){
                          $('#user_id_'+id_attr).empty();
                            $.each(data, function(key, value) {
                                $('#user_id_'+id_attr).append('<option value="'+ value['id'] +'">'+ value['username'] +'</option>');
                          });
                        }
                        else{
                            $('#user_id_'+id_attr).html('<option value=0>No Data</option>');
                        }
                      } 
                  });

                }
          
          else{
              $('#user_id_'+id).empty();
          }
        });
      $(document).on('click',".add", function()
       {
          var cunt = $('.rowcount').length;
          var cuntOption = ($('#role_1 option[value]').length)-1;
        
          var row = [];
          var html = '';
          var curr_click = $(this).attr("id");
          if($('#role_'+curr_click).val() != ''){
                if(cuntOption > cunt){
                  var inc = cunt+1;
                  html += '<div class="row rowcount" > <div class="col-sm-5"><div class="form-group"><label for="work_flow_process_id">Roles<small class="textRed">*</small></label><div class="w-100"><select id="role_'+inc+'" class="form-control rolecls" name="role_id'+inc+'" >';
                  
                $(".rolecls option:selected").each(function(){

                        row.push($(this).val());
                });
                $("#role_1 option[value]").each(function(){
                         // alert($(this).val()+'--'+opt_val);
                        if(jQuery.inArray($(this).val(), row )==-1) {
                         
                             html += '<option value="'+$(this).val()+'">'+$(this).text()+'</option>';
                        }
                                          
                });

                  html += '</select></div></div></div><div class="col-sm-5" ><div class="form-group"> <label for="user_id">Users</label><div class="w-100"><select multiple name="user_id'+inc+'[]" class="form-control usercls" id="user_id_role_'+inc+'"> <option value="0">No Data</option></select></div></div></div><div class="col-sm-2" <div class="form-group"><div style="margin-top: 75px;"><a class="btn btn-secondary" href="javascript:void(0);" onclick="removeRoleRow('+inc+')" >Remove </a></div></div></div></div></div>';
                  /*<a href="#" id="'+inc+'" class="add"  > Add Row  </a>*/
                  $('#appendhtml').append(html);
                  $("#row_count").val(cunt+1);
                  return false;
                }
                else{
                  alert("Role limit exceeded");
                  return false;
                }
              }
        else{

            alert("Please Select the role");
            return false;
        }

      }); 
/******************************************************************************************/  
      $(document).on('change',"#work_flows_name", function() {

        var category =$("#work_flows_name").val();
        if(category == 4){
          $(".Sales").hide();
          $(".Maintenance").show();
        }else{
          
          $(".Sales").show();
          $(".Maintenance").hide();
        }
      });
  })
 
function removeRoleRow(div_id){
  
  $('#role_'+div_id).parent().parent().parent().parent().remove();
  $(".rolecls").each(function(index){
        var inc = index+1;
        $(this).attr("name","role_id"+inc);
        $(this).attr("id","role_"+inc);

       // $(this).prev("li").attr("id","newId");
  });

  $(".usercls").each(function(index){
        var inc_user = index+1;
        $(this).attr("name","user_id"+inc_user+"[]");
        $(this).attr("id","user_id_role_"+inc_user);

       // $(this).prev("li").attr("id","newId");
  });
  var cunt = $('.rowcount').length;
  $("#row_count").val(cunt);
  return false;
}
</script>
@endsection 