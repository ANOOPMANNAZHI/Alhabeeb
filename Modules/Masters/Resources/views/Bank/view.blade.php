@extends('layouts.plms-app')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">


@section('content')
    <!-- start widget -->
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Bank</div>
        </div>
        {{ Breadcrumbs::render('bank.show', $bank) }}
    </div>
</div>

<div class="row">
    <div class="col-sm-12">
        <div class="card-box">
            @can('edit_bank')
				 <div class="card-head">
				<h4>
					<a href="{{route('bank.edit',[$bank->id,'backurl'=>Route::currentRouteName(),'backid'=>$bank->id])}}" class="btn btn-circle btn-primary  align-right">
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
                  <div class="col-md-5"><b> Code  </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$bank->bank_code}}</span></div>
                </div>
              </div>

       <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Name </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$bank->bank_name}}</span></div>
                </div>
              </div>

       <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Branch </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$bank->bank_branch}}</span></div>
                </div>
              </div>
		<div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Bank Cheque-book Id </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$bank->bank_chequebook_id}}</span></div>
                </div>
		</div>
		<div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Division </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{($bank->dim1Value=='02')?'PLM':'HO'}}</span></div>
                </div>
              </div>
       <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Status  </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>@if($bank->bank_status==1){{ 'Active' }}@else{{ 'Inactive' }}@endif</span></div>
                </div>
              </div>
			    <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Accounts  </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>@if($bank->accounts_bank==1){{ 'Yes' }}@else{{ 'No' }}@endif</span></div>
                </div>
              </div>
       <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Remark  </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$bank->bank_remark}}</span></div>
                </div>
              </div>
            </div>
            </form>
        </div>
    </div>
</div> 


@endsection
