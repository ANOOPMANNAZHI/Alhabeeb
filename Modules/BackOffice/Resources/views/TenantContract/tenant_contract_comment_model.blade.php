<div class="modal-dialog assign">
    <div class="modal-content">
  
    <!-- Modal Header -->
    <div class="modal-header">
      <h4 class="modal-title"> Change Status </h4>
      <button type="button" class="close" data-dismiss="modal">&times;</button>
    </div>
    
    <!-- Modal body -->
    <div class="modal-body">
    
   <div class="row">
    <div class="col">
        <div class="card card-box salesSearchBox">
        <form action="{{route('tenantContractChangeStatusStore')}}" autocomplete="off" method="POST" id="renewal_note_modal" class="form-horizontal" enctype="multipart/form-data" data-toggle="validator" onsubmit="return checkBeforeSubmit()">
        {{csrf_field()}}
        <input  type="hidden" class="form-control" name="tenant_contract_id" value="{{$tenant_contract_id}}">
        <div class="dataSearchBox ">
            
                <div class="row">

                <div class="col-sm-12">
                    <div class="form-group">
                        <label for="simpleFormCode">Status</label>
                        <div class="p-relative">
                          <i class="fa fa-file-text-o icn-add" aria-hidden="true"></i>
                           <select class="form-control" name="tenant_contract_status" id="tenant_contract_status" required>
                            
							<option value="">Select Status</option>
							<option value="0">Normal</option>
                            @if($tenantContractInfo->tenant_contract_status ==1 && $tenantContractInfo->tenant_renewal_termination_status !=8)
                            <option value="2">On Hold</option>
                            <option value="3">No Maintenance</option>
                            <option value="4">Blacklisted</option>
                            @endif
                            <option value="5">Move to Legal</option>
                            @if($tenantContractInfo->tenant_contract_status ==1 && $tenantContractInfo->tenant_renewal_termination_status !=8)
                            <option value="7">Alert</option>
                            <option value="8">Observation</option>
                            <option value="9">Watch</option>
                            @endif
                          </select>
                        </div>
                    </div>
                  </div>

                  <div class="col-sm-12">
                    <div class="form-group">
                        <label for="simpleFormCode">Comment</label>
                        <div class="p-relative">
                          <i class="fa fa-file-text-o icn-add" aria-hidden="true"></i>
                          <textarea class="form-control"  id="renewal_notes"  placeholder="Enter Comment" name="tenant_contract_comment"   data-rule-maxlength="200" data-msg-maxlength="Only allows 200 Characters" required></textarea>
                        </div>
                    </div>
                  </div>
                  
               <div class="w-100"></div>
                <div class="col">
                   <div class="w-100"></div>
                      <button type="submit" name="submit" value="submit" class="btn btn-primary">Save</button>
                  <!--     <button type="submit" value="skip"  name="skip" id="skip" class="btn btn-warning">Skip</button> -->
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
<script type="text/javascript">
  var wasSubmitted = false;    
    function checkBeforeSubmit(){
      if(!wasSubmitted) {
        wasSubmitted = true;
        return wasSubmitted;
      }
      return false;
    }    
</script>
