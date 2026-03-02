@extends('layouts.plms-app')



@section('content')

 

    <div class="page-bar">
        <div class="page-title-breadcrumb">
            <div class=" pull-left">
                <div class="page-title">{{ (isset($enquiry)||isset($complaintEnquiry))? 'Edit' : 'Add'}} Enquiry</div>
            </div>
		
            @if(isset($enquiry) && Session::get('current') != null)
            	{{Breadcrumbs::render('enquiry.edit',$enquiry,Session::get('current'))}}
        	@elseif(isset($complaintEnquiry) && Session::get('current') != null)
        		{{Breadcrumbs::render('complaints.edit',$complaintEnquiry,Session::get('current'))}}
    		@else
    			{{Breadcrumbs::render('enquiry.create')}}
            @endif 
             
        </div>
    </div>


   @include('sales::enquiry_form')


@endsection
