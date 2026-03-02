 @forelse ($unassigned_lists as $unassigned_list)
                                @php
                                  $current = 'leadAssign.index';
                                  Session::put('current', $current);                                 
                                @endphp
                                <tr>
                                    <td><input type = "checkbox" id = "switch-2" 
                                     class = "mdl-switch__input sub_chk" name="groupAssign[]" value="{{$unassigned_list->sales_enquiry_id}}">
                                     {{-- dd($unassigned_list->salesEnquiry) --}}
                                    {{--<input type="hidden" value="{{$unassigned_list->salesEnquiry->id}}" id="location{{$unassigned_list->salesEnquiry->id}}"> --}}
                                    </td> 
                                    <td>
										<a  class="no-link" title="Next Stage" href="{{route('leadAssign.nextStage',[$unassigned_list->sales_enquiry_id,$unassigned_list->sales_work_flow])}}" >
										{{$unassigned_list->sales_enquiry_no}}
										</a>
									</td>                                   
                                    <td>
										<a  class="no-link" title="Next Stage" href="{{route('leadAssign.nextStage',[$unassigned_list->sales_enquiry_id,$unassigned_list->sales_work_flow])}}" >
											{{$unassigned_list->created_at->format('d/m/Y')}}
										</a>
									</td>
                                    <td>
										<a  class="no-link" title="Next Stage" href="{{route('leadAssign.nextStage',[$unassigned_list->sales_enquiry_id,$unassigned_list->sales_work_flow])}}" >
											{{$unassigned_list->sales_enquiry_name}}
										</a>
									</td>
                                    <td>
										<a  class="no-link" title="Next Stage" href="{{route('leadAssign.nextStage',[$unassigned_list->sales_enquiry_id,$unassigned_list->sales_work_flow])}}" >
											{{$unassigned_list->sales_mobile_no}}
										</a>
									</td>
									 <td>
										<a  class="no-link" title="Next Stage" href="{{route('leadAssign.nextStage',[$unassigned_list->sales_enquiry_id,$unassigned_list->sales_work_flow])}}" >
											
											{{$unassigned_list->unit_type?? 'NA'}}
										</a>
									</td>
                                    <td>
										<a  class="no-link" title="Next Stage" href="{{route('leadAssign.nextStage',[$unassigned_list->sales_enquiry_id,$unassigned_list->sales_work_flow])}}" >
                      {{$unassigned_list->loc?? 'NA'}}
											
										</a>
									</td>
								
									<td>
									@if(isset($unassigned_list->sales_note))
										<a class="no-link" href="{{route('leadAssign.nextStage',[$unassigned_list->sales_enquiry_id,$unassigned_list->sales_work_flow])}}">
											{{ str_limit($unassigned_list->sales_note, $limit = 12, $end = '..') }}
										
										</a>
									@endif
									</td>
                                    <td>
                                    @can('edit_tenant_enquiry')
                                         <a title="Edit" href="{{route('enquiry.edit',$unassigned_list->sales_enquiry_id)}}" class="btn btn-tbl-edit btn-xs" >
                                            <i class="fa fa-pencil"></i>
                                        </a>
                                    @endcan
                                    <!--
                                    @can('view_enquiry')
                                        <a title="View" href="{{route('enquiry.show',$unassigned_list->sales_enquiry_id)}}" class="btn btn-tbl-view btn-xs" >
                                            <i class="fa fa-eye"></i>
                                        </a>
                                    @endcan -->
                                    
										<a title="Next Stage" href="{{route('leadAssign.nextStage',[$unassigned_list->sales_enquiry_id,$unassigned_list->sales_work_flow])}}" class="btn btn-tbl-view btn-xs ">
                                             <i class="fa fa-eye"></i>
                                        </a>
                                    @can('close_tenant_enquiry')
                                        <button type="submit" class="btn btn-tbl-delete closed" data-toggle="modal" data-target="#myModal" data-id = "CL" title="Close" datas-id ="{{$unassigned_list->sales_work_flow}}" datas-enid="{{$unassigned_list->sales_enquiry_id}}" data-backdrop="static" data-keyboard="false">
                                            <i class="fa fa-times-circle"></i></button>
                                    @endcan
                                        <!-- <input type="hidden" name="enquiryid" value="{{$unassigned_list->sales_enquiry_id}}" id="enquiryid">
                                        <input type="hidden" name="workflow_id" value="{{$unassigned_list->sales_work_flow}}" id="workflow_id"> -->
                                    @can('assign_tenant_enquiry')                                                                    
                                        <button title="Assign" type="button" class="btn btn-tbl-user btn-xs assignLead" data-toggle="modal" data-target="#myModal" data-id = "{{$unassigned_list->sales_enquiry_id}}" datas-id= "{{$unassigned_list->sales_work_flow}}" data-backdrop="static" data-keyboard="false">
                                            <i class="fa fa-user" aria-hidden="true"></i>
                                        </button>
                                    @endcan
                                      
                                        
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
                   							          
                            <tr class="pagination_ajax">	          
                             <td colspan="9" id="pagination_ajax"> 
                                                           					
                              {{$unassigned_lists->withPath($route)->appends(\Request::except(['page','_token']))->links()}}
                              

                               <div class="pagination_info">
                                   @include('includes.pagination_info',['paginator' => $unassigned_lists])         
                               </div>
							 	        
							</td>
						   </tr>
                            @endif

                                
              
