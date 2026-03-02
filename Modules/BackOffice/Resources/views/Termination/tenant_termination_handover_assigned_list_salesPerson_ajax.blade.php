                @forelse ($tenantTerminations as $tenantTermination)
                @php
                $current = 'handoverAssigned';
                Session::put('current', $current); 

                 $validTo =  strtotime($tenantTermination->tenant_contract_valid_to_date); 
                 $today = strtotime(date('Y-m-d'));

                 $datediff = $validTo - $today;
                 $remainingDays = $datediff / 86400;

                @endphp               
                <tr>
                  <td>@can('handover_reassign')@if($tenantTermination->termination_review_status <= 1)<input type = "checkbox" id = "switch-2" class = "mdl-switch__input sub_chk" name="Assign[]" value="{{$tenantTermination->id}}" datas-id="{{$tenantTermination->work_flow_processes_code}}" data_ac_key = "RAS">@endif
                    @endcan
                  </td>
                  <td><a class="no-link" href="{{route('handoverAssignedViewSalesPerson',$tenantTermination->id)}}">{{$tenantTermination->tenant_contract_no}} </a></td>  

                  <td><a class="no-link" href="{{route('handoverAssignedViewSalesPerson',$tenantTermination->id)}}">{{$tenantTermination->building_name}}</a></td>

                  <td><a class="no-link" href="{{route('handoverAssignedViewSalesPerson',$tenantTermination->id)}}">{{$tenantTermination->building_no}}</a></td>

                  <td><a class="no-link" href="{{route('handoverAssignedViewSalesPerson',$tenantTermination->id)}}">{{$tenantTermination->unit_no}}</a></td>

                  <td><a class="no-link" href="{{route('handoverAssignedViewSalesPerson',$tenantTermination->id)}}">{{$tenantTermination->unitType->unit_types_name}}</a></td>

                  <td><a class="no-link" href="{{route('handoverAssignedViewSalesPerson',$tenantTermination->id)}}">{{$tenantTermination->tenant_name}}</a></td>

                  <td><a class="no-link" href="{{route('handoverAssignedViewSalesPerson',$tenantTermination->id)}}">@if($tenantTermination->location_id){{$tenantTermination->location->locations_name}}@endif</a></td>   
                  <td><a class="no-link" href="{{route('handoverAssignedViewSalesPerson',$tenantTermination->id)}}">{{$tenantTermination->tenant_contact_no}}</a></td>   
                  <td><a class="no-link" href="{{route('handoverAssignedViewSalesPerson',$tenantTermination->id)}}">{{$tenantTermination->building_pc}}</a></td>    
                  <td><a class="no-link" href="{{route('handoverAssignedViewSalesPerson',$tenantTermination->id)}}">{{$tenantTermination->os}}</a></td>                   
                  <td><a class="no-link" href="{{route('handoverAssignedViewSalesPerson',$tenantTermination->id)}}">{{IsResubmitName($tenantTermination->is_resubmit)}}</a></td>                   
                  <td>
                   
                    <a href="{{route('handoverAssignedViewSalesPerson',$tenantTermination->id)}}" title="View" class="btn btn-tbl-view btn-xs">
                      <i class="fa fa-eye "></i>
                    </a>
                   
                    
                    
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
              {{$tenantTerminations->withPath($route)->appends(\Request::except(['page','ajax','_token']))->links()}}

              <div class="pagination_info">
               @include('includes.pagination_info',['paginator' => $tenantTerminations])         
             </div>
           </td> 
         </tr>
         @endif 
