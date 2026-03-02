@extends('layouts.plms-app')
@section('css')  

<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">
<!-- data tables -->
<link rel="stylesheet" href="{{ asset('public/css/datatables.min.css')}}">
@endsection

@section('search_url', route('complaintAssignedList'))
@section('search_reset', route('complaintAssignedList'))
@section('content')
    <!-- start widget -->
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title"> Assigned Complaint</div>
        </div>
         {{ Breadcrumbs::render('complaintAssignedList') }}
    </div>
</div>
<a  class=" align-right advSearch" href="#" id="enquiry_div" data-toggle="collapse" data-target="#show">
<i class="fa fa-search" aria-hidden="true"></i>  <span id="enquirySearch"> Advance Search <i class="fa fa-caret-down" aria-hidden="true"></i></span>
</a>
<div class="clearfix"></div>
<div class="row collapse @if(old('fieldName')) show @endif" id="show"  >
    <div class="col-md-12 col-sm-12 dashboardtab">
        <div class="panel tab-border card-box">
           
           @include('maintenance::enquiry_search')  
 
        </div>
    </div>
</div>
 <div class="row">
   <div class="col-md-12 col-sm-12">
        <div class="card  card-box">
            
            <div class="card-body ">
            <h4>
            <div id="pagination_info">
                     @include('includes.pagination_info',['paginator' => $ComplaintEnquiries])         
                 </div>
            @can('sub_assign')
            <button type="button" class="btn btn-circle btn-primary groupSubAssign align-right"  data-toggle="modal" data-target="#myModal" data-id="" data-backdrop="static" data-keyboard="false">Assign</button>
            @endcan
            @can('reassign')
            <button type="button" class="btn btn-circle btn-primary groupReAssign align-right"  data-toggle="modal" data-target="#myModal" data-id="" data-backdrop="static" data-keyboard="false">Group-Reassign</button>
            @endcan
             <div class="clr"></div>
            </h4>
            <div class="table-wrap">
                <div class="table-responsive">   
              <table class="table display product-overview mb-30" id="dtBasicExample">
                <thead>
                    <tr>
                        <th> @can('sub_assign')<input type = "checkbox" id = "master" class = "mdl-switch__input ">@endcan</th>
                        <th>@sortablelink('complaint_ticket_no','Tkt No',[],[ 'class' => 'sort_url'])</th>
                        <th>@sortablelink('complaintEnquiry.complaint_date','Cmp Date',[],[ 'class' => 'sort_url'])</th>
                        <th>@sortablelink('complaintEnquiry.complaint_mob_no','Cmp Mob No',[],[ 'class' => 'sort_url'])</th>
                        <th>@sortablelink('building_name','Building',[],[ 'class' => 'sort_url'])</th>
                        <th>@sortablelink('unit_code','Unit',[],[ 'class' => 'sort_url'])</th>
                        <th>@sortablelink('works_code','Category',[],[ 'class' => 'sort_url'])</th>                      
                        <th>@sortablelink('concat','Assigned To',[],[ 'class' => 'sort_url'])</th> 
                        <th>@sortablelink('ticket_status','Status',[],[ 'class' => 'sort_url'])</th> 
                        <th>Action</th>
                    </tr>
                    <tr>
                        <td></td>
                        <!-- <td> <input autocomplete="off" type="text" name="complaint_no" class="search_fields mob"id="complaint_no" value="{{old('complaint_no')}}" ></td> -->
                        <td> <input autocomplete="off" type="text" name="ticket_no" class="search_fields mob" id="ticket_no" value="{{old('complaintTicketsAll__complaint_ticket_no')}}" ></td>
                        <td><input autocomplete="off" type="date" name="created_at" class="search_fields mob" id="created_at" value="{{old('complaint_date')}}" ></td>
                        <td><input autocomplete="off" type="text" name="complaint_mob_no" class="search_fields mob" id="complaint_mob_no"  value="{{old('complaint_mob_no')}}" ></td>
                        <td><input autocomplete="off" type="text" name="building_id" class="search_fields mob" id="building_id"  value="{{old('building__building_name')}}" ></td>
                        <td><input autocomplete="off" type="text" name="unit_id" class="search_fields mob" id="unit_id"  value="{{old('unit__unit_code')}}" ></td>
                        <td><input autocomplete="off" type="text" name="category" class="search_fields mob" id="category"  value="{{old('complaintTicketsAll__work__works_code')}}" ></td>


                        <td><input autocomplete="off" type="text" name="assigned_to" class="search_fields mob" id="assigned_to"  value="{{old('assigned_to')}}" ></td>



                        <!-- <td><input autocomplete="off" type="text" name="location_id" class="search_fields mob" id="location_id"  value="{{old('location_id')}}" >
                         <input autocomplete="off" type="hidden" name="url_route" id="url_route"  value="{{route('complaintSearch')}}" ></td> -->
                        <td><select name="status" class="searchFields search_fields mob" id="status" style="width:100px;">
                                <option value="">Show All</option>
                                <option {{ (old('status') ===0)? 'selected' : '' }} value="0">{{'Open'}}</option>
                                <option {{ (old('status') === 1)? 'selected' : '' }} value="1">{{'Inprogress'}}</option>
                                
                            </select> 
                        </td>
                        <td></td>
                    </tr>
                </thead>
                <tbody id="enquiry-search">
                                                               
                    @include('maintenance::ComplaintStages.complaint_assigned_list_ajax')  
                                                            
                </tbody>
                </table>
                    </div>
                </div>
                        
                 <div class="row "  id="pagination">                                
                     {{$ComplaintEnquiries->appends(\Request::except(['page','_token','ajax']))->links()}}
                </div> 
                    
            </div>
        </div>
    </div>
</div>
<div class="modal" id="myModal">

</div>
<form id="delete-form" action="" method="POST">
    {{ method_field('DELETE') }}  {{csrf_field()}}
    <input value="delete" style="display: none;" type="submit">
</form>
@endsection
@section('scripts') 
@include('maintenance::enquiry_search_js')
@include('maintenance::search_view_js')
@include('maintenance::complaint_js')
<script type="text/javascript">
$(document).ready(function() {
    $('.groupSubAssign').on('click', function(e) {  

        var allVals = []; 
        var workflow;  
        var complaint_id;
        $(".sub_chk:checked").each(function() {  
            allVals.push($(this).attr('value'));
            workflow =$(this).attr('datas-id');
            complaint_id =$(this).attr('data-id');
        });  

        if(allVals.length <=0)  
        {  
            alert("Please Select Atleast One Ticket..!"); return false;  
            
        }else {  

            $.ajax({
                method: 'POST', // Type of response and matches what we said in the route
                url: "{{route('subAssignModal')}}", // This is the url we gave in the route
                data: {'complaint_id' : complaint_id,'workflow' : workflow,'id' : allVals,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
                success: function(response){ // What to do if we succeed
                    $("#myModal").html(response); 
                },
            });
             
        } 
        
    });
/********************************************************************************/

    $(document).on('click','.SubReAssign',function(){
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
    $('.groupReAssign').on('click', function(e) {  

        var allVals = []; 
        var workflow;  
        var complaint_id;
        $(".sub_chk:checked").each(function() {  
            allVals.push($(this).attr('value'));
            workflow =$(this).attr('datas-id');
            complaint_id =$(this).attr('data-id');
        });  

        if(allVals.length <=0)  
        {  
            alert("Please Select Atleast One Ticket..!"); return false;  
            
        }else {  

            $.ajax({
                method: 'POST', // Type of response and matches what we said in the route
                url: "{{route('groupAssignModal')}}", // This is the url we gave in the route
                data: {'complaint_id' : complaint_id,'workflow' : workflow,'id' : allVals,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
                success: function(response){ // What to do if we succeed
                    $("#myModal").html(response); 
                },
            });
             
        } 
        
    });
/**********************************************************************************/
    $(document).on('click','.ReAssign',function(){
        var allVals = []; 
        var complaint_id = $(this).attr('data-id');
        allVals.push($(this).attr('datas-id'));

        var workflow = $(this).attr('datass-id'); 

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
});
</script>
@endsection
