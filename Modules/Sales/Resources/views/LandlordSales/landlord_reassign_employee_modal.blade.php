        <!-- The Modal -->
<div class="modal-dialog assign">
    <div class="modal-content">
  
    <!-- Modal Header -->
    <div class="modal-header">
      <h4 class="modal-title">Re Open</h4>
      <button type="button" class="close" data-dismiss="modal">&times;</button>
    </div>
    
    <!-- Modal body -->
    <div class="modal-body">
    
        <div class="row">
            <div class="col">
                <div class="card card-box salesSearchBox">
                <form action="{{  route('reAssignLandlord')}}" method="POST" id="reassign_modal" class="form-horizontal" enctype="multipart/form-data" data-toggle="validator">
                {{csrf_field()}}
                <input  type="hidden" class="form-control" name="enquiryid" value="{{$enquiry_id}}">
                <input  type="hidden" class="form-control" name="workflow_id" value="{{$workflow_id}}">
                <!-- <div class="sub-head">Building Type Details</div> -->
                <div class="dataSearchBox ">
                    
                        <div class="row">
                           <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="location_id">Role</label>
                                    <select class="form-control role" required name="role" required id="role">
                                        <option value="">Select Role</option> 
                                        @foreach($roles as $role)                           
                                        <option value="{{$role->id}}" >{{ucwords(str_replace('_', ' ',$role->name))}}</option>
                                        @endforeach                          
                                    </select>
                                </div>
                            </div> 
                             <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="location_id">User</label>
                                    <select class="form-control" required name="user_id" id="user_id" required>
                                        <option value="">Select User</option> 
                                                                 
                                    </select>
                                </div>
                            </div>  
                       <div class="w-100"></div>
                        <div class="col">
                          <div class="w-100"></div>
                              <button type="submit" class="btn btn-primary">SAVE</button>
                        </div>
                           
                      </div>
                    
                </div>
                <div class="clearfix"></div>
                </form>
                    
                </div>
            </div>
        </div>  

    </div>
    
    <!-- Modal footer -->
   <!-- <div class="modal-footer">
      <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
    </div> -->

    </div>
</div>
<script>
$(document).ready(function(){
    var users = <?php echo json_encode($users)?>;
    var length = users.length;
  
  $("#reassign_modal").validate();

  if(length == 0){
    $(document).on('change',".role", function()
      {
        
        var id       = $('#role').val();
        
        if(id){
            $.ajax
                ({
                    type: "POST",
                    url: "{{url('/processAssign/usersByRoleId')}}",
                    data: {"id":id,"_token": "{{ csrf_token() }}"},
                    cache: false,
                    dataType: "json",
                    success: function(data)
                    {
                      if(data.length > 0){
                        $('#user_id').empty();
                        $('#user_id').append('<option value="">'+ 'Select User' +'</option>')
                          $.each(data, function(key, value) {
                              $('#user_id').append('<option value="'+ value['id'] +'">'+ value['username'] +'</option>');
                        });
                      }
                      else{
                          $('#user_id').html('<option value="">No Data</option>');
                      }
                    } 
                });

              }
        
        else{
            $('#user_id').empty();
            $('#user_id').append('<option value="">'+ 'Select User' +'</option>')
        }
    });
  }else {
    $(document).on('change',".role", function()
    {
      var id  = $('#role').val();
      var workflow_id  = $('#workflow_id').val();
      var enquiryid  = $('#enquiryid').val();
      $.ajax({
              type: "POST",
              url: "{{url('/usersListByRole')}}",
              data: {"id":id,"workflow_id":workflow_id,"enquiryid":enquiryid,"_token": "{{ csrf_token() }}"},
              cache: false,
              dataType: "json",
              success: function(data)
              {
                if(data.length > 0){
                  $('#user_id').empty();
                  $('#user_id').append('<option value="">'+ 'Select User' +'</option>')
                    $.each(data, function(key, value) {
                        $('#user_id').append('<option value="'+ value['id'] +'">'+ value['username'] +'</option>');
                  });
                }
                else{
                    $('#user_id').html('<option value="">No User</option>');
                }
              } 
          });
    });
  }
});
</script>
