<div class="modal-dialog assign">
  <div class="modal-content">
    
    <!-- Modal Header -->
    <div class="modal-header">
      <h4 class="modal-title">
      @if($status == 1) 
      Approve
      @else
      Reject
      @endif 
      Note
      </h4>
      <button type="button" class="close" data-dismiss="modal">&times;</button>
    </div>
    <!-- Modal body -->
    <div class="modal-body">
     <div class="row">
      <div class="col">
        <div class="card card-box salesSearchBox">
          <form action="{{route('ServiceReportStatusLandlordModalAction')}}" autocomplete="off" method="POST" id="sales_note_modal" class="form-horizontal sales_note_modal" enctype="multipart/form-data" data-toggle="validator">
            {{csrf_field()}}
            <input  type="hidden" class="form-control" name="complaint_service_report_id" value="{{$complaint_service_report_id}}">
            <input  type="hidden" class="form-control" name="status" value="{{$status}}">
            <div class="dataSearchBox ">
              <div class="row">
                <div class="col-sm-12">
                  <div class="form-group">
                    <label for="simpleFormCode">Note </label>
                    <div class="p-relative">
                      <i class="fa fa-file-text-o icn-add" aria-hidden="true"></i>
                      <textarea required class="form-control"  id="desc"  placeholder="Enter Note" name="desc"   data-rule-maxlength="200" data-msg-maxlength="Only allows 200 Characters"></textarea>
                    </div>
                  </div>
                </div>
                <div class="w-100"></div>
                <div class="col">
                 <div class="w-100"></div>
                 <button type="submit" name="submit" value="submit" class="btn btn-primary close_note">Save</button>
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

