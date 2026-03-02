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
    <div class="col-sm-12">
        <div class="card-box">
           @can('tenant_edit')
			   <div class="card-head">
				  <h4>
					<a href="{{route('tenants.edit',$tenant->id)}}" class="btn btn-circle btn-primary  align-right">
						Edit
					</a>
					<div class="clr"></div>
				</h4>
				</div>
            @endcan      
            <form action="#" id="form_sample_2" class="form-horizontal">
            <div class="card-body row">
              <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b> Code   </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$tenant->tenant_code ?? 'NA'}}</span></div>
                </div>
              </div>
          <div class="col-md-6 p-t-10">
          <div class="row">
            <div class="col-md-5"><b>Resident Id </b></div>
            <div class="col-md-1 s-clm">:</div>
            <div class="col-md-6"><span>{{$tenant->resident_id ?? 'NA'}}</span></div>
          </div>
        </div>
        @if($tenant->tenant_resident_exp_date)
        <div class="col-md-6 p-t-10">
          <div class="row">
            <div class="col-md-5"><b>Resident Exp Date </b></div>
            <div class="col-md-1 s-clm">:</div>
            <div class="col-md-6"><span>{{$tenant->tenant_resident_exp_date ?? 'NA'}}</span></div>
          </div>
        </div>
        @endif
       <div class="col-md-6 p-t-10">
          <div class="row">
            <div class="col-md-5"><b>Name </b></div>
            <div class="col-md-1 s-clm">:</div>
            <div class="col-md-6"><span>{{$tenant->tenant_name ?? 'NA'}}</span></div>
          </div>
        </div>
        <div class="col-md-6 p-t-10">
          <div class="row">
            <div class="col-md-5"><b>Mobile No  </b></div>
            <div class="col-md-1 s-clm">:</div>
            <div class="col-md-6"><span>{{$tenant->tenant_contact_no ?? 'NA'}}</span></div>
          </div>
        </div>           
        <div class="col-md-6 p-t-10">
          <div class="row">
            <div class="col-md-5"><b>Email  </b></div>
            <div class="col-md-1 s-clm">:</div>
            <div class="col-md-6"><span>{{$tenant->tenant_contact_email ?? 'NA'}}</span></div>
          </div>
        </div>
        
        <div class="col-md-6 p-t-10">
          <div class="row">
            <div class="col-md-5"><b>Gender</b></div>
            <div class="col-md-1 s-clm">:</div>
            <div class="col-md-6"><span>{{$tenant->tenant_gender_name ?? 'NA'}}</span></div>
          </div>
        </div>
        
        <div class="col-md-6 p-t-10">
          <div class="row">
            <div class="col-md-5"><b>Fax No  </b></div>
            <div class="col-md-1 s-clm">:</div>
            <div class="col-md-6"><span>{{$tenant->tenant_fax_no ?? 'NA'}}</span></div>
          </div>
        </div>
        <div class="col-md-6 p-t-10">
          <div class="row">
            <div class="col-md-5"><b>Account No  </b></div>
            <div class="col-md-1 s-clm">:</div>
            <div class="col-md-6"><span>{{$tenant->tenant_acc_no ?? 'NA'}}</span></div>
          </div>
        </div>  
        <div class="col-md-6 p-t-10">
          <div class="row">
            <div class="col-md-5"><b>Contact Person  </b></div>
            <div class="col-md-1 s-clm">:</div>
            <div class="col-md-6"><span>{{$tenant->tenant_contact_person ?? 'NA'}}</span></div>
          </div>
        </div> 
        <div class="col-md-6 p-t-10">
          <div class="row">
            <div class="col-md-5"><b>Bank Name  </b></div>
            <div class="col-md-1 s-clm">:</div>
            <div class="col-md-6"><span>{{$tenant->bank->bank_name ?? 'NA'}}</span></div>
          </div>
        </div> 
        <div class="col-md-6 p-t-10">
          <div class="row">
            <div class="col-md-5"><b>Contact Address  </b></div>
            <div class="col-md-1 s-clm">:</div>
            <div class="col-md-6"><span>{{$tenant->tenant_contact_address ?? 'NA'}}</span></div>
          </div>
        </div>
        <div class="col-md-6 p-t-10">
          <div class="row">
            <div class="col-md-5"><b>Secondary Address  </b></div>
            <div class="col-md-1 s-clm">:</div>
            <div class="col-md-6"><span>{{$tenant->tenant_secondary_address ?? 'NA'}}</span></div>
          </div>
        </div>
        @if($tenant->tenant_post_box)
        <div class="col-md-6 p-t-10">
          <div class="row">
            <div class="col-md-5"><b>Postal Box </b></div>
            <div class="col-md-1 s-clm">:</div>
            <div class="col-md-6"><span>{{$tenant->tenant_post_box ?? 'NA'}}</span></div>
          </div>
        </div>
        @endif
        <div class="col-md-6 p-t-10">
          <div class="row">
            <div class="col-md-5"><b>Postal Code </b></div>
            <div class="col-md-1 s-clm">:</div>
            <div class="col-md-6"><span>{{$tenant->tenant_pc ?? 'NA'}}</span></div>
          </div>
        </div>
        <div class="col-md-6 p-t-10">
          <div class="row">
            <div class="col-md-5"><b>Type </b></div>
            <div class="col-md-1 s-clm">:</div>
            <div class="col-md-6"><span>{{$tenant->tenantType->tenant_types_name ?? 'NA'}}</span></div>
          </div>
        </div>
        <div class="col-md-6 p-t-10">
          <div class="row">
            <div class="col-md-5"><b>Mobile No</b></div>
            <div class="col-md-1 s-clm">:</div>
            <div class="col-md-6"><span>{{$tenant->tenant_contact_no ?? 'NA'}}</span></div>
          </div>
        </div>
        <div class="col-md-6 p-t-10">
          <div class="row">
            <div class="col-md-5"><b>Office No</b></div>
            <div class="col-md-1 s-clm">:</div>
            <div class="col-md-6"><span>{{$tenant->gsm_no ?? 'NA'}}</span></div>
          </div>
        </div>
        <div class="col-md-6 p-t-10">
          <div class="row">
            <div class="col-md-5"><b>Passport No </b></div>
            <div class="col-md-1 s-clm">:</div>
            <div class="col-md-6"><span>{{$tenant->passport_no ?? 'NA'}}</span></div>
          </div>
        </div>
        <div class="col-md-6 p-t-10">
          <div class="row">
            <div class="col-md-5"><b>Commercial Reg No </b></div>
            <div class="col-md-1 s-clm">:</div>
            <div class="col-md-6"><span>{{$tenant->com_reg_no ?? 'NA'}}</span></div>
          </div>
        </div>
        
        @if($tenant->tenant_date_of_birth)
        <div class="col-md-6 p-t-10">
          <div class="row">
            <div class="col-md-5"><b>Date Of Birth </b></div>
            <div class="col-md-1 s-clm">:</div>
            <div class="col-md-6"><span>{{$tenant->tenant_date_of_birth ?? 'NA'}}</span></div>
          </div>
        </div>
        @endif
       @if($tenant->tenant_employer_name)
        <div class="col-md-6 p-t-10">
          <div class="row">
            <div class="col-md-5"><b>Employer Name </b></div>
            <div class="col-md-1 s-clm">:</div>
            <div class="col-md-6"><span>{{$tenant->tenant_employer_name ?? 'NA'}}</span></div>
          </div>
        </div>
        @endif
        @if($tenant->tenant_residence_tel)
        <div class="col-md-6 p-t-10">
          <div class="row">
            <div class="col-md-5"><b>Resident Telephone No </b></div>
            <div class="col-md-1 s-clm">:</div>
            <div class="col-md-6"><span>{{$tenant->tenant_residence_tel ?? 'NA'}}</span></div>
          </div>
        </div>
        @endif
        @if($tenant->tenant_personal_email)
        <div class="col-md-6 p-t-10">
          <div class="row">
            <div class="col-md-5"><b>Personal Email </b></div>
            <div class="col-md-1 s-clm">:</div>
            <div class="col-md-6"><span>{{$tenant->tenant_personal_email ?? 'NA'}}</span></div>
          </div>
        </div>
        @endif
        @if($tenant->tenant_ice_name)
        <div class="col-md-6 p-t-10">
          <div class="row">
            <div class="col-md-5"><b>ICE Name</b></div>
            <div class="col-md-1 s-clm">:</div>
            <div class="col-md-6"><span>{{$tenant->tenant_ice_name ?? 'NA'}}</span></div>
          </div>
        </div>
        @endif
        @if($tenant->tenant_ice_contact_no)
        <div class="col-md-6 p-t-10">
          <div class="row">
            <div class="col-md-5"><b>ICE Contact No </b></div>
            <div class="col-md-1 s-clm">:</div>
            <div class="col-md-6"><span>{{$tenant->tenant_ice_contact_no ?? 'NA'}}</span></div>
          </div>
        </div>
        @endif
        @if($tenant->designation)
        <div class="col-md-6 p-t-10">
          <div class="row">
            <div class="col-md-5"><b>Designation </b></div>
            <div class="col-md-1 s-clm">:</div>
            <div class="col-md-6"><span>{{$tenant->designation ?? 'NA'}}</span></div>
          </div>
        </div>
        @endif
        @if($tenant->nationalities_id)
        <div class="col-md-6 p-t-10">
          <div class="row">
            <div class="col-md-5"><b>Nationality </b></div>
            <div class="col-md-1 s-clm">:</div>
            <div class="col-md-6"><span>{{$tenant->nationality->nationality ?? 'NA'}}</span></div>
          </div>
        </div>
        @endif
        <div class="col-md-6 p-t-10">
          <div class="row">
            <div class="col-md-5"><b>Status </b></div>
            <div class="col-md-1 s-clm">:</div>
            <div class="col-md-6"><span> {{$tenant->StatusName}}</span></div>
          </div>
        </div>
          </div>
          </form>
        </div>
    </div>
</div> 
@if($docs->count()>0)
<div class="row">
  <div class="col-sm-12">
    <div class="card-box">
      <div class="card-head">
        <header> Tenant Docs</header>
        <!-- <button id = "panel-button" 
                       class = "mdl-button mdl-js-button mdl-button--icon pull-right" 
                       data-upgraded = ",MaterialButton">
         <i class = "material-icons">more_vert</i>
          </button>
          <ul class = "mdl-menu mdl-menu--bottom-right mdl-js-menu mdl-js-ripple-effect"
             data-mdl-for = "panel-button">
             <li class = "mdl-menu__item"><i class="material-icons">assistant_photo</i>Action</li>
             <li class = "mdl-menu__item"><i class="material-icons">print</i>Another action</li>
             <li class = "mdl-menu__item"><i class="material-icons">favorite</i>Something else here</li>
          </ul> -->
      </div>
      <div class="card-body row">
    <div id="aniimated-thumbnials" class="list-unstyled row clearfix">
          @foreach ($docs as $doc) 
              <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 m-b-20"> 
                <a target="_blank" href="{{asset('storage/app/'.$doc->tenant_doc_path_name)}}">
                {{$doc->tenant_doc_name}}  </a> </div>
         
          @endforeach 
          </div>
      </div>
    </div>
  </div>
</div>
@endif
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
                          @if($contract->tenant_contract_status == 1)<a class="no-link" href="{{route('tenant-contract.show',$contract->id)}}">{{$contract->tenant_contract_no}}</a>
                          @else
                          <a class="no-link" href="{{route('tenant-contract.edit',$contract->id)}}">{{$contract->tenant_contract_no}}</a>
                          @endif
                        </td>
                        <td>
                          @if($contract->tenant_contract_status == 1)<a class="no-link" href="{{route('tenant-contract.show',$contract->id)}}">{{$contract->tenant_contract_muncipality_agr_no}}</a>
                          @else
                          <a class="no-link" href="{{route('tenant-contract.edit',$contract->id)}}">{{$contract->tenant_contract_muncipality_agr_no}}</a>
                          @endif
                           </td>
                        <td>@if($contract->tenant_contract_status == 1)<a class="no-link" href="{{route('tenant-contract.show',$contract->id)}}">{{number_format($contract->tenant_contract_agreement_amt,3)}}</a>
                          @else
                          <a class="no-link" href="{{route('tenant-contract.edit',$contract->id)}}">{{number_format($contract->tenant_contract_agreement_amt,3)}}</a>
                          @endif
                           </td>
                        <td>@if($contract->tenant_contract_status == 1)<a class="no-link" href="{{route('tenant-contract.show',$contract->id)}}">{{$contract->building->building_name}}</a>
                          @else
                          <a class="no-link" href="{{route('tenant-contract.edit',$contract->id)}}">{{$contract->building->building_name}}</a>
                          @endif
                          </td>
                        <td>@if($contract->tenant_contract_status == 1)<a class="no-link" href="{{route('tenant-contract.show',$contract->id)}}">{{$contract->unit->unit_code}}</a>
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
