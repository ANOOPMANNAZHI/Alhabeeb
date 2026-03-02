         
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
                <form autocomplete="off" action="{{ !isset($complaintChecklist)? route('complaints.store'): route('checklistUpdate',$complaintChecklist->id)}}" method="POST" id="checklist_form"" class="form-horizontal" enctype="multipart/form-data" data-toggle="validator">
                {{csrf_field()}} @if(isset($complaintChecklist)){{method_field('PUT')}}@endif   
                <div class="row bb-1 mb-3">
                    <div class="col-sm-4">
                        <label for="simpleFormEmail">Complaint Category</label>
                            <select class="form-control" required name="work_id" id="workId" required>
                                <option value="">Select Complaint Category </option>
                                @foreach($works as $work)
                                <option {{ isset($complaintChecklist)? ((old('work_id',$complaintChecklist->work_id) == $work->id)? 'selected' : '') : 'selected'}} value="{{$work->id}}">{{$work->works_code}} </option>
                                @endforeach
                            </select>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label for="simpleFormEmail">Description</label>
                            <textarea class="form-control"  rows="2" placeholder="Enter Description" name="checklist_desc" id="checklistDesc">{{ isset($complaintChecklist)?  old('checklist_desc',$complaintChecklist->checklist_desc): old('checklist_desc')}}</textarea>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="dataSearchLabel w-100"></div>
                            <input type="hidden" name="url" id="url" value="{{ isset($complaintChecklist)? old('url',$url):old('url')}}">
                            <button type="Submit" class="btn btn-primary dataSearchLabel update_complaint">Update</button>
                    </div>
                </div>
                </form>  

            </div>
        </div>
    </div>
</div>