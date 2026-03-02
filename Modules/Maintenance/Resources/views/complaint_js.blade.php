<script>
$(document).ready(function() {

    $("#checklist_form").validate()
	$(document).on('click','.checklistEdit',function(){

        var checklist_id = $(this).attr('dataa-id');
        $.ajax({
            method: 'POST', // Type of response and matches what we said in the route
            //url: '../../tenantContract/'+tenantContract_id+'/edit', // This is the url we gave in the route
           // url: '../complaints/ticketEdit',
            url: "{{route('checklistEdit')}}",
            data: {'checklist_id' : checklist_id,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
            success: function(response){ // What to do if we succeed
                $("#myModal").html(response); 
            },
        });
        return true;
        
         
    });
/***************************************************************************/
	$("#myModal").on("hidden.bs.modal", function(){
	    $("#myModal").html("");
	    $(this).removeData('bs.modal');
	});
    $('#master').on('click', function(e) {
        if($(this).is(':checked',true))  
        {
            $(".sub_chk").prop('checked', true);  
        } else {  
            $(".sub_chk").prop('checked',false);  
        }  
    });
/**********************************************************************************/
    $(".sub_chk").on('click', function(e) {
     $("#master").prop('checked',false);
    });
    
/**********************************************************************************/
    $(document).on('click','.assignLead',function(){       
        var allVals = []; 
        var complaint_id = $(this).attr('data-id');
        allVals.push($(this).attr('datas-id'));

        var workflow = $(this).attr('datass-id');
        /*$('input:hidden[name=enquiryIds]').val(allVals);*/
        $.ajax({
          method: 'POST', // Type of response and matches what we said in the route
          url: "{{route('groupAssignModal')}}", // This is the url we gave in the route
          data: {'complaint_id' : complaint_id,'workflow' : workflow,'id' : allVals,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
          success: function(response){ // What to do if we succeed
              $("#myModal").html(response); 
          },
        });
        return true; 
         
    });

/**********************************************************************************/
    $(document).on('click','.SubAssign',function(){ 
        var allVals = []; 
        var complaint_id = $(this).attr('data-id');
        allVals.push($(this).attr('datas-id'));

        var workflow = $(this).attr('datass-id'); 
        
        $.ajax({
            method: 'POST', // Type of response and matches what we said in the route
            url: "{{route('subAssignModal')}}", // This is the url we gave in the route
            data: {'complaint_id' : complaint_id,'workflow' : workflow,'id' : allVals,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
            success: function(response){ // What to do if we succeed
                $("#myModal").html(response); 
            },
        });
        return true;  
         
    });

/**********************************************************************************/
    $(document).on('click', '.close_modal',function(e) {        
               
           var complaint_checklist_id = [];
            var complaint_id                = $(this).attr('data-id');
            var idd = $(this).attr('datas-id');
            if(idd != ""){
               complaint_checklist_id.push(idd);
            }
           
            var stage_id                    = $(this).attr('dataa_id');
            var assigned_id                    = $(this).attr('datassign-id');
            $.ajax({
                method: 'POST', // Type of response and matches what we said in the route
                url: "{{route('ticketCloseModal')}}", // This is the url we gave in the route
                data: {'stage_id' : stage_id,'assigned_id' : assigned_id,'complaint_id' : complaint_id,'complaint_checklist_id':complaint_checklist_id,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
                success: function(response){ // What to do if we succeed
                    $("#myModal").html(response); 
                },
            });
            return true;
    });
/**********************************************************************************/
    $("#show").on("hide.bs.collapse", function(){
            $("#enquirySearch").html('Advance Search <i class="fa fa-caret-down" aria-hidden="true"></i>');
    });
    $("#show").on("show.bs.collapse", function(){
        $("#enquirySearch").html('Advance Search <i class="fa fa-caret-up" aria-hidden="true"></i>');
    });
/**********************************************************************************/
    $(document).on('click', '.complaint_close_modal',function(e) {        
                
            var complaint_id                = $(this).attr('data-id');
            /*var complaint_checklist_id      = $(this).attr('datas-id');
            var stage_id                    = $(this).attr('dataa_id');*/
            $.ajax({
                method: 'POST', // Type of response and matches what we said in the route
                url: "{{route('closeComplaintModal')}}", // This is the url we gave in the route
                data: {'complaint_id' : complaint_id,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
                success: function(response){ // What to do if we succeed
                    $("#myModal").html(response); 
                },
            });
            return true;
    });

});

    
function formatDate(date) {
    var d = new Date(date),
    month = '' + (d.getMonth() + 1),
    day = '' + d.getDate(),
    year = d.getFullYear();

    if (month.length < 2) 
    month = '0' + month;
    if (day.length < 2) 
    day = '0' + day;

    return [year, month, day].join('-');
}

</script>