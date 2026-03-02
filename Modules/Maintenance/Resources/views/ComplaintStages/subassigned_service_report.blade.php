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
<!-- table starts -->
<div class="row">
    <div class="col-sm-12">
        <div class="card-box">
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
                  <div class="col-md-5"><b>Location  </b></div>
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
                  <div class="col-md-5"><b>Description </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span></span></div>
                </div>
           </div>
           <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Assigned to</b></div>
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
            </div>
            </form>
        </div>
    </div>
</div>
<!-- ends-->
<!-- button starts-->
<div class="row float-right">
<div class="col-sm-12">
  <a href="" class="btn btn-circle btn-primary">UPDATE STATUS</a> 
  </div>
</div>
<!-- ends -->
<br/>
<br/>
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
                        <td>LDHTY122</td>     
                        <td>Text</td>     
                        <td>Sample Text</td>     
                </tbody>
              </table>
       </div>
       </div>
    </div>
    <!--ends -->
        <!--complaint category -->
      <div class="col-md-12">
     <div class="panel-body">
            <div class="dataSearchBox ">
                    <form autocomplete="off" action="" method="GET" id="leade_search" class="form-horizontal"  data-toggle="validator">
                         <div><h4>Select Complaint Category</h4></div>
                            <div class="row">
                                <div class="col-sm-5">
                                    <div class="form-group">
                                        <label for="fieldName">Item Name</label>
                                        <div class="p-relative">
                                       <!--   <i class="fa fa-address-book-o icn-add" aria-hidden="true"></i> -->
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
                                        <input required type="text" class="form-control" name="fieldValue" id="fieldValue" placeholder="Enter Quantity" value="">
                                    </div>
                                    </div>
                                </div>
                                <div class="col-sm-1">
                                    <div class="dataSearchLabel w-100 margin" style="margin-bottom: 31px;"></div>
                                    <button type="submit" class="btn btn-primary">ADD</button>
                                </div>
                            </div>
                    </form>
                </div>
        </div>
</div>
    <!-- ends-->
    <!-- ticket tabkle-->
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
        <div class="col-sm-8 float-left">
          <b>Tenant Name:Arun Kurian</b>
        </div>
        <div class="col-sm-4 float-right">
            <label for="simpleFormEmail"><b>Signanture</b></label>
            <label>:</label>
            <input type="text" name="signature">
        </div>
        <div class="col-sm-2"><button type="submit" class="btn btn-primary" style="    margin-top: 10px;">SUBMIT</button></div>
      </div>
      </div>
      </div>
    <!--ends -->
@endsection
@section('scripts')
@include('maintenance::complaint_js')
@endsection