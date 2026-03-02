               @forelse ($landlordTerminations as $landlordTermination)
               @php
               $current = 'TerminatedContractLandlord';
               Session::put('current', $current); 
               @endphp             
               <tr>
                 <td><a class="no-link" href="{{route('TerminatedContractLandlordView',$landlordTermination->id)}}">{{$landlordTermination->landlord_contract_no}}</a></td>  

                 <td><a class="no-link" href="{{route('TerminatedContractLandlordView',$landlordTermination->id)}}">{{$landlordTermination->building_name}}</a></td>

                 <td><a class="no-link" href="{{route('TerminatedContractLandlordView',$landlordTermination->id)}}">{{$landlordTermination->vendor_name}}</a></td>

                 <td><a class="no-link" href="{{route('TerminatedContractLandlordView',$landlordTermination->id)}}">{{date('d/m/Y',strtotime($landlordTermination->landlord_contract_valid_from_date))}}</a></td>   
                
                 <?php if($landlordTermination->landlord_contract_valid_to_date == ""){?> 
                   <td>-</td>         
                   <td>
                 <?php }?>
                 <?php if($landlordTermination->landlord_contract_valid_to_date != ""){?> 
                   <td><a class="no-link" href="{{route('TerminatedContractLandlordView',$landlordTermination->id)}}">{{date('d/m/Y',strtotime($landlordTermination->landlord_contract_valid_to_date))}}</a></td>         
                   <td>
                 <?php }?>
                 <?php if($landlordTermination->end_date == ""){?> 
                   -</td>         
                   <td>
                 <?php }?>
                 <?php if($landlordTermination->end_date != ""){?> 
                   <a class="no-link" href="{{route('TerminatedContractLandlordView',$landlordTermination->id)}}">{{date('d/m/Y',strtotime($landlordTermination->end_date))}}</a></td>         
                   <td>
                 <?php }?>


                 <a href="{{route('TerminatedContractLandlordView',$landlordTermination->id)}}" title="View" class="btn btn-tbl-view btn-xs">
                    <i class="fa fa-eye "></i>
                  </a> 
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
