@extends('layouts.plms-app')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">


@section('content')
<!-- start widget -->
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">View Landlord Renewal Due</div>
    </div>
    {{ Breadcrumbs::render('landlordRenewal.show') }}
  </div>
</div>
<div class="row">
  <div class="col">
   <div class="card card-box salesSearchBox">

    <h4>


      <input type="hidden" name="landlord_contract_id" id="landlord_contract_id" value="{{$renewalDue->id}}">
      @can('landlord_renewal_due_accept')
      <button type="button" class="btn btn-circle btn-primary align-right accept" data-id="1" datas-id="ACPT" data-flow-id="401" >Renewing</button>
      @endcan
      @can('landlord_renewal_due_terminate')
      <button type="button" class="btn btn-circle btn-danger align-right terminate" data-id="7" datas-id="TMT" data-flow-id="601">Vacating</button>
      @endcan
    </h4>


    <div class="sub-head">Agreement Details</div>
    <div class="dataSearchBox">
      <div class="card-body row">

        <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
            <h5 class="details"><b>Agreement No :  </b><span>{{$renewalDue->landlord_contract_no}}</span></h5>
          </div>
        </div>
        <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
            <h5 class="details"><b>Old Agreement No :  </b><span>{{$renewalDue->landlord_contract_old_no ?? ''}}</span></h5>
          </div>
        </div>
        <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
            <h5 class="details"><b>Agreement Date  :  </b><span>{{$renewalDue->created_at->format('d/m/Y')}}</span></h5>
          </div>
        </div>
      </div>
    </div>
    <div class="sub-head">Landlord Details</div>
    <div class="dataSearchBox">    
      <div class="card-body row">
        <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
            <h5 class="details"><b>Landlord Name :  </b><span>{{$renewalDue->vendorInfo->vendor_name}}</span></h5>
          </div>
        </div>
        <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
            <h5 class="details"><b>Landlord Code :  </b><span>{{$renewalDue->vendorInfo->vendor_code}}</span></h5>
          </div>
        </div>

        <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
           <h5 class="details"><b>Building Name :  </b><span>{{$renewalDue->buildingInfo->building_name}}</span></h5>
         </div>
       </div> 

       <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
         <h5 class="details"><b>Building Code :  </b><span>{{$renewalDue->buildingInfo->building_code}}</span></h5>
       </div>
     </div> 

   </div>
 </div>

 <div class="sub-head">Payment Information</div>   
 <div class="dataSearchBox">  
  <div class="card-body row">
   <div class="col-lg-6 p-t-20"> 
    <div class = "txt-full-width">
     <h5 class="details"><b>Duration Type  :  </b><span>@if($renewalDue->landlord_contract_duration_type==1)Month
      @elseif($renewalDue->landlord_contract_duration_type==2)Year
      @else
      Day
      @endif</span></h5>
    </div>
  </div> 
  <div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Management Type  :  </b><span>{{$renewalDue->managementTypeInfo->management_types_name ?? ''}}</span></h5>
  </div>
</div> 
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Management Fee  :  </b><span>{{$renewalDue->managementTypeInfo->management_types_name ?? ''}}</span></h5>
  </div>
</div> 
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Payment Type  :  </b><span>{{$renewalDue->paymentMethodInfo->payment_method_code ?? ''}}</span></h5>
  </div>
</div>
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Contract Amount :  </b><span>{{ numberFormat($renewalDue->landlord_contract_amt) ?? '' }} OMR</span></h5>
  </div>
</div> 
</div>

</div>
<div class="sub-head">Contract Details</div>   
<div class="dataSearchBox">  
  <div class="card-body row">
  <div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Valid From :  </b><span>{{$renewalDue->landlord_contract_valid_from_date->format('d/m/Y') ?? ''}}</span></h5>
  </div>
</div> 
 <div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Valid To :  </b><span>{{$renewalDue->landlord_contract_valid_to_date->format('d/m/Y') ?? ''}}</span></h5>
  </div>
</div> 
 <div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Free Lease Period :  </b><span>{{$renewalDue->landlord_free_lease_period ?? ''}}</span></h5>
  </div>
</div> 
 <div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Close Activity :  </b><span>{{$renewalDue->close_activity ?? ''}}</span></h5>
  </div>
</div>  
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Marketing Executive :  </b><span>{{$renewalDue->landlord_marketing_executive ?? ''}}</span></h5>
  </div>
</div> 
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Remarks :  </b><span>{{$renewalDue->landlord_contract_note ?? ''}}</span></h5>
  </div>
</div> 

  </div>
</div>

</div></div>
</div> 
@endsection
@section('scripts')
<script>
  $(document).ready(function() {

    $('.accept').on('click', function(e) {        

      var action_key =  $(this).attr('datas-id');
      var landlord_contract_id = $("#landlord_contract_id").val();
      var status = $(this).attr('data-id');
      var process_flow = $(this).attr('data-flow-id');
      /*   if (confirm('Do you want to Approval Accept?')) {*/
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
        /*}else {
            return false;
          }*/        

        });
    $('.terminate').on('click', function(e) {        

      var action_key =  $(this).attr('datas-id');
      var landlord_contract_id = $("#landlord_contract_id").val();
      var status = $(this).attr('data-id');
      var process_flow = $(this).attr('data-flow-id');
      /*if (confirm('Do you want to Terminate?')) {*/
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
        /*}else {
            return false;
          }*/        

        });
  });
</script>
@endsection