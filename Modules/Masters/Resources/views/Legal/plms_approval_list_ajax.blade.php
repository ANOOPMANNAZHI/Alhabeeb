                @forelse ($plmsApprovals as $plmsApproval)
                @php
                $current = 'plmsApproval.index';
                Session::put('current', $current); 

                @endphp             
                <tr>
                 <td><a class="no-link" href="{{route('plmsApprovalShow',$plmsApproval->id)}}">{{$plmsApproval->tenantContract->tenant_contract_no ?? ''}}</a></td>   

                 <td><a class="no-link" href="{{route('plmsApprovalShow',$plmsApproval->id)}}">{{$plmsApproval->tenantContract->tenant->tenant_name }}</a></td>

                 <td><a class="no-link" href="{{route('plmsApprovalShow',$plmsApproval->id)}}">{{$plmsApproval->building->building_name}}</a></td>

                 <td><a class="no-link" href="{{route('plmsApprovalShow',$plmsApproval->id)}}">{{$plmsApproval->Unit->unit_code}}</a></td>

                 <td><a class="no-link" href="{{route('plmsApprovalShow',$plmsApproval->id)}}">{{$plmsApproval->tenantContract->tenant_contract_valid_to_date->format('d/m/Y')}}</a></td>

                 <td>
                  @if($plmsApproval->tenantContract->tenant_contract_status == 1)
                  <span class=" btn-circle btn-success btn-sm m-b-10 status"><b>Active</b></span>
                  @else 
                  <span  class=" btn-circle btn-danger btn-sm m-b-10"><b>Inactive</b></span>
                  @endif
                  </td>

                  <td><a class="no-link" href="{{route('plmsApprovalShow',$plmsApproval->id)}}">{{numberFormat($plmsApproval->tenantContract->tenant_contract_rent)}}</a></td>

                  <td>
                    
                    <button type="button" class="btn btn-tbl-general btn-xs approve" data-id="{{$plmsApproval->id}}" title="Approve" alt="Approve" data-key="APRV" data-flow-id="801" data-status="0" data-toggle="modal" data-target="#myModal" data-backdrop="static" data-keyboard="false"><i class="fa fa-check "></i></button>
                    
                    
                    <button type="button" class="btn btn-tbl-violet btn-xs referback" data-id="{{$plmsApproval->id}}" title="Refer back" alt="Refer back" data-key="RFRBK" data-flow-id="801"  data-status="0" data-toggle="modal" data-target="#myModal" data-backdrop="static" data-keyboard="false"><i class="fa fa-pie-chart"></i></button>
                    
                    
                    <button type="button" class="btn btn-tbl-delete btn-xs close_type" title="Close"  data-id="{{$plmsApproval->id}}" data-flow-id="801"><i class="fa fa-times-circle "></i></button>
                    
                     
                    <a href="{{route('plmsApprovalShow',$plmsApproval->id)}}" title="View" class="btn btn-tbl-view btn-xs">
                      <i class="fa fa-eye "></i>
                    </a>
                     

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
              {{$plmsApprovals->withPath($route)->appends(\Request::except(['page','_token','ajax']))->links()}} 

              <div class="pagination_info">
                                   @include('includes.pagination_info',['paginator' => $plmsApprovals])         
                               </div>
            </td>                           

          </tr>
          @endif 
