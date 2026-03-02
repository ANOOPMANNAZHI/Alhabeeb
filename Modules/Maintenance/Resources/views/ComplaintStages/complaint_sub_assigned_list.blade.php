@extends('layouts.plms-app')
@section('css')  

<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">
<!-- data tables -->
<link rel="stylesheet" href="{{ asset('public/css/datatables.min.css')}}">
@endsection

@section('search_url', route('complaintSubAssignedList'))
@section('search_reset', route('complaintSubAssignedList'))
@section('content')
    <!-- start widget -->
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title"> Sub Assigned </div>
        </div>
         {{ Breadcrumbs::render('complaintSubAssignedList') }}
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
            @can('sub_reassign')  
            <button type="button" class="btn btn-circle btn-primary groupSubAssign align-right"  data-toggle="modal" data-target="#myModal" data-id="" data-backdrop="static" data-keyboard="false">Group-Reassign</button>
            @endcan
            <div class="clr"></div>
            </h4>
            <div class="table-wrap">
                <div class="table-responsive">   
              <table class="table display product-overview mb-30" id="dtBasicExample">
                <thead>
                    <tr>
                        <th>@can('sub_reassign')  <input type = "checkbox" id = "master" class = "mdl-switch__input "> @endcan</th>
                        <th>@sortablelink('complaint_no','Cmp No',[],[ 'class' => 'sort_url'])
                        </th>
                        <th>@sortablelink('complaint_date','Cmp Date',[],[ 'class' => 'sort_url'])</th>
                        <th>@sortablelink('complainer_name','Cmp Name',[],[ 'class' => 'sort_url'])</th>
                        <th>@sortablelink('complaint_mob_no','Mob No',[],[ 'class' => 'sort_url'])</th>
                        <th>@sortablelink('building_name','Building',[],[ 'class' => 'sort_url'])</th>                        
                        <th>@sortablelink('unit_code','Unit',[],[ 'class' => 'sort_url'])</th>
                        
                        <th>@sortablelink('assigned_name','Supervisor',[],[ 'class' => 'sort_url'])</th>
                        <th>@sortablelink('sub_assigned_name','Technician',[],[ 'class' => 'sort_url'])</th>
                        <th title="Status">Status</th> 
                        <th>Action</th>
                    </tr>
                    <tr>
                        <td></td>
                        <td> <input autocomplete="off" type="text" name="complaint_no" class="search_fields mob" id="complaint_no" value="{{old('complaint_no')}}" ></td>
                        <td><input autocomplete="off" type="date" name="created_at" class="search_fields mob" id="created_at" value="{{old('created_at')}}" ></td>
                        <td><input autocomplete="off" type="text" name="complainer_name" class="search_fields mob" id="complainer_name" value="{{old('complainer_name')}}" ></td>
                        <td><input autocomplete="off" type="text" name="complaint_mob_no" class="search_fields mob" id="complaint_mob_no"  value="{{old('complaint_mob_no')}}" ></td>
                        <td><input autocomplete="off" type="text" name="building_id" class="search_fields mob" id="building_id"  value="{{old('building__building_name')}}" ></td>
                        
                        <td><input autocomplete="off" type="text" name="unit_id" class="search_fields mob" id="unit_id"  value="{{old('unit__unit_code')}}" >
                        <input autocomplete="off" type="hidden" name="url_route" id="url_route"  value="{{route('complaintSearch')}}" ></td>
                        

                        <td><input autocomplete="off" type="text" name="assignedPerson" class="search_fields mob" id="assignedPerson"  value="{{old('assignedPerson__employee__employee_name')}}" ></td>

                        <td><input autocomplete="off" type="text" name="subAssignedPerson" class="search_fields mob" id="subAssignedPerson"  value="{{old('subAssignedPerson__employee__employee_name')}}" ></td>
                        <td>
                           <!--  <select name="status" class="searchFields search_fields mob" id="status" style="width:100px;">
                                <option value="">Show All</option>
                                
                                <option {{ (old('status') == 0)? 'selected' : '' }} value="0">{{'Open'}}</option>
                                <option {{ (old('status') == 1)? 'selected' : '' }} value="1">{{'Partialy Closed'}}</option>
                                <option {{ (old('status') == 2)? 'selected' : '' }} value="2">{{'Closed'}}</option>
                                
                            </select> -->
                        </td>
                        <td></td>
                    </tr>
                </thead>
                <tbody id="enquiry-search">
                                                               
                    @include('maintenance::ComplaintStages.complaint_sub_assigned_list_ajax')  
                                                            
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
<div class="modal" id="myModal"></div>
<div class="modal" id="myModal_close"></div>
<form id="delete-form" action="" method="POST">
    {{ method_field('DELETE') }}  {{csrf_field()}}
    <input value="delete" style="display: none;" type="submit">
</form>
@endsection
@section('scripts') 

@include('maintenance::enquiry_search_js')
@include('maintenance::search_js')
@include('maintenance::complaint_js')
<script type="text/javascript">
$(document).ready(function() {
    $('.groupSubAssign').on('click', function(e) {  

        var allVals = []; 
        var assign_id = []; 
        var workflow;  
        var complaint_id;
        $(".sub_chk:checked").each(function() {
            var d = $(this).attr('value');
            var result = d.substring(1, d.length-1);
            var str_array = result.split(',');
            for(var i = 0; i < str_array.length; i++) {
                
                allVals.push(str_array[i]);
            }
            workflow =$(this).attr('datas-id');
            assign_id.push($(this).attr('datassign-id'));
            complaint_id =$(this).attr('data-id');
        });  

        if(allVals.length <=0)  
        {  
            alert("Please Select Atleast One Ticket..!"); return false;  
            
        }else {  

            $.ajax({
                method: 'POST', // Type of response and matches what we said in the route
                url: "{{route('subReAssignModal')}}", // This is the url we gave in the route
                data: {'complaint_id' : complaint_id,'assign_id' : assign_id,'workflow' : workflow,'id' : allVals,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
                success: function(response){ // What to do if we succeed
                    $("#myModal").html(response); 
                },
            });
             
        } 
        
    });
/********************************************************************************/
    $('.SubReAssign').on('click', function(e) {
        var allVals = [];
         var assign_id = []; 
        var complaint_id = $(this).attr('data-id');
        var d = $(this).attr('datas-id');
        var result = d.substring(1, d.length-1);
        var str_array = result.split(',');
        for(var i = 0; i < str_array.length; i++) {
            
            allVals.push(str_array[i]);
        }
        

        var workflow = $(this).attr('datass-id'); 
        assign_id.push($(this).attr('datassign-id'));

        $.ajax({
            method: 'POST', // Type of response and matches what we said in the route
            url: "{{route('subReAssignModal')}}", // This is the url we gave in the route
            data: {'complaint_id' : complaint_id,'assign_id' : assign_id,'workflow' : workflow,'id' : allVals,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
            success: function(response){ // What to do if we succeed
                $("#myModal").html(response); 
            },
        });
        return true;  
        
    });
});
</script>

@endsection
