<script>

   $(document).on('click','.add_complaint',function(){ 
        var work = $("#work_id").val();
        var no = $('#complaint_form tr').length+1;
        var checklist_desc = $("#checklist_desc").val();
        if(work !="" && checklist_desc !=""){
          $.ajax({
              method: "POST",
              url: "{{route('addSubComplaint')}}",
              data: {no:no,work:work,checklist_desc:checklist_desc, "_token" : $('meta[name="csrf-token"]').attr('content')},
              success: function(data){                            
                  if(data != 0){
                  $('#complaint_form').append(data); 
                  $('.worksIds').val(''); 
                  $('.checkList').val('');                           
                  }               
              }           
          });
        }else{
          alert("Please Select Category And Enter Description");
        }             
    });
/***************************************************************************/
    $(document).on('click','.remove_complaint',function(){
      var row = $(this).closest('tr').attr('id'); // Or continue to use the invalid ID selector: '#'+id
     
      var siblings =  $(this).closest('td').siblings('td.selected').text();
      $(this).closest('tbody .tr').remove();
      
        
        //$(this).closest('tbody .tr').remove();
        
    });

/***************************************************************************/
    $(document).on('click','.TicketEdit',function(){

        var work_id = $(this).attr('data-id');
        var desc = $(this).attr('data-ids');
        var no = $(this).attr('data-idNo');
        
        $.ajax({
            method: 'POST', // Type of response and matches what we said in the route
            //url: '../../tenantContract/'+tenantContract_id+'/edit', // This is the url we gave in the route
           // url: '../complaints/ticketEdit',
            url: "{{route('ticketEdit')}}",
            data: {'work_id' : work_id,'desc' : desc,'no' : no,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
            success: function(response){ // What to do if we succeed
                $("#myModal").html(response); 
            },
        });
        return true;
        
         
    });
/***************************************************************************/
$(document).on('click','.update_complaint',function(){ 
	var work = $("#workId").val();
	var no = $('#No').val();
	var checklist_desc = $("#checklistDesc").val(); 
	if(work !="" && checklist_desc !=""){
	  $.ajax({
	      method: "POST",
	      url: "{{route('updateSubComplaint')}}",
	      data: {no:no,work:work,checklist_desc:checklist_desc, "_token" : $('meta[name="csrf-token"]').attr('content')},
	      success: function(data){                            
	          
	      	 //and there for the td elements
	        $("#work_id"+no).html(data['works_code']+'<input type="hidden" name="works_id[]" value="'+data['work_id']+'">');

	        $("#checklist_desc"+no).html(data['checklist_desc']+'<input type="hidden" name="checklist_des[]" value="'+data['checklist_desc']+'">');
	        $("#action"+no).html('<button class="btn btn-tbl-delete btn-xs remove_complaint" type="button"><i class="fa fa-trash-o "></i></button> <button type="button" class="btn btn-tbl-edit btn-xs TicketEdit" data-toggle="modal" data-target="#myModal" data-id = "'+data['work_id']+'" data-ids = "'+data['checklist_desc']+'" data-idNo="'+data['no']+'"><i class="fa fa-pencil-square-o"></i></button>');
	        
	      	//$('tbody#complaint_form tr#'+no).html(data);
	      	$('#myModal').modal('toggle');                    
	                         
	      }           
	  });
	}else{
	  alert("Please Select Category And Enter Description");
}             
});

</script>