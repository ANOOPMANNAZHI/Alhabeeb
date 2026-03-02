               @forelse ($landlordTerminations as $landlordTermination)
               @php
               $current = 'landlordTermination.index';
               Session::put('current',$current); 
               @endphp               
               <tr>
                 <td><a class="no-link" href="{{route('landlordTermination.show',$landlordTermination->id)}}">{{$landlordTermination->landlord_contract_no}}</a></td>  

                 <td><a class="no-link" href="{{route('landlordTermination.show',$landlordTermination->id)}}">{{$landlordTermination->building_name}}</a></td>

                 <td><a class="no-link" href="{{route('landlordTermination.show',$landlordTermination->id)}}">{{$landlordTermination->vendor_name}}</a></td>

                 <td><a class="no-link" href="{{route('landlordTermination.show',$landlordTermination->id)}}">{{date('d/m/Y',strtotime($landlordTermination->landlord_contract_valid_from_date))}}</a></td>   
                 <td><a class="no-link" href="{{route('landlordTermination.show',$landlordTermination->id)}}">{{isset($landlordTermination->landlord_contract_valid_to_date)?date('d/m/Y',strtotime($landlordTermination->landlord_contract_valid_to_date)):''}}</a></td>                      
                 
                 <td>
                  <a href="{{route('landlordTermination.show',$landlordTermination->id)}}" title="View" class="btn btn-tbl-view btn-xs">
                    <i class="fa fa-eye "></i>
                  </a>
                  @can('landlord_renewal_due_accept')
                  <button type="button" class="btn btn-tbl-violet btn-xs landlordRenew" data-id="1" datas-id="ACPT" data-flow-id="401" data-contract="{{$landlordTermination->contract_id}}" data-toggle="tooltip" data-placement="top" title="Renewing"><i class="fa fa-life-ring"></i> </button>
                  @endcan
                
                  @can('lc_verify')
                   <a href="{{route('landlordTerminationStage',[$landlordTermination->id,$landlordTermination->contract_id,$landlordTermination->work_flow_processes_code,'ACPT'])}}" title="Verify" class="btn btn-tbl-general btn-xs">
                    <i class="fa fa-check "></i>
                  </a>
                  @endcan
           
                  @can('landlord_early_termination')
                  <!-- Only Premature Termination have Edit Button -->
                  @if($landlordTermination->termination_type_status == 1)
                  <a href="{{route('landlordTermination.edit',$landlordTermination->id)}}" title="Edit" class="btn btn-tbl-edit btn-xs">
                    <i class="fa fa-pencil "></i>
                  </a>
                  @endif
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

             <td colspan="6" id="pagination_ajax">                

             {{$landlordTerminations->withPath($route)->appends(\Request::except(['page','ajax','_token','route']))->links()}}

             <div class="pagination_info">
                                   @include('includes.pagination_info',['paginator' => $landlordTerminations])         
                               </div>

           </td>                           

         </tr>
        @endif 
