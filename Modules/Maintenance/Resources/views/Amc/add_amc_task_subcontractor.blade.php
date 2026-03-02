<tr class="tr" id="{{$no}}">
 	<td id="amc_schedule_period_from_text{{$no}}">{{date("d/m/Y", strtotime($amc_schedule_period_from_text))}}<input type="hidden" name="amc_schedule_period_from_text_sub[]" value="{{$amc_schedule_period_from_text}}"></td>

 	<td id="amc_schedule_period_to_text{{$no}}">{{date("d/m/Y", strtotime($amc_schedule_period_to_text))}}<input type="hidden" name="amc_schedule_period_to_text_sub[]" value="{{$amc_schedule_period_to_text}}"></td>

 	<td id="amentity_types_id{{$no}}">{{$amenity->amenityType->amentity_types_name}}<input type="hidden" name="amentity_types_idd_sub[]" value="{{$amentity_types_id}}"></td>	

 	<td id="amc_schedule_status{{$no}}"><button type="button" class="btn label label-primary label-mini">OPEN</button></td>

 	<td id="action{{$no}}">

 		<button class="btn btn-tbl-delete btn-xs remove_amenity" type="button">
 			<i class="fa fa-trash-o "></i>
 		</button>
 		<button type="button" class="btn btn-tbl-edit btn-xs AmenityEditSub" data-toggle="modal" data-target="#myModal" data-id = "{{$amentity_types_id}}" data-frm = "{{$amc_schedule_period_from_text}}" data-to = "{{$amc_schedule_period_to_text}}" data-f = "{{$from}}" data-t = "{{$to}}" data-num="{{$no}}">
 			<i class="fa fa-pencil"></i> 
 		</button>
 	</td> 	
 </tr>