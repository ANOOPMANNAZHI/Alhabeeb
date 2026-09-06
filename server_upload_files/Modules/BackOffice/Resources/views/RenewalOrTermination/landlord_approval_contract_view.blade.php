@extends('layouts.plms-app')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">


@section('content')
<!-- start widget -->
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">Landlord Contract Approval View</div>
    </div>
    {{ Breadcrumbs::render('landlordApprovalShow',$landlordApprove) }} 
  </div>
</div>

<div class="row">
  <div class="col">
   <div class="card card-box salesSearchBox">

    <h4>
          <input type="hidden" name="landlord_contract_id" id="landlord_contract_id" value="{{$landlordApprove->newLandlordContract->id}}">
          <input type="hidden" name="landlord_old_contract_id" id="landlord_old_contract_id" value="{{$landlordApprove->oldLandlordContract->id}}">
          @can('landlord_renewal_contract_reject')
          <button type="button" class="btn btn-circle btn-danger align-right terminate" data-id="4" datas-id="RJCT" data-flow-id="402" data-toggle="modal" data-target="#myModal" data-backdrop="static" data-keyboard="false">Reject</button>
          @endcan

          @can('landlord_renewal_contract_approve')
          <button type="button" class="btn btn-circle btn-primary align-right accept" data-id="5" datas-id="ACPT" data-flow-id="402" data-toggle="modal" data-target="#myModal" data-backdrop="static" data-keyboard="false">Approve</button>
          @endcan
    </h4>


    <div class="sub-head">Agreement Details</div>
    <div class="dataSearchBox">
      <div class="card-body row">

        <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
            <h5 class="details"><b>Agreement No :  </b><span>{{$landlordApprove->newLandlordContract->landlord_contract_no ?? ''}}</span></h5>
          </div>
        </div>
        <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
            <h5 class="details"><b>Old Agreement No :  </b><span>{{$landlordApprove->newLandlordContract->landlord_contract_old_no ?? ''}}</span></h5>
          </div>
        </div>
        <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
            <h5 class="details"><b>Agreement Date  :  </b><span>{{$landlordApprove->newLandlordContract->created_at->format('d/m/Y') ?? ''}}</span></h5>
          </div>
        </div>
      </div>
    </div>
    <div class="sub-head">Landlord Details</div>
    <div class="dataSearchBox">    
      <div class="card-body row">
        <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
            <h5 class="details"><b>Landlord Name :  </b><span>{{$landlordApprove->newLandlordContract->landlord_contract_name ?? ''}}</span></h5>
          </div>
        </div>
        <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
            <h5 class="details"><b>Landlord Code :  </b><span>{{$landlordApprove->newLandlordContract->vendor_code ?? ''}}</span></h5>
          </div>
        </div>

        <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
           <h5 class="details"><b>Building Name :  </b><span>{{$landlordApprove->newLandlordContract->buildingInfo->building_name ?? ''}}</span></h5>
         </div>
       </div> 

       <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
         <h5 class="details"><b>Building Code :  </b><span>{{$landlordApprove->newLandlordContract->buildingInfo->building_code ?? ''}}</span></h5>
       </div>
     </div> 

   </div>
 </div>

 <div class="sub-head">Payment Information</div>   
 <div class="dataSearchBox">  
  <div class="card-body row">
   <div class="col-lg-6 p-t-20"> 
    <div class = "txt-full-width">
     <h5 class="details"><b>Duration Type  :  </b><span>@if($landlordApprove->newLandlordContract->landlord_contract_duration_type==1)Month
            @elseif($landlordApprove->newLandlordContract->landlord_contract_duration_type==2)Year
            @else
            Day
            @endif</span></h5>
    </div>
  </div> 
  <div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Start Date  :  </b><span>@if($landlordApprove->newLandlordContract->start_date){{$landlordApprove->newLandlordContract->start_date->format('d/m/Y') ?? ''}}@endif</span></h5>
  </div>
</div> 
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Management Fee  :  </b><span>{{$landlordApprove->newLandlordContract->landlord_contract_management_fee ?? ''}}</span></h5>
   @if($landlordApprove->newLandlordContract->landlord_contract_cleaning_charge)
   <h5 class="details"><b>Cleaning Charges  :  </b><span>{{ ($landlordApprove->newLandlordContract->cleaning_charge_method ?? null) == 1 ? ($landlordApprove->newLandlordContract->landlord_contract_cleaning_charge ?? '').' %' : numberFormat($landlordApprove->newLandlordContract->landlord_contract_cleaning_charge ?? 0).' OMR' }}</span></h5>
   @endif
  </div>
</div> 
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Payment Type  :  </b><span>{{$landlordApprove->newLandlordContract->paymentMethodInfo->payment_method_code ?? ''}}</span></h5>
  </div>
</div> 
</div>

</div>
<div class="sub-head">Contract Details</div>   
<div class="dataSearchBox">  
  <div class="card-body row">
  <div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Valid From :  </b><span>{{$landlordApprove->newLandlordContract->landlord_contract_valid_from_date->format('d/m/Y') ?? ''}}</span></h5>
  </div>
</div> 
 <div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Valid To :  </b><span>{{$landlordApprove->newLandlordContract->landlord_contract_valid_to_date->format('d/m/Y') ?? ''}}</span></h5>
  </div>
</div> 
 <div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Free Lease Period :  </b><span>{{$landlordApprove->newLandlordContract->landlord_free_lease_period ?? ''}}</span></h5>
  </div>
</div> 
 <div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Close Activity :  </b><span>{{$landlordApprove->newLandlordContract->close_activity ?? ''}}</span></h5>
  </div>
</div>  
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Marketing Executive :  </b><span>{{$landlordApprove->newLandlordContract->landlord_marketing_executive ?? ''}}</span></h5>
  </div>
</div> 
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Remarks :  </b><span>{{$landlordApprove->newLandlordContract->landlord_contract_note ?? ''}}</span></h5>
  </div>
</div> 

  </div>
</div>

</div></div>
</div> 
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
              <input type="hidden" name="renewal_id" value="{{$landlordApprove->id}}">
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
               <tr @if (in_array('backoffice_executive', $rolesNames) === true) 
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

<div class="modal" id="myModal">

</div>
@endsection
@section('scripts')
<script>
  $(document).ready(function() {

    $('.accept').on('click', function(e) {        

      var action_key =  $(this).attr('datas-id');
      var landlord_contract_id = $("#landlord_contract_id").val();
      var landlord_old_contract_id = $("#landlord_old_contract_id").val();
      var status = $(this).attr('data-id');
      var process_flow = $(this).attr('data-flow-id');
      /*if (confirm('Do you want to Approval Accept?')) {*/
        $.ajax({
            method: 'POST', // Type of response and matches what we said in the route
            url: "{{route('landlordRenewalApproveReject')}}", // This is the url we gave in the route
            data: {'process_flow':process_flow,'action_key' : action_key,'landlord_contract_id' : landlord_contract_id,'landlord_old_contract_id' : landlord_old_contract_id,'status' : status,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
            success: function(response){ // What to do if we succeed
              $("#myModal").html(response);
                //alert(response);
               //window.location.href = response;
             },
           });
        return true;
        /*}else {
            return false;
          } */       
          
        });
    $('.terminate').on('click', function(e) {        

      var action_key =  $(this).attr('datas-id');
      var landlord_contract_id = $("#landlord_contract_id").val();
      var landlord_old_contract_id = $("#landlord_old_contract_id").val();
      var status = $(this).attr('data-id');
      var process_flow = $(this).attr('data-flow-id');
      /*  if (confirm('Do you want to Reject?')) {*/
        $.ajax({
            method: 'POST', // Type of response and matches what we said in the route
            url: "{{route('landlordRenewalApproveReject')}}", // This is the url we gave in the route
            data: {'process_flow':process_flow,'action_key' : action_key,'landlord_contract_id' : landlord_contract_id,'landlord_old_contract_id' : landlord_old_contract_id,'status' : status,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
            success: function(response){ // What to do if we succeed
              $("#myModal").html(response);
               //window.location.href = response;
             },
           });
        return true;
        /*}else {
            return false;
          } */       
          
        });
  });
</script>
@endsection