               @forelse ($contractRenewals as $contractRenewal)
               @php
               $current = 'landlordRenewal.index';
               Session::put('current', $current); 
               @endphp                 
               <tr>
                   <td> @can('landlord_contract_renewed_view')
                   <a class="no-link" href="{{route('landlordRenewedContract',$contractRenewal->newLandlordContract->id)}}">{{$contractRenewal->oldLandlordContract->landlord_contract_no}}</a> @endcan</td>

                   <td> @can('landlord_contract_renewed_view')
                   <a class="no-link" href="{{route('landlordRenewedContract',$contractRenewal->newLandlordContract->id)}}">{{$contractRenewal->newLandlordContract->landlord_contract_no}}</a> @endcan</td>  


                   <td> @can('landlord_contract_renewed_view')
                   <a class="no-link" href="{{route('landlordRenewedContract',$contractRenewal->newLandlordContract->id)}}">{{$contractRenewal->newLandlordContract->buildingInfo->building_name}}</a> @endcan</td>

                   <td> @can('landlord_contract_renewed_view')
                   <a class="no-link" href="{{route('landlordRenewedContract',$contractRenewal->newLandlordContract->id)}}">{{$contractRenewal->newLandlordContract->vendorInfo->vendor_name}}</a> @endcan</td>

                   <td> @can('landlord_contract_renewed_view')
                   <a class="no-link" href="{{route('landlordRenewedContract',$contractRenewal->newLandlordContract->id)}}">{{$contractRenewal->newLandlordContract->landlord_contract_valid_from_date->format('d/m/Y')}}</a> @endcan</td>  

                   <td> @can('landlord_contract_renewed_view')
                   <a class="no-link" href="{{route('landlordRenewedContract',$contractRenewal->newLandlordContract->id)}}">{{$contractRenewal->newLandlordContract->landlord_contract_valid_to_date->format('d/m/Y')}}</a> @endcan</td>   
                    <td>
                    @can('landlord_contract_renewed_view')
                    <a href="{{route('landlordRenewedContract',$contractRenewal->newLandlordContract->id)}}" title="View" class="btn btn-tbl-view btn-xs">
                        <i class="fa fa-eye "></i>
                    </a>
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
      {{$contractRenewals->withPath($route)->appends(\Request::except(['page','ajax','_token','route']))->links()}} 

       <div class="pagination_info">
                                   @include('includes.pagination_info',['paginator' => $contractRenewals])         
                               </div>
  </td>
</tr>
 @endif 
