	                     @php $count = 1; @endphp
                                @forelse ($documntation_lists as $documntation_list)
                                @php
                                  $current = 'documentationList';
                                  Session::put('current', $current);   
                                  $val = ''; $bul =''; $dat = '';

                                @endphp
                                
                                <tr>
                                  <td><input type = "checkbox" id = "switch-2" 
                                     class = "mdl-switch__input sub_chk" name="groupRe-Assign[]" value="{{$documntation_list->sales_enquiry_id}}">
                                     
                                    <input type="hidden" value="{{$documntation_list->sales_work_flow}}" id="wrk_flow">
                                    </td>
									               <td><a class="no-link" href="{{route('leadAssign.nextStage',[$documntation_list->sales_enquiry_id,$documntation_list->sales_work_flow])}}">{{$documntation_list->sales_enquiry_no}}</a></td>
                                    <td><a class="no-link" href="{{route('leadAssign.nextStage',[$documntation_list->sales_enquiry_id,$documntation_list->sales_work_flow])}}">{{$documntation_list->created_at->format('d/m/Y')}}</a></td>
                                   <td><a class="no-link" href="{{route('leadAssign.nextStage',[$documntation_list->sales_enquiry_id,$documntation_list->sales_work_flow])}}">{{$documntation_list->sales_enquiry_name}}</a>
                                    </td>
                                    <td><a class="no-link" href="{{route('leadAssign.nextStage',[$documntation_list->sales_enquiry_id,$documntation_list->sales_work_flow])}}">
                                     {{$documntation_list->agre_build?? 'NA'}} </a>
                                   </td>
                                    <td><a class="no-link" href="{{route('leadAssign.nextStage',[$documntation_list->sales_enquiry_id,$documntation_list->sales_work_flow])}}">
                                      {{$documntation_list->agre_unit?? 'NA'}}
                                    </a></td>
                                   
                                    <td><a class="no-link" href="{{route('leadAssign.nextStage',[$documntation_list->sales_enquiry_id,$documntation_list->sales_work_flow])}}">
                                       {{$documntation_list->agre_duration?? 'NA'}}
                                     
                                    </a>
                                  </td>
                                    <td>
									                 
                                    <a class="no-link" href="{{route('leadAssign.nextStage',[$documntation_list->sales_enquiry_id,$documntation_list->sales_work_flow])}}">
                                       {{$documntation_list->agre_start?? 'NA'}}
                                    
                                    </a>
                                    
                                	</td>
                                    <td><a class="no-link" href="{{route('leadAssign.nextStage',[$documntation_list->sales_enquiry_id,$documntation_list->sales_work_flow])}}">
                                      {{$documntation_list->agre_rent?? 'NA'}}
                                    
                                    </a></td>

                                    <td>
                                    @if($documntation_list->employee_name)
                                    <a class="no-link" href="{{route('leadAssign.nextStage',[$documntation_list->sales_enquiry_id,$documntation_list->sales_work_flow])}}">
                                      {{$documntation_list->employee_name}}
                                    </a>
                                    @endif
                                    </td>
                                   <td align="center">

                                    
                                        <button type="submit" class="btn btn-tbl-delete closed" data-toggle="modal" data-target="#myModal" data-id = "CL" title="Close" datas-id ="{{$documntation_list->sales_work_flow}}" datas-enid="{{$documntation_list->sales_enquiry_id}}" data-backdrop="static" data-keyboard="false">
                                            <i class="fa fa-times-circle"></i></button>
                                   
                                       <!--  <input type="hidden" name="enquiryid" value="{{$documntation_list->sales_enquiry_id}}" id="enquiryid">
                                        <input type="hidden" name="workflow_id" value="{{$documntation_list->sales_work_flow}}" id="workflow_id"> -->
                                    @can('edit_enquiry')
                                         <a title="Edit" href="{{route('enquiry.edit',$documntation_list->sales_enquiry_id)}}" class="btn btn-tbl-edit btn-xs" >
                                            <i class="fa fa-pencil"></i>
                                        </a>
                                    @endcan                                    
                                        <a  title="Documentation" href="{{route('leadAssign.nextStage',[$documntation_list->sales_enquiry_id,$documntation_list->sales_work_flow])}}" class="btn btn-tbl-view btn-xs ">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                    @can('reassign_tenant')
                                        <button title="Re-Assign" type="button" class="btn btn-tbl-user btn-xs assignLead" data-toggle="modal" data-target="#myModal" data-id = "{{$documntation_list->sales_enquiry_id}}" datas-id= "{{$documntation_list->sales_work_flow}}" data-backdrop="static" data-keyboard="false">
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
                     		        
								     {{$documntation_lists->withPath($route)->appends(\Request::except(['page','_token','ajax']))->links()}}

        <div class="pagination_info">
          @include('includes.pagination_info',['paginator' => $documntation_lists])         
        </div>
                      						        
								    </td>
                    </tr>
                     @endif
