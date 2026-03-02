
              @forelse ($landlordTerminations as $landlordTermination)
               @php
               $current = 'LCTerminationVerify';
               Session::put('current', $current); 
               @endphp             
               <tr>
                 <td><a class="no-link" href="{{route('LCTerminationVerifyView',$landlordTermination->id)}}">{{$landlordTermination->landlord_contract_no}}</a></td>  

                 <td><a class="no-link" href="{{route('LCTerminationVerifyView',$landlordTermination->id)}}">{{$landlordTermination->building_name}}</a></td>

                 <td><a class="no-link" href="{{route('LCTerminationVerifyView',$landlordTermination->id)}}">{{$landlordTermination->vendor_name}}</a></td>

                 <td><a class="no-link" href="{{route('LCTerminationVerifyView',$landlordTermination->id)}}">{{date('d/m/Y',strtotime($landlordTermination->landlord_contract_valid_from_date))}}</a></td>   
                 <td><a class="no-link" href="{{route('LCTerminationVerifyView',$landlordTermination->id)}}">{{ !empty($landlordTermination->landlord_contract_valid_to_date)? date('d/m/Y',strtotime($landlordTermination->landlord_contract_valid_to_date)): ''}}</a></td>  
                 <td><a class="no-link" href="{{route('LCTerminationVerifyView',$landlordTermination->id)}}">{{ !empty($landlordTermination->end_date)? date('d/m/Y',strtotime($landlordTermination->end_date)): ''}}</a></td>     
                 <td>
                   <span class="label {{terminationTypeStatusClass($landlordTermination->termination_type_status)}} label-mini">@if($landlordTermination->termination_type_status == 0){{'Open For Termination'}} @else{{$landlordTermination->TerminationTypeStatusName}}@endif</span> 
                  </td>                  
                 
                 <td>
                  @if($landlordTermination->termination_type_status == 0 || $landlordTermination->termination_type_status == 4)
                  @can('lc_termination_verification_send')
                   <a href="{{route('landlordTerminationChangeStatus',[$landlordTermination->id,2])}}" title="Send for Approval" class="btn btn-tbl-general btn-xs">
                    <i class="fa fa-check "></i>
                  </a>
                  @endcan
                  @endif
                  @can('lc_termination_verification_list')
                  <a href="{{route('LCTerminationVerifyView',$landlordTermination->id)}}" title="View" class="btn btn-tbl-view btn-xs">
                    <i class="fa fa-eye "></i>
                  </a>
                  @endcan
                  @if($landlordTermination->termination_type_status == 4)
                  @can('landlord_renewal_due_accept')
                  <button type="button" class="btn btn-tbl-violet btn-xs landlordRenew" data-id="1" datas-id="ACPT" data-flow-id="401" data-contract="{{$landlordTermination->contract_id}}" data-toggle="tooltip" data-placement="top" title="Renewing"><i class="fa fa-life-ring"></i> </button>
                  @endcan
                  @endif
                </td>
              </tr>

              @empty 
              <tr>
               <td colspan="7" align="center">
                <p>No Record</p>
              </td>
            </tr>
             @endforelse



            @if(isset($request->ajax)) 
            <tr>
             <td colspan="5" id="pagination_ajax"> 
             {{$landlordTerminations->withPath($route)->appends(\Request::except(['page','ajax','_token','route']))->links()}}

             <div class="pagination_info">
                                   @include('includes.pagination_info',['paginator' => $landlordTerminations])         
                               </div>
           </td>                           

         </tr>
        @endif
