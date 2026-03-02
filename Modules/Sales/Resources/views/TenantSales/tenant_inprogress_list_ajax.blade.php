        @php $count = 1; @endphp
                  @forelse ($inprogress_lists as $inprogress_list)
                  @php
                    $current = 'inprogressList';
                    Session::put('current', $current);                                 
                  @endphp
                  <tr>
                   <td><input type = "checkbox" id = "switch-2" class = "mdl-switch__input sub_chk" name="groupRe-Assign[]" value="{{$inprogress_list->sales_enquiry_id}}">
                     
                    <input type="hidden" value="{{$inprogress_list->sales_work_flow}}" id="wrk_flow">
                  </td>               
									<td><a class="no-link" href="{{route('leadAssign.nextStage',[$inprogress_list->sales_enquiry_id,$inprogress_list->sales_work_flow])}}">
                      {{$inprogress_list->sales_enquiry_no}}         
                  </a></td>
                  <td><a class="no-link" href="{{route('leadAssign.nextStage',[$inprogress_list->sales_enquiry_id,$inprogress_list->sales_work_flow])}}">
                     {{$inprogress_list->created_at->format('d/m/Y')}}    
                  </a></td>
                  <td> 
					         <a class="no-link" href="{{route('leadAssign.nextStage',[$inprogress_list->sales_enquiry_id,$inprogress_list->sales_work_flow])}}">
                   {{$inprogress_list->inprogress_days}} </a>
                  </td>
                  <td><a class="no-link" href="{{route('leadAssign.nextStage',[$inprogress_list->sales_enquiry_id,$inprogress_list->sales_work_flow])}}">{{$inprogress_list->sales_enquiry_name}}</a></td>
                  <td><a class="no-link" href="{{route('leadAssign.nextStage',[$inprogress_list->sales_enquiry_id,$inprogress_list->sales_work_flow])}}">{{$inprogress_list->sales_mobile_no}}</a></td>
                  <td><a class="no-link" href="{{route('leadAssign.nextStage',[$inprogress_list->sales_enquiry_id,$inprogress_list->sales_work_flow])}}">

                    {{$inprogress_list->unit_type}}
                    </a>
                  </td>
                  <td><a class="no-link" href="{{route('leadAssign.nextStage',[$inprogress_list->sales_enquiry_id,$inprogress_list->sales_work_flow])}}">
                     {{$inprogress_list->loc}}</a>
                  </td>
                  <td>
                  @if($inprogress_list->employee_name)
                  <a class="no-link" href="{{route('leadAssign.nextStage',[$inprogress_list->sales_enquiry_id,$inprogress_list->sales_work_flow])}}">
                  {{$inprogress_list->employee_name}}
                   </a>
                  @endif
                  </td>
                  <td>
                    @if($inprogress_list->sales_note != null)
                    <a class="no-link" href="{{route('leadAssign.nextStage',[$inprogress_list->sales_enquiry_id,$inprogress_list->sales_work_flow])}}">
                     
                      {{str_limit(trim($inprogress_list->sales_note), $limit = 12) }}  
                  
                  </a> @endif
                </td>
                  <td align="center">
                  
                      <button type="submit" class="btn btn-tbl-delete closed" data-toggle="modal" data-target="#myModal" data-id = "CL" title="Close" datas-id ="{{$inprogress_list->sales_work_flow}}" datas-enid="{{$inprogress_list->sales_enquiry_id}}" data-backdrop="static" data-keyboard="false">
                          <i class="fa fa-times-circle"></i></button>
                 
            
                  @can('edit_tenant_enquiry')
                       <a title="Edit" href="{{route('enquiry.edit',$inprogress_list->sales_enquiry_id)}}" class="btn btn-tbl-edit btn-xs" >
                          <i class="fa fa-pencil"></i>
                      </a>
                  @endcan                                 
                      <a  title="Inprogress" href="{{route('leadAssign.nextStage',[$inprogress_list->sales_enquiry_id,$inprogress_list->sales_work_flow])}}" class="btn btn-tbl-view btn-xs ">
                          <i class="fa fa-eye"></i>
                      </a>
                  @can('reassign_tenant')  
                      <button title="Re-Assign" type="button" class="btn btn-tbl-user btn-xs assignLead" data-toggle="modal" data-target="#myModal" data-id = "{{$inprogress_list->sales_enquiry_id}}" datas-id= "{{$inprogress_list->sales_work_flow}}" data-backdrop="static" data-keyboard="false">
                          <i class="fa fa-user" aria-hidden="true"></i>
                      </button>
                  @endcan   
                      
                  </td>
              </tr>
                                @php $count++; @endphp 
                                @empty
                                <tr>
                                    <td colspan="11" align="center">
                                    <p>No Record</p>
                                   </td>
                                </tr>
                                @endforelse
                                
                                
                                
                                 @if(isset($request->ajax))	
                                 
                                <tr>                   							          
                                  <td colspan="11" id="pagination_ajax"> 
						           		        {{$inprogress_lists->appends(\Request::except(['page','_token','ajax']))->links()}}

                                  <div class="pagination_info">
                                 @include('includes.pagination_info',['paginator' => $inprogress_lists])         
                             </div>
								        
								                 </td>                           
                  
                                 </tr>
                                 @endif
