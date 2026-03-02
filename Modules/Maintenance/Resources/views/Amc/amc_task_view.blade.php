@extends('layouts.plms-app')


@section('content')
<!-- start widget -->
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">AMC Task View</div>
    </div>
    {{ Breadcrumbs::render('amcTask.show') }}
  </div>
</div>

<div class="row">
  <div class="col-sm-12">
    <div class="card-box">
             <div class="card-head">
              <h4>
       @if($amcTask->amc_schedule_task_status==0)
                      <button type="button" class="btn btn-circle btn-primary align-right close_type" title="Close" data-toggle="modal" data-target="#myModal" data-id="{{$amcTask->id}}" data-backdrop="static" data-keyboard="false">Close</button>
                    @endif 
        <div class="clr"></div>
            </h4>
            </div>
            <form action="#" id="form_sample_2" class="form-horizontal">
              <div class="card-body row"> 
                
                
               <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b> Contract No  </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$amcTask->amcSchedule->amcContract->amc_contract_no ?? ''}}</span></div>
                </div>
              </div>

              <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Contractor/Technician  </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$amcTask->amcSchedule->vendor->vendor_name ?? $amcTask->amcSchedule->technician->username }}</span></div>
                </div>
              </div>
               <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Amenity  </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$amcTask->amenityType->amentity_types_name}}</span></div>
                </div>
              </div> 
              <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Start Date </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$amcTask->amc_schedule_from_date->format('d/m/Y')}}</span></div>
                </div>
              </div>
              <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>End Date  </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$amcTask->amc_schedule_to_date->format('d/m/Y')}}</span></div>
                </div>
              </div>
              <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Status</b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>@if($amcTask->amc_schedule_task_status==0)open @else closed @endif</span></div>
                </div>
              </div>    
            </div>
          </form>
        </div>
      </div>
    </div>
  <div class="modal" id="myModal">
    
  </div>
  @endsection
  @section('scripts')
  <script>
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
  </script>
  @endsection