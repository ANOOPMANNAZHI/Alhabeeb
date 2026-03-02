@extends('layouts.plms-app')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">


@section('content')
    <!-- start widget -->
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Tenant</div>
        </div>
        {{ Breadcrumbs::render('tenants.show') }}
    </div>
</div>

<div class="row">
    <div class="col">
		<div class="card card-box salesSearchBox">
			 @can('tenant_edit')
			  <h4>
				<a href="{{route('tenants.edit',$tenant->id)}}" class="btn btn-circle btn-primary  align-right">
					Edit
				</a>
				<div class="clr"></div>
			</h4>
			@endcan  
		<div class="dataSearchBox">
			
			<div class="card-body row">	
				<div class="col-lg-6 p-t-20"> 
					<div class = "txt-full-width">
						<h5 class="details"><b>Tenant Type :  </b><span>{{$tenant->tenantType->tenant_types_name}}</span></h5>
					</div>
				</div>
				<div class="col-lg-6 p-t-20"> 
					<div class = "txt-full-width">
						<h5 class="details"><b>Name  :  </b><span>{{$tenant->tenant_name}}</span></h5>
					</div>
				</div>
				<div class="col-lg-6 p-t-20"> 
					<div class = "txt-full-width">
						<h5 class="details"><b>Residence ID No  :  </b><span>{{$tenant->resident_id}}</span></h5>
					</div>
				</div>
				 
				<div class="col-lg-6 p-t-20"> 
					<div class = "txt-full-width">
						<h5 class="details"><b>Residence ID Exp Date  :  </b><span>{{isset($tenant->tenant_resident_exp_date)?$tenant->tenant_resident_exp_date->format('d/m/Y'):''}}</span></h5>
					</div>
				</div>
				<div class="col-lg-6 p-t-20"> 
					<div class = "txt-full-width">
						<h5 class="details"><b>Nationality  :  </b><span>{{$tenant->nationality->nationality ?? 'NA'}}</span></h5>
					</div>
				</div>
				<div class="col-lg-6 p-t-20"> 
					<div class = "txt-full-width">
						<h5 class="details"><b>Passport No  :  </b><span>{{$tenant->passport_no ?? 'NA'}}</span></h5>
					</div>
				</div>
				<div class="col-lg-6 p-t-20"> 
					<div class = "txt-full-width">
						<h5 class="details"><b>Gender  :  </b><span>{{$tenant->tenant_gender_name ?? 'NA'}}</span></h5>
					</div>
				</div>
				@if(!empty($tenant->tenant_date_of_birth))
				<div class="col-lg-6 p-t-20"> 
					<div class = "txt-full-width">
						<h5 class="details"><b>Date Of Birth :  </b><span>

{{$tenant->tenant_date_of_birth ? date('d/m/Y',strtotime($tenant->tenant_date_of_birth)):''}}

						</span></h5>
					</div>
				</div>
				@endif
				<div class="col-lg-6 p-t-20"> 
					<div class = "txt-full-width">
						<h5 class="details"><b>Employer Name :  </b><span>{{$tenant->tenant_employer_name?? 'NA'}}</span></h5>
					</div>
				</div>
				<div class="col-lg-6 p-t-20"> 
					<div class = "txt-full-width">
						<h5 class="details"><b>Office Location:  </b><span>{{(isset($tenant->location_id))?$tenant->location->locations_name: 'NA'}}</span></h5>
					</div>
				</div>
				<div class="col-lg-6 p-t-20"> 
					<div class = "txt-full-width">
						<h5 class="details"><b>Designation:  </b><span>{{(isset($tenant->designation))?$tenant->designation: 'NA'}}</span></h5>
					</div>
				</div>
			</div>
	   </div>
		<div class="sub-head">Contact Detail</div>
		<div class="dataSearchBox">    
			<div class="card-body row">
				<div class="col-lg-6 p-t-20"> 
					<div class = "txt-full-width">
						<h5 class="details"><b>Contact Address :  </b><span>{{$tenant->tenant_contact_address ?? 'NA'}}</span></h5>
					</div>
				</div>	
				<div class="col-lg-6 p-t-20"> 
					<div class = "txt-full-width">
						<h5 class="details"><b>Secondary Address :  </b><span>{{$tenant->tenant_secondary_address ?? 'NA'}}</span></h5>
					</div>
				</div>
				<div class="col-lg-6 p-t-20"> 
					<div class = "txt-full-width">
						<h5 class="details"><b>Postal Box :  </b><span>{{$tenant->tenant_post_box?? 'NA'}}</span></h5>
					</div>
				</div>	
            	<div class="col-lg-6 p-t-20"> 
					<div class = "txt-full-width">
						<h5 class="details"><b>Postal Code:  </b><span>{{$tenant->tenant_pc?? 'NA'}}</span></h5>
					</div>
				</div>
				<div class="col-lg-6 p-t-20"> 
					<div class = "txt-full-width">
						<h5 class="details"><b>Mobile No:  </b><span>{{$tenant->tenant_contact_no?? 'NA'}}</span></h5>
					</div>
				</div>
				<div class="col-lg-6 p-t-20"> 
					<div class = "txt-full-width">
						<h5 class="details"><b>Office No:  </b><span>{{$tenant->gsm_no?? 'NA'}}</span></h5>
					</div>
				</div>
				<div class="col-lg-6 p-t-20"> 
					<div class = "txt-full-width">
						<h5 class="details"><b>Residence Tel:  </b><span>{{$tenant->tenant_residence_tel?? 'NA'}}</span></h5>
					</div>
				</div>
				<div class="col-lg-6 p-t-20"> 
					<div class = "txt-full-width">
						<h5 class="details"><b>Personal Email:  </b><span>{{$tenant->tenant_personal_email?? 'NA'}}</span></h5>
					</div>
				</div>
				<div class="col-lg-6 p-t-20"> 
					<div class = "txt-full-width">
						<h5 class="details"><b>Fax No:  </b><span>{{$tenant->tenant_fax_no?? 'NA'}}</span></h5>
					</div>
				</div>
				<div class="col-lg-6 p-t-20"> 
					<div class = "txt-full-width">
						<h5 class="details"><b>ICE Name:  </b><span>{{$tenant->tenant_ice_name?? 'NA'}}</span></h5>
					</div>
				</div>
				<div class="col-lg-6 p-t-20"> 
					<div class = "txt-full-width">
						<h5 class="details"><b>ICE Contact No:  </b><span>{{$tenant->tenant_ice_contact_no?? 'NA'}}</span></h5>
					</div>
				</div>
				<div class="col-lg-6 p-t-20"> 
					<div class = "txt-full-width">
						<h5 class="details"><b>Contact Email:  </b><span>{{$tenant->tenant_contact_email?? 'NA'}}</span></h5>
					</div>
				</div>
			</div>
	   </div>
	   <div class="sub-head">Bank Detail</div>
		<div class="dataSearchBox">    
			<div class="card-body row">
				<div class="col-lg-6 p-t-20"> 
					<div class = "txt-full-width">
						<h5 class="details"><b>Bank :  </b><span>{{$tenant->bank->bank_name ?? 'NA'}}</span></h5>
					</div>
				</div>	
				<div class="col-lg-6 p-t-20"> 
					<div class = "txt-full-width">
						<h5 class="details"><b>Account No :  </b><span>{{$tenant->tenant_acc_no ?? 'NA'}}</span></h5>
					</div>
				</div>	
			</div>
		</div>


		@if($docs->count()>0)
		<div class="sub-head">Docs Upload</div> 
		<div class="dataSearchBox">    
			<div class="card-body row">
				@foreach ($docs as $key=>$doc)
				<div class="col-lg-6 p-t-20"> 
					<div class = "txt-full-width">
						<h5 class="details"><b>Doc Category :  </b>
						<span>
							
						 @if (array_key_exists($doc->tenant_doc_category, $doc_category))
						
								{{$doc_category[$doc->tenant_doc_category]}}
						 @endif
						</span></h5>
					</div>
				</div>	
				<div class="col-lg-6 p-t-20"> 
					<div class = "txt-full-width">
						<h5 class="details"><b>Docs :  </b>
						<span>
						<a target="_blank" href="{{asset('storage/app/'.$doc->tenant_doc_path_name)}}">
                {{$doc->tenant_doc_name}}  </a>
						</span>
						</h5>
					</div>
				</div>
				@endforeach 
			</div>
		</div>
	 @endif
     </div>  
     </div>   
</div> 

<div class="row">
   <div class="col-md-12 col-sm-12">
        <div class="card  card-box">
            
            <div class="card-body ">
            <h4> Tenant Agreement  </h4>      

              
              <table class="table display product-overview mb-30" id="dtBasicExample">
                <thead>
                    <tr>
                        <th>Sl No.</th>
                        <th>Contract No</th>
                        <th>Muncipal Agr No</th>
                        <th>Agreement Amt</th>
                        <th>Building</th>
                        <th>Unit</th>
                        <th>Status</th>
                        
                    </tr>
                </thead>
                <tbody>
                    @forelse ($contracts as $contract)
                    <tr>
                        <td>{{$loop->iteration}}</td>
                        <td>
                          @if($contract->tenant_contract_status == 1 || 
                          $contract->work_flow_processes_code == 108)<a class="no-link" href="{{route('tenant-contract.show',$contract->id)}}">{{$contract->tenant_contract_no}}</a>
                          @else
                          <a class="no-link" href="{{route('tenant-contract.edit',$contract->id)}}">{{$contract->tenant_contract_no}}</a>
                          @endif
                        </td>
                        <td>
                          @if($contract->tenant_contract_status == 1 || 
                          $contract->work_flow_processes_code == 108)<a class="no-link" href="{{route('tenant-contract.show',$contract->id)}}">{{$contract->tenant_contract_muncipality_agr_no}}</a>
                          @else
                          <a class="no-link" href="{{route('tenant-contract.edit',$contract->id)}}">{{$contract->tenant_contract_muncipality_agr_no}}</a>
                          @endif
                           </td>
                        <td>@if($contract->tenant_contract_status == 1 || 
                          $contract->work_flow_processes_code == 108)<a class="no-link" href="{{route('tenant-contract.show',$contract->id)}}">{{numberFormat($contract->tenant_contract_value)}} OMR</a>
                          @else
                          <a class="no-link" href="{{route('tenant-contract.edit',$contract->id)}}">{{$contract->tenant_contract_value}} OMR</a>
                          @endif
                           </td>
                        <td>@if($contract->tenant_contract_status == 1 || 
                          $contract->work_flow_processes_code == 108)<a class="no-link" href="{{route('tenant-contract.show',$contract->id)}}">{{$contract->building->building_name}}</a>
                          @else
                          <a class="no-link" href="{{route('tenant-contract.edit',$contract->id)}}">{{$contract->building->building_name}}</a>
                          @endif
                          </td>
						  <td>
						@if($contract->tenant_contract_status == 1 || $contract->work_flow_processes_code == 108)
						<a class="no-link" href="{{route('tenant-contract.show',$contract->id)}}">{{$contract->unit->unit_code}}</a>
						@else
						<a class="no-link" href="{{route('tenant-contract.edit',$contract->id)}}">{{$contract->unit->unit_code}}</a>
						@endif
						</td>
                       
                        <td>{{$contract->TenantContractStatusName}}</td>
                    </tr>  
                    @empty
                    <tr>
                        <td colspan="7" align="center">
                        <p>No Record</p>
                       </td>
                    </tr>
                    @endforelse
                </tbody>
              </table>
             
              {{$contracts->links()}}      
            </div>
        </div>
    </div>
</div>

@endsection
