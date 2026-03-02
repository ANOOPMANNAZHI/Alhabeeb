@extends('layouts.plms-app')
 

@section('content')
    <!-- start widget -->
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Service Report</div>
        </div>
        {{ Breadcrumbs::render('complaintStage.show') }}
    </div>
</div>
<a href="" class="btn btn-circle btn-primary  align-right">UPDATE STATUS</a> 
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
                  <a href=""><i class="fa fa-pencil"></a>
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
    <div class="page-title">Ticket</div>
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
                        <td></td>     
                        <td></td>     
                        <td></td>     
                </tbody>
              </table>
       </div>
       </div>
    </div>
    <!--ends -->
     <!-- ticket tabkle-->
    <div class="col-md-12">
    <div class="card-box">
      <div class="card-body">
      <table class="table display product-overview mb-30" id="">
                <thead>
                    <tr>
                        <th>Item Name</th>
                        <th>Category</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td></td>     
                        <td></td>     
                        <td></td>     
                </tbody>
              </table>
       </div>
       </div>
    </div>
    <!--ends -->
    <!--complaint category -->
     <div class="panel-body">
            <div class="dataSearchBox ">
                    <form autocomplete="off" action="" method="GET" id="leade_search" class="form-horizontal"  data-toggle="validator">
                         <div class="page-title">Select Complaint Category</div>
                            <div class="row">
                                <div class="col-sm-5">
                                    <div class="form-group">
                                        <label for="fieldName">Item Name</label>
                                        <div class="p-relative">
                                         <i class="fa fa-address-book-o icn-add" aria-hidden="true"></i>
                                        <select class="form-control" required name="fieldName">
                                          <option value="">Select Item</option>
                                            <option value=""></option>
                                        </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-5">
                                    <div class="form-group">
                                        <label for="fieldValue"> Quantity</label>
                                        <div class="p-relative">
                                         <i class="fa fa-align-left icn-add" aria-hidden="true"></i>
                                        <input required type="text" class="form-control" name="fieldValue" id="fieldValue" placeholder="Enter Quantity" value="">
                                    </div>
                                    </div>
                                </div>
                                <div class="col-sm-1">
                                    <div class="dataSearchLabel w-100 margin"></div>
                                    <button type="submit" class="btn btn-primary">ADD</button>
                                </div>
                            </div>
                    </form>
                </div>
        </div>

    <!-- ends-->
     <!-- Last Part-->
    <div class="col-md-12">
    <div class="card-box">
      <div class="card-body">
      <table class="table display product-overview mb-30" id="">
                <thead>
                    <tr>
                        <th>Item Name</th>
                        <th>Quantity</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td></td>     
                        <td></td>     
                        <td></td>     
                </tbody>
              </table>
       </div>
       </div>
    </div>
    <!--ends -->
    <!-- tenant name-->
    <div class="col-md-12">
    <div class="card-box">
      <div class="card-body">
        <div class="col-sm-6 float-left">
          <p>Tenant Name:Arun Kurian</p>
        </div>
        <div class="col-sm-6 float-right">
            <label for="simpleFormEmail">Signanture</label>
            <input type="text" name="signature">
        </div>
      </div>
      </div>
      </div>
    <!--ends -->
</div>
@endsection
@section('scripts')
@include('maintenance::complaint_js')
@endsection