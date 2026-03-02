                 @forelse ($ComplaintEnquiries as $complaintEnquiry)
                 @php
                 $current = 'complaintClosedList';
                 Session::put('current', $current); 

                 @endphp                            
                 <tr>
                    <td><a class="no-link" href="{{route('complaintClosed.view',$complaintEnquiry->id)}}">{{$complaintEnquiry->complaint_no}}</a></td>  
                    <td><a class="no-link" href="{{route('complaintClosed.view',$complaintEnquiry->id)}}">{{$complaintEnquiry->complaint_date->format('d/m/Y')}}</a></td>
                    <td><a class="no-link" href="{{route('complaintClosed.view',$complaintEnquiry->id)}}">{{$complaintEnquiry->complainer_name}}</a></td>
                    <td><a class="no-link" href="{{route('complaintClosed.view',$complaintEnquiry->id)}}">{!! substr($complaintEnquiry->complaint_mob_no, 5) !!}</a></td>
                    <td><a class="no-link" href="{{route('complaintClosed.view',$complaintEnquiry->id)}}">{{$complaintEnquiry->building->building_name}}</a></td>                    
                    <td>@if(isset($complaintEnquiry->unit_id))<a class="no-link" href="{{route('complaintClosed.view',$complaintEnquiry->id)}}">{{$complaintEnquiry->Unit->unit_code}}</a>@endif</td> 
                    <td><a class="no-link" href="{{route('complaintClosed.view',$complaintEnquiry->id)}}">{{$complaintEnquiry->location->locations_name}}</a></td>
                 	<td>
                     @can('complaint_closed_view')
                 		<a href="{{route('complaintClosed.view',$complaintEnquiry->id)}}" title="View" class="btn btn-tbl-view btn-xs">
                            <i class="fa fa-eye "></i>
                        </a>
                    @endcan

                     @if(auth()->user()->hasRole(['maintenance_coordinator','super_admin']) && auth()->user()->can('close'))                     
                 		<button title="Close" type="button" class="btn btn-tbl-delete btn-xs complaint_close_modal" data-toggle="modal" data-target="#myModal" data-id = "{{$complaintEnquiry->id}}" datas-id="" data-backdrop="static" data-keyboard="false">
                          <i class="fa fa-times-circle "></i>
                        </button>
                    @endif
                 	</td>
                 </tr>

                 @empty 
                 <tr>
                 	<td colspan="8" align="center">
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
