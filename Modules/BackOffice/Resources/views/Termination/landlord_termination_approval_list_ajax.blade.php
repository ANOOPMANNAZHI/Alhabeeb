              @forelse ($landlordTerminations as $landlordTermination)
               @php
               $current = 'LCTerminationApproval';
               Session::put('current', $current);
               $outstanding = chekOutstanding($landlordTermination->building_id);
               

               @endphp              
               <tr>
                 <td><a class="no-link" href="{{route('LCTerminationApprovalView',$landlordTermination->id)}}">{{$landlordTermination->landlord_contract_no}}</a></td>  

                 <td><a class="no-link" href="{{route('LCTerminationApprovalView',$landlordTermination->id)}}">{{$landlordTermination->building_name}}</a></td>

                 <td><a class="no-link" href="{{route('LCTerminationApprovalView',$landlordTermination->id)}}">{{$landlordTermination->vendor_name}}</a></td>

                 <td><a class="no-link" href="{{route('LCTerminationApprovalView',$landlordTermination->id)}}">{{date('d/m/Y',strtotime($landlordTermination->landlord_contract_valid_from_date))}}</a></td>   
                 <td><a class="no-link" href="{{route('LCTerminationApprovalView',$landlordTermination->id)}}">{{!empty($landlordTermination->landlord_contract_valid_to_date)?date('d/m/Y',strtotime($landlordTermination->landlord_contract_valid_to_date)): ''}}</a></td>       
                 <td>
                  @can('lc_approval_termination_list')
                 <a href="{{route('LCTerminationApprovalView',$landlordTermination->id)}}" title="View" class="btn btn-tbl-view btn-xs">
                    <i class="fa fa-eye "></i>
                  </a>
                  @endcan
                  @can('lc_termination_verification_approve')
                  <input type="hidden" name="outstanding" value="{{$outstanding}}" id="outstanding">
                   <a href="{{route('landlordTerminationChangeStatus',[$landlordTermination->id,3])}}" title="Approve" class="btn btn-tbl-general btn-xs Approve">
                    <i class="fa fa-check "></i>
                  </a>
                  @endcan
                  @can('lc_termination_verification_reject')
                  <a href="{{route('landlordTerminationChangeStatus',[$landlordTermination->id,4])}}" title="Reject" class="btn btn-tbl-reject btn-xs">
                    <i class="fa fa-exclamation-triangle"></i>
                  </a> 
                  @endcan
                </td>
              </tr>

              @empty 
              <tr>
               <td colspan="6" align="center">
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
