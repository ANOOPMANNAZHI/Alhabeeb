               @forelse ($renewalDues as $renewalDue)
               @php
               $current = 'landlordRenewal.index';
               Session::put('current', $current); 
               @endphp                 
               <tr>
                   <td>@can('landlord_renewal_due_list')<a class="no-link" href="{{route('landlordRenewal.show',$renewalDue->id)}}">{{$renewalDue->landlord_contract_no}}</a>@endcan</td>  

                   <td>@can('landlord_renewal_due_list')<a class="no-link" href="{{route('landlordRenewal.show',$renewalDue->id)}}">{{$renewalDue->buildingInfo->building_name}}</a>@endcan</td>

                   <td>@can('landlord_renewal_due_list')<a class="no-link" href="{{route('landlordRenewal.show',$renewalDue->id)}}">{{$renewalDue->landlord_contract_valid_from_date->format('d/m/Y')}}</a>@endcan</td>

                   <td>@can('landlord_renewal_due_list')<a class="no-link" href="{{route('landlordRenewal.show',$renewalDue->id)}}">{{$renewalDue->landlord_contract_valid_to_date->format('d/m/Y')}}</a>@endcan</td>

                   <td>@can('landlord_renewal_due_list')<a class="no-link" href="{{route('landlordRenewal.show',$renewalDue->id)}}">{{$renewalDue->vendorInfo->vendor_name}}</a>@endcan</td>   
                    <td>
                    @can('landlord_renewal_due_accept')
                    <button type="button" class="btn btn-tbl-general btn-xs accept" data-id="1" datas-id="ACPT" data-flow-id="401" data-contract="{{$renewalDue->id}}" data-toggle="tooltip" data-placement="top" title="Renewing"><i class="fa fa-check"></i> </button>
                    @endcan
                    @can('landlord_renewal_due_terminate')
                     <button type="button" class="btn btn-tbl-violet btn-xs terminate" data-id="7" datas-id="TMT" data-flow-id="601" data-contract="{{$renewalDue->id}}" data-toggle="tooltip" data-placement="top" title="Vacating"><i class="fa fa-life-ring"></i></button>
                     @endcan
                     
                     <a href="{{route('landlordRenewal.show',$renewalDue->id)}}" title="View" class="btn btn-tbl-view btn-xs">
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
            @php                                         

          if(isset($request->ajax)) 
          $renewalDues->withPath($route);

          if(!empty($request->landlord_contract_no))
          $renewalDues->appends(['landlord_contract_no' => $request->landlord_contract_no]);

          if(!empty($request->building_id))
          $renewalDues->appends(['building_id' => $request->building_id]);


          if(!empty($request->landlord_contract_valid_from_date))
          $renewalDues->appends(['landlord_contract_valid_from_date' => $request->landlord_contract_valid_from_date]);

          if(!empty($request->landlord_contract_valid_to_date))
          $renewalDues->appends(['landlord_contract_valid_to_date' => $request->landlord_contract_valid_to_date]);                                   

          $fieldName =  app('request')->input('fieldName');

          if(!empty($fieldName)){
          $fieldName =  app('request')->input('fieldName');
          $operation =  app('request')->input('operation');
          $fieldValue =  app('request')->input('fieldValue');
          $logic =  app('request')->input('logic');
          $renewalDues->appends(['fieldName' => $fieldName,
          'operation' => $operation,
          'fieldValue' => $fieldValue,
          'logic' => $logic,
          ]);   
      }

      @endphp 

      {{$renewalDues->links()}} 
       <div class="pagination_info">
                                   @include('includes.pagination_info',['paginator' => $renewalDues])         
                               </div>

  </td>                           

</tr>
 @endif 
