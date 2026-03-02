
<tr class="tr" id="{{$no}}">
	<td id="no{{$no}}" class="counters"></td>
	<td id="work_id{{$no}}">{{$works_code}}<input type="hidden" name="works_id[]" value="{{$work_id}}"></td>
	<td id="checklist_desc{{$no}}">{{$checklist_desc}}<input type="hidden" name="checklist_des[]" value="{{$checklist_desc}}"></td>

	<td id="action{{$no}}">
     
    <button class="btn btn-tbl-delete btn-xs remove_complaint" type="button">
        <i class="fa fa-trash-o "></i>
    </button>
    <button type="button" class="btn btn-tbl-edit btn-xs TicketEdit" data-toggle="modal" data-target="#myModal" data-id = "{{$work_id}}" data-ids = "{{$checklist_desc}}" data-idNo="{{$no}}">
      <i class="fa fa-pencil-square-o"></i> 
    </button>
	</td> 	
</tr>