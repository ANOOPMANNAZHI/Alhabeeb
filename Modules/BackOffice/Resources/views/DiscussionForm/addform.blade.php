<div class="modal-dialog assign  modal-lg">
    <div class="modal-content">
  
    <!-- Modal Header -->
    <div class="modal-header">
      <h4 class="modal-title"> Discussion Forum </h4>
      <button type="button" class="close" data-dismiss="modal">&times;</button>
    </div>
    
    <!-- Modal body -->
    <div class="modal-body">
       
   <div class="row">
    <div class="col">
        <div class="card card-box salesSearchBox">
        <form action="{{route('discussion.store',$tenant_contract_id)}}" autocomplete="off" method="POST" id="renewal_note_modal" class="form-horizontal" data-toggle="validator">
        {{csrf_field()}}       
        <div class="dataSearchBox ">            
                <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="simpleFormCode">Category</label>
                        <div class="p-relative">
                          <i class="fa fa-file-text-o icn-add" aria-hidden="true"></i>
                           <select class="form-control" name="discussion_category_id" id="discussion_category_id" required>
                            <option value="">Select Category</option>
                            @foreach($discussionCategories as $category) 
                            <option value="{{$category->id}}">{{$category->category}}</option>
                            @endforeach 
                          </select>
                        </div>
                    </div>
                  </div>

                  <div class="col-sm-6">
                    <div class="form-group">
                        <label for="simpleFormCode">Comment</label>
                        <div class="p-relative">
                          <i class="fa fa-file-text-o icn-add" aria-hidden="true"></i>
                          <input required class="form-control"   placeholder="Enter Comment" name="discussion"   data-rule-maxlength="200" data-msg-maxlength="Only allows 200 Characters"> 
                        </div>
                    </div>
                  </div>
                  
               <div class="w-100"></div>
                <div class="col">
                   <div class="w-100"></div>
                      <input type="submit"  name="submit" value="Save"  class="save btn btn-primary">                
                   </div>                   
              </div>
        
        </div>
        <div class="col-sm-12 text-right">
        
    </div>
        <div class="clearfix"></div>
        </form>
        <br>

        @if(count($discussionForums) > 0)
        <label for="simpleFormCode"><strong>Discussion</strong></label>
        <div class="table-responsive1">
              <table class="table display product-overview mb-30">
                <thead>
                    <tr>
                        <th>Sl No.</th>
                        <th>Category</th>
                        <th>Comment</th>
                         <th>Comment By </th>
                        <th>Date</th>                        
                    </tr>
                </thead>
                <tbody>
                  @foreach($discussionForums as $discussion)
                  <tr>
                    <td>{{$loop->iteration}}</td>
                    <td>{{$discussion->category->category}}</td>
                    <td>{{$discussion->discussion}}</td>
                    <td>{{($discussion->user->user_type == 'admin') ? ucwords($discussion->user->username) : ucwords($discussion->user->employee->employee_name) }}</td>
                    <td>{{$discussion->created_at->format('d/m/Y')}}</td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div> 
            @endif

        </div>
    </div>
</div> 

    </div>
    
    <!-- Modal footer -->
    <div class="modal-footer">
      
    </div>

    </div>
</div>
<!-- <script type="text/javascript">
  $(document).ready(function() {
   $("#renewal_note_modal").validate({     
      
      submitHandler: function(form) { 
       $('.save').prop('disabled', true);
        $(form).submit();
      }
    });
     });
</script> -->

