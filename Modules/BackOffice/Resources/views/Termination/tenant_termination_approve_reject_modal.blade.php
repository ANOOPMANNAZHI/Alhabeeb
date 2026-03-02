<div class="modal-dialog assign">
    <div class="modal-content">
  
    <!-- Modal Header -->
    <div class="modal-header">
      <h4 class="modal-title"> {{$box_title}} Note</h4>
      <button type="button" class="close" data-dismiss="modal">&times;</button>
    </div>
    
    <!-- Modal body -->
    <div class="modal-body">
    
   <div class="row">
    <div class="col">
        <div class="card card-box salesSearchBox">
        <form action="{{route('terminateApproveOrRejectModalAction')}}" autocomplete="off" method="POST" id="renewal_note_modal" class="form-horizontal tenant_landlord_approve_reject_modal" enctype="multipart/form-data" data-toggle="validator">
        {{csrf_field()}}
        <input  type="hidden" class="form-control" name="terminationId" value="{{$terminatId}}">
        <input  type="hidden" class="form-control" name="status" value="{{$action_key}}">
        <div class="dataSearchBox ">
            
                <div class="row">
                  <div class="col-sm-12">
                    <div class="form-group">
                        <label for="simpleFormCode">Note </label>
                        <div class="p-relative">
                          <i class="fa fa-file-text-o icn-add" aria-hidden="true"></i>
                          <textarea class="form-control" required id="note"  placeholder="Enter Note" name="note"   data-rule-maxlength="200" data-msg-maxlength="Only allows 200 Characters"></textarea>
                        </div>
                    </div>
                  </div>
                  
               <div class="w-100"></div>
                <div class="col">
                   <div class="w-100"></div>
                      <button type="submit" name="submit" value="submit" class="btn btn-primary submit_note">Save</button>
                      <!--<button type="submit" value="skip"  name="skip" id="skip" class="btn btn-warning">Skip</button>-->
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

<script>
$(document).ready(function() {

  $(".tenant_landlord_approve_reject_modal").validate({
    submitHandler: function(form) {
          $('.submit_note').prop('disabled', true);
          return true;
       }
   });
 }); 
  

</script>