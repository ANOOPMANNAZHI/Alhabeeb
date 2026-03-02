              @php $perPage =  ($enquiries ->currentpage()-1) * $enquiries ->perpage() ; @endphp
                 @forelse ($enquiries as $enquiry)
                 @php
                  $current = 'enquiry.index';
                  Session::put('current', $current);
                  
                  @endphp 
                  <tr>
                      <td> 
              @if(array_search($enquiry->work_flow_processes_code,[101,201]) !== false) <a class="no-link" title="Edit" href="{{route('enquiry.edit',$enquiry->sale_enquiry_id)}}" >{{$enquiry->sales_enquiry_no}}</a>@else 
                <a class="no-link" href="{{route('enquiry.show',$enquiry->sale_enquiry_id)}}">{{$enquiry->sales_enquiry_no}}</a>
              @endif
                      </td>
                       <td> 
              @if(array_search($enquiry->work_flow_processes_code,[101,201]) !== false) <a class="no-link" title="Edit" href="{{route('enquiry.edit',$enquiry->sale_enquiry_id)}}" >{{$enquiry->created_at->format('d-m-Y')}}</a>@else 
                <a class="no-link" href="{{route('enquiry.show',$enquiry->sale_enquiry_id)}}">{{$enquiry->created_at->format('d/m/Y')}}</a>
              @endif
                      </td>
                     @if($type == 'tenant')
                      <td> 
              
                <a class="no-link" title="Edit" href="{{route('enquiry.edit',$enquiry->sale_enquiry_id)}}" >
                  {{$enquiry->fc_att}}
                </a>
              
                      </td>
                @endif
                      <td>
              <a class="no-link" title="Edit" href="{{route('enquiry.edit',$enquiry->sale_enquiry_id)}}" >
                {{$enquiry->cust}}</a>
        
                      </td>
                       <td>
               <a class="no-link" title="Edit" href="{{route('enquiry.edit',$enquiry->sale_enquiry_id)}}" >{{$enquiry->cust_no}}</a>
            
              </td>
               @if($type == 'tenant')
              <td>
              
              @if(array_search($enquiry->enquiry_flow,[101,201]) !== false) <a class="no-link" title="Edit" href="{{route('enquiry.edit',$enquiry->sale_enquiry_id)}}" >
                {{$enquiry->unit_type}}
              </a>@else 
                <a class="no-link" href="{{route('enquiry.show',$enquiry->sale_enquiry_id)}}">
                {{$enquiry->unit_type}}
              </a>
              @endif
                      </td>
                      <td>
              

                <a class="no-link" href="{{route('enquiry.show',$enquiry->sale_enquiry_id)}}"> 
                  {{$enquiry->loc}}
                </a>
  
            </td>
            @endif
                      <td>
              <span class="label {{$enquiry->enquiry_status_class}} label-mini">
                                {{($enquiry->refer_back)? 'Referred Back' : $enquiry->enquiryFlowStatus->work_flow_processes_name}}
                

              </span>
                      </td>                       
                      <td>

                       <a class="no-link" title="{{trim($enquiry->sales_note)}}" href="{{route('enquiry.edit',$enquiry->sale_enquiry_id)}}" >    
                         {{str_limit(trim($enquiry->sales_note), $limit = 12) }} 
                          
                         </a>
                      </td>

                      <td>
                        <a class="no-link" href="{{route('enquiry.show',$enquiry->sale_enquiry_id)}}">
                        @if(array_search($enquiry->employee_name,[101,201]) !== false)
                         {{$enquiry->employee_name }} 
                         @else 
                           {{$enquiry->employee_name }} 
                         @endif
                       </a>
                      </td>
                      
                      <td align="center">
                       <a href="{{route('enquiry.show',$enquiry->sale_enquiry_id)}}" class="btn btn-tbl-view btn-xs" title="View">
                         <i class="fa fa-eye"></i>
                       </a>

                        @if(array_search($enquiry->work_flow_processes_code,[101,201]) !== false)
                        <a title="Edit" href="{{route('enquiry.edit',$enquiry->sale_enquiry_id)}}" class="btn btn-tbl-edit btn-xs">
                              <i class="fa fa-pencil"></i>
                        </a>
                        
             @hasanyrole('call_center|super_admin')
               <a title="Reminder" href="{{route('enquiry.reminder',$enquiry->sale_enquiry_id)}}" class="btn btn-tbl-general btn-xs">
                  <i class="fa fa-bell"></i>
              </a>
             @endrole
                        
                        @endif
                     </td>
                  </tr>
                  
                  @empty
                  
                  <tr>
            <td colspan="10" align="center"> No Record </td>
                  
                  </tr>
                  
                  @endforelse
                  
                 
                  @if(isset($request->ajax))
                  <tr>                                          
                    <td colspan="10" id="pagination_ajax"> 
          {{$enquiries->appends(\Request::except(['page','_token','ajax']))->links()}}
 
                     <div class="pagination_info">
                        @include('includes.pagination_info',['paginator' => $enquiries])       
                     </div>


                   </td>                  
                  </tr>







                  @endif
