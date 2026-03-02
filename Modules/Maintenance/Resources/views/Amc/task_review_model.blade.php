         
<div class="modal-dialog modal-lg assign">
  <div class="modal-content">

    <!-- Modal Header -->
    <div class="modal-header">
      <h4 class="modal-title">Remark </h4>
      <button type="button" class="close" data-dismiss="modal">&times;</button>
    </div>

    <!-- Modal body -->
    <div class="modal-body">
      <div class="row">
        <div class="col">
           <div class="card card-box salesSearchBox">
        <form method="post" autocomplete="off" id="maintenance-form" action="{{route('addReview')}}">
         {{csrf_field()}}
         <div class="dataSearchBox">
          
          <div class="row">
            <div class="col-sm-12">
              <div class="form-group">
                       <label for="amc_schedule_period_from_text">Remark<small class="textRed">*</small></label>
              <div class="p-relative">
               <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
               <textarea class="form-control" name="amc_schedule_task_remarks" required rows="4" cols="50"></textarea>
             </div>
            </div>
            <div class="w-100"></div>
       
            <div class="w-100"></div>
                <div class="dataSearchLabel w-100"></div>
                <input type="hidden" name="no" value="" id="No">
                <button type="submit" class="btn btn-primary margin">Submit</button>
            </div>
              
         
         </div>
        <input type="hidden" name="amc_task_id" value="{{$amc_task_id}}">
        <input type="hidden" name="url" id="url" value="{{ isset($amcTask)? old('url',$nowUrl):$nowUrl}}">
       
    </div>
   
      </div>
      </div>
  </form>

</div>
</div>
</div>
</div>