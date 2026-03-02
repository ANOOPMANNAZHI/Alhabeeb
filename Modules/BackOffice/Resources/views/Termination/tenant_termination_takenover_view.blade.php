@extends('layouts.plms-app')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">


@section('content')
<!-- start widget -->
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">Taken Over for Termination View</div>
    </div>
    {{ Breadcrumbs::render('takeoverForTerminationView',$termination) }} 
  </div>
</div>
<div class="row">
  <div class="col-sm-12">
    <div class="card-box">
      <div class="card-head">
        <h4>
		  @can('takenover_resubmit')
      <button type="submit" class="btn btn-circle btn-primary align-right resubmit" data-toggle="modal" data-target="#myModal" data-id = "RESUB" title="Resubmit" id="{{$termination->tenantContract->id}}" datas-id ="{{$termination->id}}" datas-enid="{{$termination->work_flow_processes_code}}" data-backdrop="static" data-keyboard="false" >
          Resubmit
      </button>
      <!--
			<a href="{{route('tenantTerminationStage',[$termination->id,$termination->tenantContract->id,$termination->work_flow_processes_code,'RESUB'])}}" title="Resubmit" class="btn btn-circle btn-primary  align-right">
				Resubmit
			</a> -->
		  @endcan
      @can('tenant_terminate')
      <button type="submit" class="btn btn-circle btn-primary align-right terminate" data-toggle="modal" data-target="#myModal_terminate" data-id = "TMT" title="Resubmit" id="{{$termination->tenantContract->id}}" datas-id ="{{$termination->id}}" datas-enid="{{$termination->work_flow_processes_code}}" data-backdrop="static" data-keyboard="false" >
          Terminate
      </button>
      <!--
      <a href="{{route('tenantTerminationStage',[$termination->id,$termination->tenantContract->id,$termination->work_flow_processes_code,'TMT'])}}" title="Terminate" class="btn btn-circle btn-primary  align-right ">
                Terminate
      </a> -->
      @endcan       
        </h4>
      </div>


      <div class="card-body row"> 

       <div class="col-md-6 p-t-10">
        <div class="row">
          <div class="col-md-5"><b> Contract No  </b></div>
          <div class="col-md-1 s-clm">:</div>
          <div class="col-md-6"><span>{{$tenantContract->tenant_contract_no}}</span></div>
        </div>
      </div>
      <div class="col-md-6 p-t-10">
        <div class="row">
          <div class="col-md-5"><b>Building Name  </b></div>
          <div class="col-md-1 s-clm">:</div>
          <div class="col-md-6"><span>{{$tenantContract->building->building_name}}</span></div>
        </div>
      </div>
      <div class="col-md-6 p-t-10">
        <div class="row">
          <div class="col-md-5"><b>Unit No </b></div>
          <div class="col-md-1 s-clm">:</div>
          <div class="col-md-6"><span>{{$tenantContract->unit->unit_no}}</span></div>
        </div>
      </div> 
      <div class="col-md-6 p-t-10">
        <div class="row">
          <div class="col-md-5"><b>Tenant Name  </b></div>
          <div class="col-md-1 s-clm">:</div>
          <div class="col-md-6"><span>{{$tenantContract->tenant->tenant_name}}</span></div>
        </div>
      </div> 
      <div class="col-md-6 p-t-10">
        <div class="row">
          <div class="col-md-5"><b>Mob No </b></div>
          <div class="col-md-1 s-clm">:</div>
          <div class="col-md-6"><span>{{$tenantContract->tenant->tenant_contact_no}}</span></div>
        </div>
      </div>
      <div class="col-md-6 p-t-10">
        <div class="row">
          <div class="col-md-5"><b>Location</b></div>
          <div class="col-md-1 s-clm">:</div>
          <div class="col-md-6"><span>{{$tenantContract->building->location->locations_name??'NA'}}</span></div>
        </div>
      </div>
       <div class="col-md-6 p-t-10">
        <div class="row">
          <div class="col-md-5"><b>Way No</b></div>
          <div class="col-md-1 s-clm">:</div>
          <div class="col-md-6"><span>{{$tenantContract->building->building_address??'NA'}}</span></div>
        </div>
      </div>
      <div class="col-md-6 p-t-10">
        <div class="row">
          <div class="col-md-5"><b>OutStanding</b></div>
          <div class="col-md-1 s-clm">:</div>
          <div class="col-md-6"><span>{{ ($outstandingOs > 0)? 'Yes' : 'No' }}</span></div>
        </div>
      </div>
      <div class="col-md-6 p-t-10">
        <div class="row">
          <div class="col-md-5"><b>Last Paid Date</b></div>
          <div class="col-md-1 s-clm">:</div>
          <div class="col-md-6"><span>{{isset($tenantContract->tenant->tenant_contract_last_paid_date)?$tenantContract->tenant->tenant_contract_last_paid_date->format('d/m/Y'):''}}</span></div>
        </div>
      </div>
      <div class="col-md-6 p-t-10">
        <div class="row">
          <div class="col-md-5"><b>Last Paid</b></div>
          <div class="col-md-1 s-clm">:</div>
          <div class="col-md-6"><span>{{$tenantContract->tenant->tenant_contract_last_paid_amt??''}}</span></div>
        </div>
      </div>
    </div>
  </div>


  <!-- Status Ribbon Starts -->
<div class="row">
  
    <div class="col-sm-12">
        <div class="panel">
            <header class="panel-heading panel-heading-blue">
                <div class="ribbon"><span>Status</span></div>
               Status </header>
            <div class="panel-body light-green">
            <div class="card-body row">

            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Tenant Status :  </b><span>{{$tenantContract->tenant->tenant_status_name}}</span></h5>
                </div>
            </div>
             <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Contract Status :  </b><span>{{$tenantContract->tenant_contract_status_name}}</span></h5>
                </div>
            </div>
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Municipality Registration :  </b><span>{{($tenantContract->tenant_contract_is_reg_municipality==NULL)?"No":"Yes"}}</span></h5>
                </div>
            </div>
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Unit Status :  </b><span>{{$tenantContract->unit->vacant_status_name}}</span></h5>
                </div>
            </div>
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Key Status :  </b><span>
                    @if(!empty($tenantContract->unit->key)){{$tenantContract->unit->key->status_name}}
                    @else
                    NA
                    @endif</span></h5>
                </div>
            </div>
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Remaining days to Expiry :  </b><span>{{$remainingDays}} Days</span></h5>
                </div>
            </div>
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Rent paid Up To:  </b><span>
                     @if(!empty($tenantContract->tenant_contract_last_paid_date))
                     {{$tenantContract->tenant_contract_last_paid_date->format('d/m/Y')}}
                     @else
                     NA
                     @endif</span></h5>
                </div>
            </div>
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b> </b><span></span></h5>
                </div>
            </div>
            </div>

            </div>
        </div>
    </div>
</div>
<!-- Status Ribbon Ends -->


  <div class="card card-box salesSearchBox " id="agdiv">
    <div class="dataSearchBox">    
        <div class="card-body row">
       
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>TakeOver Date :  </b><span>{{ (!empty($tenantContract->terminationContract->termination_takenover_date))? $tenantContract->terminationContract->termination_takenover_date->format('d/m/Y') : "NA"}}</span></h5>
                </div>
            </div>
            <div class="col-lg-6 p-t-20" style="display: none">
            <div class = "txt-full-width">
              <h5 class="details"><b>TakeOver Date :  </b><span class="closeTaken">{{$tenantContract->terminationContract->termination_takenover_date->format('d/m/Y')?? "NA"}}</span>
                <span class="openTaken" style="display: none">
                  <input type="date" name="termination_takenover_date" id="termination_takenover_date" class="form-controll"></span>

                </div>
              </div>

              <input type="hidden" name="termination_id" id="termination_id" value="{{$tenantContract->terminationContract->id}}">


              <div class="col-lg-6 p-t-20">
                <div class = "txt-full-width">
                  <h5 class="details"><b>Termination Date:  </b><span class="closeTermination">{{$tenantContract->terminationContract->termination_date->format('d/m/Y')?? "NA"}}</span>
                    <span class="openTermination" style="display: none"><input type="date" name="termination_date" id="termination_date" class="form-controll"></span>
                    <button class="editTermination"><i class="fa fa-pencil"></i></button>
                    <button class="saveTerminationbtn" title="Save" style="display:none;"><i class="fa fa-save"></i></button>
                    <button class="closeTerminationbtn" style="display:none;"><i class="fa fa-close"></i></button>
                  </h5>
                </div>
              </div>
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Remark:  </b><span>{{$tenantContract->terminationContract->termination_remark?? "NA"}}</span></h5>
                </div>
            </div>
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Assigned To: </b><span>{{isset($tenantContract->terminationContract->assignedTo) ? $tenantContract->terminationContract->assignedTo->employee->employee_name:'NA'}}</span></h5>
                </div>
            </div>
            
        </div>
    </div>
  </div>
  <div class="card card-box salesSearchBox " id="agdiv">
    <div class="dataSearchBox">
        <div class="card-body row">
           
            <div class="col-lg-4 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Electricity Acc/No :  </b><span>{{$termination->termination_electricity_acc_no}}</span></h5>
                   
                </div>
            </div>
            <div class="col-lg-4 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Closing Reading  :  </b><span>{{$termination->termination_electricity_close_reading}}</span></h5>
                </div>
            </div>
            <div class="col-lg-4 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Amount RO :  </b><span> {{isset($termination->termination_electricity_amount)? numberFormat($termination->termination_electricity_amount)." OMR":"NA"}}</span></h5>                   
                </div>
            </div>
            <div class="col-lg-4 p-t-20"> 
              <div class = "txt-full-width">
                  <h5 class="details"><b>Water Acc/No :  </b><span>{{$termination->termination_water_acc_no}}</span></h5>
                 
              </div>
          </div>
          <div class="col-lg-4 p-t-20"> 
              <div class = "txt-full-width">
                  <h5 class="details"><b>Closing Reading  :  </b><span>{{$termination->termination_water_close_reading}}</span></h5>
              </div>
          </div>
          <div class="col-lg-4 p-t-20"> 
              <div class = "txt-full-width">
                  <h5 class="details"><b>Amount RO  :  </b><span> {{isset($termination->termination_water_amount)? numberFormat($termination->termination_water_amount)." OMR":"NA"}}</span></h5>

              </div>
          </div>
        </div>
    </div>
  </div>

  @if(count($groupedWork)> 0)
  <div class="card card-box salesSearchBox " id="agdiv">

  @foreach($groupedWork as $checklist)
    
    <div class="sub-head">{{$checklist->first()->work->works_code}}</div>
    <div class="dataSearchBox">
        <div class="card-body row">
         @foreach($checklist as $subWork)
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b> {{$subWork->subWorks->sub_work}}:  </b><span>{{isset($subWork->termination_amount)? numberFormat($subWork->termination_amount)." OMR":"NA"}}</span></h5>
                   
                </div>
            </div>
           @endforeach
        </div>
    </div>
    
  @endforeach
    
  </div>
  @endif
  @if(count($tenantContract->terminationChecklistOther)> 0)
  <div class="card card-box salesSearchBox " id="agdiv">
   <div class="sub-head">Others</div>
    <div class="dataSearchBox">
        <div class="card-body row">
          @foreach($tenantContract->terminationChecklistOther as $other)
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>{{$other->termination_other_work}} :  </b><span>{{isset($other->termination_amount)? numberFormat($other->termination_amount)." OMR":"NA"}}</span></h5>
                   
                </div>
            </div>
          @endforeach
        </div>
    </div>

  </div>
  @endif
  @if(count($tenantContract->terminationChecklistOther)> 0 || count($groupedWork)> 0)
  <div class="card card-box salesSearchBox " id="agdiv">
    <div class="dataSearchBox">
        <div class="card-body row">
           
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Total Amount :  </b><span>{{isset($termination->termination_total_amount)? numberFormat($termination->termination_total_amount)." OMR":"NA"}}</span></h5>
                    
                </div>
            </div>
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Total of Electricity and Water  :  </b><span>{{isset($termination->termination_total_elec_water_amount)? numberFormat($termination->termination_total_elec_water_amount)." OMR":"NA"}}</span></h5>
                </div>
            </div>
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Discount on Total Maintenance Due:  </b><span>{{isset($termination->termination_discount_maintenance_due)? numberFormat($termination->termination_discount_maintenance_due)." OMR":"NA"}}</span>
                    </h5>
                </div>
            </div>
            <div class="col-lg-6 p-t-20"> 
              <div class = "txt-full-width">
                  <h5 class="details"><b>Net Amount:  </b><span>{{isset($termination->termination_net_amount)? numberFormat($termination->termination_net_amount)." OMR":"NA"}}</span></h5>                 
              </div>
          </div>
        </div>
    </div>
  </div>
@endif
<!-- ends-->

</div>

<!-- Document Show Starts -->
<div class="col-sm-12">
  <div class="card card-box salesLeadBox">
    <div class="card-head">
      <div class="col"><h4>Open For Termination Document List</h4></div>
  </div>
  <div class="card-body">
   <div class="col">
      <div class="row">

        <div class="col leadInformation">

          <table class="table" >
            <thead>
              <tr style="background: #f5f5f5;">
                <th>Sl No</th>
                <th>Document</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($openTerminationDocument as $key=>$document)
            <tr>
                <td>{{ ++$key }}</td>
                <td><a href="{{asset('storage/app/'.$document->termination_doc)}}" target="_blank">{{$document->termination_doc_name}}</a></td>

            </tr>                     
            @empty
            <tr>
                <td colspan="3" >
                  <p>No Record</p>
              </td>
          </tr>
          @endforelse
      </tbody>
  </table>

</div>
</div>
</div>

</div>
</div>
</div>
<!-- Document Show Ends -->

<div class="col-sm-12">
    <div class="card card-box salesLeadBox">
      <div class="card-head">
        <div class="col"><h4>Termination Notes</h4></div>
      </div>
      <div class="card-body">
       <div class="col">
        <div class="row">

          <div class="col leadInformation">
           <div class="table-responsive1">
            <table class="table" >
              <thead>
                <tr style="background: #f5f5f5;">
                  <th>Stage</th>
                  <th>Note</th>
                  <th>Created By</th>
                  <th>Created At</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($terminationNotes as $notes)
              
                  <tr>
                    <td>{{$notes->WorkFlowProcessesCode->work_flow_processes_name}}</td>
                    <td>{{$notes->termination_notes}}</td>
                    <td>{{$notes->createdBy->employee->employee_name??'Admin'}}</td>
                    <td>{{$notes->created_at->format('d/m/Y')}}</td>
                  </tr>                     
                  @empty
                  <tr>
                      <td colspan="3" >
                      <p>No Record</p>
                     </td>
                  </tr>
                  @endforelse
               
              </tbody>
            </table>

          </div>
        </div>
      </div>
    </div>

  </div>
</div>
</div>
<div class="col-sm-12">
    <div class="card card-box salesLeadBox">
      <div class="card-head">
        <div class="col"><h4>Inspection Image</h4></div>
      </div>
      <div class="card-body">
       <div class="col">
        <div class="row">

          <div class="col leadInformation">
           <div class="table-responsive1">
            <table class="table" >
              <thead>
                <tr style="background: #f5f5f5;">
                  <th>Category</th>
                  <th>Document</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($terminationDocument as $document)
              
                  <tr>
                    <td>{{$document->termination_doc_type}}</td>
                    <td><a href="{{asset('storage/app/'.$document->termination_doc)}}" target="_blank">
							{{$document->termination_doc_name}} 
						</a>
					</td>
                  </tr>                     
                  @empty
                  <tr>
                      <td colspan="3" >
                      <p>No Record</p>
                     </td>
                  </tr>
                  @endforelse
               
              </tbody>
            </table>

          </div>
        </div>
      </div>
    </div>

  </div>
</div>
</div>
</div>
<div class="modal" id="myModal"></div>
<div class="modal" id="myModal_terminate"></div>
@endsection
@section('scripts')
<script>
 $(document).ready(function() {
    $("#myModal").on("hidden.bs.modal", function(){
            $("#myModal").html("");
            $(this).removeData('bs.modal');
    });
    $("#myModal_terminate").on("hidden.bs.modal", function(){
            $("#myModal_terminate").html("");
            $(this).removeData('bs.modal');
    });
	
  });
 /****************************************************************************/
  $(".editTermination").on('click',function(e){
    var valText = $(".closeTermination").text();
    var datesplit = valText.split('/');
    var terminateDt = datesplit[2] + "-" + datesplit[1] + "-" + datesplit[0];

    var valText = $(".closeTaken").text();
    var datesplit = valText.split('/');

    var takeOverDt = datesplit[2] + "-" + datesplit[1] + "-" + datesplit[0];

    var dtToday = new Date();

    var month = dtToday.getMonth() + 1;
    var day = dtToday.getDate();
    var year = dtToday.getFullYear();
    if(month < 10)
      month = '0' + month.toString();
    if(day < 10)
      day = '0' + day.toString();

    var maxDate = year + '-' + month + '-' + day;

    $("#termination_date").val(terminateDt).show();
    if(takeOverDt)

      $('#termination_date').attr('min', maxDate);
    else
      $('#termination_date').attr('min', maxDate);

    $(".openTermination").show();
    $(".closeTerminationbtn").show();
    $(".editTermination").hide();
    $(".closeTermination").hide();
    $(".saveTerminationbtn").show();

  });
  /****************************************************************************/

  $(".closeTerminationbtn").on('click',function(e){

    $(".openTermination").hide();
    $(".editTermination").show();
    $(".closeTermination").show();
    $(".saveTerminationbtn").hide();
    $(".closeTerminationbtn").hide();  


  });
  /****************************************************************************/

     $(".saveTerminationbtn").on('click',function(e){
      var termination_id              = $("#termination_id").val();
      var termination_takenover_date  = $("#termination_takenover_date").val();
      var termination_takenover_date_txt  = $(".closeTaken").text().split('/');
      var termination_date_txt            = $(".closeTermination").text().split('/');
      var termination_date            = $("#termination_date").val();
      var clsName                     = $(this).attr('class');
      var dtToday                     = new Date();

      if(termination_takenover_date_txt && clsName=='saveTerminationbtn'){
        termination_takenover_dt = termination_takenover_date_txt[2]+'-'+   termination_takenover_date_txt[1]+'-'+termination_takenover_date_txt[0];
        termination_takenover_date = termination_takenover_dt;
        termination_date_dt = termination_date_txt[2]+'-'+   termination_date_txt[1]+'-'+termination_date_txt[0];
      }


      if(termination_id && (termination_id || termination_date )){

        $.ajax
        ({
          type: "POST",
          url: "{{route('terminationUpdateExtraFields')}}",
          data: {"termination_id":termination_id,"termination_date":termination_date,"_token": "{{ csrf_token() }}"},
          cache: false,
          success: function(data)
          {
          if(termination_date && clsName=='saveTerminationbtn'){

            var d = new Date(termination_date);  
            var day = d.getDate();
            var month_index = d.getMonth()+1;
            var year = d.getFullYear();  
            if(month_index < 10) month_index = '0'+month_index;
            $(".closeTermination").text( day + "/" + month_index + "/" + year).show();
            $(".openTermination").hide();
            $(".editTermination").show();
            $(".saveTerminationbtn").hide();
            $(".closeTerminationbtn").hide();  

          }
        }
      });
      }
      else{

        alert("Termination Value Is Incorrect");
        return false;
      }
    });

/****************************************************************************/
 $(document).on('click','.resubmit, .terminate', function(e) {        

            var action_key    = $(this).attr('data-id');
            var terminatedId  = $(this).attr('datas-id');
            var workflow_id   = $(this).attr('datas-enid');
            var contractId    = $(this).attr('id');
          
            if(action_key == 'TMT')
               var elementId =  'myModal_terminate';
            else
               var elementId =  'myModal';

            $.ajax({
                method: 'POST', // Type of response and matches what we said in the route
                url: "{{route('terminateResubmitOrTerminateModal')}}", // This is the url we gave in the route
                data: {'terminatedId' : terminatedId,'action_key' : action_key,'contractId' : contractId,'workflow_id' : workflow_id,'action_key':action_key,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
                success: function(response){ // What to do if we succeed
                    $("#"+elementId).html(response); 
                },
            }); 
            
            return true;
  });      
</script>
@endsection
