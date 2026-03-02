                                @php 


                                $curr_loop =  ($lead->currentPage() == 1)? 1 :   ( (($lead->currentPage() - 1) * $lead->perPage()) + 1 );

                                if(\Request::input('curr_url'))
                                $curr_url =  \Request::input('curr_url');
                                else
                                $curr_url =  url()->current();                

                                @endphp 
                                @forelse ($lead as $list)
                                <tr>
                                    <!--<td>
                                    <input type = "checkbox" id = "switch-2" 
                                    class = "mdl-switch__input sub_chk" name="groupAssign[]" value="{{$list->id}}"></td> -->
                                    <td>{{ $curr_loop + $loop->index }}</td>
                                    <td><a class="no-link" href="{{route('landlordLeadAssign.nextStage',[$list->salesEnquiry->id,$list->work_flow_processes_code])}}">{{$list->salesEnquiry->sales_enquiry_no}}</a></td>

                                    <td><a class="no-link" href="{{route('landlordLeadAssign.nextStage',[$list->salesEnquiry->id,$list->work_flow_processes_code])}}">{{$list->salesEnquiry->sales_enquiry_name}}</a></td>
                                    <td><a class="no-link" href="{{route('landlordLeadAssign.nextStage',[$list->salesEnquiry->id,$list->work_flow_processes_code])}}">{{$list->salesEnquiry->sales_building_name}}</a></td>
                                    <td><a class="no-link" href="{{route('landlordLeadAssign.nextStage',[$list->salesEnquiry->id,$list->work_flow_processes_code])}}">{{$list->salesEnquiry->sales_email}}</a></td>
                                    <td><a class="no-link" href="{{route('landlordLeadAssign.nextStage',[$list->salesEnquiry->id,$list->work_flow_processes_code])}}">{{$list->salesEnquiry->sales_mobile_no}}</a></td>
                                    
                                    <td>
                                    	@can('edit_landlord_enquiry')
                                    	<a title="Edit" href="{{route('enquiry.edit',$list->salesEnquiry->id)}}" class="btn btn-tbl-edit btn-xs" title="Edit">
                                    		<i class="fa fa-pencil"></i>
                                    	</a>
                                    	@endcan
                                    	@can('close_landlord_enquiry')
                                    	<button type="submit" class="btn btn-tbl-delete closed" data-toggle="modal" data-target="#myModal" data-id = "CL" title="Close" data-backdrop="static" data-keyboard="false">
                                    		<i class="fa fa-times-circle"></i></button>
                                    		<input type="hidden" name="enquiryid" value="{{$list->salesEnquiry->id}}" id="enquiryid">
                                    		<input type="hidden" name="workflow_id" value="{{$list->work_flow_processes_code}}" id="workflow_id">
                                    		@endcan


                                    		<a  title="View" href="{{route('landlordLeadAssign.nextStage',[$list->salesEnquiry->id,$list->work_flow_processes_code])}}" class="btn btn-tbl-view btn-xs accept">

                                    			<i class="fa fa-eye" aria-hidden="true"></i>
                                    		</a> 

                                    	</td>
                                    </tr>

                                    @empty
                                    <tr>
                                    	<td colspan="7" align="center">
                                    		<p>No Record</p>
                                    	</td>
                                    </tr>
                                    @endforelse

                                    @if(isset($request->ajax))	
                                    <tr>

                                    <td colspan="6" id="pagination_ajax">	  

                                    {{$lead->withPath($route)->appends(\Request::except(['page','_token','ajax','route']))->links()}}

                                    <div class="pagination_info">
                                   @include('includes.pagination_info',['paginator' => $lead])         
                               </div>

                                    </td>                           

                                </tr>
                                @endif


                                
                                
                                
