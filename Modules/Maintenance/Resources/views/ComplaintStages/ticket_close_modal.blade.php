<div class="modal-dialog assign">
    <div class="modal-content">
  
    <!-- Modal Header -->
    <div class="modal-header">
      <h4 class="modal-title">Close Note</h4>
      <button type="button" class="close" data-dismiss="modal">&times;</button>
    </div>
    
    <!-- Modal body -->
    <div class="modal-body">
    
   <div class="row">
    <div class="col">
        <div class="card card-box salesSearchBox">
        <form action="{{route('ticketCloseModalAction')}}" autocomplete="off" method="POST" id="sales_note_modal" class="form-horizontal sales_note_modal" enctype="multipart/form-data" data-toggle="validator">
        {{csrf_field()}}
        <input  type="hidden" class="form-control" name="complaint_id" value="{{$complaint_id}}">
        
        @foreach($complaint_checklist_id as $checklist)
        <input  type="hidden" class="form-control" name="complaint_checklist_id[]" value="{{$checklist}}">
        @endforeach
        
        <input  type="hidden" class="form-control" name="stage_id" value="{{$stage_id}}">
     
        <div class="dataSearchBox ">
            
                <div class="row">
                  <div class="col-sm-12">
                    <div class="form-group">
                        <label for="simpleFormCode">Note </label>
                        <div class="p-relative">
                          <i class="fa fa-file-text-o icn-add" aria-hidden="true"></i>
              <textarea class="form-control"  id="complaint_processes_note"  placeholder="Enter Note" name="complaint_processes_note"   data-rule-maxlength="200" data-msg-maxlength="Only allows 200 Characters" required>{{ isset($complaint_processes_note)?  old('complaint_processes_note'): old('complaint_processes_note')}}</textarea>
                        </div>
                    </div>
                  </div>
                  
               <div class="w-100"></div>
                <div class="col">
                   <div class="w-100"></div>
                      <button type="submit" name="submit" value="submit" class="btn btn-primary close_note">Save</button>
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
<script>
$(document).ready(function() {

  $(".sales_note_modal").validate({
    submitHandler: function(form) {
          $('.close_note').prop('disabled', true);
          form.submit();
     }
   });
 }); 
</script>
