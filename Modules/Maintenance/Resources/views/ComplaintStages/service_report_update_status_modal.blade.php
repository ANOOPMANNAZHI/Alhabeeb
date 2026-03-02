         
<div class="modal-dialog modal-lg assign">
    <div class="modal-content">
  
    <!-- Modal Header -->
    <div class="modal-header">
      <h4 class="modal-title">Update @if($symbol == null) {{'Status'}} @else {{'Description'}} @endif </h4>
      <button type="button" class="close" data-dismiss="modal">&times;</button>
    </div>
    
    <!-- Modal body -->
        <div class="modal-body">
            <div class="dataSearchBox panel-heading-lightblue">
               <!--  <h4>Select Complaint Category</h4> -->
                <form autocomplete="off" action="{{ !isset($serviceReport)? '': route('storeServiceReportStatusUpdate',$serviceReport->id)}}" method="POST" id="status_form"" class="form-horizontal" enctype="multipart/form-data" data-toggle="validator">
                {{csrf_field()}} @if(isset($serviceReport)){{method_field('PUT')}}@endif   
                <div class="row bb-1 mb-3">
                    @if($symbol == null)
                    <div class="col-sm-5">
                        <label for="simpleFormEmail">Status</label>
                            <select class="form-control" name="status" id="status" required>
                                <option value="">Select Status </option>

                                <!-- <option {{ (old('status',isset($serviceReport)?  $serviceReport->complaint_assign_status : '') == 0)?  'selected':''  }} value="0">{{'Open'}}</option> -->
                                <option {{ (old('status',isset($serviceReport)?  $serviceReport->complaint_assign_status : '') == 1)?  'selected':''  }} value="1">{{'In Progress '}}</option>
                                <option {{ (old('status',isset($serviceReport)?  $serviceReport->complaint_assign_status : '') == 2)?  'selected':''  }} value="2">{{'Attended'}}</option>
                                <option {{ (old('status',isset($serviceReport)?  $serviceReport->complaint_assign_status : '') == 3)?  'selected':''  }} value="3">{{'Completed'}}</option>
                                @unlessrole('technician')
                                <option {{ (old('status',isset($serviceReport)?  $serviceReport->complaint_assign_status : '') == 4)?  'selected':''  }} value="4">{{'Closed'}}</option>
                                @endunlessrole
                            </select>
                    </div>
                    @endif
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label for="simpleFormEmail">Description</label>
                            <textarea class="form-control" rows="2" placeholder="Enter Description" name="note" id="note">@if($symbol != null){{ isset($serviceReport)? old('note',$serviceReport->complaint_assign_note):old('note')}}@endif</textarea>
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="dataSearchLabel w-100"></div>
                            @foreach($tickets as $checklist)
                                <input  type="hidden" class="form-control" name="complaint_checklist_id[]" value="{{$checklist}}">
                            @endforeach
                            <input type="hidden" name="complaint_id" id="complaint_id" value="{{ isset($serviceReport)? old('complaint_id',$complaint_id):old('complaint_id')}}">
                            <input type="hidden" name="url" id="url" value="{{ isset($serviceReport)? old('url',$nowUrl):old('url')}}">
                            <button type="Submit" class="btn btn-primary dataSearchLabel update_complaint">Update</button>
                    </div>
                </div>
                </form>  

            </div>
        </div>
    </div>
</div>
