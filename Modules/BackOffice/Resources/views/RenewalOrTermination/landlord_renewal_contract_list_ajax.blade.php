                @forelse ($contractRenewals as $contractRenewal)
                @php
                $current = 'renewalContract';
                Session::put('current', $current); 
                @endphp              
                <tr>
                 <td>
                  

                  @if($contractRenewal->new_contract_id)
                  <a class="no-link" href="{{route('landlordRenewalContract',$contractRenewal->newLandlordContract->id)}}"> 
                  {{$contractRenewal->newLandlordContract->landlord_contract_old_no}} </a>
                  @else <a class="no-link" href="{{route('landlordRenewalContractCreate',[$contractRenewal->oldLandlordContract->id,$contractRenewal->oldLandlordContract->landlord_renewal_termination_status])}}"> {{$contractRenewal->oldLandlordContract->landlord_contract_no}} </a>@endif
                </td>  

                <td>
                  @if($contractRenewal->new_contract_id)
                  <a class="no-link" href="{{route('landlordRenewalContract',$contractRenewal->newLandlordContract->id)}}"> 
                  {{$contractRenewal->newLandlordContract->landlord_contract_no}} </a>
                  @else <a class="no-link" href="{{route('landlordRenewalContractCreate',[$contractRenewal->oldLandlordContract->id,$contractRenewal->oldLandlordContract->landlord_renewal_termination_status])}}"> {{$contractRenewal->oldLandlordContract->landlord_contract_old_no}} </a>@endif
               </td>

                <td>@if($contractRenewal->new_contract_id)
                <a class="no-link" href="{{route('landlordRenewalContract',$contractRenewal->newLandlordContract->id)}}">
                  {{$contractRenewal->newLandlordContract->buildingInfo->building_name}} </a> @else  <a class="no-link" href="{{route('landlordRenewalContractCreate',[$contractRenewal->oldLandlordContract->id,$contractRenewal->oldLandlordContract->landlord_renewal_termination_status])}}">{{$contractRenewal->oldLandlordContract->buildingInfo->building_name}} </a> @endif
                </td>

                <td>@if($contractRenewal->new_contract_id)
                <a class="no-link" href="{{route('landlordRenewalContract',$contractRenewal->newLandlordContract->id)}}">
                  {{$contractRenewal->newLandlordContract->vendorInfo->vendor_name}} </a> @else  <a class="no-link" href="{{route('landlordRenewalContractCreate',[$contractRenewal->oldLandlordContract->id,$contractRenewal->oldLandlordContract->landlord_renewal_termination_status])}}">{{$contractRenewal->oldLandlordContract->vendorInfo->vendor_name}} </a> @endif
                </td> 

                <td>
                  @if($contractRenewal->new_contract_id)
                   <a class="no-link" href="{{route('landlordRenewalContract',$contractRenewal->newLandlordContract->id)}}">
                  {{$contractRenewal->newLandlordContract->landlord_contract_valid_from_date->format('d/m/Y')}} </a> @else  <a class="no-link" href="{{route('landlordRenewalContractCreate',[$contractRenewal->oldLandlordContract->id,$contractRenewal->oldLandlordContract->landlord_renewal_termination_status])}}"> {{$contractRenewal->oldLandlordContract->landlord_contract_valid_from_date->format('d/m/Y')}} </a> @endif</td>

                  <td>@if($contractRenewal->new_contract_id)
                  <a class="no-link" href="{{route('landlordRenewalContract',$contractRenewal->newLandlordContract->id)}}">
                    {{(!empty($contractRenewal->newLandlordContract->landlord_contract_valid_to_date))?$contractRenewal->newLandlordContract->landlord_contract_valid_to_date->format('d/m/Y'):''}} </a> @else <a class="no-link" href="{{route('landlordRenewalContractCreate',[$contractRenewal->oldLandlordContract->id,$contractRenewal->oldLandlordContract->landlord_renewal_termination_status])}}"> {{ (!empty($contractRenewal->oldLandlordContract->landlord_contract_valid_to_date))?  $contractRenewal->oldLandlordContract->landlord_contract_valid_to_date->format('d/m/Y') : ''}} </a> @endif</td>


                    <td> 
                      @if(!empty($contractRenewal->newLandlordContract))
                      @if($contractRenewal->newLandlordContract->landlord_renewal_termination_status==3)
                      <button type="button" class="btn label label-primary label-mini">Under Approval</button>

                      @elseif($contractRenewal->newLandlordContract->landlord_renewal_termination_status==2)
                      <button type="button" class="btn label label-warning label-mini">Under Renewal</button>
                      @else($contractRenewal->newLandlordContract->landlord_renewal_termination_status == 4)
                      <button type="button" class="btn label label-danger label-mini">Rejected</button>
                      @endif
                      @else
                      <button type="button" class="btn label label-success label-mini">Requested</button>
                      @endif
                    </td>
                    <td>
                     @if($contractRenewal->oldLandlordContract->landlord_renewal_termination_status==1)
                     @can('landlord_renewal_contract_add')
                     <a href="{{route('landlordRenewalContractCreate',[$contractRenewal->oldLandlordContract->id,$contractRenewal->oldLandlordContract->landlord_renewal_termination_status])}}" title="Renew Contract" class="btn btn-tbl-general btn-xs">
                      <i class="fa fa-pie-chart" aria-hidden="true"></i>   
                    </a>
                    @endcan 
                    @endif
                    
                    @if($contractRenewal->new_contract_id && in_array($contractRenewal->newLandlordContract->landlord_renewal_termination_status,[2,3,4]))

                    @can('landlord_renewal_contract_list')    
                    <a href="{{route('landlordRenewalContract',$contractRenewal->newLandlordContract->id)}}" title="View" class="btn btn-tbl-view btn-xs">
                      <i class="fa fa-eye "></i>
                    </a>
                    @endcan 

                     
                    @can('landlord_renewal_contract_edit')        
                    <a href="{{route('landlordRenewalNewContractEdit',$contractRenewal->newLandlordContract->id)}}" title="Edit" class="btn btn-tbl-edit btn-xs">
                      <i class="fa fa-pencil"></i>
                    </a>
                    @endcan
                    
                    @can('landlord_renewal_send_approval')
                    @if($contractRenewal->newLandlordContract->landlord_renewal_termination_status==2)
                    <a href="{{route('landlordSentForApproval',[$contractRenewal->oldLandlordContract->id,$contractRenewal->newLandlordContract->id])}}" title="Sent for Approval" class="btn btn-tbl-general btn-xs">
                      <i class="fa fa-thumbs-up" aria-hidden="true"></i> 
                    </a>
                    @endif
                     @endcan 
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
                {{$contractRenewals->withPath($route)->appends(\Request::except(['page','ajax','_token','route']))->links()}} 

                <div class="pagination_info">
                                   @include('includes.pagination_info',['paginator' => $contractRenewals])         
                               </div>
               </td>
          </tr>
          @endif 
