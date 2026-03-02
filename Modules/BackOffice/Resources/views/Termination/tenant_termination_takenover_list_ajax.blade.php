              @forelse ($tenantTerminations as $tenantTermination)
               @php
               $current = 'takeoverForTermination';
               Session::put('current', $current); 
               @endphp                
               <tr>
                 <td><a class="no-link" href="{{route('takeoverForTerminationView',$tenantTermination->id)}}">{{$tenantTermination->tenant_contract_no}}</a></td>  

                 <td><a class="no-link" href="{{route('takeoverForTerminationView',$tenantTermination->id)}}">{{$tenantTermination->building_name}}</a></td> 

                 <td><a class="no-link" href="{{route('takeoverForTerminationView',$tenantTermination->id)}}">{{$tenantTermination->unit_no}}</a></td>

                 <td><a class="no-link" href="{{route('takeoverForTerminationView',$tenantTermination->id)}}">{{$tenantTermination->tenant_name}}</a></td>

                 <td><a class="no-link" href="{{route('takeoverForTerminationView',$tenantTermination->id)}}">{{date('d/m/Y',strtotime($tenantTermination->tenant_contract_start_date))}}</a></td>   
                 <td><a class="no-link" href="{{route('takeoverForTerminationView',$tenantTermination->id)}}">{{date('d/m/Y',strtotime($tenantTermination->tenant_contract_valid_to_date))}}</a></td>   
                 <td><a class="no-link" href="{{route('takeoverForTerminationView',$tenantTermination->id)}}">{{numberFormat($tenantTermination->tenant_contract_rent)}}</a></td>   
                 <td><a class="no-link" href="{{route('takeoverForTerminationView',$tenantTermination->id)}}">{{$tenantTermination->tenant_contract_muncipality_agr_no}}</a></td>   
                 <td><a class="no-link" href="">{{$tenantTermination->os}}</a></td>     
                 <td>
                  @can('tenant_terminate')
                  <button type="submit" title="Terminate" class="btn btn-tbl-general btn-xs resubmit" data-toggle="modal" data-target="#myModal_terminate" data-id = "TMT" id="{{$tenantTermination->contract_id}}" datas-id ="{{$tenantTermination->id}}" datas-enid="{{$tenantTermination->work_flow_processes_code}}" data-backdrop="static" data-keyboard="false" >
                  <i class="fa fa-exclamation-triangle"></i>
                  </button>
                    <!--<a href="{{route('tenantTerminationStage',[$tenantTermination->id,$tenantTermination->contract_id,$tenantTermination->work_flow_processes_code,'TMT'])}}" title="Terminate" class="btn btn-tbl-general btn-xs">
                    <i class="fa fa-exclamation-triangle"></i>
                  </a> -->
                  @endcan
                  @can('takenover_resubmit')
                 <button type="submit" title="Resubmit" class="btn btn-tbl-delete btn-xs delete_type terminate" data-toggle="modal" data-target="#myModal" data-id = "RESUB" id="{{$tenantTermination->contract_id}}" datas-id ="{{$tenantTermination->id}}" datas-enid="{{$tenantTermination->work_flow_processes_code}}" data-backdrop="static" data-keyboard="false" >  
                      <i class="fa fa-thumbs-o-down"></i>
                </button>
                  <!--<a href="{{route('tenantTerminationStage',[$tenantTermination->id,$tenantTermination->contract_id,$tenantTermination->work_flow_processes_code,'RESUB'])}}" title="Resubmit" class="btn btn-tbl-delete btn-xs delete_type">
                    <i class="fa fa-thumbs-o-down"></i>
                  </a> -->
                  @endcan
                   
                   
                  @can('takenover_termination_list')
                  <a href="{{route('takeoverForTerminationView',$tenantTermination->id)}}" title="View" class="btn btn-tbl-view btn-xs"><i class="fa fa-eye "></i>
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



             <td colspan="10" id="pagination_ajax">               
            
                {{$tenantTerminations->withPath($route)->appends(\Request::except(['page','ajax','_token']))->links()}}

                <div class="pagination_info">
                                   @include('includes.pagination_info',['paginator' => $tenantTerminations])         
                               </div>

           </td>                           

         </tr>
         @endif
