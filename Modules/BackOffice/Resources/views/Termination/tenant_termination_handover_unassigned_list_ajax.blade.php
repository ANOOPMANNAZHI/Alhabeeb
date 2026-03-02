              @forelse ($tenantTerminations as $tenantTermination)
               @php
               $current = 'handoverUnassigned';
               Session::put('current', $current); 
               @endphp           
               <tr>
                <td>@can('handover_assign')<input type = "checkbox" id = "switch-2" 
                 class = "mdl-switch__input sub_chk" name="groupAssign[]" value="{{$tenantTermination->id}}" datas-id="{{$tenantTermination->work_flow_processes_code}}"data_ac_key = "AS">@endcan
                </td>
                 <td><a class="no-link" href="{{route('handoverUnassignedView',$tenantTermination->id)}}">{{$tenantTermination->tenant_contract_no}}</a></td>  

                 <td><a class="no-link" href="{{route('handoverUnassignedView',$tenantTermination->id)}}">{{$tenantTermination->building_name}}</a></td>

                 <td><a class="no-link" href="{{route('handoverUnassignedView',$tenantTermination->id)}}">{{$tenantTermination->unit_no}}</a></td>

                 <td><a class="no-link" href="{{route('handoverUnassignedView',$tenantTermination->id)}}">{{$tenantTermination->tenant_name}}</a></td>

                 <td><a class="no-link" href="{{route('handoverUnassignedView',$tenantTermination->id)}}">{{isset($tenantTermination->location_id)?$tenantTermination->location->locations_name: ''}}</a></td>   
                 <td><a class="no-link" href="{{route('handoverUnassignedView',$tenantTermination->id)}}">{{$tenantTermination->tenant_contact_no}}</a></td>   
                 <td><a class="no-link" href="{{route('handoverUnassignedView',$tenantTermination->id)}}">{{$tenantTermination->building_pc}}</a></td>   
                 <td><a class="no-link" href="{{route('handoverUnassignedView',$tenantTermination->id)}}">{{$tenantTermination->os}}</a></td>     
                 <td>
                  @can('handover_unassigned_list')
                 <a title="View" href="{{route('handoverUnassignedView',$tenantTermination->id)}}" class="btn btn-tbl-view btn-xs">
                    <i class="fa fa-eye "></i>
                  </a>
                @endcan
                  @can('handover_assign')
                  <button type="button" class="btn btn-tbl-general btn-xs assignLead"  data-toggle="modal" data-target="#myModal" data-id="{{$tenantTermination->work_flow_processes_code}}" datas-id = "{{$tenantTermination->id}}" data_ac_key = "AS" title="Assign" data-backdrop="static" data-keyboard="false">  <i class="fa fa-user" aria-hidden="true"></i></button>
                  @endcan
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
