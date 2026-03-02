@extends('layouts.plms-app')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">


@section('content')
<!-- start widget -->
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">Add Rent Receipt</div>
    </div>
    <!-- breadcrum -->
  </div>
</div>

<div class="row">
  <div class="col-sm-12">
    <div class="card-box">

      <div class="card-head">
        <h4>
           <button type="button" title="Scan Qr Code" class="btn btn-circle btn-primary align-right scan" data-id="" datas-id="Tenant" data-toggle="modal" data-target="#myModal" data-placement="top" >Scan QrCode</button> 
        </h4>
         </div>
         <!--Agreement Section starts -->
         <div class="card card-box salesSearchBox">
            <div class="dataSearchBox">
                <div class="card-body row">
                   
                        <div class="col-lg-6 p-t-20"> 
                            <div class = "txt-full-width">
                                <h5 class="details"><b>Mobile No :  </b><span>
                                    <input type="text" name="" id="" class="form-controll"></span></h5>
                            </div>
                        </div>
                        <div class="col-lg-6 p-t-20"> 
                            <div class = "txt-full-width">
                                <h5 class="details"><b>Resident ID  :  </b><span>
                                    <input type="text" name="" id="" class="form-controll"></span></h5>
                            </div>
                        </div>
                </div>
            </div>
            <div class="sub-head">Payment Details</div>
            <div class="dataSearchBox">    
                <div class="card-body row">
               
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Receipt No :  </b><span><input type="text" name="" id="" class="form-controll"></span></h5>
                        </div>
                    </div>
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Receipt Date :  </b><span><input type="text" name="" id="" class="form-controll"></span></h5>
                        </div>
                    </div>
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Payment Method :  </b><span></span></h5>
                        </div>
                    </div>
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Bank :  </b><span><input type="text" name="" id="" class="form-controll"></span></h5>
                        </div>
                    </div>
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Cheque No :  </b><span><input type="text" name="" id="" class="form-controll"></span></h5>
                        </div>
                    </div>
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Amount :  </b><span><input type="text" name="" id="" class="form-controll"></span></h5>
                        </div>
                    </div>
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Comment :  </b><span><input type="text" name="" id="" class="form-controll"></span></h5>
                        </div>
                    </div>
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Remark :  </b><span><input type="text" name="" id="" class="form-controll"></span></h5>
                        </div>
                    </div>
                    
                    
                </div>
            </div>
            <div class="sub-head">Tenant Details</div>
            <div class="dataSearchBox">    
                <div class="card-body row">
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Building Name :  </b><span><input type="text" name="building_name" id="building_name" class="form-controll" readonly></span></h5>
                        </div>
                    </div>
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Building Code :  </b><span><input type="text" name="building_code" id="building_code" class="form-controll" readonly></span></h5>
                        </div>
                    </div>
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Unit No :  </b><span><input type="text" name="unit_name" id="unit_name" class="form-controll" readonly></span></h5>
                        </div>
                    </div>
                   <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Unit Code :  </b><span><input type="text" name="unit_code" id="unit_code" class="form-controll" readonly></span></h5>
                        </div>
                    </div>
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Tenant Name :  </b><span><input type="text" name="tenant_name" id="tenant_name" class="form-controll" readonly></span></h5>
                        </div>
                    </div>
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Tenant Code :  </b><span><input type="text" name="tenant_code" id="tenant_code" class="form-controll" readonly></span></h5>
                        </div>
                    </div>
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Agreement No :  </b><span><input type="text" name="agreement_no" id="agreement_no" class="form-controll" readonly></span></h5>
                        </div>
                    </div>
                    
                </div>
             </div>
            <div class="sub-head">Finacial Documentaion</div>
            <div class="dataSearchBox">    
                <div class="card-body row">
              
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Deposit Rent Amount :  </b><span></span></h5>
                        </div>
                    </div>
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Guarantee Cheque Amount:  </b><span> </span></h5>
                        </div>
                    </div>
                
                    
                </div>
            </div>

        </div>
<!--Payment ends -->
<div class="clearfix"></div>

<!--Remaining Invoices ends -->
</div>
</div>

<div class="modal" id="myModal">

</div>
@endsection
@section('scripts')
<script type="text/javascript">


$(document).on('click','.scan',function(){       

    $.ajax({
      method: 'POST', // Type of response and matches what we said in the route
      url: "{{route('keyScanForPaymentReceipt')}}", // This is the url we gave in the route
      data: {"_token": "{{ csrf_token() }}"}, // a JSON object to send back
      success: function(response){ // What to do if we succeed
          $("#myModal").html(response); 
      },
    });
    return true; 
         
});
/***************************************************************************************/
$(document).ready(function() {
   $("#myModal").on("hidden.bs.modal", function(){
        $("#myModal").html("");
        $(this).removeData('bs.modal');
    }); 

    
/***************************************************************************************/
});
</script>
@endsection