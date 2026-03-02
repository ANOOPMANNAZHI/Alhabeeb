        <!-- The Modal -->
        <div class="modal-dialog modal-lg assign">
          <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
              <h4 class="modal-title">
                @if($tenantContract->tenant_contract_status == 1)
                {{'Tenant Contract View'}}
                @elseif($tenantContract->work_flow_processes_code == 106)
                {{'Final Document View'}}
                @else 
                {{'Preliminary Document View'}}
                @endif</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
              </div>

              <!-- Modal body -->
              <div class="modal-body">

               <div class="row">
                <div class="col-sm-12">
                  <div class="card-box">
                    <div class="card-head">
                      <header>Property Section</header>
                    </div>
                    <form action="#" id="form_sample_2" class="form-horizontal">
                      <div class="card-body row">
                        <div class="col-lg-6 p-t-20"> 
                          <div class = "txt-full-width">
                           <h5 class="details"><b>Building Name :  </b><span>{{$tenantContract->building->building_name??''}}</span></h5>
                         </div>
                       </div>
                       <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                         <h5 class="details"><b>Building Code :  </b><span>{{$tenantContract->building->building_code??''}}</span></h5>
                       </div>
                     </div>                                                     

                     <div class="col-lg-6 p-t-20"> 
                      <div class = "txt-full-width">
                       <h5 class="details"><b>Unit No :  </b><span>{{$tenantContract->unit->unit_no??''}}</span></h5>
                     </div>
                   </div>
                   <div class="col-lg-6 p-t-20"> 
                    <div class = "txt-full-width">
                     <h5 class="details"><b>Unit Code :  </b><span>{{$tenantContract->unit->unit_code??''}}</span></h5>
                   </div>
                 </div>
                 <div class="col-lg-6 p-t-20"> 
                  <div class = "txt-full-width">
                   <h5 class="details"><b>Unit Usage:  </b><span>{{$tenantContract->unit_usage??''}}</span></h5>
                 </div>
               </div>
               <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                 <h5 class="details"><b>Unit Type:  </b><span>{{$tenantContract->unit->unit->unit_types_name??''}}</span></h5>
               </div>
             </div>
           </div>
         </form>
       </div>
       <div class="card-box">
        <div class="card-head">
          <header>Tenant Section</header>
        </div>
        <div class="card-body row">

          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
             <h5 class="details"><b>Tenant Name :  </b><span>{{$tenantContract->tenant->tenant_name??''}}</span></h5>
           </div>
         </div> 
		 <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
           <h5 class="details"><b>Tenant Type:  </b>
           <span>{{$tenantContract->tenant->tenantType->tenant_types_name??''}}</span></h5>
         </div>
       </div> 
	  @if(isset($tenantContract->tenant->tenant_company_name))
       <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
           <h5 class="details"><b>Company Name :  </b><span>{{$tenantContract->tenant->tenant_company_name??''}}</span></h5>
         </div>
       </div> 
       @endif
       @if(isset($tenantContract->tenant->tenant_contact_person))
       <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
           <h5 class="details"><b>Contact Person :  </b><span>{{$tenantContract->tenant->tenant_contact_person??''}}</span></h5>
         </div>
       </div> 
       @endif
       @if(isset($tenantContract->tenant->resident_id))
       <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
           <h5 class="details"><b>Residence ID No :  </b><span>{{$tenantContract->tenant->resident_id}}</span></h5>
         </div>
       </div> 
       @endif
       @if(isset($tenantContract->tenant->tenant_resident_exp_date))
       <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
           <h5 class="details"><b>Residence ID Exp Date :  </b><span>{{$tenantContract->tenant->tenant_resident_exp_date->format('d/m/Y')}}</span></h5>
         </div>
       </div> 
       @endif
        @if(isset($tenantContract->tenant->com_reg_no))
       <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
           <h5 class="details"><b>Commercial Reg. No :  </b><span>{{$tenantContract->tenant->com_reg_no}}</span></h5>
         </div>
       </div> 
       @endif
       <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
           <h5 class="details"><b>Mobile No :  </b><span>{{$tenantContract->tenant->tenant_contact_no}}</span></h5>
         </div>
       </div> 
       @if(!empty($tenantContract->tenant->nationality->nationality))

       <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
         <h5 class="details"><b>Nationality :  </b><span>{{$tenantContract->tenant->nationality->nationality??''}}</span></h5>
       </div>
     </div> 
     @endif
     @if(!empty($tenantContract->tenant->designation))

     <div class="col-lg-6 p-t-20"> 
      <div class = "txt-full-width">
       <h5 class="details"><b> Tenant Designation :  </b><span>{{$tenantContract->tenant->designation??''}}</span></h5>
     </div>
   </div> 

   @endif
 </div>
</div>
<div class="card-box">
  <div class="card-head">
    <header>Contract Section</header>
  </div>
  <div class="card-body row">


    <div class="col-lg-6 p-t-20"> 
      <div class = "txt-full-width">
       <h5 class="details"><b>Contract No :  </b><span>{{$tenantContract->tenant_contract_no??''}}</span></h5>
     </div>
   </div> 
   @if(!empty($tenantContract->created_at))
   <div class="col-lg-6 p-t-20"> 
    <div class = "txt-full-width">
     <h5 class="details"><b>Contract Date :  </b><span>{{$tenantContract->created_at->format('d/m/Y')}}</span></h5>
   </div>
 </div> 
 @endif
 @if(!empty($tenantContract->tenant_contract_no_members))
 <div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Contract No Members :  </b><span>{{$tenantContract->tenant_contract_no_members??''}}</span></h5>
 </div>
</div> 
@endif
@if(!empty($tenantContract->tenant_contract_address))
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Contract Address :  </b><span>{{$tenantContract->tenant_contract_address??''}}</span></h5>
 </div>
</div> 
@endif
                <!-- @if(!empty($tenantContract->tenant_contract_agreement_amt))
                <div class="col-lg-6 p-t-20"> 
                  <div class = "txt-full-width">
                     <h5 class="details"><b>Contract Agreement Amt :  </b><span>{{numberFormat($tenantContract->tenant_contract_agreement_amt)}} OMR</span></h5>
                  </div>
                </div> 
                @endif -->
                 @if(isset($tenantContract->tenant_contract_muncipality_agr_no))
                <div class="col-lg-6 p-t-20"> 
                  <div class = "txt-full-width">
                   <h5 class="details"><b>Muncipality Agreement No :  </b><span>{{$tenantContract->tenant_contract_muncipality_agr_no??''}}</span></h5>
                 </div>
               </div> 
                @endif
               
          @if(!empty($tenantContract->tenant_contract_start_date))              
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
             <h5 class="details"><b>Start Date:  </b><span>{{$tenantContract->tenant_contract_start_date->format('d/m/Y')??''}}</span></h5>
           </div>
         </div>
         @endif
         @if(!empty($tenantContract->tenant_contract_effective_date)) 
         <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
           <h5 class="details"><b>Effective Date:  </b><span>{{$tenantContract->tenant_contract_effective_date->format('d/m/Y')??''}}</span></h5>
         </div>
       </div>
       @endif
       @if(!empty($tenantContract->tenant_contract_valid_to_date))
       <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
         <h5 class="details"><b>End Date:  </b><span>{{$tenantContract->tenant_contract_valid_to_date->format('d/m/Y')??''}}</span></h5>
       </div>
     </div>
     @endif
     <div class="col-lg-6 p-t-20"> 
			<div class = "txt-full-width">
			 <h5 class="details"><b>Rent :  </b><span>{{numberFormat($tenantContract->tenant_contract_rent)}} OMR</span></h5>
		   </div>
		 </div>
		 @if(isset($tenantContract->tenant_contract_rent)
		 && isset($tenantContract->tenant_contract_value)
		 )
		 <div class="col-lg-6 p-t-20"> 
		  <div class = "txt-full-width">
		   <h5 class="details"><b>Contract Value :  </b>
			<span>
			  {{numberFormat($tenantContract->tenant_contract_value)}} 
			  OMR
			</span>
		  </h5>
		</div>
	  </div>
	  @endif
     <div class="col-lg-6 p-t-20"> 
      <div class = "txt-full-width">
       <h5 class="details"><b>Payment Method:  </b><span>{{$tenantContract->TenantContractPaymentName??''}}</span></h5>
     </div>
   </div>
   @php 
   if(isset($tenantContract->tenant_contract_duration_countdown)){
   $duration = $tenantContract->tenant_contract_duration_countdown;
   $count = explode('-',$duration); 
 }

 @endphp 

 <div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Duration:  </b><span> 
    @if(isset($tenantContract->tenant_contract_duration_countdown))
    {{$count[0]? $count[0].'y ':''}} {{$count[1]? $count[1].'m ':''}} {{$count[2]? $count[2].'d ':''}}
    @endif
  </span></h5>
</div>
</div>
                <!--<div class="col-lg-6 p-t-20"> 
                  <div class = "txt-full-width">
                     <h5 class="details"><b>Contract Registered In:  </b><span>{{$tenantContract->tenant_contract_registered_in==1?'Muscat':'Not In Muscat'}}</span></h5>
                  </div>
                </div> -->

                @if(!empty($tenantContract->enant_contract_muncipality_agr_no))
                <div class="col-lg-6 p-t-20"> 
                  <div class = "txt-full-width">
                   <h5 class="details"><b>Munipality Agreement No:  </b><span>{{$tenantContract->tenant_contract_muncipality_agr_no??''}}</span></h5>
                 </div>
               </div>
               @endif
               @if(!empty($tenantContract->tenant_contract_last_paid_date))
               <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                 <h5 class="details"><b>Vacant Since:  </b><span>{{$tenantContract->tenant_contract_last_paid_date->format('d/m/Y')}}</span></h5>
               </div>
             </div>
             @endif
             @if(!empty($tenantContract->tenant_contract_last_paid_amt))
             <div class="col-lg-6 p-t-20"> 
              <div class = "txt-full-width">
               <h5 class="details"><b>Rent Paid by Previous Tenant:  </b><span>{{numberFormat($tenantContract->tenant_contract_last_paid_amt)}} OMR</span></h5>
             </div>
           </div>
           @endif
           @if(!empty($tenantContract->tenant_contract_electric_water))
           <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
             <h5 class="details"><b>Deposit Electric/Water :  </b><span>{{$tenantContract->tenant_contract_electric_water??''}}</span></h5>
           </div>
         </div>
         @endif
         @if(!empty($tenantContract->tenant_contract_deposit_amt))
         <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
           <h5 class="details"><b>Deposit Rent Amount:  </b><span>{{$tenantContract->tenant_contract_deposit_amt??''}}</span></h5>
         </div>
       </div>
       @endif
       @if(!empty($tenantContract->tenant_contract_guarantee_cheque_details))
       <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
         <h5 class="details"><b>Guarantee Cheque Amount:  </b><span>{{$tenantContract->tenant_contract_guarantee_cheque_details??''}}</span></h5>
       </div>
     </div>
     @endif
     @if(!empty($tenantContract->tenant_marketing_executive))
     <div class="col-lg-6 p-t-20"> 
      <div class = "txt-full-width">
       <h5 class="details"><b>Marketing Executive Name:  </b><span>{{$market->employee_name}}</span></h5>
     </div>
   </div>
   @endif
   @if(!empty($tenantContract->tenant_contract_note))
   <div class="col-lg-6 p-t-20"> 
    <div class = "txt-full-width">
     <h5 class="details"><b>Remark:  </b><span>{{$tenantContract->tenant_contract_note??''}}</span></h5>
   </div>
 </div>
 @endif
 @if(isset($tenantContract->pdc_check))
 <div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>PDC:  </b><span>{{$tenantContract->pdc_check==1?'Full':'Partial'}}</span></h5>
 </div>
</div>
@endif
@if(!empty($tenantContract->partial_comment))
 <div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Comment:  </b><span>{{$tenantContract->partial_comment}}</span></h5>
 </div>
</div>
@endif
@if(isset($tenantContract->deposit_check))
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Deposit:  </b><span>{{$tenantContract->deposit_check==1?'Yes':''}}</span></h5>
 </div>
</div>
@endif


<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Occupant ID Upload:  </b>

   <span>@foreach($tenantContract->tenantDocument as $documents)
	
    <a target="_blank" href="{{asset('storage/app/'.$documents->tenant_documents_file_name)}}">
    @if (pathinfo($documents->tenant_documents_name, PATHINFO_EXTENSION) == 'doc')
    <i class="fa fa-file" aria-hidden="true"></i>
    @elseif (pathinfo($documents->tenant_documents_name, PATHINFO_EXTENSION) == 'pdf')
    <i class="fa fa-file-pdf-o" aria-hidden="true"></i>
    @elseif(pathinfo($documents->tenant_documents_name, PATHINFO_EXTENSION) == 'docx')
    <i class="fa fa-file-word-o" aria-hidden="true"></i>
    @else
    <i class="fa fa-copy" aria-hidden="true"></i>
    @endif
    {{$documents->tenant_documents_name}}</a>

	<br/>
    @endforeach</span></h5>
  </div>
</div>


@if(isset($tenantContract->tenant->tenantDocs[0]) )
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Visiting Card Upload:  </b>
        <span>
    <a target="_blank" href="{{asset('storage/app/'.$documents->tenant_documents_file_name)}}">
    @if (pathinfo($documents->tenant_documents_name, PATHINFO_EXTENSION) == 'doc')
    <i class="fa fa-file" aria-hidden="true"></i>
    @elseif (pathinfo($documents->tenant_documents_name, PATHINFO_EXTENSION) == 'pdf')
    <i class="fa fa-file-pdf-o" aria-hidden="true"></i>
    @elseif(pathinfo($documents->tenant_documents_name, PATHINFO_EXTENSION) == 'docx')
    <i class="fa fa-file-word-o" aria-hidden="true"></i>
    @else
    <i class="fa fa-copy" aria-hidden="true"></i>
    @endif
   <a target="_blank" href="{{asset('storage/app/'.$documents->tenant_doc_path_name)}}">
   {{$tenantContract->tenant->tenantDocs[0]->tenant_doc_name}}</a>
   
   <br/>
  </span></h5>
  </div>
</div>
@endif
@if(isset($tenantContract->tenant->tenantDocs[1]) )
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Others:  </b><span>

    
    <a target="_blank" href="{{asset('storage/app/'.$documents->tenant_documents_file_name)}}">
    @if (pathinfo($documents->tenant_documents_name, PATHINFO_EXTENSION) == 'doc')
    <i class="fa fa-file" aria-hidden="true"></i>
    @elseif (pathinfo($documents->tenant_documents_name, PATHINFO_EXTENSION) == 'pdf')
    <i class="fa fa-file-pdf-o" aria-hidden="true"></i>
    @elseif(pathinfo($documents->tenant_documents_name, PATHINFO_EXTENSION) == 'docx')
    <i class="fa fa-file-word-o" aria-hidden="true"></i>
    @else
    <i class="fa fa-copy" aria-hidden="true"></i>
    @endif
   <a target="_blank" href="{{asset('storage/app/'.$documents->tenant_doc_path_name)}}">{{$tenantContract->tenant->tenantDocs[1]->tenant_doc_name}}</a>
   
   <br/>
  </span></h5>
  </div>
</div>
@endif

</div>
</div>
</div>
</div> 

</div>

<!-- Modal footer -->
<div class="modal-footer">
  <!-- <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button> -->
</div>

</div>
</div>
@section('scripts')
<script>
  $(document).ready(function() {
    $("#form_sample_2").validate()
  });
</script>
@endsection
