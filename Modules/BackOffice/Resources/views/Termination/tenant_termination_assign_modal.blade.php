        <!-- The Modal -->
        <div class="modal-dialog assign">
            <div class="modal-content">
              
                <!-- Modal Header -->
                <div class="modal-header">
                  <h4 class="modal-title">Assigning</h4>
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
                                @forelse($useres as $user)
                                <tr>
                                    <td class="user-circle-img">
                                        @if($user->employee->employee_picture)
                                        
                                        <img src="{{asset('storage/app/'.$user->employee->employee_picture)}}"  width="50px" height="50px">
                                        @else
                                        <img src="{{asset('public/img/user_default.jpeg')}}"  width="50px" height="50px">
                                        @endif
                                    </td>
                                    <td>{{$user->employee->employee_name}} @if($user->employee->job_category_id) {{'-'}} {{$user->employee->job->job_category_name}} @endif</td><!-- employee->employee_name -->
                                    <td>
                                        <form action="{{ route('terminationStoreGroupAssign')}}" autocomplete="off" method="POST" id="leade_group_assign" class="form-horizontal" enctype="multipart/form-data" data-toggle="validator">
                                            {{csrf_field()}}
                                            <input type="hidden" name="user_id" id="user_id" value="{{$user->id}}">
                                            <input type="hidden" name="role" id="role" value="{{$user->default_role}}">
                                            <input type="hidden" name="workflow_id" value="{{$workflow}}">
                                            <input type="hidden" name="next_process_id" value="{{$next_process_id}}">
                                            @foreach($terminationArr as $termination)
                                            <input type="hidden" name="termination_id[]" id="termination_id" value="{{$termination}}">
                                            @endforeach
                                            <button type="submit" class="btn btn-warning">Assign</button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" align="center">
                                        <p>No Record</p>
                                    </td>
                                </tr>
                                @endforelse
                                <tr>
                                    <td class="user-circle-img">
                                     @if(Auth::user()->employee->employee_picture)
                                     
                                     <img src="{{asset('storage/app/'.Auth::user()->employee->employee_picture)}}"  width="50px" height="50px">
                                     @else
                                     <img src="{{asset('public/img/user_default.jpeg')}}"  width="50px" height="50px">
                                     @endif
                                     
                                 </td>
                                 <td>MySelf</td><!-- employee->employee_name -->
                                 <td>
                                    <form action="{{ route('terminationStoreGroupAssign')}}" autocomplete="off" method="POST" id="leade_group_assign" class="form-horizontal" enctype="multipart/form-data" data-toggle="validator">
                                        {{csrf_field()}}
                                        <input type="hidden" name="user_id" id="user_id" value="{{Auth::user()->id}}">
                                        <input type="hidden" name="role" id="role" value="{{Auth::user()->default_role}}">
                                        <input type="hidden" name="workflow_id" value="{{$workflow}}">
                                        <input type="hidden" name="next_process_id" value="{{$next_process_id}}">
                                        @foreach($terminationArr as $termination)
                                        <input type="hidden" name="termination_id[]" id="termination_id" value="{{$termination}}">
                                        @endforeach
                                        <button type="submit" class="btn btn-warning">Assign</button>
                                    </form>
                                </td>
                            </tr>
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
