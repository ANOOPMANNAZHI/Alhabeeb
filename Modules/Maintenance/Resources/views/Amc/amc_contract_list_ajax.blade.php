                 @forelse ($amcContracts as $amcContract)
                 @php
                 $current = 'amcContract.index';
                 Session::put('current', $current); 
                 
                 $flag=false;
                 if(  $amcContract->amc_contract_status == 0 ){
                            if(  count($amcContract->schedules) == 0 || count( $amcContract->cancelSchedules) >0 )
                            {
                            $flag=true;
                            }}
                 @endphp                              
                 <tr>
                 	<td>@if($flag==true)
                    @can('amc_contract_edit')<a class="no-link" href="{{route('amcContract.edit',$amcContract->id)}}">{{$amcContract->amc_contract_no}}</a>@endcan @else @can('amc_contract_view')<a class="no-link" href="{{route('amcContract.show',$amcContract->id)}}">{{$amcContract->amc_contract_no}}</a>@endcan @endif</td>  

                 	<td>@if($flag==true)
                     @can('amc_contract_edit')<a class="no-link" href="{{route('amcContract.edit',$amcContract->id)}}">{{$amcContract->vendor->vendor_name}}</a>@endcan @else @can('amc_contract_view') <a class="no-link" href="{{route('amcContract.show',$amcContract->id)}}">{{$amcContract->vendor->vendor_name}}</a> @endcan  @endif</td>

                 	<td>@if($flag==true)
                     @can('amc_contract_edit')<a class="no-link" href="{{route('amcContract.edit',$amcContract->id)}}">{{$amcContract->building->building_name}}</a>@endcan @else @can('amc_contract_view')<a class="no-link" href="{{route('amcContract.show',$amcContract->id)}}">{{$amcContract->building->building_name}}</a>@endcan @endif</td>

                 	<td>@if($flag==true)
                     @can('amc_contract_edit')<a class="no-link" href="{{route('amcContract.edit',$amcContract->id)}}">{{$amcContract->amc_contract_period_from->format('d/m/Y')}}</a>@endcan @else @can('amc_contract_view')<a class="no-link" href="{{route('amcContract.show',$amcContract->id)}}">{{$amcContract->amc_contract_period_from->format('d/m/Y')}}</a>@endcan @endif</td>

                 	<td>@if($flag==true)
                     @can('amc_contract_edit')<a class="no-link" href="{{route('amcContract.edit',$amcContract->id)}}">{{$amcContract->amc_contract_period_to->format('d/m/Y')}}</a>@endcan @else @can('amc_contract_view')<a class="no-link" href="{{route('amcContract.show',$amcContract->id)}}">{{$amcContract->amc_contract_period_to->format('d/m/Y')}}</a>@endcan @endif</td>

                 	<td>@if($flag==true)
                    @can('amc_contract_edit')<a class="no-link" href="{{route('amcContract.edit',$amcContract->id)}}">{{$amcContract->paymentMethod->payment_method_code}}</a>@endcan @else @can('amc_contract_view')<a class="no-link" href="{{route('amcContract.show',$amcContract->id)}}">{{$amcContract->paymentMethod->payment_method_code}}</a>@endcan @endif</td>    
                 	<td>
                 		@can('amc_contract_view') 
                 		<a href="{{route('amcContract.show',$amcContract->id)}}" title="View" class="btn btn-tbl-view btn-xs">
                 			<i class="fa fa-eye "></i>
                 		</a>
                        
                 		@endcan
                 		@can('amc_contract_edit') 
                        @if(  $amcContract->amc_contract_status == 0 )
                            @if(  count($amcContract->schedules) == 0 || count( $amcContract->cancelSchedules) >0 )
                 		    <a title="Edit" href="{{route('amcContract.edit',$amcContract->id)}}" class="btn btn-tbl-edit btn-xs" title="Edit">
                     			<i class="fa fa-pencil"></i>
                     		</a>  
                            @endif    
                        @endif                                               
                 		@endcan 
                 		{{-- @can('complaint_enquiries_destroy') --}}
                 		@if($amcContract->amc_contract_status==0)
                 		<button type="button" class="btn btn-tbl-cancel btn-xs cancel_type" title="Cancel"  data-id="{{$amcContract->id}}"   datas-id = "{{$amcContract->amcSchedule->id ?? ''}}"><i class="fa fa-ban"></i></button>

                 		@endif
                 		
                 		{{--  @endcan   --}}              
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
                 	
                 	{{$amcContracts->withPath($route)->appends(\Request::except(['page','_token','ajax']))->links()}} 

                    <div class="pagination_info">
                                   @include('includes.pagination_info',['paginator' => $amcContracts])         
                               </div>
                 	
                 </td>                           
                 
             </tr>
             @endif 
