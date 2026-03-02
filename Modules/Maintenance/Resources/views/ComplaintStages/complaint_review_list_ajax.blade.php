                @forelse ($ComplaintEnquiries as $complaintWorkOrder)
                 @php
                 $current = 'complaintReviewList';
                 Session::put('current', $current); 

                 @endphp                              
                 <tr>
                 	<td><a class="no-link" href="{{route('complaintReview.view',$complaintWorkOrder->service_report_id)}}">{{$complaintWorkOrder->service_report_no}}</a> 
                 	<td><a class="no-link" href="{{route('complaintReview.view',$complaintWorkOrder->service_report_id)}}">{{$complaintWorkOrder->complaint_no}}</a>
                    </td>
                    <td><a class="no-link" href="{{route('complaintReview.view',$complaintWorkOrder->service_report_id)}}">{{$complaintWorkOrder->complaint_date->format('d/m/Y')}}</a></td>  
                    <td><a class="no-link" href="{{route('complaintReview.view',$complaintWorkOrder->service_report_id)}}">{{$complaintWorkOrder->complainer_name}}</a></td>
                    <td><a class="no-link" href="{{route('complaintReview.view',$complaintWorkOrder->service_report_id)}}">{!! substr($complaintWorkOrder->complaint_mob_no, 5) !!}</a></td>    
                    <td><a class="no-link" href="{{route('complaintReview.view',$complaintWorkOrder->service_report_id)}}">{{$complaintWorkOrder->building_name}}</a></td>
                    <td>@if(isset($complaintWorkOrder->unit_code))<a class="no-link" href="{{route('complaintReview.view',$complaintWorkOrder->service_report_id)}}">{{$complaintWorkOrder->unit_code}}</a>
                    @endif</td>
                    <td><a class="no-link" href="{{route('complaintReview.view',$complaintWorkOrder->service_report_id)}}">{{$complaintWorkOrder->locations_name}}</a></td>
                    
                   	<td>	                        	
                 	<span class="label {{$complaintWorkOrder->service_report_status_class}} label-mini"> {{ucwords($complaintWorkOrder->ServiceReportStatusName)}}</span>
                 	</td>
                 	<td>
                    @can('review_view') 
                 		<a href="{{route('complaintReview.view',$complaintWorkOrder->service_report_id)}}" title="View" class="btn btn-tbl-view btn-xs">
                            <i class="fa fa-eye "></i>
                        </a>
                    @endcan
                    @can('close') 
                 		<button title="Close" type="button" class="btn btn-tbl-delete btn-xs close_modal" data-toggle="modal" data-target="#myModal" data-id = "{{$complaintWorkOrder->complaint_enquiries_id}}" datas-id="" dataa_id = "" datassign-id="{{$complaintWorkOrder->assigned_to}}" data-backdrop="static" data-keyboard="false">
                          <i class="fa fa-times-circle "></i>
                        </button>
                    @endcan
                 		<!-- <a title="Reminder" href="" class="btn btn-tbl-general btn-xs">
                 			<i class="fa fa-bell"></i>
                 		</a> -->

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

                 	{{$ComplaintEnquiries->withPath($route)->appends(\Request::except(['page','_token','ajax']))->links()}}

                    <div class="pagination_info">
                                   @include('includes.pagination_info',['paginator' => $ComplaintEnquiries])         
                               </div>

                 </td>                           

             </tr>
            @endif 
