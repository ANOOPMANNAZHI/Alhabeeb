<div class="modal-dialog assign">
    <div class="modal-content">
  
    <!-- Modal Header -->
    <div class="modal-header">
      <h4 class="modal-title"> Penalty</h4>
      <button type="button" class="close" data-dismiss="modal">&times;</button>
    </div>
    
    <!-- Modal body -->
    <div class="modal-body">
    
   <div class="row">
    <div class="col">
        <div class="card card-box salesSearchBox">
        <form action="{{route('tenantTerminationPenaltyStore')}}" autocomplete="off" method="POST" id="renewal_note_modal" class="form-horizontal" enctype="multipart/form-data" data-toggle="validator">
        {{csrf_field()}}
        <input  type="hidden" class="form-control" name="tenant_contract_id" value="{{$tenantContract->id}}">
        <div class="dataSearchBox ">
            
                <div class="row">
                  <div class="col-sm-6">
                    <div class="form-group">
                        <label for="simpleFormCode">From Date </label>
                        <div class="p-relative">
                          <i class="fa fa-file-text-o icn-add" aria-hidden="true"></i>
                          <input type="date" name="tenant_penalty_start_date" id="tenant_penalty_start_date" class="form-control" required min="{{$tenantContract->tenant_contract_start_date->format('Y-m-d')}}">
                          
                          <input type="hidden" name="fromdate_max" value="{{$tenantContract->tenant_contract_valid_to_date->format('Y-m-d')}}" id="fromdate_max">
                        </div>
                    </div>
                  </div>
                  <div class="col-sm-6">
                    <div class="form-group">
                        <label for="simpleFormCode">To Date </label>
                        <div class="p-relative">
                          <i class="fa fa-file-text-o icn-add" aria-hidden="true"></i>
                          <input type="date" name="tenant_penalty_valid_to_date" id="tenant_penalty_valid_to_date" class="form-control tenant_penalty_valid_to_date" required >
                          
                          <input type="hidden" name="tenant_contract_rent" id="tenant_contract_rents" value="{{$tenantContract->tenant_contract_rent}}">
                          <input type="hidden" id="invoice_amount"  name="tenant_penalty_invoice_amt" value="">
                          <input type="hidden" name="todate_max" value="{{$tenantContract->tenant_contract_valid_to_date->format('Y-m-d')}}" id="todate_max">
                        </div>
                    </div>
                  </div>
                  
               <div class="w-100"></div>
                <div class="col">
                   <div class="w-100"></div>
                      <button type="submit" name="submit" value="submit" class="btn btn-primary">Save</button>
                      <!-- <button type="submit" value="skip"  name="skip" id="skip" class="btn btn-warning">Skip</button> -->
                   </div>
                   
              </div>
        
        </div>
        <div class="col-sm-12 text-right">
        
    </div>
        <div class="clearfix"></div>
        </form>
            
        </div>
    </div>
</div> 

    </div>
    
    <!-- Modal footer -->
    <div class="modal-footer">
      
    </div>

    </div>
</div>
