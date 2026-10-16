@extends('layouts.plms-app')
@section('css')
<link href="{{ asset('public/css/legal.css') }}" rel="stylesheet">
@endsection

 @section('search_url', route('referBackCases')) 
 @section('search_reset', route('referBackCases')) 

@section('content')
@include('masters::Legal._list_shell', ['lgTitle' => 'Referred back cases', 'lgSub' => 'Cases sent back for ARE review', 'lgCrumb' => 'referBackCases', 'lgPaginator' => $closedLegalCases,
    'lgRows' => 'masters::Legal.referback_cases_list_ajax', 'lgSearchRoute' => route('activeCasesSearch')])
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
</script>

@endsection