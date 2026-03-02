        <!-- The Modal -->
<div class="modal-dialog assign">
    <div class="modal-content">
  
    <!-- Modal Header -->
    <div class="modal-header">
      <h4 class="modal-title">Enquiry Assigning</h4>
      <button type="button" class="close" data-dismiss="modal">&times;</button>
    </div>
    
    <!-- Modal body -->
    <div class="modal-body">
    
        <div class="table-wrap">
            <div class="table-responsive">
                
                <table class="table display product-overview mb-30" id="support_table5">
                    <thead>
                        <tr>
                            <th>Photo</th>
                            <th>Name</th>
                            <th>Action</th>                                                              
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        <tr>
                            <td class="user-circle-img">
                                @if($user->employee->employee_picture)
                    
                                  <img src="{{asset('storage/app/'.$user->employee->employee_picture)}}"  width="50px" height="50px">
                                  @else
                                    <img src="{{asset('public/img/user_default.jpeg')}}"  width="50px" height="50px">
                                @endif
                           </td>
                            <td>{{$user->employee->employee_name}}</td><!-- employee->employee_name -->
                            <td>
                             <button  data-user="{{$user->id}}"   data-role="{{$user->default_role}}"   class="btn btn-warning save-assign" >Assign</button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" align="center">
                            <p>No Record</p>
                           </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                
            </div>
        </div> 

    </div>
    
    <!-- Modal footer -->
    <div class="modal-footer">
      <!-- <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button> -->
    </div>

    </div>
</div>


 <form action="{{ route('leadAssign.store')}}" autocomplete="off" method="POST" id="leade_group_assign" class="form-horizontal reassign_modal"  data-toggle="validator">
     {{csrf_field()}}
    <input type="hidden" name="action_key" value="AS">
    <input type="hidden" name="user_id" id="user_id" value="">
    <input type="hidden" name="role" id="role" value="">
    <input type="hidden" name="workflow_id" value="101">
    <input type="hidden" name="enquiryId" id="enquiryId" value="{{$enquiry_id}}">
    <input type="hidden" name="redirectPage" id="redirectPage" value="{{$page}}">    
</form>



<script>
$(document).ready(function() {
	
 $('.save-assign').click(function(){
    
    var user_id = $(this).attr('data-user');
    var role = $(this).attr('data-role');

    $('#user_id').val(user_id);
    $('#role').val(role);

    $('.save-assign').prop('disabled', true);
    $('#leade_group_assign').submit();    

  })

});

</script>
