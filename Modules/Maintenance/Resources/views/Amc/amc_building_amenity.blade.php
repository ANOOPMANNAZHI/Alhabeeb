
	<tr class="tr" id="{{$no}}">
		<td id="no{{$no}}" class="counters"></td>
		<td id="">{{$amenity_name}}<input type="hidden" name="amentity_types_tech_id[]" value="{{$amentity_types_id}}" id="amentity_types_tech_id" class="amentity_types_tech_id"></td>
		<td id="amentity_types_id{{$no}}">{{$amenity_code}}<input type="hidden" name="" value=""></td>
		<td id="action{{$no}}">
	     
	    <button class="btn btn-tbl-delete btn-xs remove_amenity" type="button" data-id="{{$amentity_types_id}}">
	        <i class="fa fa-trash-o "></i>
	    </button>
		</td> 	
	</tr>

	
