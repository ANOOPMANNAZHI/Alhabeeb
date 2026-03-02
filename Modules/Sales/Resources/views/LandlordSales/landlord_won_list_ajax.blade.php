                      @php
                      $curr_loop =  ($approveList->currentPage() == 1)? 1 :   ( (($approveList->currentPage() - 1) * $approveList->perPage()) + 1 );

                      if(\Request::input('curr_url'))
                      $curr_url =  \Request::input('curr_url');
                      else
                      $curr_url =  url()->current();                

                      @endphp
                      @forelse ($approveList as $list)
                      <tr>
                      	<td>{{ $curr_loop + $loop->index }}</td>

                      	<td><a href="{{route('contractApprovalListInfo', [$list->salesEnquiry->id,$list->salesEnquiry->work_flow_processes_code])}}" class="no-link">{{$list->salesEnquiry->sales_enquiry_no}}</a></td>
                      	<td><a href="{{route('contractApprovalListInfo', [$list->salesEnquiry->id,$list->salesEnquiry->work_flow_processes_code])}}" class="no-link">{{$list->salesEnquiry->sales_enquiry_name}}</a></td>
                      	<td><a href="{{route('contractApprovalListInfo', [$list->salesEnquiry->id,$list->salesEnquiry->work_flow_processes_code])}}" class="no-link">{{$list->salesEnquiry->landlordContract->buildingInfo->building_name}}</a></td>
                      	<td><a href="{{route('contractApprovalListInfo', [$list->salesEnquiry->id,$list->salesEnquiry->work_flow_processes_code])}}" class="no-link">{{$list->salesEnquiry->sales_email}}</a></td>
                      	<td><a href="{{route('contractApprovalListInfo', [$list->salesEnquiry->id,$list->salesEnquiry->work_flow_processes_code])}}" class="no-link">{{$list->salesEnquiry->sales_mobile_no}}</a></td>

                      	<td>
                      		<a href="{{route('contractApprovalListInfo', [$list->salesEnquiry->id,$list->salesEnquiry->work_flow_processes_code])}}" class="btn btn-tbl-view btn-xs">
                      			<i class="fa fa-eye"></i>
                      		</a>


                      	</td>
                      </tr>

                      @empty
                      <tr>
                      	<td colspan="6" align="center">
                      		<p>No Record</p>
                      	</td>
                      </tr>
                      @endforelse


                      @if(isset($request->ajax))	

                      <tr>

                      	<td colspan="5" id="pagination_ajax">                      	  

                      	{{$approveList->withPath($route)->appends(\Request::except(['page','_token','ajax']))->links()}}

                        <div class="pagination_info">
                                   @include('includes.pagination_info',['paginator' => $approveList])         
                               </div>

                      </td>                           

                  </tr>
                  @endif
