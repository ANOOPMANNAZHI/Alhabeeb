
@php $num = 0; @endphp
 @for($j =0; $j<=$Numtimes-1;$j++){
 	@php $num++; @endphp
	 @foreach ($amenitys as $amenity) {
	 <tr class="tr tr{{$amenity->amenities_type_id}}" id="{{$num}}">
	 	<td id="amc_schedule_period_from_text{{$num}}">{{date('d/m/Y',strtotime($effectiveDates[$j]))}}<input type="hidden" name="amc_schedule_period_from_text_sub[]" value="{{$effectiveDates[$j]}}"></td>

	 	<td id="amc_schedule_period_to_text{{$num}}">{{date('d/m/Y',strtotime($endDates[$j]))}} <input type="hidden" name="amc_schedule_period_to_text_sub[]" value="{{$endDates[$j]}}"></td>

	 	<td id="amentity_types_id{{$num}}">{{$amenity->amenityType->amentity_types_name}}<input type="hidden" name="amentity_types_idd_sub[]" value="{{$amenity->amenities_type_id}}"></td>	

	 	<td id="amc_schedule_status{{$num}}"><button type="button" class="btn label label-primary label-mini">OPEN</button></td>

	 	<td id="action{{$num}}">

	 		<button class="btn btn-tbl-delete btn-xs remove_amenity" type="button">
	 			<i class="fa fa-trash-o "></i>
	 		</button>
	 		<button type="button" class="btn btn-tbl-edit btn-xs AmenityEditSub" data-toggle="modal" data-target="#myModal" data-id = "{{$amentity_types_id}}" data-frm = "{{$effectiveDates[$j]}}" data-to = "{{$endDates[$j]}}" data-idNo="{{$num}}" data-num="{{$num}}"  data-f="{{$from}}" data-t="{{$to}}">
	 			<i class="fa fa-pencil"></i> 
	 		</button>
	 	</td> 	
	 </tr>
	 @php $num++; @endphp
	 @endforeach
 @endfor
 