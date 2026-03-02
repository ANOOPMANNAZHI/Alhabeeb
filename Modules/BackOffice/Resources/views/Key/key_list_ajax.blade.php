              @forelse ($keys as $key)
               @php
               $current = 'keyManagement.index';
               Session::put('current', $current); 
               @endphp              
               <tr>
                 <td>{{$key->building->building_name}}</td>
                 <td>{{$key->unit->unit_code}}</td>

                <td>@if($key->user_id){{ucwords(str_replace('_', ' ',$key->user->getRoleNames()->implode(', ') ))}}@elseif($key->tenant_id){{"Tenant"}}@else{{"Landlord"}}@endif</td>

                <td>@if($key->user_id){{$key->user->username}}@elseif($key->tenant_id){{$key->tenant->tenant_name}}@else{{$key->landlord->vendor_name}}@endif</td>

                 <td>
                  @can('keys_accept_for_tenant')
                  <button type="button" title="Handover To Tenant" class="btn btn-tbl-edit btn-xs keyHandover" data-id="{{$key->id}}" datas-id="Tenant" data-toggle="modal" data-target="#myModal" data-placement="top" ><i class="fa fa-sign-language"></i></button>
                  @endcan
                  @can('keys_accept_for_landlord')
                  <button type="button"title="Handover To Landlord" class="btn btn-tbl-violet btn-xs keyHandover" data-id="{{$key->id}}"  datas-id="Landlord" data-toggle="modal" data-target="#myModal" data-placement="top" ><i class="fa fa-hand-paper-o"></i></button>
                  @endcan
                </td>
              </tr>

              @empty 
              <tr>
               <td colspan="5" align="center">
                <p>No Record</p>
              </td>
            </tr>
             @endforelse


             @if(isset($request->ajax)) 
            <tr>
             <td colspan="5" id="pagination_ajax">
             {{$keys->withPath($route)->appends(\Request::except(['page','ajax','_token','route']))->links()}}

              <div class="pagination_info">
                                   @include('includes.pagination_info',['paginator' => $keys])         
                               </div>
           </td>
         </tr>
        @endif
