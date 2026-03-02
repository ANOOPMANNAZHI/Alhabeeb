<div class="modal-dialog assign">
    <div class="modal-content">
  
    <!-- Modal Header -->
    <div class="modal-header">
      <h4 class="modal-title">@if($action_key == 'CL') {{'Close Note'}} @else {{$stage}}  @endif</h4>
      <button type="button" class="close" data-dismiss="modal">&times;</button>
    </div>
    
    <!-- Modal body -->
    <div class="modal-body">
    
   <div class="row">
    <div class="col">
        <div class="card card-box salesSearchBox">
        <form action="{{  route('moveNextStage')}}" autocomplete="off" method="POST" id="sales_note_modal" class="form-horizontal" enctype="multipart/form-data" data-toggle="validator">
        {{csrf_field()}}
        <input  type="hidden" class="form-control" name="sales_id" value="{{$sales_id}}">
        <input  type="hidden" class="form-control" name="enquiryid" value="{{$enquiryid}}">
        <input  type="hidden" class="form-control" name="workflow_id" value="{{$workflow_id}}">
        <input  type="hidden" class="form-control" name="action_key" value="{{$action_key}}">
        <!-- <div class="sub-head">Building Type Details</div> -->
        <div class="dataSearchBox ">
            
                <div class="row">
                  <div class="col-sm-12">
                    <div class="form-group">
                        <label for="simpleFormCode">Note </label>
                        <div class="p-relative">
                        	<i class="fa fa-file-text-o icn-add" aria-hidden="true"></i>
							<textarea required class="form-control"  id="sales_lead_note_name"  placeholder="Enter Note" name="sales_lead_note_name"   data-rule-maxlength="200" data-msg-maxlength="Only allows 200 Characters">{{ isset($salesActivity)?  old('sales_lead_note_name',$salesActivity->sales_activities_name): old('sales_lead_note_name')}}</textarea>
                        </div>
                    </div>
                  </div>
                  
               <div class="w-100"></div>
                <div class="col">
                   <div class="w-100"></div>
                      <button type="submit" name="submit" value="submit" class="btn btn-primary">SAVE</button>
                      <!--<button type="submit" value="skip"  name="skip" id="skip" class="btn btn-warning">SKIP</button> -->
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
      <!-- <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button> -->
    </div>

    </div>
</div>
<script>
 /*   $("#sales_note_modal").validate();
  $(document).ready(function() {
    @if(\Auth::user()->default_role_name == 'sales_person')
      $("#sales_lead_note_name").attr('required',true) ; 
      $("#skip").hide();   
    @endif
  }); */
</script>
