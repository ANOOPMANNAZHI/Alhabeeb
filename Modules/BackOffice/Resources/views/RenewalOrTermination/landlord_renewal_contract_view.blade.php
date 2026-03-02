@extends('layouts.plms-app')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">


@section('content')
<!-- start widget -->
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">Landlord Contract Renewal View</div>
    </div>
    {{ Breadcrumbs::render('landlordRenewalShow',$landlordContract) }} 
  </div>
</div>
<div class="row">
  <div class="col">
   <div class="card card-box salesSearchBox">

    <h4>
<input type="hidden" name="landlord_contract_id" id="landlord_contract_id" value="">
          @if($landlordContract->newLandlordContract->landlord_renewal_termination_status==2 || $landlordContract->newLandlordContract->landlord_renewal_termination_status == 4) 
          @can('landlord_renewal_send_approval')
          <a title="Edit" href="{{route('landlordSentForApproval',[$landlordContract->oldLandlordContract->id,$landlordContract->newLandlordContract->id])}}" class="btn btn-circle btn-primary  align-right" title="Edit">Sent For Approval 
          </a>
          @endcan
          @endif 
          @if($landlordContract->newLandlordContract->landlord_renewal_termination_status==2 || $landlordContract->newLandlordContract->landlord_renewal_termination_status == 4) 
          @can('landlord_renewal_contract_edit') 
          <a title="Edit" href="{{route('landlordRenewalNewContractEdit',$landlordContract->newLandlordContract->id)}}" class="btn btn-circle btn-primary  align-right" title="Edit">Edit 
          </a> 
          @endcan
          @endif
    </h4>


    <div class="sub-head">Agreement Details</div>
    <div class="dataSearchBox">
      <div class="card-body row">

        <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
            <h5 class="details"><b>Agreement No :  </b><span>{{$landlordContract->newLandlordContract->landlord_contract_no ?? ''}}</span></h5>
          </div>
        </div>
        <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
            <h5 class="details"><b>Old Agreement No :  </b><span>{{$landlordContract->oldLandlordContract->landlord_contract_no ?? ''}}</span></h5>
          </div>
        </div>
        <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
            <h5 class="details"><b>Agreement Date  :  </b><span>{{$landlordContract->newLandlordContract->created_at->format('d/m/Y') ?? ''}}</span></h5>
          </div>
        </div>
      </div>
    </div>
    <div class="sub-head">Landlord Details</div>
    <div class="dataSearchBox">    
      <div class="card-body row">
        <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
            <h5 class="details"><b>Landlord Name :  </b><span>{{$landlordContract->newLandlordContract->vendorInfo->vendor_name ?? ''}}</span></h5>
          </div>
        </div>
        <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
            <h5 class="details"><b>Landlord Code :  </b><span>{{$landlordContract->newLandlordContract->vendorInfo->vendor_code ?? ''}}</span></h5>
          </div>
        </div>

        <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
           <h5 class="details"><b>Building Name :  </b><span>{{$landlordContract->newLandlordContract->buildingInfo->building_name ?? ''}}</span></h5>
         </div>
       </div> 

       <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
         <h5 class="details"><b>Building Code :  </b><span>{{$landlordContract->newLandlordContract->buildingInfo->building_code ?? ''}}</span></h5>
       </div>
     </div> 

   </div>
 </div>

 <div class="sub-head">Payment Information</div>   
 <div class="dataSearchBox">  
  <div class="card-body row">
   <div class="col-lg-6 p-t-20"> 
    <div class = "txt-full-width">
     <h5 class="details"><b>Duration Type  :  </b><span>@if($landlordContract->newLandlordContract->landlord_contract_duration_type==1)Month
            @elseif($landlordContract->newLandlordContract->landlord_contract_duration_type==2)Year
            @else
            Day
            @endif</span></h5>
    </div>
  </div> 
  <div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Management Type  :  </b><span>{{$landlordContract->newLandlordContract->managementTypeInfo->management_types_name ?? ''}}</span></h5>
  </div>
</div> 
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Management Fee  :  </b><span>{{$landlordContract->newLandlordContract->landlord_contract_management_fee ?? ''}}</span></h5>
  </div>
</div> 
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Payment Type  :  </b><span>{{$landlordContract->newLandlordContract->paymentMethodInfo->payment_method_code ?? ''}}</span></h5>
  </div>
</div>
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Contract Amount :  </b><span>{{ numberFormat($landlordContract->newLandlordContract->landlord_contract_amt) ?? '' }} OMR</span></h5>
  </div>
</div> 
</div>

</div>
<div class="sub-head">Contract Details</div>   
<div class="dataSearchBox">  
  <div class="card-body row">
  <div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Valid From :  </b><span>{{$landlordContract->newLandlordContract->landlord_contract_valid_from_date->format('d/m/Y') ?? ''}}</span></h5>
  </div>
</div> 

 <div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Free Lease Period :  </b><span>{{$landlordContract->newLandlordContract->landlord_free_lease_period ?? ''}}</span></h5>
  </div>
</div> 
 <div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Close Activity :  </b><span>{{$landlordContract->newLandlordContract->close_activity ?? ''}}</span></h5>
  </div>
</div>  
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Marketing Executive :  </b><span>{{$landlordContract->newLandlordContract->marketExecutiveEmployeeInfo->employee_name.'('.$landlordContract->newLandlordContract->marketExecutiveEmployeeInfo->employee_code.')' ?? ''}}</span></h5>
  </div>
</div> 
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Remarks :  </b><span>{{$landlordContract->newLandlordContract->landlord_contract_note ?? ''}}</span></h5>
  </div>
</div> 

  </div>
</div>

</div></div>
</div> 
<!-- -->
<!-- starts-->
<div class="row">
  <div class="col-sm-12">
    <div class="card card-box salesLeadBox">
      <div class="card-head">
        <div class="col"><h4>Note</h4></div>
      </div>
      <div class="card-body">
        <div class="add-note-section">
          <form action="{{route('landlordRenewalNoteStore')}}" autocomplete="off" method="POST" id="renewal_notes" class="form-horizontal" enctype="multipart/form-data">
           {{csrf_field()}}
           <div class="col-sm-12">
            <div class="form-group">
              <label for="simpleFormEmail">Renewal Note</label>
              <textarea class="form-control" rows="2" required name="renewal_note" placeholder="Enter Note" maxlength="200"></textarea>
              <input type="hidden" name="renewal_id" value="{{$landlordContract->id}}">
              <input type="hidden" name="current_url" value="{{url()->current()}}">
            </div>
          </div>
          <div class="col-sm-2"><button type="submit"  name="submit" class="btn btn-primary">Save</button></div>
        </form>
      </div>
      <div class="col-md-12">
        <div class="col p-0">
          <h4><strong></strong><div class="clr"></div></h4>

          <div class="table-responsive1">
            <table class="table" id="note_datatable">
              <thead>
                <tr style="background: #f5f5f5;">
                  <th>Progress</th>
                  <th>Date & Time</th>
                  <th>Created By</th>
                </tr>
              </thead>
              <tbody>
               @forelse ($renewalNotes as $renewalNote)
               <tr  @if (in_array('backoffice_executive', $rolesNames) === true) 
               bgcolor ="ADD8E6";
               @elseif(in_array('are', $rolesNames) ===true)
               bgcolor = 'FFC0CB';
               @elseif(in_array('super_admin', $rolesNames) ===true)
               bgcolor = '#ffcb80';
               @else
               bgcolor = 'FFF8DC';
               @endif>
               <td>{{$renewalNote->renewal_notes_note}} </td>
               <td class="d-t">{{$renewalNote->created_at->format('d/m/Y h:m A')}}</td>
               <td>{{$renewalNote->createdBy->username}} </td>

             </tr>                        
             @empty
             <tr>
              <td colspan="3" align="center">
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
<!-- ends-->
<div class="card card-box salesLeadBox">
  <div class="card-head">
    <div class="col"><h4>Renewal Stage Note</h4></div>
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

            @forelse ($notes as $note)

            @if($note->renewal_notes!="")
            <tr>
              <td>{{$note->workFlowProcess->work_flow_processes_name}} </td>
              <td>{{$note->renewal_notes}}</td>
              <td>{{$note->createdBy->username}} </td>
              <td>{{$note->created_at->format('d/m/Y h:i:s')}} </td>
            </tr> 
            @endif                       
            @empty
            <tr>
              <td colspan="4" align="center">
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
<!-- ends-->
<!-- -->




@endsection
@section('scripts')
<script>
  $(document).ready(function() {

    $('.accept').on('click', function(e) {        

      var action_key =  $(this).attr('datas-id');
      var landlord_contract_id = $("#landlord_contract_id").val();
      var status = $(this).attr('data-id');
      var process_flow = $(this).attr('data-flow-id');
      if (confirm('Do you want to Approval Accept?')) {
        $.ajax({
            method: 'POST', // Type of response and matches what we said in the route
            url: "{{route('landlordRenewalStage')}}", // This is the url we gave in the route
            data: {'process_flow':process_flow,'action_key' : action_key,'landlord_contract_id' : landlord_contract_id,'status' : status,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
            success: function(response){ // What to do if we succeed
                //$("#myModal").html(response);
                //alert(response);
                window.location.href = response;
              },
            });
        return true;
      }else {
        return false;
      }        

    });
    $('.terminate').on('click', function(e) {        

      var action_key =  $(this).attr('datas-id');
      var landlord_contract_id = $("#landlord_contract_id").val();
      var status = $(this).attr('data-id');
      var process_flow = $(this).attr('data-flow-id');
      if (confirm('Do you want to Terminate?')) {
        $.ajax({
            method: 'POST', // Type of response and matches what we said in the route
            url: "{{route('landlordRenewalStage')}}", // This is the url we gave in the route
            data: {'process_flow':process_flow,'action_key' : action_key,'landlord_contract_id' : landlord_contract_id,'status' : status,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
            success: function(response){ // What to do if we succeed
                //$("#myModal").html(response);
                window.location.href = response;
              },
            });
        return true;
      }else {
        return false;
      }        

    });
  });
</script>
@endsection