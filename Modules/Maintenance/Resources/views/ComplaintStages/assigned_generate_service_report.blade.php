@extends('layouts.plms-app')
 

@section('content')
    <!-- start widget -->
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Tickets</div>
        </div>
        {{ Breadcrumbs::render('complaintStage.show') }}
    </div>
</div>
<a href="" class="btn btn-circle btn-primary  align-right">GENERATE SERVICE REPORT</a> 
<div class="row">
    <div class="col-sm-12">
        <div class="card-box">
            <!-- <div class="card-head">
              <header>View Building Type</header>
            </div> -->
            <form action="#" id="form_sample_2" class="form-horizontal">
            <div class="card-body row"> 
                
          
          	<div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b> Complaint No  </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span></span></div>
                </div>
          	</div>

            <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Assigned to  </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span></span></div>
                </div>
         	 </div> 
         	 <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Ticket No</b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span></span></div>
                </div>
         	 </div>
         	 <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Unit  </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span></span></div>
                </div>
         	 </div>
         	 <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Complaint Date</b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span></span></div>
                </div>
         	 </div>
         	 <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Location </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span></span></div>
                </div>
           </div>
           <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Building</b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span></span></div>
                </div>
           </div>
            </div>
            </form>
        </div>
    </div>
</div>
@endsection
@section('scripts')
@include('maintenance::complaint_js')
@endsection