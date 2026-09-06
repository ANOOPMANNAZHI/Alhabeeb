@extends('layouts.plms-app')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">


@section('content')

    <!-- start widget -->
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Landlord Contract View</div>
        </div>
       
            {{ Breadcrumbs::render('landlord-contract-show',$landlordContractInfo) }}
         
    </div>
</div>

<div class="row">
	<div class="col">
		 <div class="card card-box salesSearchBox">

			 <div class="sub-head">Agreement Details 

			 	 @if($landlordContractInfo->landlord_contract_duration == 1)
			 	   <a href="#close" class="btn btn-danger pull-right"  data-toggle="modal" data-target="#myModal" title="Close Contract" data-backdrop="static" data-keyboard="false">Close Contract</a>
			 	 @endif

			 </div>
			 <div class="dataSearchBox">
				<div class="card-body row">
                        <input type="hidden" id="contractId" value="{{$landlordContractInfo->id}}">
                        <div class="col-lg-6 p-t-20"> 
                            <div class = "txt-full-width">
                                <h5 class="details"><b>Agreement No :  </b><span>{{$landlordContractInfo->landlord_contract_no}}</span></h5>
                            </div>
                        </div>
                        <div class="col-lg-6 p-t-20"> 
                            <div class = "txt-full-width">
                                <h5 class="details"><b>Agreement Date  :  </b><span>{{$landlordContractInfo->created_at->format('d/m/Y')}}</span></h5>
                            </div>
                        </div>
                </div>
			 </div>
			<div class="sub-head">Landlord Details</div>
				<div class="dataSearchBox">    
					<div class="card-body row">
							<div class="col-lg-6 p-t-20"> 
								<div class = "txt-full-width">
									<h5 class="details"><b>Landlord Name :  </b>
										<span>{{$landlordContractInfo->vendorInfo->vendor_name}}</span>
									</h5>
								</div>
							</div>
							<div class="col-lg-6 p-t-20"> 
								<div class = "txt-full-width">
									<h5 class="details"><b>Landlord Code :  </b><span>{{$landlordContractInfo->vendorInfo->vendor_code}}</span></h5>
								</div>
							</div>
						 
						  <div class="col-lg-6 p-t-20"> 
							<div class = "txt-full-width">
							   <h5 class="details"><b>Building Name :  </b><span>{{$landlordContractInfo->buildingInfo->building_name}}</span></h5>
							</div>
						  </div> 
						
						  <div class="col-lg-6 p-t-20"> 
							<div class = "txt-full-width">
							   <h5 class="details"><b>Building Code :  </b><span>{{$landlordContractInfo->buildingInfo->building_code}}</span></h5>
							</div>
						  </div> 
						
						</div>
				</div>
				
				<div class="sub-head">Payment Information</div>   
				<div class="dataSearchBox">  
					<div class="card-body row">
					@if(isset($landlordContractInfo->paymentMethodInfo))
						<div class="col-lg-6 p-t-20"> 
							<div class = "txt-full-width">
							   <h5 class="details"><b>Payment Term :  </b><span>{{$landlordContractInfo->paymentMethodInfo->payment_method_code}}</span></h5>
							</div>
						</div>
					@endif
					@if(!empty($landlordContractInfo->landlord_contract_amt))
					<div class="col-lg-6 p-t-20"> 
						<div class = "txt-full-width">
						   <h5 class="details"><b>Contract Amount :  </b><span>{{numberFormat($landlordContractInfo->landlord_contract_amt)}} OMR</span></h5>
						</div>
					</div>
					@endif
					@if($landlordContractInfo->landlord_contract_duration)
					<div class="col-lg-6 p-t-20"> 
						<div class = "txt-full-width">
						   <h5 class="details"><b>Duration Type :  </b>
						   <span>
						   @if($landlordContractInfo->landlord_contract_duration == 1)
								Open
							@else
								Close
							@endif
						   
						   </span></h5>
						</div>
					</div>
					@endif
					@if($landlordContractInfo->management_id)
					<div class="col-lg-6 p-t-20"> 
						<div class = "txt-full-width">
						   <h5 class="details"><b>Management Type :  </b><span>{{$landlordContractInfo->managementTypeInfo->management_types_name}}</span></h5>
						</div>
					</div>
					@endif
					 @if(isset($landlordContractInfo->management_fee_type))
					<div class="col-lg-6 p-t-20"> 
						<div class = "txt-full-width">
						   <h5 class="details"><b>Management Fees Type :  </b>
						   <span>
							@if($landlordContractInfo->management_fee_type==1)
							  Management Fees
							@elseif($landlordContractInfo->management_method==2)
								Maintenance
							@elseif($landlordContractInfo->management_method==3)
						   Legal
							@elseif($landlordContractInfo->management_method==4)
							 Caretakers Fee
							@endif
						   </span></h5>
						</div>
					</div>
					@endif
						@if(isset($landlordContractInfo->management_fee_type) && ($landlordContractInfo->management_id==3 ||$landlordContractInfo->management_id==2) )
						<div class="col-lg-6 p-t-20"> 
							<div class = "txt-full-width">
							   <h5 class="details"><b>Management Fee :  </b>
							   <span>
							   @if($landlordContractInfo->management_method==1)
						  Percentage
						@elseif($landlordContractInfo->management_method==2)
						  Amount
						@endif
						</span></h5>
						</div>
					</div>
					@endif
				   @if(isset($landlordContractInfo->landlord_contract_management_fee) && ($landlordContractInfo->management_id==3 ||$landlordContractInfo->management_id==2) )
					<div class="col-lg-6 p-t-20"> 
					  <div class = "txt-full-width">
						   <h5 class="details"><b>Management Fee :  </b>
						   <span>						  
						   {{($landlordContractInfo->management_method==1)?  $landlordContractInfo->landlord_contract_management_fee.' %' : numberFormat($landlordContractInfo->landlord_contract_management_fee).'OMR' }}
						   </span></h5>
						</div>
					</div>
					@endif
				   @if(isset($landlordContractInfo->landlord_contract_cleaning_charge) && $landlordContractInfo->landlord_contract_cleaning_charge)
					<div class="col-lg-6 p-t-20">
					  <div class = "txt-full-width">
						   <h5 class="details"><b>{{($landlordContractInfo->cleaning_charge_method==1)?'Cleaning Value':'Cleaning Amount' }} :  </b>
						   <span>{{($landlordContractInfo->cleaning_charge_method==1)?  $landlordContractInfo->landlord_contract_cleaning_charge.' %' : numberFormat($landlordContractInfo->landlord_contract_cleaning_charge).' OMR' }}</span>
						   </h5>
						</div>
					</div>
				   @endif
					@if(isset($landlordContractInfo->landlord_contract_percentage) && $landlordContractInfo->management_method==1 && ($landlordContractInfo->management_id==3 ||$landlordContractInfo->management_id==2) )
					<div class="col-lg-6 p-t-20"> 
						<div class = "txt-full-width">
						   <h5 class="details"><b>Percentage type :  </b>
						   <span>
								@if($landlordContractInfo->landlord_contract_percentage==1)
									Rent Income
								  
								@elseif($landlordContractInfo->landlord_contract_percentage==2)
									Rent Collection
								@endif
						   
						   </span></h5>
						</div>
					</div>
					@endif
				</div>

		</div>
       <div class="sub-head">Contract Duration</div>   
			<div class="dataSearchBox">  
				<div class="card-body row">
			   @if(isset($landlordContractInfo->start_date))
				<div class="col-lg-6 p-t-20"> 
					<div class = "txt-full-width">
					   <h5 class="details"><b>Start Date :  </b><span>{{$landlordContractInfo->start_date->format('d/m/Y')}}</span></h5>
					</div>
				</div>
				@endif
				@if(isset($landlordContractInfo->landlord_free_lease_period))
				<div class="col-lg-6 p-t-20"> 
					<div class = "txt-full-width">
					   <h5 class="details"><b>Free Lease Period :  </b><span>{{$landlordContractInfo->landlord_free_lease_period}}</span></h5>
					</div>
				</div>
				@endif
				@if($landlordContractInfo->landlord_contract_valid_from_date)
				<div class="col-lg-6 p-t-20"> 
					<div class = "txt-full-width">
					   <h5 class="details"><b>Valid From :  </b><span>{{$landlordContractInfo->landlord_contract_valid_from_date->format('d/m/Y')}}</span></h5>
					</div>
				</div>
				@endif
				@if($landlordContractInfo->landlord_contract_valid_to_date)
				<div class="col-lg-6 p-t-20"> 
					<div class = "txt-full-width">
					   <h5 class="details"><b>Valid To :  </b><span>{{$landlordContractInfo->landlord_contract_valid_to_date->format('d/m/Y')}}</span></h5>
					</div>
				</div>
				@endif

				@if($landlordContractInfo->end_date)
				<div class="col-lg-6 p-t-20"> 
					<div class = "txt-full-width">
					   <h5 class="details"><b>End Date :  </b><span>{{$landlordContractInfo->end_date->format('d/m/Y')}}</span></h5>
					</div>
				</div>
				@endif
			 
				@if($landlordContractInfo->landlord_contract_agreement_amt)
				<div class="col-lg-6 p-t-20"> 
					<div class = "txt-full-width">
					   <h5 class="details"><b>Agreement Amount :  </b><span>{{$landlordContractInfo->landlord_contract_agreement_amt}}</span></h5>
					</div>
				</div>
				@endif
				 @if($landlordContractInfo->close_activity)
				<div class="col-lg-6 p-t-20"> 
					<div class = "txt-full-width">
					   <h5 class="details"><b>Close Activity:  </b><span>{{$landlordContractInfo->close_activity}}</span></h5>
					</div>
				</div>
				@endif
				@if($landlordContractInfo->landlord_contract_address)
				<div class="col-lg-6 p-t-20"> 
					<div class = "txt-full-width">
					   <h5 class="details"><b>Contract Address :  </b><span>{{$landlordContractInfo->landlord_contract_address}}</span></h5>
					</div>
				</div>
				@endif
				@if($landlordContractInfo->landlord_marketing_executive)
				<div class="col-lg-6 p-t-20"> 
					<div class = "txt-full-width">
					   <h5 class="details"><b>Marketing Executive :  </b><span>{{$landlordContractInfo->marketExecutiveEmployeeInfo->employee_name}}</span></h5>
					</div>
				</div>
				@endif
				 @if($landlordContractInfo->landlord_contract_note)
				<div class="col-lg-6 p-t-20"> 
					<div class = "txt-full-width">
					   <h5 class="details"><b>Remark :  </b><span>{{$landlordContractInfo->landlord_contract_note}}</span></h5>
					</div>
				</div>
				@endif      
               </div>
			</div>
    
    </div></div>
</div> 
<div class="card card-box salesLeadBox">
  <div class="card-head">
    <div class="col"><h4>Note</h4></div>
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
              <th>User</th>
              <th>Date Time</th>
            </tr>
          </thead>
          <tbody> 
				  
          @forelse ($notesArray as $note)

            @if(!empty($note['stage'])) 
            <tr>
			  <td>{{$note['stage']}} </td>
              <td>{{$note['sales_notes']}}</td>
              <td>{{$note['employee_name']}}</td>
              <td>{{$note['created_at']}}</td>
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

<div class="modal" id="myModal">

<div class="modal-dialog assign">
	<div class="modal-content">

		<!-- Modal Header -->
		<div class="modal-header">
			<h4 class="modal-title"> Close Contract </h4>
			<button type="button" class="close" data-dismiss="modal">&times;</button>
		</div>

		<!-- Modal body -->
		<div class="modal-body">

			<div class="row">
				<div class="col">
					<div class="card card-box salesSearchBox">
						<div class="form-group">
							<label> <b>Choose Contract To Date </b></label>
							<input autocomplete="off" type="date" name="created_at" class="contract_search_field created_at" id="created_at" >
						</div>
						<div class="alert alert-success" id="msg" style="display: none;">
							Contract Details Updated pleaase wait.. <span id="time" class="text-danger">3</span>
						</div>
					</div>
				<button type="button" class="btn btn-primary pull-right" onclick="closeContract()">Save</button>
					
				</div>
			</div> 

		</div>

	</div>
</div>
</div>

<script type="text/javascript">
	function closeContract(){

		contaract = $("#contractId").val();
		end_date  = $("#created_at").val();

		$.ajax
		({
			type: "POST",
			url: "{{route('closeLandlordContract')}}",
			data: {"contract_id":contaract,"end_date":end_date,"_token": "{{ csrf_token() }}"},
			cache: false,
			success: function(data)
			{
                $("#msg").css("display","block");
				var time = 3;
				//alert("Contarct Closed  auto refresh in "+time+" sec");
				setInterval( function() {

			        time--;

			        $('#time').html(time);

			        if (time === 0) {

			            location.reload();
			        }    


			    }, 1000 );
			}
		});

	}
</script>

@endsection
@section('scripts')

@endsection

