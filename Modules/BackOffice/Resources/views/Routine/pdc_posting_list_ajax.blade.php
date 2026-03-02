      @php $count = 1; @endphp
      @forelse ($pdc as $key=>$pdcData) 
      <tr>
        <td>
          @if($key==0)
            <input type="hidden" name="custom_direction" id="custom_direction" value="{{$custom_direction??'asc'}}">
            <input type="hidden" name="field_name" id="field_name" value="{{$field_name??'pdc_check_no'}}">
          @endif          
          <input type="hidden" name="search_count" value="{{count($pdc)}}" >
          <input type="hidden" name="row_collection" value="0" id="row_collection">
          <input type ="checkbox" id ="checkItem" name="pdc_id[]" class ="mdl-switch__input sub_chk" value="{{ $pdcData->id }}">
        <input type="hidden" name="contract_id_{{$pdcData->id}}" value="{{ $pdcData->tenant_contract_id }}" >
        </td>
        <td>{{$pdc->perPage()*($pdc->currentPage()-1)+$count}}</td>
        <td>{{ $pdcData->pdc_check_no}}
        <input type="hidden" name="pdc_check_no_{{$pdcData->id}}" value="{{ $pdcData->pdc_check_no}}" >
        <input type="hidden" name="pdc_type_{{$pdcData->id}}" value="{{ $pdcData->pdc_type}}" >
        </td>
        <td>{{ $pdcData->pdc_check_date->format('d/m/Y') ?? ''}}
        <input type="hidden" name="pdc_check_date_{{$pdcData->id}}" value="{{ $pdcData->pdc_check_date->format('d/m/Y') ?? ''}}" >
        </td>
        <td>
		@if($pdcData->pdc_type_name =='')
		Others
	    @else
		{{ $pdcData->pdc_type_name }}
	    @endif
	   </td>
        <td>{{ $pdcData->tenantContractInfo->building->building_name}}</td>
        <td>{{ $pdcData->tenantContractInfo->building->building_code}}</td> 
        <td>{{ $pdcData->tenantContractInfo->Unit->unit_code}}</td>
        <td>{{ $pdcData->tenantContractInfo->tenant->tenant_name}}</td>
        <td>{{ $pdcData->tenantContractInfo->tenant->tenant_code}}</td> 
        <td>{{ $pdcData->tenantContractInfo->tenant_contract_no}}</td>
        <td>{{ $pdcData->bankInfo->bank_name}}</td>
        <td>{{ numberFormat($pdcData->pdc_amt)}}
         <input type="hidden" name="pdc_amt_{{$pdcData->id}}" value="{{$pdcData->pdc_amt}}" ></td>


      </tr>  
	  @php $count++; @endphp
      @empty 
      <tr>
        <td id="no_record" colspan="13" align="center">
          <p>No Record</p>
        </td>
      </tr>
      @endforelse 
      @if(!empty($totalamt))
      <tr class="total_tr">
        <td colspan="12" align="right">Total</td>
        <td  align="center"><span id="total_amt"><b>{{ numberFormat($totalamt) }}</b></span></td>
      </tr>
      @endif
               
