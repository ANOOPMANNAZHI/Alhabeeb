              @forelse ($tenantTerminations as $tenantTermination)
               @php
               $current = 'tenantTermination.index';
               Session::put('current', $current); 
               @endphp       
                       
               <tr>
                 <td><a class="no-link" href="{{route('tenantTermination.show',$tenantTermination->contract_id)}}">{{$tenantTermination->tenant_contract_no}}</a></td>  

                 <td><a class="no-link" href="{{route('tenantTermination.show',$tenantTermination->contract_id)}}">{{$tenantTermination->building_name}}</a></td>

                 <td><a class="no-link" href="{{route('tenantTermination.show',$tenantTermination->contract_id)}}">{{$tenantTermination->unit_no}}</a></td>

                 <td><a class="no-link" href="{{route('tenantTermination.show',$tenantTermination->contract_id)}}">{{$tenantTermination->tenant_name}}</a></td>

                 <td><a class="no-link" href="{{route('tenantTermination.show',$tenantTermination->contract_id)}}">{{date('d/m/Y',strtotime($tenantTermination->tenant_contract_start_date)) }}</a></td>   
                 <td><a class="no-link" href="{{route('tenantTermination.show',$tenantTermination->contract_id)}}">{{date('d/m/Y',strtotime($tenantTermination->tenant_contract_valid_to_date)) }}</a></td>   
                 <td><a class="no-link" href="{{route('tenantTermination.show',$tenantTermination->contract_id)}}">{{numberFormat($tenantTermination->tenant_contract_rent)}}</a></td>   
                 <td><a class="no-link" href="{{route('tenantTermination.show',$tenantTermination->contract_id)}}">{{$tenantTermination->tenant_contract_muncipality_agr_no}}</a></td>   
                 <td><a class="no-link" href="{{route('tenantTermination.show',$tenantTermination->contract_id)}}">{{$tenantTermination->os}}</a></td>   
                 <td>
                   <span class="label {{terminationTypeStatusClass($tenantTermination->termination_type_status)}} label-mini">{{$tenantTermination->termination_type_status_class}}{{$tenantTermination->TerminationTypeStatusName}}</span> 
                 </td>
                 
                 
                 <td>
                  @can('open_for_termination_list')
                  <a href="{{route('tenantTermination.show',$tenantTermination->contract_id)}}" title="View" class="btn btn-tbl-view btn-xs">
                    <i class="fa fa-eye "></i>
                  </a>
                  @endcan
                  
                  @if($tenantTermination->termination_type_status == 1 || $tenantTermination->termination_type_status == 4)
                  @can('tenant_early_termination')
                    <a href="{{route('tenantTermination.edit',$tenantTermination->id)}}" title="Edit" class="btn btn-tbl-edit btn-xs">
                      <i class="fa fa-pencil "></i>
                    </a>

                    <a href="#CancelEralyTermination" title="Cancel Early Termination" class="btn btn-tbl-edit btn-xs btn-danger" onclick="cancelTermination(<?php echo $tenantTermination->contract_id?>)">
                      <i class="fa fa-refresh "></i>
                    </a>

                  @endcan
                  @can('tenant_termination_send_approval')
                  <a href="{{route('tenantTerminationOpenStatus',[$tenantTermination->id,2])}}" title="Send For Approval" class="btn btn-tbl-general btn-xs">
                     <i class="fa fa-life-ring"></i>
                  </a>
                  @endcan
                  <!-- <button title="Send For Approval" type="button" class="btn btn-tbl-general btn-xs Approve" data-toggle="modal" data-target="#myModal" datas-id="" data-id="2">
                    <i class="fa fa-check"></i>
                  </button> -->

                  <!-- <a href="{{route('tenantRenewalStage',[$tenantTermination->contract_id,301,1,-1])}}" title="Renew" class="btn btn-tbl-violet btn-xs">
                    <i class="fa fa-life-ring"></i>
                  </a> --> 
                  
                  @elseif($tenantTermination->termination_type_status == 0)
                  @can('termination_handover')
                   <a href="{{route('tenantTerminationStage',[$tenantTermination->id,$tenantTermination->contract_id,$tenantTermination->work_flow_processes_code,'ACPT'])}}" title="Handover" class="btn btn-tbl-general btn-xs">
                    <i class="fa fa-handshake-o "></i>
                  </a>
                  @endcan
                  <!-- 304/2/0 -->
                  <a href="{{route('tenantRenewalStage',[$tenantTermination->contract_id,304,2,0])}}" title="Renew" class="btn btn-tbl-violet btn-xs">
                    <i class="fa fa-life-ring"></i>
                  </a>
                  @elseif($tenantTermination->termination_type_status == 3)
                  @can('termination_handover')
                  <a href="{{route('tenantTerminationStage',[$tenantTermination->id,$tenantTermination->contract_id,$tenantTermination->work_flow_processes_code,'ACPT'])}}" title="Handover" class="btn btn-tbl-general btn-xs delete_type">
                    <i class="fa fa-handshake-o"></i>
                  </a>
                  @endcan
                  @endif
                </td>
              </tr>

              @empty 
              <tr>
               <td colspan="11" align="center">
                <p>No Record</p>
              </td>
            </tr>
             @endforelse 



            @if(isset($request->ajax)) 
            <tr>
             <td colspan="10" id="pagination_ajax"> 
            
                {{$tenantTerminations->withPath($route)->appends(\Request::except(['page','ajax','_token']))->links()}}

                <div class="pagination_info">
                                   @include('includes.pagination_info',['paginator' => $tenantTerminations])         
                               </div>

           </td>                           

         </tr>
        @endif 
