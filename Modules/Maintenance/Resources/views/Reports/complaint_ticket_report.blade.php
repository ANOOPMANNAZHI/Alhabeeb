@extends('layouts.plms-app')
@section('css')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">
<link href="{{asset('public/css/formlayout.css')}}" rel="stylesheet" type="text/css" />
@endsection 
@section('content')
<!-- start widget -->
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">Maintenance  Report - (Open Tickets/ Closed Tickets)</div>
    </div>
    {{ Breadcrumbs::render('showComplaintTicketReport') }}
  </div>
</div>
<div class="row">
  <div class="col">
    <div class="card card-box salesSearchBox">
        @can('view_complaint_ticket_report') 
      <form action="{{route('complaintTicketReportPdf')}}" target="_blank" method="POST" id="form_sample_2" class="form-horizontal" enctype="multipart/form-data" data-toggle="validator">
        {{csrf_field()}} 
        <div class="dataSearchBox ">

          <div class="row">
             <div class="col-sm-6 val1">
            <div class="form-group">
              <label for="simpleFormEmail">From Date<small class="textRed">*</small></label>
              <div class="p-relative">
                <i class="fa fa-codepen icn-add" aria-hidden="true"></i>
                <input  type="date" class="form-control" id="start_date"  placeholder="Enter Date" name="start_date" required>

              </div>
            </div>
          </div>  
          <div class="col-sm-6 val1">
            <div class="form-group">
              <label for="simpleFormEmail">To Date<small class="textRed">*</small></label>
              <div class="p-relative">
                <i class="fa fa-codepen icn-add" aria-hidden="true"></i>
                <input  type="date" class="form-control" id="end_date"  placeholder="Enter Date" name="end_date" required>
              </div>
            </div>
          </div> 
            <div class="col-sm-6">
              <div class="form-group">
                <label for="filter_type">Filter<small class="textRed">*</small></label>
                <div class="p-relative">
                 <i class="fa fa-cubes icn-add" aria-hidden="true"></i>
                 <select class="form-control" id="filter_type"  name="filter_type" required>
                  <option value="">Select Filter</option>                
                  <option value="1">Open Tickets</option>                
                  <option value="2">Closed Tickets</option>               
                  <option value="3">Complaints Registration Category Wise</option>               
                  <option value="4">Complaints Registration Building Wise</option>               
                  <option value="5">Complaints Registration Contractor Wise</option>               
                  <option value="6">Complaints Registration Technician Wise</option>               
                  <option value="7">Complaints Registration VIP</option>               
                  <option value="8">Open Tickets Contractor Wise</option>               
                  <option value="9">Open Tickets Technician Wise</option>               
                  <option value="10">Complaint Registration Al Habib Maintained By Buildings</option>               
                  <option value="11">Complaint Registration Maintained By Landlord</option>               
                  <option value="12">Total no Of Complaints Registered</option>               
                  <option value="13">Closed Tickets within 24 hours</option>               
                  <option value="14">Closed Tickets within 48 hours</option>               
                  <option value="15">Closed Tickets more than 48 hours</option>

                  <option value="16">Complaint Registration Unit wise-In case of more than 10 tickets registered in a month Period</option>                
                </select> 
              </div>               
            </div>
          </div> 

         
          <div class="w-100"></div>
          <div class="col-sm-6 val2">
            <div class="form-group">
            <label for="employee_name">Value<small class="textRed">*</small></label>
              <div class="p-relative">
                <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
                <select class="form-control" name="category" id="category" style="display: none;">
                  <option value="">Select Category</option>
                  @foreach($works as $work)
                  <option value="{{$work->works_code}}">{{$work->works_code}}</option>
                  @endforeach
                </select>
                <select class="form-control" name="building_name" id="building_name" style="display: none;">
                  <option value="">Select Building</option>
                  @foreach($buildings as $building)
                  <option value="{{$building->building_name}}">{{$building->building_name}}</option>
                  @endforeach
                </select>
                 <select class="form-control" name="Contractor" id="Contractor" style="display: none;">
                  <option value="">Select SubContractor</option>
                  @foreach($vendors as $vendor)
                  <option value="{{$vendor->vendor_name}}">{{$vendor->vendor_name}}</option>
                  @endforeach
                </select>
                 <select class="form-control" name="Technician" id="Technician" style="display: none;">
                  <option value="">Select Technician</option>
                  @foreach($technicians as $technician)
                  <option value="{{$technician->employee->employee_name}}">{{$technician->employee->employee_name}}</option>
                  @endforeach
                </select>
                <input  type="text" class="form-control" id="unit_code"  placeholder="Enter Unit Code" name="unit_code" style="display: none;">
              </div>
            </div>
          </div> 
          <div class="col-sm-6">
              <div class="form-group">
                <label for="download_type">Download Type<small class="textRed">*</small>  </label>
                <div class="p-relative">
                 <i class="fa fa-cubes icn-add" aria-hidden="true"></i>
                 <select class="form-control" id="download_type"  name="download_type" required>
                  <option value="">Select Download Type</option>                
                  <option value="pdf">PDF</option>                
                  <option value="excel">Excel</option>               
                </select> 
              </div>               
            </div>
          </div>
 
            <div class="w-100"></div>
          <div class="col">
            <div class="w-100"></div>
            <button type="submit" name="search" class="btn btn-primary">Generate</button>
          </div>

        </div>

      </div>
      <div class="clearfix"></div>
    </form>
   @endcan 
  </div>
</div>
</div>
@endsection
@section('scripts')

@include('maintenance::reports.complaint_ticket_js')
  @endsection
