@extends('layouts.plms-app')



@section('content')

 

    <div class="page-bar">
        <div class="page-title-breadcrumb">
            <div class=" pull-left">
                <div class="page-title">{{ (isset($enquiry)||isset($complaintEnquiry))? 'Edit' : 'Add'}} Complaint</div>
            </div>
            {{ (isset($complaintEnquiry))?   Breadcrumbs::render('complaint.edit',$complaintEnquiry,Session::get('current')) :  Breadcrumbs::render('complaint.create') }} 

            
             
        </div>
    </div>

    <link rel="stylesheet" href="{{ asset('public/css/jquery-ui.css')}} "><!-- //code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css -->
<link rel="stylesheet" href="{{ asset('public/css/tokenize2.min.css')}}">



<style>
  body {counter-reset:section 0 sec 0;}
  .counters:before
  {
    counter-increment:section;
    content:counter(section);
  }
  .countn:before
  {
    counter-increment:sec;
    content:counter(sec);
  }
  

</style>

<div class="row">  

  <!-- activities -->
  <div class="col-md-12 col-sm-12 dashboardtab">
    <div class="panel tab-border card-box">
      <header class="panel-heading panel-heading-gray custom-tab ">



        <ul class="nav nav-tabs">

          @if(isset($complaintEnquiry) || !isset($enquiry)&& !isset($complaintEnquiry))
          <li class="nav-item"><a href="#maintenance" data-toggle="tab" class=" active">Maintenance</a>
          </li>  
          @endif                                     
        </ul>

      </header>
      <div class="panel-body">
        <div class="tab-content">                                     



   @include('maintenance::complaint_form')

   </div>
</div>
</div>
</div>
</div>           

</div>
<div class="modal" id="myModal">

</div>


@endsection



@section('scripts')
@include('sales::add_sub_complaint_js')
@include('maintenance::complaint_js')
<script src="{{ asset('public/js/jquery-ui.js') }} "></script><!-- https://code.jquery.com/ui/1.12.1/jquery-ui.js -->
<script src="{{ asset('public/js/tokenize2.min.js') }}" ></script>
@include('maintenance::complaint_enquiry_js')
@endsection


