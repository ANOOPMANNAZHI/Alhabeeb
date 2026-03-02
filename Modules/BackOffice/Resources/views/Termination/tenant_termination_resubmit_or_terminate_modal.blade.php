<div class="modal-dialog assign">
    <div class="modal-content">
  
    <!-- Modal Header -->
    <div class="modal-header">
      <h4 class="modal-title">@if($action_key == 'RESUB') {{'Resubmit'}} @else {{'Terminate'}}  @endif</h4>
      <button type="button" class="close" data-dismiss="modal">&times;</button>
    </div>
    
    <!-- Modal body -->
    <div class="modal-body">
    
   <div class="row">
    <div class="col">
        <div class="card card-box salesSearchBox">
        <form action="{{route('terminateResubmitOrTerminateModalAction')}}" autocomplete="off" method="POST" id="comment_modal" class="form-horizontal sales_note_modal" enctype="multipart/form-data" data-toggle="validator">
        {{csrf_field()}}
        <input  type="hidden" class="form-control" name="workflow_id" value="{{$workflow_id}}">
        <input  type="hidden" class="form-control" name="contractId" value="{{$contractId}}">
        <input  type="hidden" class="form-control" name="terminatId" value="{{$terminatId}}">
        <input  type="hidden" class="form-control" name="action_key" value="{{$action_key}}">
        <!-- <div class="sub-head">Building Type Details</div> -->
        <div class="dataSearchBox ">
            
                <div class="row">
                  <div class="col-sm-12">
                    <div class="form-group">
                        <label for="simpleFormCode">Note </label>
                        <div class="p-relative">
                            <i class="fa fa-file-text-o icn-add" aria-hidden="true"></i>
                            <textarea class="form-control"  id="comment"  placeholder="Enter Note" name="comment"   data-rule-maxlength="200" data-msg-maxlength="Only allows 200 Characters" required></textarea>
                        </div>
                    </div>
                  </div>
               
                <div class="col">
                   <div class="w-100"></div>
                      <button type="submit" name="submit" value="submit" class="btn btn-primary comment_btn">Save</button>
                      <!-- <button type="submit" value="skip"  name="skip" id="skip" class="btn btn-warning">Skip</button> -->
                   </div>
                   
              </div>
                
        </div>
  
        </form>
            
        </div>
    </div>
</div> 

    </div>
    
    </div>
</div>
<script>
$(document).ready(function() {

  $(".comment_modal").validate({
      submitHandler: function(form) {
          $('.comment_btn').prop('disabled', true);
          form.submit();
       }
   });
 }); 
  
</script>
