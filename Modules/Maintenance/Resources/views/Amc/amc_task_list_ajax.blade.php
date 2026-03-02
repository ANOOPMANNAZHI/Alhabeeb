               @forelse ($amcTasks as $amcTask)
               @php
               $current = 'amcTask.index';
               Session::put('current', $current); 

               @endphp                 
               <tr>
                   <td>@can('amc_task_view')<a class="no-link" href="{{route('amcTask.show',$amcTask->id)}}">{{$amcTask->amcSchedule->amcContract->amc_contract_no ?? ''}}</a>@endcan</td>  

                   <td>@if($amcTask->amcSchedule->user_id=='')
                    <a title="Status" class="change_status">Sub-contractor
                        @endif
                        @if($amcTask->amcSchedule->vendor_id=='')In-house
                        @endif
                    </a></td> 

                   <td>@can('amc_task_view')<a class="no-link" href="{{route('amcTask.show',$amcTask->id)}}">{{$amcTask->amcSchedule->vendor->vendor_name ?? $amcTask->amcSchedule->technician->username }}</a>@endcan</td>
                   <td>@can('amc_task_view')<a class="no-link" href="{{route('amcTask.show',$amcTask->id)}}">{{$amcTask->amcSchedule->building->building_name}}</a>@endcan</td>

                   <td>@can('amc_task_view')<a class="no-link" href="{{route('amcTask.show',$amcTask->id)}}">{{$amcTask->amenityType->amentity_types_name}}</a>@endcan</td>

                   <td>@can('amc_task_view')<a class="no-link" href="{{route('amcTask.show',$amcTask->id)}}">{{$amcTask->amc_schedule_from_date->format('d/m/Y')}}</a>@endcan</td>

                   <td>@can('amc_task_view')<a class="no-link" href="{{route('amcTask.show',$amcTask->id)}}">{{$amcTask->amc_schedule_to_date->format('d/m/Y')}}</a>@endcan</td>

                   <td>@can('amc_task_view')<a class="no-link" href="{{route('amcTask.show',$amcTask->id)}}">{{$amcTask->amcSchedule->paymentMethod->payment_method_code}}</a>@endcan</td>

                   <td>@can('amc_task_view')<a class="no-link" href="{{route('amcTask.show',$amcTask->id)}}">@if($amcTask->amc_schedule_task_status==0)<button type="button" class="btn label label-success label-mini">open</button> @else <button type="button" class="btn label label-success label-mini">closed</button> @endif</a>@endcan</td>
                   
                      
                    <td>
                      @can('amc_task_view') 
                      <a href="{{route('amcTask.show',$amcTask->id)}}" title="View" class="btn btn-tbl-view btn-xs">
                        <i class="fa fa-eye "></i>
                    </a>
                    @endcan
                    {{-- @can('complaint_enquiries_destroy') --}}
                    @if($amcTask->amc_schedule_task_status==0)
                      <button type="button" class="btn btn-tbl-delete btn-xs close_type" title="Close" data-toggle="modal" data-target="#myModal" id="{{$amcTask->amcSchedule->amc_contract_type}}" data-id="{{$amcTask->id}}" data-backdrop="static" data-keyboard="false"><i class="fa fa-times-circle "></i></button>
                    @endif

                  {{--  @endcan   --}} 
                  @role('maintenance_supervisor|super_admin|maintenance_head')
                  @if(!empty($amcTask->amcSchedule->user_id) && $amcTask->amc_schedule_task_status == 0)
                  <a title="Reminder" href="{{route('addTask.reminder',$amcTask->id)}}" class="btn btn-tbl-general btn-xs">
                      <i class="fa fa-bell"></i>
                  </a>
                  @endif
                  @endrole             
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

      {{$amcTasks->withPath($route)->appends(\Request::except(['page','_token','ajax']))->links()}} 

      <div class="pagination_info">
                                   @include('includes.pagination_info',['paginator' => $amcTasks])         
                               </div>

  </td>                           

</tr>
 @endif  
