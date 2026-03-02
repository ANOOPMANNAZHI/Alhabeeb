@extends('layouts.plms-app')



@section('content')


                   <div class="page-bar">
                        <div class="page-title-breadcrumb">
                            <div class=" pull-left">
                                <div class="page-title">Landlord Enquiry</div>
                            </div>
                            {{Breadcrumbs::render('enquiry.landlord') }}
                        </div>
                    </div>
                   <!-- start widget -->
        
          <!-- end widget -->
<div class="row">
<div class="col">
<div class="card card-box salesSearchBox">





<div class="dataSearchBox">
    <form>
        <div class="row">
            <div class="col-sm-5">
                <div class="form-group">
                    <label for="simpleFormEmail">Mobile Number</label>
                    <input type="phone" class="form-control" id="simpleFormEmail" placeholder="Enter mobile number">
                </div>
            </div>
            <div class="col-sm-5">
                <div class="form-group">
                    <label for="simpleFormEmail"> Name</label>
                    <input type="text" class="form-control" id="simpleFormEmail" placeholder="Enter name">
                </div>
            </div>
            <div class="col-sm-2">
                <div class="dataSearchLabel w-100"></div>
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#myModal">Search</button>
            </div>
        </div>
    </form>
</div>



<div class="dataSearchBox">
                                                <div class="row">
                                                 <div class="table-responsive">
                                            <table class="table display product-overview mb-30" id="support_table5">
                                                <thead>
                                                    <tr>
                                                        <th>No</th>
                                                        <th>Customer Name</th>
                                                        <th>Company</th>
                                                        <th>Email</th>
                                                        <th>Phone</th>
                                                        <th>Enquiry Source</th>
                                                        
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>

                                                   @foreach($enquiries as $enquiry) 
                                                    <tr>
                                                        <td>1</td>
                                                        <td>{{$enquiry->sales_enquiry_name}}</td>
                                                        <td>{{$enquiry->sales_company_name}}</td>
                                                        <td>{{$enquiry->sales_email}}</td>
                                                        <td>{{$enquiry->sales_mobile_no}}</td>
                                                        <td>Trade Show</td>
                                                        
                                                        <td>
                                                         <a href="{{route('enquiry.show',$enquiry->id)}}" class="btn btn-tbl-view btn-xs">
                                                           <i class="fa fa-eye"></i>
                                                         </a>


                                                          <a title="Edit" href="{{route('enquiry.edit',$enquiry->id)}}" class="btn btn-tbl-edit btn-xs">
                                                                <i class="fa fa-pencil"></i>
                                                          </a>
                                                           
                                                        </td>
                                                    </tr>
                                                    @endforeach
          
                                                </tbody>
                                            </table>
                                        </div>
  
                                       </div>
                                     </div>

                                        </div>
                                    </div>
                                </div>





@endsection