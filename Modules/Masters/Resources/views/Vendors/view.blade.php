@extends('layouts.plms-app')
 

@section('content')
    <!-- start widget -->
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Vendor</div>
        </div>
         {{ Breadcrumbs::render('vendors.show',$vendor) }}
    </div>
</div>

<div class="row">
    <div class="col">
		 <div class="card card-box salesSearchBox">
           <div class="row">
            <div class="col-sm-12">
				@can('edit_vendor') 
				<h4>
					<a href="{{route('vendors.edit',[$vendor->id,'backurl'=>Route::currentRouteName(),'backid'=>$vendor->id])}}" class="btn btn-circle btn-primary  align-right">
						Edit
					</a>
					<div class="clr"></div>
				</h4>
				@endcan
				
			</div>
		</div>
		 <div class="dataSearchBox">
			<div class="card-body row">
				<div class="col-lg-6 p-t-20"> 
					<div class = "txt-full-width">
						<h5 class="details"><b>Vendor Code  :  </b><span>{{$vendor->vendor_code}}</span></h5>
					</div>
				</div>
				<div class="col-lg-6 p-t-20"> 
					<div class = "txt-full-width">
						<h5 class="details"><b>Name  :  </b><span>{{$vendor->vendor_name}}</span></h5>
					</div>
				</div>
				<div class="col-lg-6 p-t-20"> 
					<div class = "txt-full-width">
						<h5 class="details"><b>Contact Person:  </b><span>{{$vendor->vendor_contact_person}}</span></h5>
					</div>
				</div>
				<div class="col-lg-6 p-t-20"> 
					<div class = "txt-full-width">
						<h5 class="details"><b>Contact Number  :  </b><span>{{$vendor->vendor_contact_no}}</span></h5>
					</div>
				</div>
				<div class="col-lg-6 p-t-20"> 
					<div class = "txt-full-width">
						<h5 class="details"><b>Contact Email :  </b><span><a href="mailto:{{$vendor->vendor_contact_email}}">{{$vendor->vendor_contact_email}}</a></span></h5>
					</div>
				</div>
				<div class="col-lg-6 p-t-20"> 
					<div class = "txt-full-width">
						<h5 class="details"><b>Fax No.  :  </b><span>{{$vendor->vendor_fax_no}}</span></h5>
					</div>
				</div>
				<div class="col-lg-6 p-t-20"> 
					<div class = "txt-full-width">
						<h5 class="details"><b>Status  :  </b><span>{{$vendor->vendor_status_name}}</span></h5>
					</div>
				</div>
			</div>
		</div>
        <div class="sub-head">Contact Details</div>
        <div class="dataSearchBox">    
        <div class="card-body row">

            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Contact Address :  </b><span>{{$vendor->vendor_contact_address}}</span></h5>
                </div>
            </div>
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Contact Secondary Address  :  </b><span>{{$vendor->vendor_secondary_address}}</span></h5>
                </div>
            </div>
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>PostCode:  </b><span>{{$vendor->vendor_pc}}</span></h5>
                </div>
            </div>
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Location:  </b><span>{{$vendor->vendorLocation->locations_name}}</span></h5>
                </div>
            </div>
            
			</div>
		</div> 
    <div class="sub-head">Bank Details</div>
        <div class="dataSearchBox">    
        <div class="card-body row">
			@if(isset($vendor->vendorBank->bank_name))
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Bank :  </b><span>{{$vendor->vendorBank->bank_name}}</span></h5>
                </div>
            </div>
            @endif
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Account No :  </b><span>{{$vendor->vendor_acc_no}}</span></h5>
                </div>
            </div>
            @if(!empty($vendor->swift_code))
            <div class="col-lg-6 p-t-20">
                <div class = "txt-full-width">
                    <h5 class="details"><b>Swift Code :  </b><span>{{$vendor->swift_code}}</span></h5>
                </div>
            </div>
            @endif
            
			</div>
		</div>       
         
        </div>
    </div>
</div>

@endsection
