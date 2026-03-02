@extends('layouts.plms-app')
 

@section('content')
    <!-- start widget -->
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Service Report</div>
        </div>
        {{ Breadcrumbs::render('complaintStage.index') }}
    </div>
</div>
<a href="" class="btn btn-circle btn-primary  align-right">APPROVE</a> 
<a href="" class="btn btn-circle btn-primary  align-right">REJECT</a> 
<a href="" class="btn btn-circle btn-primary  align-right">SENT FOR LANDLORD APPROVAL</a> 
<div class="row" style="margin-top: 7%;">
    <div class="col-sm-12">
        <div class="card-box">
            <!-- <div class="card-head">
              <header>View Building Type</header>
            </div> -->
            <form action="#" id="form_sample_2" class="form-horizontal">
            <div class="card-body row"> 
                
          
          	<div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b> Work Order No  </b></div>
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
                  <div class="col-md-5"><b>Complaint No</b></div>
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
                  <div class="col-md-5"><b>Work Order Date</b></div>
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
           <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Status</b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span></span></div>
                </div>
           </div>
            <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Description</b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span></span></div>
                </div>
           </div>
           <div class="col-md-6 p-t-10">
                <div class="row float-right">
                  <a href=""><i class="fa fa-pencil"></i></a>
                </div>
           </div>
            </div>
            </form>
        </div>
    </div>
    <!-- start note -->
    <div class="col-sm-12">
      <div class="card-box">
          <div class="add-note-section">
            <form id="service_report-form" action=""" method="POST">
            {{csrf_field()}}
              <div class="col-sm-12">
                <div class="form-group">
                    <label for="simpleFormEmail">Note</label>
                    <textarea class="form-control" rows="2" required name="sales_notes" placeholder="Enter Note"></textarea>
                </div>
            </div>
            <div class="col-sm-2"><button type="submit" class="btn btn-primary">SAVE</button></div>
            </form>
            <div class="col-md-12">
                <div class="col p-0">
            <h4><strong></strong><div class="clr"></div></h4>
           
            <div class="table-responsive1">
                <table class="table" id="note_datatable">
                    <thead>
                        <tr style="background: #f5f5f5;">
                            <th>Note</th>
                            <th>Date & Time</th>
                            <th>Created By</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td></td>
                            <td class="d-t"></td>
                            <td></td>
                           
                        </tr>  
                        <tr>
                            <td colspan="3" align="center">
                            <p>No Record</p>
                           </td>
                        </tr>
                    </tbody>
                </table>

            </div>
        </div>
            </div>
        </div>
      </div>
    </div>
    <!-- end note-->
      <!-- ticket tabkle-->
    <div class="col-md-12">
    <div class="card-box">
    <div><b>Ticket</b></div>
      <div class="card-body">
      <table class="table display product-overview mb-30" id="">
                <thead>
                    <tr>
                        <th>Ticket</th>
                        <th>Category</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>LDHTY122</td>     
                        <td>Text</td>     
                        <td>Sample Text</td>     
                </tbody>
              </table>
       </div>
       </div>
    </div>
    <!--ends -->
    @endsection
@section('scripts')
@include('maintenance::complaint_js')
@endsection