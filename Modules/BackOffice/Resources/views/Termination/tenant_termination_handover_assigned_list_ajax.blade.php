                @forelse ($tenantTerminations as $tenantTermination)
                @php
                $current = 'handoverAssigned';
                Session::put('current', $current); 

                 $validTo =  strtotime($tenantTermination->tenant_contract_valid_to_date); 
                 $today = strtotime(date('Y-m-d'));

                 $datediff = $validTo - $today;
                 $remainingDays = $datediff / 86400;

                @endphp               
                <tr>
                  <td>@can('handover_reassign')@if($tenantTermination->termination_review_status <= 1)<input type = "checkbox" id = "switch-2" class = "mdl-switch__input sub_chk" name="Assign[]" value="{{$tenantTermination->id}}" datas-id="{{$tenantTermination->work_flow_processes_code}}" data_ac_key = "RAS">@endif
                    @endcan
                  </td>
                  <td><a class="no-link" href="{{route('handoverAssignedView',$tenantTermination->id)}}">{{$tenantTermination->tenant_contract_no}} </a></td>  

                  <td><a class="no-link" href="{{route('handoverAssignedView',$tenantTermination->id)}}">{{$tenantTermination->building_name}}</a></td>

                  <td><a class="no-link" href="{{route('handoverAssignedView',$tenantTermination->id)}}">{{$tenantTermination->building_no}}</a></td>

                  <td><a class="no-link" href="{{route('handoverAssignedView',$tenantTermination->id)}}">{{$tenantTermination->unit_no}}</a></td>

                  <td><a class="no-link" href="{{route('handoverAssignedView',$tenantTermination->id)}}">{{$tenantTermination->unitType->unit_types_name}}</a></td>

                  <td><a class="no-link" href="{{route('handoverAssignedView',$tenantTermination->id)}}">{{$tenantTermination->tenant_name}}</a></td>

                  <td><a class="no-link" href="{{route('handoverAssignedView',$tenantTermination->id)}}">@if($tenantTermination->location_id){{$tenantTermination->location->locations_name}}@endif</a></td>   
                  <td><a class="no-link" href="{{route('handoverAssignedView',$tenantTermination->id)}}">{{$tenantTermination->tenant_contact_no}}</a></td>   
                  <td><a class="no-link" href="{{route('handoverAssignedView',$tenantTermination->id)}}">{{$tenantTermination->building_pc}}</a></td>    
                  <td><a class="no-link" href="{{route('handoverAssignedView',$tenantTermination->id)}}">{{$tenantTermination->os}}</a></td>                   
                  <td><a class="no-link" href="{{route('handoverAssignedView',$tenantTermination->id)}}">{{IsResubmitName($tenantTermination->is_resubmit)}}</a></td>                   
                  <td>
                    @can('handover_assigned_list')
                    <a href="{{route('handoverAssignedView',$tenantTermination->id)}}" title="View" class="btn btn-tbl-view btn-xs">
                      <i class="fa fa-eye "></i>
                    </a>
                    @endcan
                    @if($tenantTermination->termination_review_status <= 1)
                    @if(count($tenantTermination->terminationChecklist) >0  && $tenantTermination->termination_review_status == 0)
                    @can('termination_inspection_create')
                    <a href="{{route('handoverAssignedInspectionEdit',$tenantTermination->id)}}" title="Edit" class="btn btn-tbl-edit btn-xs">
                     <i class="fa fa-pencil "></i>
                   </a>  
                   @endcan
                   @endif 
                   @can('handover_reassign')              
                   <button type="button" class="btn btn-tbl-general btn-xs assignLead"  data-toggle="modal" data-target="#myModal" data-id="{{$tenantTermination->work_flow_processes_code}}" datas-id = "{{$tenantTermination->id}}" data_ac_key = "RAS" title="ReAssign" data-backdrop="static" data-keyboard="false">  <i class="fa fa-user" aria-hidden="true"></i></button>
                   @endcan
                  @can('termination_refer_back') 
                  @if($remainingDays < 0)
                   <button type="button" class="btn btn-tbl-violet btn-xs referback"  data-toggle="modal" data-target="#myModal" data-id="{{$tenantTermination->id}}
                   " datas-id="{{$tenantTermination->contract_id}}
                   " title="Refer Back for Renewal" data-backdrop="static" data-keyboard="false">  <i class="fa fa-undo" aria-hidden="true"></i></button>
                   @elseif($remainingDays > 0)
                   <button type="button" onclick="referBackEarlyTermination(<?php echo $tenantTermination->contract_id?>)" class="btn btn-tbl-violet btn-xs" style="background-color: red;" title="Refer Back to normal">  <i class="fa fa-undo" aria-hidden="true"></i></button>
                   @endif

                   @endcan
                   @if($tenantTermination->termination_review_status <= 2)
                   @can('termination_reminder')
                   <a title="Reminder" href="{{route('InspectionReminder',$tenantTermination->id)}}" class="btn btn-tbl-general btn-xs"><i class="fa fa-bell"></i></a>
                   @endcan
                   @endif
                   @if( $tenantTermination->termination_tenant_signature && ($tenantTermination->termination_review_status == 1 || ($tenantTermination->work_flow_processes_code == 503 && $tenantTermination->termination_review_status == null)))
                   @can('send_for_review')
                   <a title="Send for Review" href="{{route('tenantTerminationReview',$tenantTermination->id)}}" class="btn btn-tbl-general btn-xs">
                     <i class="fa fa-paper-plane-o"></i>
                   </a> 
                   @endcan
                   @endif

                   @endif
                   @if($tenantTermination->termination_review_status == 2 ||  empty($tenantTermination->termination_notes) )
                   @can('termination_taken_over')
                   <a href="{{route('tenantTerminationStage',[$tenantTermination->id,$tenantTermination->contract_id,$tenantTermination->work_flow_processes_code,'ACPT'])}}" title="TakenOver" class="btn btn-tbl-general btn-xs ">
                    <i class="fa fa-hand-o-right"></i>
                  </a>
                  @endcan
                  @endif
                  @if($tenantTermination->termination_review_status == 2)
                                     @php
                   $tenantEmail = $tenantTermination->tenant->tenant_contact_email ?? $tenantTermination->tenant->tenant_personal_email;
                   @endphp
                   @if(!empty($tenantEmail))
                   @can('inspection_send_email')
                   <a href="{{route('inspectionSendMail',$tenantTermination->id)}}" title="Send Mail" class="btn btn-tbl-violet btn-xs">
                    <i class="fa fa-envelope"></i>
                  </a>
                  @endcan
                  @endif
                  @endif
                  

                  @if(empty($tenantTermination->termination_notes))
                  @can('termination_inspection_create')
                  <a title="Inspection Create" href="{{route('handoverAssignedInspection',$tenantTermination->id)}}" class="btn btn-tbl-violet btn-xs"><i class="fa fa-info-circle"></i></a>
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
              {{$tenantTerminations->withPath($route)->appends(\Request::except(['page','ajax','_token']))->links()}}

              <div class="pagination_info">
               @include('includes.pagination_info',['paginator' => $tenantTerminations])         
             </div>
           </td> 
         </tr>
         @endif 
