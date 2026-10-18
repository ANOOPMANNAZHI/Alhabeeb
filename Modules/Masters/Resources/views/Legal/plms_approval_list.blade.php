@extends('layouts.plms-app')
@section('css')
<link href="{{ asset('public/css/legal.css') }}" rel="stylesheet">
@endsection

   @section('search_url', route('plmsApproval')) 
 @section('search_reset', route('plmsApproval')) 

@section('content')
@include('masters::Legal._list_shell', ['lgTitle' => 'PLM approval', 'lgSub' => 'Cases waiting for priority, lawyer and hand-over decision', 'lgCrumb' => 'legalPlmsApproval', 'lgPaginator' => $plmsApprovals,
    'lgRows' => 'masters::Legal.plms_approval_list_ajax', 'lgSearchRoute' => route('plmsApprovalSearch')])
<div class="modal lg lg-modal" id="myModal">

    </div>
<form id="delete-form" action="" method="POST">
    {{ method_field('DELETE') }}  {{csrf_field()}}
    <input value="delete" style="display: none;" type="submit">
</form>
@endsection
@section('scripts') 
@include('masters::Legal.legal_search_js')
@include('masters::Legal.legal_case_search_js')
<script>   


jQuery('.delete_type').click(function (event) {
    var action = $(this).attr("href");
    event.preventDefault();
    if (confirm('Do you want to Delete this Complaint?')) {
        jQuery("#delete-form").attr('action', action);
        jQuery("#delete-form").submit();
    } else {
        return false;
    }
});
/**********************************************************************************/
    $("#show").on("hide.bs.collapse", function(){
            $("#enquirySearch").html('Advance Search <i class="fa fa-caret-down" aria-hidden="true"></i>');
    });
    $("#show").on("show.bs.collapse", function(){
        $("#enquirySearch").html('Advance Search <i class="fa fa-caret-up" aria-hidden="true"></i>');
    });
/**********************************************************************************/
$(document).ready(function() {

    $(document).on('click','.approve', function(e) {        

        var action_key =  $(this).attr('data-key');
        var legal_id = $(this).attr('data-id');
        var process_flow = $(this).attr('data-flow-id');
         var are_status = $(this).attr('data-status');
       /* if (confirm('Do you want to Approval Accept?')) {*/
        $.ajax({
            method: 'POST', // Type of response and matches what we said in the route
            url: "{{route('plmsApproveReferBack')}}", // This is the url we gave in the route
            data: {'process_flow':process_flow,'action_key' : action_key,'legal_id' : legal_id,'are_status' : are_status,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
            success: function(response){ // What to do if we succeed
                $("#myModal").html(response);
                //alert(response);
               //window.location.href = response;
            },
        });
        return true;
        /*}else {
            return false;
        } */       
         
    });
/**********************************************************************************/
 $(document).on('click','.referback', function(e) {        

        var action_key =  $(this).attr('data-key');
        var legal_id = $(this).attr('data-id');
        var process_flow = $(this).attr('data-flow-id');
        var are_status = $(this).attr('data-status');
       /* if (confirm('Do you want to Approval Accept?')) {*/
        $.ajax({
            method: 'POST', // Type of response and matches what we said in the route
            url: "{{route('plmsApproveReferBack')}}", // This is the url we gave in the route
            data: {'process_flow':process_flow,'action_key' : action_key,'legal_id' : legal_id,'are_status' : are_status,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
            success: function(response){ // What to do if we succeed
                $("#myModal").html(response);
                //alert(response);
               //window.location.href = response;
            },
        });
        return true;
        /*}else {
            return false;
        } */       
         
    });
/**********************************************************************************/
$(document).on('click','.close_type', function(e) {        
        var legal_id = $(this).attr('data-id');
        var process_flow = $(this).attr('data-flow-id');
       /* if (confirm('Do you want to Approval Accept?')) {*/
        $.ajax({
            method: 'POST', // Type of response and matches what we said in the route
            url: "{{route('legalCloseForm')}}", // This is the url we gave in the route
            data: {'process_flow':process_flow,'legal_id' : legal_id,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
            success: function(response){ // What to do if we succeed
               // $("#myModal").html(response);
               // alert(response);
               // window.location.href = response;
               $("#myModal").html(response);
            },
        });
        return true;
        /*}else {
            return false;
        } */       
         
    });
/**********************************************************************************/
    });
/**********************************************************************************/
</script>

@endsection