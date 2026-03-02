                @forelse ($tenancyDetails as $tenancyDetail)
                @php
                $current = 'tenancyDetail.index';
                Session::put('current', $current); 

                @endphp                
                <tr>
                  <td>@can('tenancy_details_list'){{$tenancyDetail->unit_no ?? ''}}@endcan</td>   

                  <td>@can('tenancy_details_list'){{$tenancyDetail->unit->unit_types_name ?? ''}}@endcan</td>

                  <td>@can('tenancy_details_list'){{$tenancyDetail->tenantContract->tenant->tenant_name ?? 'Vacant' }}@endcan</td>

                  <td>@can('tenancy_details_list'){{$tenancyDetail->tenantContract->tenant_contract_no ?? ''}}@endcan</td>

                  
                  <td>
                    @if($tenancyDetail->tenantContract)
                    @can('tenancy_details_list')
                    {{$tenancyDetail->tenantContract->tenant_contract_start_date->format('d/m/Y') ?? ''}}
                    @endcan
                    @endif
                  </td>
                    

                   
                    <td>
                     @if($tenancyDetail->tenantContract)
                      @can('tenancy_details_list'){{$tenancyDetail->tenantContract->tenant_contract_valid_to_date->format('d/m/Y') ?? ''}}
                       @endcan
                     @endif
                     </td>
                    

                    <td>@can('tenancy_details_list')
                      {{ ($tenancyDetail->tenantContract)?numberFormat($tenancyDetail->tenantContract->tenant_contract_rent): ''}}@endcan</td>

                    <td>@can('tenancy_details_list'){{$tenancyDetail->tenantContract->TenantContractPaymentName ?? ''}}@endcan</td> 

                    
                    <td>@if(!empty($tenancyDetail->tenantContract->tenant_contract_last_paid_date))@can('tenancy_details_list'){{$tenancyDetail->tenantContract->tenant_contract_last_paid_date->format('d/m/Y') ?? ''}}@endcan  @endif</td>
                  
                    <!-- <td>@can('tenancy_details_list')<a class="no-link" href="">{{$tenancyDetail->unit->vacant_status_name}}</a>@endcan</td> -->
                    @empty 
                    <tr>
                     <td colspan="10" align="center">
                      <p>No Record</p>
                    </td>
                  </tr>
                  @endforelse



                  
               @if($tenancyDetails->count('unit_no')>0)
               <tr>
                <td><b>Unit No: {{$tenancyDetails->count('unit_no')}}</b></td>
                <td colspan="2"><b><span class="pull-right">Total Occupied Units: {{$occupied ?? '0'}}</span></b></td>
                <td colspan="2"><b>Total Vaccant Units: {{$vaccant ?? '0'}}</b></td>
                <td colspan="4"><b>Total Rent: {{numberFormat($sum)}} OMR</b></td>
                
              </tr>
              @endif
