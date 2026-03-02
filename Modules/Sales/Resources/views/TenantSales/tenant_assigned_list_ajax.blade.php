                 @forelse ($assigned_lists as $assigned_list)
                                @php
                                  $current = 'leadAssign.assignedList';
                                  Session::put('current', $current); 
                                                      
                                @endphp                                
                                <tr>
                                	<td><input type = "checkbox" id = "switch-2" 
                                     class = "mdl-switch__input sub_chk" name="groupRe-Assign[]" value="{{$assigned_list->sales_enquiry_id}}">
                                     
                                    <input type="hidden" value="{{$assigned_list->sales_work_flow}}" id="wrk_flow">
                                    </td>
									<td><a class="no-link" href="{{route('leadAssign.nextStage',[$assigned_list->sales_enquiry_id,$assigned_list->sales_work_flow])}}">{{$assigned_list->sales_enquiry_no}}</a></td>
                                    
                                    <td><a class="no-link" href="{{route('leadAssign.nextStage',[$assigned_list->sales_enquiry_id,$assigned_list->sales_work_flow])}}">{{$assigned_list->created_at->format('d/m/Y')}}</a></td>
                                    <td><a class="no-link" href="{{route('leadAssign.nextStage',[$assigned_list->sales_enquiry_id,$assigned_list->sales_work_flow])}}">{{$assigned_list->sales_enquiry_name}}</td>
                                    <td><a class="no-link" href="{{route('leadAssign.nextStage',[$assigned_list->sales_enquiry_id,$assigned_list->sales_work_flow])}}">{{$assigned_list->sales_mobile_no}}</a></td>
                                    <td><a class="no-link" href="{{route('leadAssign.nextStage',[$assigned_list->sales_enquiry_id,$assigned_list->sales_work_flow])}}">

										
										{{$assigned_list->unit_type?? 'NA'}}
										</a>
									</td>
                                    <td><a class="no-link" href="{{route('leadAssign.nextStage',[$assigned_list->sales_enquiry_id,$assigned_list->sales_work_flow])}}">
                                    	
                                      {{$assigned_list->loc?? 'NA'}}
                                    	</a></td>
                                    <td>
                                	@if($assigned_list->employee_name)
                  <a class="no-link" href="{{route('leadAssign.nextStage',[$assigned_list->sales_enquiry_id,$assigned_list->sales_work_flow])}}">
									 {{$assigned_list->employee_name}}
                  </a>
									@endif
									</td>
                <td>
                @if(isset($assigned_list->sales_note))
                <a class="no-link" href="{{route('leadAssign.nextStage',[$assigned_list->sales_enquiry_id,$assigned_list->sales_work_flow])}}">
                                 {{ str_limit($assigned_list->sales_note, $limit = 12, $end = '..') }} 
    
                </a>
                @endif
                </td>
                                    <td >
                                    
                                       <button type="submit" class="btn btn-tbl-delete closed" data-toggle="modal" data-target="#myModal1" data-id = "CL" title="Close" datas-id ="{{$assigned_list->sales_work_flow}}" datas-enid="{{$assigned_list->sales_enquiry_id}}" data-backdrop="static" data-keyboard="false" >
                                            <i class="fa fa-times-circle"></i></button>
                                    
                                    @can('edit_tenant_enquiry')
                                         <a title="Edit" href="{{route('enquiry.edit',$assigned_list->sales_enquiry_id)}}" class="btn btn-tbl-edit btn-xs" >
                                            <i class="fa fa-pencil"></i>
                                        </a>
                                    @endcan
                                        <!-- <input type="hidden" name="enquiryid" value="{{$assigned_list->sales_enquiry_id}}" id="enquiryid">
                                        <input type="hidden" name="workflow_id" value="{{$assigned_list->sales_work_flow}}" id="workflow_id"> -->                                  
                                        <a  title="Inprogress" href="{{route('leadAssign.nextStage',[$assigned_list->sales_enquiry_id,$assigned_list->sales_work_flow])}}" class="btn btn-tbl-view btn-xs ">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                    @can('reassign_tenant') 
                                        <button title="Re-Assign" type="button" class="btn btn-tbl-user btn-xs assignLead" data-toggle="modal" data-target="#myModal" data-id = "{{$assigned_list->sales_enquiry_id}}" datas-id= "{{$assigned_list->sales_work_flow}}" data-backdrop="static" data-keyboard="false">
                                            <i class="fa fa-user" aria-hidden="true"></i>
                                        </button>
                                    @endcan
                                   
                                    <!-- Temainder bell Visible For Every One except Sales Person -->
                                    @if($assigned_list->created_by == Auth::user()->id && Auth::user()->getRoles()[0] != 2)
                                     <a title="Reminder" href="{{route('leadAssign.reminder',$assigned_list->sales_enquiry_id)}}" class="btn btn-tbl-general btn-xs">
										  <i class="fa fa-bell"></i>
									 </a>
									@endif
                                    
                                    
                                    
                                        <form id="close-form" action="" method="POST">
                                             {{csrf_field()}}
                                            <input type="hidden" name="action_key" value="CL">
                                            <input type="hidden" name="workflow_id" value="{{$assigned_list->sales_work_flow}}">
                                            <input style="display: none;" type="submit">
                                        </form>
                                        
                                        
                                    </td>
                                </tr>
                                
                                @empty
                                <tr>
                                    <td colspan="10" align="center">
                                    <p>No Record</p>
                                   </td>
                                </tr>
                                @endforelse
                                
                                
                                
                                
                                 @if(isset($request->ajax))	
                                 
                                <tr>
									
					            			
                   							          
                                  <td colspan="5" id="pagination_ajax">
								        
								        {{$assigned_lists->appends(\Request::except(['page','_token','ajax']))->links()}}


                        <div class="pagination_info">
                                   @include('includes.pagination_info',['paginator' => $assigned_lists])         
                               </div>
								        
								        </td>                           
                  
                                 </tr>
                                 @endif
