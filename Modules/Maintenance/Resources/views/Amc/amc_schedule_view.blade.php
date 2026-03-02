@extends('layouts.plms-app')


@section('content')
<style>
  input[type=date]::-webkit-inner-spin-button {
    -webkit-appearance: none;
    display: none;
  }
</style>
<!-- start widget -->
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">AMC Schedule View</div>
    </div>
    {{ Breadcrumbs::render('amcSchedule.show') }}
  </div>
</div>

<div class="row">
  <div class="col-sm-12">
    <div class="card-box">
            <!-- <div class="card-head">
              <header>View Building Type</header>
            </div> -->
            <form action="#" id="form_sample_2" class="form-horizontal">
              <div class="card-body row"> 


               <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b> Contract No  </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$amcSchedule->amcContract->amc_contract_no ?? 'nil'}}</span></div>
                </div>
              </div>

              <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Contractor/Engineer</b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$amcSchedule->vendor->vendor_name ?? $amcSchedule->technician->username }}</span></div>
                </div>
              </div> 
              <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Building </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$amcSchedule->building->building_name}}</span></div>
                  <input type="hidden" name="building_text_id" id="building_text_id" value="{{$amcSchedule->building_id}}">
                  <input type="hidden" name="amc_schedule_id" id="amc_schedule_id" value="{{$amcSchedule->id}}">
                  <input type="hidden" name="amc_schedule_period_from" id="amc_schedule_period_from" value="{{$amcSchedule->amc_schedule_period_from->format('Y-m-d')}}">

                  <input type="hidden" name="amc_schedule_period_to" id="amc_schedule_period_to" value="{{$amcSchedule->amc_schedule_period_to->format('Y-m-d')}}">
                </div>
              </div>
              <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Unit </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$amcSchedule->unit->unit_code??''}}</span></div>
                </div>
              </div>
              <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Start Date</b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$amcSchedule->amc_schedule_period_from->format('d/m/Y')}}</span></div>
                </div>
              </div>
              <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>End Date </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$amcSchedule->amc_schedule_period_to->format('d/m/Y')}}</span></div>
                </div>
              </div>
              <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Frequency  </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$amcSchedule->paymentMethod->payment_method_code}}</span></div>
                </div>
              </div>
              <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Status  </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>
                    @if($amcSchedule->amc_schedule_status==0){{'Open'}}@elseif($amcSchedule->amc_schedule_status==1){{'Closed'}}@else {{'Cancelled'}} @endif

                  </span></div>
                </div>
              </div> 
              
              <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Description  </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$amcSchedule->amc_schedule_description}}</span></div>
                </div>
              </div>     
            </div>
          </form>
        </div>
        <!-- -->
        

        <div class="card-head">
          <h4>
          <div id="pagination_info">
                     @include('includes.pagination_info',['paginator' => $amcScheduleTasks])         
                 </div>
            @if($amcSchedule->amc_schedule_status==0)
            <a  class="btn btn-circle btn-primary  align-right AddTaskAmenity"  data-toggle="modal" data-target="#myModal" data-backdrop="static" data-keyboard="false">Add</a> 
            @endif 
            <div class="clr"></div>
          </h4>
        </div>
        <!-- task list-->
        <table class="table display product-overview mb-30" id="dtBasicExample">
          <thead>
            <tr>
              <th>Start Dt</th>
              <th>End Dt</th>
              <th>Amenity</th>
              <th>Status</th>
              <th>Remark</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($amcScheduleTasks as $amcScheduleTask)
            <tr>                        
              <td>{{$amcScheduleTask->amc_schedule_from_date->format('d/m/Y')}}</td>                         
              <td>{{$amcScheduleTask->amc_schedule_to_date->format('d/m/Y')}}</td>                         
              <td>{{$amcScheduleTask->amenityType->amentity_types_name}}</td>                         
              <td><a title="Status" class="change_status">@if($amcScheduleTask->amc_schedule_task_status==0)<button type="button" class="btn label label-info label-mini">Open</button>@else<button type="button" class="btn label label-danger label-mini">Closed @endif</td>                         
              <td>{{$amcScheduleTask->amc_schedule_task_remarks}}</td>                         
              <td> 
                @if($amcScheduleTask->amc_schedule_task_status==0)
                <button type="button" class="btn btn-tbl-cancel btn-xs close_type" title="Cancel" data-toggle="modal" data-target="#myModal" data-id="{{$amcScheduleTask->id}}" data-backdrop="static" data-keyboard="false"><i class="fa fa-ban "></i></button>
                @endif
                <!-- edit -->
                @if($amcScheduleTask->amc_schedule_task_status==0)
                <button type="button" class="btn btn-tbl-edit btn-xs updateAmenity" title="Edit" data-toggle="modal" data-target="#myModal" data-id="{{$amcScheduleTask->amc_schedule_id}}"  datas-id = "{{$amcScheduleTask->id}}" datas-idd = "{{$amcScheduleTask->amenities_type_id}}" data-backdrop="static" data-keyboard="false"><i class="fa fa-pencil"></i></button>
                @endif
                <!-- edit -->
                @if(count($amcScheduleTasks)>1 && $amcScheduleTask->amc_schedule_task_status==0)
                <a href="{{route('amcTask.destroy',$amcScheduleTask->id)}}" title="Delete" class="btn btn-tbl-delete btn-xs delete_type">
                  <i class="fa fa-trash-o "></i>
                </a>
                @endif

                @role('maintenance_supervisor|super_admin|maintenance_head|maintenance_coordinator')
                @if(!empty($amcScheduleTask->AmcSchedule->user_id) && $amcScheduleTask->amc_schedule_task_status == 0)
                <a title="Reminder" href="{{route('addTask.reminder',$amcScheduleTask->id)}}" class="btn btn-tbl-general btn-xs">
                  <i class="fa fa-bell"></i>
                </a>
                @endif

                @endrole
              </td>                         
            </tr>  
            @empty 
            <tr>
              <td colspan="8" align="center">
                <p>No Record</p>
              </td>
            </tr>
            @endforelse 
          </tbody>
        </table>

        {{ $amcScheduleTasks->links() }}   
        <!--ends -->
      </div>
    </div>
    <div class="modal" id="myModal">

    </div>
    <form id="delete-form" action="" method="POST">
      {{ method_field('DELETE') }}  {{csrf_field()}}
      <input value="delete" style="display: none;" type="submit">
    </form>
    @endsection
    @section('scripts')
    <script>
      /***************************************************************************/
      $(document).ready(function(){
        $("#update-amenity-form").validate();
      });
      /***************************************************************************/
//add new schedule
$(document).on('click','.AddTaskAmenity',function(){

  var building_text_id = $("#building_text_id").val();
  var amc_schedule_id = $("#amc_schedule_id").val();
  var amc_schedule_period_from = $("#amc_schedule_period_from").val();
  var amc_schedule_period_to = $("#amc_schedule_period_to").val();
  $.ajax({
            method: 'POST', // Type of response and matches what we said in the route
            //url: '../../tenantContract/'+tenantContract_id+'/edit', // This is the url we gave in the route
           // url: '../complaints/ticketEdit',
           url: "{{route('addTaskAmenity')}}",
            data: {'building_text_id' : building_text_id,'amc_schedule_id' : amc_schedule_id,'amc_schedule_period_from' : amc_schedule_period_from,'amc_schedule_period_to' : amc_schedule_period_to,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
            success: function(response){ // What to do if we succeed
              $("#myModal").html(response); 
            },
          });
  return true;


});
/***************************************************************************/
$(document).on('click','.updateAmenity',function(){

  var amc_schedule_id = $(this).attr('data-id');
  var amc_task_id = $(this).attr('datas-id');
  var amenities_type_id = $(this).attr('datas-idd');
  var amc_schedule_period_from = $("#amc_schedule_period_from").val();
  var amc_schedule_period_to = $("#amc_schedule_period_to").val();

  $.ajax({
            method: 'POST', // Type of response and matches what we said in the route
            //url: '../../tenantContract/'+tenantContract_id+'/edit', // This is the url we gave in the 
            url: "{{route('editTaskAmenity')}}",
           //url: '../../complaintStage/'+item_id+'/edit',
          data: {'amc_schedule_id' : amc_schedule_id,'amc_task_id' : amc_task_id,'amenities_type_id' : amenities_type_id,'amc_schedule_period_from' : amc_schedule_period_from,'amc_schedule_period_to' : amc_schedule_period_to,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
            success: function(response){ // What to do if we succeed
              $("#myModal").html(response); 
            },
          });
  return true;


});
/***********************************************************/

/***************************************************************************/
//delete schedule task
jQuery('.delete_type').click(function (event) {
  var action = $(this).attr("href");
  event.preventDefault();
  if (confirm('Do you want to Delete this Amenity Task?')) {
    jQuery("#delete-form").attr('action', action);
    jQuery("#delete-form").submit();
  } else {
    return false;
  }
});
/***************************************************************************/
$(document).on('click','.close_type',function(){

  var amc_task_id = $(this).attr('data-id');

  $.ajax({
        method: 'POST', // Type of response and matches what we said in the route
        //url: '../../tenantContract/'+tenantContract_id+'/edit', // This is the url  we gave in the 
        url: "{{route('checkTask')}}",
        //url: '../../complaintStage/'+item_id+'/edit',
        data: {'amc_task_id' : amc_task_id,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
        success: function(response){ // What to do if we succeed
          //alert(response);
          $("#myModal").on("hidden.bs.modal", function(){
            $("#myModal").html("");
            $(this).removeData('bs.modal');
          });
          if(response == 0){
            $.ajax({
              method: 'POST', // Type of response and matches what we said in the route
              //url: '../../tenantContract/'+tenantContract_id+'/edit', // This is the url  we gave in the 
              url: "{{route('closeTask')}}",
              //url: '../../complaintStage/'+item_id+'/edit',
              data: {'amc_task_id' : amc_task_id,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
              success: function(responses){ // What to do if we succeed
                $("#myModal").html(responses); 
              },
            });
          }else{

           alert("Close The Previous Task Before Closing This !");
           $("#myModal").modal('hide');
           return false;
         }
         
       },

     });
  return true;


});
/***************************************************************************/
</script>
@endsection