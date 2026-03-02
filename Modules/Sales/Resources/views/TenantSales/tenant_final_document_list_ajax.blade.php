@php $count = 1; @endphp
                                @forelse ($finalDocumentationLists as $approval_list)
                                @php
                                  $current = 'finalDocumentationList';
                                  Session::put('current', $current); 
                                  $val = ''; $bul =''; $dat = '';                                
                                @endphp
                                <tr>
                                    <td><a class="no-link" href="{{route('leadAssign.nextStage',[$approval_list->sales_enquiry_id,$approval_list->sales_work_flow])}}" >
                                      {{$approval_list->sales_enquiry_no}}</a></td>
                                    <td><a class="no-link" href="{{route('leadAssign.nextStage',[$approval_list->sales_enquiry_id,$approval_list->sales_work_flow])}}" >{{$approval_list->created_at->format('d/m/Y')}}</a></td>
                                    <td><a class="no-link" href="{{route('leadAssign.nextStage',[$approval_list->sales_enquiry_id,$approval_list->sales_work_flow])}}" >{{$approval_list->sales_enquiry_name}}</a></td>
                                     <td><a class="no-link" href="{{route('leadAssign.nextStage',[$approval_list->sales_enquiry_id,$approval_list->sales_work_flow])}}" >
                                      {{$approval_list->agre_build}}</a></td>
                                      <td><a class="no-link" href="{{route('leadAssign.nextStage',[$approval_list->sales_enquiry_id,$approval_list->sales_work_flow])}}" >{{$approval_list->agre_unit}}</a></td>
                                      
                                    <td><a class="no-link" href="{{route('leadAssign.nextStage',[$approval_list->sales_enquiry_id,$approval_list->sales_work_flow])}}" >
                                      {{$approval_list->agre_start}}</a></td>
                                    <td><a class="no-link" href="{{route('leadAssign.nextStage',[$approval_list->sales_enquiry_id,$approval_list->sales_work_flow])}}" >
                                      {{number_format($approval_list->agre_rent,3,",","")}}
                                    </a></td>
                                    <td align="center">
                                    @can('close_tenant_enquiry')    
                                        <button type="submit" class="btn btn-tbl-delete closed" data-toggle="modal" data-target="#myModal" data-id = "CL" title="Close" datas-id ="{{$approval_list->sales_work_flow}}" datas-enid="{{$approval_list->sales_enquiry_id}}"  data-backdrop="static" data-keyboard="false">
                                            <i class="fa fa-times-circle"></i></button>
                                    @endcan
                                    @hasrole('super_admin')
                                    @can('edit_tenant_enquiry')
                                         <a title="Edit" href="{{route('enquiry.edit',$approval_list->sales_enquiry_id)}}" class="btn btn-tbl-edit btn-xs" >
                                            <i class="fa fa-pencil"></i>
                                        </a>
                                    @endcan
                                    @endhasrole
                                   
                                       <!--  <input type="hidden" name="enquiryid" value="{{$approval_list->sales_enquiry_id}}" id="enquiryid">
                                        <input type="hidden" name="workflow_id" value="{{$approval_list->sales_work_flow}}" id="workflow_id"> -->                                  
                                        <a  title="Documentation" href="{{route('leadAssign.nextStage',[$approval_list->sales_enquiry_id,$approval_list->sales_work_flow])}}" class="btn btn-tbl-view btn-xs ">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                    @can('reassign_tenant')
                                       <button title="Re-Assign" type="button" class="btn btn-tbl-user btn-xs assignLead" data-toggle="modal" data-target="#myModal" data-id = "{{$approval_list->sales_enquiry_id}}" datas-id= "{{$approval_list->sales_work_flow}}" data-backdrop="static" data-keyboard="false">
                                            <i class="fa fa-user" aria-hidden="true"></i>
                                        </button>
                                    @endcan   
                                    </td>
                                </tr>
                                @php $count++; @endphp 
                                @empty
                                <tr>
                                    <td colspan="9" align="center">
                                    <p>No Record</p>
                                   </td>
                                </tr>
                                @endforelse
                                
                                
                                
                     @if(isset($request->ajax))	
                                 
                        <tr>      
                         <td colspan="5" id="pagination_ajax">								        
								        {{$finalDocumentationLists->withPath($route)->appends(\Request::except(['page','_token','ajax']))->links()}}

                         <div class="pagination_info">
                                   @include('includes.pagination_info',['paginator' => $finalDocumentationLists])         
                               </div>								        
								        </td>
                        </tr>
                     @endif
