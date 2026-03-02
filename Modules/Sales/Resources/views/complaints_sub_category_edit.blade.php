         
<div class="modal-dialog modal-lg assign">
    <div class="modal-content">
  
    <!-- Modal Header -->
    <div class="modal-header">
      <h4 class="modal-title">Edit Ticket </h4>
      <button type="button" class="close" data-dismiss="modal">&times;</button>
    </div>
    
    <!-- Modal body -->
        <div class="modal-body">
            <div class="dataSearchBox panel-heading-lightblue">
                <h4>Select Complaint Category</h4>

                <div class="row bb-1 mb-3">
                    <div class="col-sm-4">
                        <label for="simpleFormEmail">Category</label>
                            <select class="form-control" name="work_id" id="workId" required>
                                <option value="">Select Category </option>
                                @foreach($works as $work)
                                <option {{ isset($work_id)? ((old('work_id',$work_id) == $work->id)? 'selected' : '') : 'selected'}} value="{{$work->id}}">{{$work->works_code}} </option>
                                @endforeach
                            </select>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label for="simpleFormEmail">Description</label>
                            <textarea class="form-control" rows="2" placeholder="Enter Description" name="checklist_desc" id="checklistDesc">{{ isset($checklist_desc)?  old('checklist_desc',$checklist_desc): old('checklist_desc')}}</textarea>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="dataSearchLabel w-100"></div>
                        <input type="hidden" name="no" value="{{ isset($no)?  old('no',$no): old('no')}}" id="No">
                            <button type="button" class="btn btn-primary dataSearchLabel update_complaint">Update</button>
                    </div>
                </div>
                  

            </div>
        </div>
    </div>
</div>