					@forelse ($legalCases as $legalCase)
                @php
                $current = 'legalCase.index';
                Session::put('current', $current); 

                @endphp             
                <tr>
                 <td><a class="no-link" href="{{route('legalCase.show',$legalCase->id)}}">{{$legalCase->tenantContract->tenant_contract_no ?? ''}}</a></td>   

                 <td><a class="no-link" href="{{route('legalCase.show',$legalCase->id)}}">{{$legalCase->tenantContract->tenant->tenant_name }}</a></td>

                 <td><a class="no-link" href="{{route('legalCase.show',$legalCase->id)}}">{{$legalCase->building->building_name}}</a></td>

                 <td><a class="no-link" href="{{route('legalCase.show',$legalCase->id)}}">{{$legalCase->Unit->unit_code}}</a></td>

                 <td><a class="no-link" href="{{route('legalCase.show',$legalCase->id)}}">{{$legalCase->tenantContract->tenant_contract_valid_to_date->format('d/m/Y')}}</a></td>

                 <td>

                  @if($legalCase->tenantContract->tenant_contract_status == 1)
                        <span class=" btn-circle btn-success btn-sm m-b-10 status"><b>{{$legalCase->tenantContract->tenant_contract_status_name}}</b></span>
                        @else 
                        <span  class=" btn-circle btn-danger btn-sm m-b-10"><b>{{$legalCase->tenantContract->tenant_contract_status_name}}</b></span>
                        @endif
                 </td>

                 <td><a class="no-link" href="{{route('legalCase.show',$legalCase->id)}}">{{numberFormat($legalCase->tenantContract->tenant_contract_rent)}}</a></td>

                 <td><!-- <button type="button" class="btn label label-success label-mini">Approval</button>  <button type="button" class="label label-primary label-mini">Lawyer</button> -->
                 <span class="label {{$legalCase->work_flow_processes_code_class}} label-mini">{{$legalCase->workFlowProcess->work_flow_processes_name}} {{isset($legalCase->legalAdvicerNote->legal_notes_status)?'-'.$legalCase->legalAdvicerNote->legal_notes_status_name:''}}    </span>
                 </td>


                 <td>
                   
                  <a href="{{route('legalCase.show',$legalCase->id)}}" title="View" class="btn btn-tbl-view btn-xs">
                    <i class="fa fa-eye "></i>
                  </a>
                  
                  @if($legalCase->work_flow_processes_code == 804 && $legalCase->are_status == 0)
                  @can('legal_are_stage') 
                  <button type="button" class="btn btn-tbl-violet btn-xs are_stage" data-id="{{$legalCase->id}}" data-flow-id="801" data-status="0" data-toggle="modal" data-target="#myModal"><i class="fa fa-arrow-right" aria-hidden="true" title="Verification" data-backdrop="static" data-keyboard="false"></i></button>
                  </a>
                  @endcan
                  @endif 
                  @if($legalCase->work_flow_processes_code == 804 && $legalCase->are_status == 1)
                  @can('legal_are_stage') 
                  <button type="button" class="btn btn-tbl-violet btn-xs are_stage" data-id="{{$legalCase->id}}" data-flow-id="802" data-status="0" data-toggle="modal" data-target="#myModal"><i class="fa fa-arrow-right" aria-hidden="true" title="Verification" data-backdrop="static" data-keyboard="false"></i></button>
                  </a>
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
             {{$legalCases->withPath($route)->appends(\Request::except(['page','_token','ajax']))->links()}} 

              <div class="pagination_info">
                                   @include('includes.pagination_info',['paginator' => $legalCases])         
                               </div>
             </td>
         </tr>
         @endif  
