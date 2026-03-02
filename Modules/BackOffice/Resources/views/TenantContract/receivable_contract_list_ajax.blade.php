@php $count = 1; $totalamount = 0;    @endphp
@forelse ($tenantContracts as $contract)


<tr>  
<td>                        
  <a class="no-link">{{$contract->building_name}}</a>
</td>
   
<td>                     
   <a class="no-link">{{$contract->tenant_name}}</a>

 </td>  
         
  <td>      
  <input type="hidden" name="occuipied_units" id="occuipied_units" value="{{getOccupiedUnits($contract->id)}}">  
   <a class="no-link">{{$contract->tenant_contract_no}}</a>
 </td>
 <td>
  <a class="no-link">@if($contract->tenant_contract_start_date){{$contract->tenant_contract_start_date->format('d/m/Y')}}@endif</a>                       
</td>
<td>
  <a class="no-link">@if($contract->tenant_contract_valid_to_date){{$contract->tenant_contract_valid_to_date->format('d/m/Y')}}@endif</a>                        
</td>

<td>
  <a class="no-link">@if($contract->tenant_contract_rent){{numberFormat($contract->tenant_contract_rent)}}@endif</a>                      
</td>

   <td>
    <a class="no-link">{{getReceivables($contract->tenant_contract_effective_date,$contract->tenant_contract_valid_to_date,$nextDays,$contract->tenant_contract_rent,$contract->id)}}</a>  

   <!--  <a class="no-link">{{$contract->tenant_contract_effective_date}}-{{$contract->tenant_contract_valid_to_date}}-{{$nextDays}}-{{$contract->tenant_contract_rent}}-{{$contract->id}}</a>  -->

  </td>
 




    </tr>
    @php $count++; @endphp 
    @empty
    <tr>
      <td colspan="14" align="center">
       <p>No Record</p>
     </td>
   </tr>
   @endforelse

   <tr>
     <td colspan="14" align="center">
       <p>{{$nextDays}}</p>
     </td>
   </tr>


   @if(isset($request->ajax)) 
   <tr>
    <td colspan="8" id="pagination_ajax">                   

      {{$tenantContracts->appends(\Request::except(['page','ajax']))->links()}}
      <div class="pagination_info">
       @include('includes.pagination_info',['paginator' => $tenantContracts])         
     </div>

   </td>                           

 </tr>
 @endif


