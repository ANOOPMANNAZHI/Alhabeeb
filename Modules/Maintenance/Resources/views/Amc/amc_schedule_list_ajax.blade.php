                @forelse ($amcSchedules as $amcSchedule)
                @php
                $current = 'amcSchedule.index';
                Session::put('current', $current); 
                
                @endphp                          
                <tr>
                	<td>@if($amcSchedule->amc_schedule_status==0 && count($amcSchedule->taskStatus)== 0)
                    @can('amc_schedule_edit') <a class="no-link" href="{{route('amcSchedule.edit',$amcSchedule->id)}}">{{$amcSchedule->amcContract->amc_contract_no ?? ''}}</a>@endcan @else @can('amc_schedule_view')<a class="no-link" href="{{route('amcSchedule.show',$amcSchedule->id)}}">{{$amcSchedule->amcContract->amc_contract_no ?? ''}}</a> @endcan @endif</td>  

                    <td>@if($amcSchedule->amc_schedule_status==0 && count($amcSchedule->taskStatus)== 0)
                    @can('amc_schedule_edit') <a class="no-link" href="{{route('amcSchedule.edit',$amcSchedule->id)}}">@if($amcSchedule->vendor_id){{'Sub-Contractor' }}@endif 
                    @if($amcSchedule->user_id){{'In-house' }}@endif</a>@endcan @else @can('amc_schedule_view')<a class="no-link" href="{{route('amcSchedule.show',$amcSchedule->id)}}">@if($amcSchedule->vendor_id){{'Sub-Contractor' }}@endif 
                    @if($amcSchedule->user_id){{'Engineer' }}@endif</a> @endcan @endif</td> 

                	<td>
                    @if($amcSchedule->amc_schedule_status==0 && count($amcSchedule->taskStatus)== 0)
                    @can('amc_schedule_edit')<a class="no-link" href="{{route('amcSchedule.edit',$amcSchedule->id)}}">@if($amcSchedule->vendor_id){{$amcSchedule->vendor->vendor_name }}@endif 
                    @if($amcSchedule->user_id){{$amcSchedule->technician->username }}@endif</a>@endcan @else @can('amc_schedule_view')<a class="no-link" href="{{route('amcSchedule.show',$amcSchedule->id)}}">@if($amcSchedule->vendor_id){{$amcSchedule->vendor->vendor_name }}@endif 
                    @if($amcSchedule->user_id){{$amcSchedule->technician->username }}@endif</a>@endcan @endif</td>

                	<td>@if($amcSchedule->amc_schedule_status==0 && count($amcSchedule->taskStatus)== 0)
                   @can('amc_schedule_edit') <a class="no-link" href="{{route('amcSchedule.edit',$amcSchedule->id)}}">{{$amcSchedule->building->building_name}}</a>@endcan @else @can('amc_schedule_view') <a class="no-link" href="{{route('amcSchedule.show',$amcSchedule->id)}}">{{$amcSchedule->building->building_name}}</a>@endcan @endif</td>

                	<td>@if($amcSchedule->amc_schedule_status==0 && count($amcSchedule->taskStatus)== 0)
                    @can('amc_schedule_edit')<a class="no-link" href="{{route('amcSchedule.edit',$amcSchedule->id)}}">{{$amcSchedule->amc_schedule_period_from->format('d/m/Y')}}</a>@endcan @else @can('amc_schedule_view')
                    <a class="no-link" href="{{route('amcSchedule.show',$amcSchedule->id)}}">{{$amcSchedule->amc_schedule_period_from->format('d/m/Y')}}</a>@endcan @endif</td>

                	<td>@if($amcSchedule->amc_schedule_status==0 && count($amcSchedule->taskStatus)== 0)
                    @can('amc_schedule_edit')<a class="no-link" href="{{route('amcSchedule.edit',$amcSchedule->id)}}">{{$amcSchedule->amc_schedule_period_to->format('d/m/Y')}}</a>@endcan @else @can('amc_schedule_view')<a class="no-link" href="{{route('amcSchedule.show',$amcSchedule->id)}}">{{$amcSchedule->amc_schedule_period_to->format('d/m/Y')}}</a>@endcan @endif</td>

                	<td>@if($amcSchedule->amc_schedule_status==0 && count($amcSchedule->taskStatus)== 0)
                    @can('amc_schedule_edit')<a class="no-link" href="{{route('amcSchedule.edit',$amcSchedule->id)}}">{{$amcSchedule->paymentMethod->payment_method_code}}</a>@endcan @else @can('amc_schedule_view')<a class="no-link" href="{{route('amcSchedule.show',$amcSchedule->id)}}">{{$amcSchedule->paymentMethod->payment_method_code}}</a>@endcan @endif</td>  
                	<td>
                		<a title="Status" class="change_status">@if($amcSchedule->amc_schedule_status==0)<button type="button" class="btn label label-success label-mini">Open</button>@elseif($amcSchedule->amc_schedule_status==1)<button type="button" class="btn label label-danger label-mini">Closed</button>@else <button type="button" class="btn label label-info label-mini">Cancel</button> @endif
                		</a>
                	</td>  
                	<td>
                		@can('amc_schedule_view') 
                		<a href="{{route('amcSchedule.show',$amcSchedule->id)}}" title="View" class="btn btn-tbl-view btn-xs">
                			<i class="fa fa-eye "></i>
                		</a>
                		@endcan
                        
                		@can('amc_schedule_edit') 
                        @if($amcSchedule->amc_schedule_status==0 && count($amcSchedule->taskStatus)== 0)
                		<a title="Edit" href="{{route('amcSchedule.edit',$amcSchedule->id)}}" class="btn btn-tbl-edit btn-xs" title="Edit">
                			<i class="fa fa-pencil"></i>
                		</a>  
                         @endif                                                  
                		@endcan 
                        @if($amcSchedule->amc_schedule_status==0)
                        <button type="button" class="btn btn-tbl-cancel btn-xs cancel_type" title="Cancel"  data-id="{{$amcSchedule->id}}"><i class="fa fa-ban"></i></button>
                        @endif        
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
                	{{$amcSchedules->withPath($route)->appends(\Request::except(['page','_token','ajax']))->links()}}      
                     <div class="pagination_info">
                                   @include('includes.pagination_info',['paginator' => $amcSchedules])         
                               </div>          	
                   </td>
                </tr>
                @endif 
