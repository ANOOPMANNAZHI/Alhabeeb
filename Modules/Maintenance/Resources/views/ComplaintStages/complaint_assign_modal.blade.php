        <!-- The Modal -->
<div class="modal-dialog assign">
    <div class="modal-content">
  
    <!-- Modal Header -->
    <div class="modal-header">
      <h4 class="modal-title">Ticket Assigning</h4>
      <button type="button" class="close" data-dismiss="modal">&times;</button>
    </div>
    
    <!-- Modal body -->
    <div class="modal-body">
    
        <div class="row">
            <div class="col">
                <div class="card card-box salesSearchBox">
                <form action="{{route('storeGroupAssign')}}" method="POST" id="assign_modal" class="form-horizontal" enctype="multipart/form-data" data-toggle="validator">
                {{csrf_field()}}

                <input  type="hidden" class="form-control" name="next_process_id" id="next_process_id" value="{{$next_process_id}}">
                <input  type="hidden" class="form-control" name="complaint_id" id="complaint_id" value="{{$complaint_id}}">
                <input  type="hidden" class="form-control" name="contractor_type" id="contractor_type" value="">
                @foreach($ticketArr as $enq)
                  <input type="hidden" name="ticketId[]" id="ticketId" class="ticketId" value="{{$enq}}">
                @endforeach
                <div class="sub-head"></div>
                <div class="dataSearchBox ">
                      <h4> </h4>
                        <div class="row">
                           <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="location_id">Role</label>
                                    <select class="form-control role" required name="role" required id="role">
                                        <option value="">Select Role</option> 
                                        @foreach($rolees as $role)     
                                                              
											                   <option value="{{$role->id}}" >{{ucwords(str_replace('_', ' ',$role->name))}}</option>
									    
                                        @endforeach 
                                        <option value="501" >{{ucwords('sub contractor')}}</option>                         
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
                              <button type="submit" class="btn btn-primary">Assign</button>
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
    <div class="modal-footer">
      <!-- <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button> -->
    </div>

    </div>
</div>
<script>
$(document).ready(function(){
    
  
  $("#reassign_modal").validate();
  
  
    $(document).on('change',".role", function()
    {
      var id  = $('#role').val();
      if(id != ""){
      	if(id != 501){
	        var workflow_id  = $('#next_process_id').val();
	        var complaint_id  = $('#complaint_id').val();
	        var enquiryid  = $('#enquiryid').val();
	        var ticket_id  = $('.ticketId:first').val();
	        
	        $.ajax({
	            type: "POST",
	            url: "{{route('getUserByRole')}}",
	            data: {"id":id,"workflow_id":workflow_id,'complaint_id':complaint_id,"enquiryid":enquiryid,"ticket_id":ticket_id,"_token": "{{ csrf_token() }}"},
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
	                $("#contractor_type").val(0);
	              }
	              else{
	                  $('#user_id').html('<option value="">No User</option>');
	              }
	            } 
	        });
	      }else {
	        $.ajax({
	            type: "POST",
	            url: "{{route('getSubContractor')}}",
	            data: {'complaint_id':complaint_id,"enquiryid":enquiryid,"ticket_id":ticket_id,"_token": "{{ csrf_token() }}"},
	            cache: false,
	            dataType: "json",
	            success: function(data)
	            {
	              if(data.length > 0){
	                $('#user_id').empty();
	                $('#user_id').append('<option value="">'+ 'Select Contractor' +'</option>')
	                  $.each(data, function(key, value) {
	                      $('#user_id').append('<option value="'+ value['id'] +'">'+ value['vendor_name'] +'</option>');
	                });
	                $("#contractor_type").val(1);
	              }
	              else{
	                  $('#user_id').html('<option value="">No Contractor</option>');
	              }
	            } 
	        });

	      }
      }else{
      	$('#user_id').html('<option value="">No User</option>');
      }
      
      
    });

});
</script>
