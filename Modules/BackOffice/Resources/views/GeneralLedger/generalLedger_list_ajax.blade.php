            @php 

              $curr_loop =  ($generalLedgers->currentPage() == 1)? 1 :   ( (($generalLedgers->currentPage() - 1) * $generalLedgers->perPage()) + 1 );

                if(\Request::input('curr_url'))
                 $curr_url =  \Request::input('curr_url');
                else
                 $curr_url =  url()->current();   


                if($curr_url == route('generalLedgerApproval'))
                     $view_url = 'generalLedgerApproval.show';
                else 
                     $view_url = 'generalLedger.show';
                                
            @endphp  

            @forelse ($generalLedgers as $generalLedger)
                 <tr>
                    <td><a class="no-link" href="{{route($view_url,$generalLedger->id)}}">{{ $curr_loop + $loop->index }}  
                    </a></td> 
                    <td><a class="no-link" href="{{route($view_url,$generalLedger->id)}}">{{$generalLedger->ledger_type}} </a></td>
                    <td><a class="no-link" href="{{route($view_url,$generalLedger->id)}}">{{$generalLedger->voucher_no}}</a></td>  
                    <td><a class="no-link" href="{{route($view_url,$generalLedger->id)}}">{{$generalLedger->jv_refer_no}} </a></td>
                  
                    <td><a class="no-link" href="{{route($view_url,$generalLedger->id)}}"> {{$generalLedger->doc_date->format('d/m/Y')}}</a></td>
                                   
                    <td><a class="no-link" href="{{route($view_url,$generalLedger->id)}}">{{(!empty($generalLedger->bank_id))? $generalLedger->bank->bank_name : '' }}</a></td> 
                    <td><a class="no-link" href="{{route($view_url,$generalLedger->id)}}">{{numberFormat($generalLedger->amount)}}</a></td>

                    @if($curr_url != route('generalLedgerApproval'))

                    <td><a class="no-link" href="{{route($view_url,$generalLedger->id)}}">
                  
                    @if($generalLedger->general_ledger_status == 3 )
					  <span class="label label-info label-mini">Post</span>
					@else
						@php $status = explode('|',$generalLedger->GeneralLedgerApprovalStatusName) @endphp
					   <span class="label {{reset($status)}} label-mini"> {{end($status)}}</span></a></td>
					@endif
					</td> 
                    @endif
                    
                    @if($curr_url == route('generalLedgerApproval'))
                    <td>
						
						<a class="no-link" href="{{route($view_url,$generalLedger->id)}}">
                        @if($generalLedger->GeneralLedgerApprovalStatus==2)
                              <span class="label label-primary label-mini"> {{'Approval'}}</span>      
                        
                        @else
                                <span class="label success label-mini">{{'UnApprove'}} </span>   
                        @endif
						
					   </a>
					
					</td> 
                    @endif 
                 	
                    <td>                    
                    @can('view_general_ledger')
                     		<a href="{{route($view_url,$generalLedger->id)}}" title="View" class="btn btn-tbl-view btn-xs">
                                <i class="fa fa-eye "></i>
                            </a>
                    @endcan                
                     

                    @if($curr_url != route('generalLedgerApproval') && auth()->user()->can('edit_general_ledger') && in_array($generalLedger->general_ledger_status,[1,4])) 
                        <a href="{{route('generalLedger.edit',$generalLedger->id)}}" title="Edit" class="btn btn-tbl-edit btn-xs">
                            <i class="fa fa-pencil "></i>
                        </a>
                    @endif 

                   @if( ($curr_url !=  route('generalLedgerApproval') ) && $generalLedger->general_ledger_status != 3 && $generalLedger->general_ledger_approval_status == 4 && auth()->user()->can('post_general_ledger') )              
                      <a href="{{route('generalLedger.action',[$generalLedger->id,'post'])}}" class="btn btn-tbl-violet btn-xs confirm" title="Post" ><i class="fa fa-pie-chart"></i></a>
                   @endif
                    
                    @if($curr_url != route('generalLedgerApproval')  && in_array($generalLedger->general_ledger_approval_status ,[0,1,5]) && auth()->user()->can('delete_general_ledger')  && $generalLedger->general_ledger_status != 3) 
                        <a href="{{route('generalLedger.destroy',$generalLedger->id)}}" title="Delete" class="btn btn-tbl-delete btn-xs delete">
                            <i class="fa fa-trash-o "></i>
                        </a> 
                    @endif

                    @if(url()->current() != route('generalLedgerApproval') && 
                        auth()->user()->can('ledger_approval') == true && in_array($generalLedger->general_ledger_approval_status,[0,1,5]))   
                    <a href="{{route('generalLedger.action',[$generalLedger->id,'approve'])}}" title="{{($generalLedger->general_ledger_approval_status == 1 || $generalLedger->general_ledger_approval_status == 0 )?'Approve' : 'UnApprove' }} " class="btn btn-tbl-general btn-xs confirm">
                      <i class="fa {{ ($generalLedger->general_ledger_approval_status == 1|| $generalLedger->general_ledger_approval_status == 0)? 'fa-check' : 'fa-reply' }}"></i>                    
                    </a>
                    @elseif( ($curr_url !=  route('generalLedgerApproval') ) && $generalLedger->general_ledger_status == 1 && $generalLedger->general_ledger_approval_status != 2 && auth()->user()->can('send_for_approval_general_ledger'))  
                      <a href="{{route('generalLedger.action',[$generalLedger->id,'send-for-approval'])}}" title="Send For Approval" class="btn btn-tbl-general btn-xs confirm">
                        <i class="fa fa-hand-o-right"></i>
                    </a>
                    @endif

                    @if(url()->current() != route('generalLedgerApproval') && 
                        auth()->user()->can('ledger_approval') == true && in_array($generalLedger->general_ledger_approval_status,[4]))   
                    <a href="{{route('generalLedger.action',[$generalLedger->id,'unapprove'])}}" title="{{($generalLedger->general_ledger_approval_status == 4)?'UnApprove':'Approve'}} " class="btn btn-tbl-general btn-xs confirm">
                      <i class="fa {{ ($generalLedger->general_ledger_approval_status == 4)? 'fa-reply':'fa-check'}}"></i>                    
                    </a>
                    @elseif( ($curr_url !=  route('generalLedgerApproval') ) && $generalLedger->general_ledger_status != 3 && $generalLedger->general_ledger_approval_status == 4 && auth()->user()->can('send_for_unapproval_general_ledger'))  
                      <a href="{{route('generalLedger.action',[$generalLedger->id,'send-for-unapproval'])}}" title="Send For Draft" class="btn btn-tbl-general btn-xs confirm"> <i class="fa fa-hand-o-left"></i></a>
                    @endif

                 @if($curr_url ==  route('generalLedgerApproval'))
                    @if(auth()->user()->can('ledger_approval') && $generalLedger->general_ledger_approval_status == 2)                                   
                    <a href="{{ route('generalLedger.action',[$generalLedger->id,'approve'])}}" title="Approve" class="btn btn-tbl-general btn-xs confirm">
                     <i class="fa fa-check"></i>
                    </a>
                    @endif

                   @if(auth()->user()->can('ledger_approval') && $generalLedger->general_ledger_approval_status == 3)                    
                    <a href="{{ route('generalLedger.action',[$generalLedger->id,'unapprove']) }}" title="Unapprove" class="btn btn-tbl-general btn-xs confirm">
                     <i class="fa fa-reply"></i>
                    </a>
                   @endif 
               @endif
                  
                     
                 	</td>
                 </tr>

                 @empty 
                 <tr>
                 	<td colspan="{{(url()->current() == route('generalLedgerApproval'))? '9':'10'}}" align="center">
                 		<p>No Record</p>
                 	</td>
                 </tr>
                @endforelse 



                  @if(isset($ajax))   
                    <tr>                
                      <td colspan="6" id="pagination_ajax">
                       {{$generalLedgers->withPath($route)
                                       ->appends(\Request::except(['page','_token','ajax']))
                                       ->links()}}

                                        <div class="pagination_info">
                                   @include('includes.pagination_info',['paginator' => $generalLedgers])         
                               </div>
                     </td>
                     </tr>
                   @endif




               
