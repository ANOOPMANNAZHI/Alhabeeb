@extends('layouts.plms-app')

@section('css')  
<!-- data tables -->
<link rel="stylesheet" href="{{ asset('public/css/datatables.min.css')}}">
@endsection


@section('search_reset', route('leadAssign.assignedList'))

@section('content')
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Assigned Enquiry</div>
        </div>
        {{ Breadcrumbs::render('leadAssign.assignedList') }}
    </div>
</div>

<a  class=" align-right advSearch" href="#" id="enquiry_div" data-toggle="collapse" data-target="#show">
    <i class="fa fa-search" aria-hidden="true"></i>  <span id="enquirySearch"> Advance Search <i class="fa fa-caret-down" aria-hidden="true"></i></span>
</a>
<div class="clearfix"></div>
<div class="row collapse @if(old('fieldName')) show @endif" id="show"  >
	<div class="col-md-12 col-sm-12 dashboardtab">
		<div class="panel tab-border card-box">
         
           @include('sales::enquiry_search')  
           
       </div>
   </div>
</div>

<div class="row">
    <div class="col-md-12 col-sm-12">
        <div class="card  card-box">



            <!-- <div class="card-head">
                <header>Open Enquiry List</header>
                
            </div> -->
            <div class="card-body ">
                <h4>
                 <div id="pagination_info">
                     @include('includes.pagination_info',['paginator' => $assigned_lists])         
                 </div>
                 @can('reassign_tenant')       
                 <button type="button" class="btn btn-circle btn-primary re_Assign align-right"  data-toggle="modal" data-target="#myModal" data-backdrop="static" data-keyboard="false">Group-Reassign</button>
                 @endcan
                 <div class="clr"></div></h4>
                 <div class="table-wrap">
                   <div class="table-responsive1">
                    <table class="table display product-overview mb-30" id="dtBasicExample">
                        <thead>
                           <tr>
                            <th><input type = "checkbox" id = "master" 
                               class = "mdl-switch__input "></th>
                               <th title="Enquiry no">@sortablelink('salesEnquiry.sales_enquiry_no','Enq No',[],[ 'class' => 'sort_url'])</th>
                               <th title="Enquiry date">@sortablelink('salesEnquiry.created_at','Enq Dt',[],[ 'class' => 'sort_url'])</th>
                               <th>@sortablelink('salesEnquiry.sales_enquiry_name','Customer',[],[ 'class' => 'sort_url'])</th>
                               <th>@sortablelink('salesEnquiry.sales_mobile_no','Mob No',[],[ 'class' => 'sort_url'])</th>
                               <th>@sortablelink('unit_type','Unit Type',[],[ 'class' => 'sort_url'])</th>
                               <th>@sortablelink('loc','Location',[],[ 'class' => 'sort_url'])</th>
                               <th>@sortablelink('employee_name','Assigned No',[],[ 'class' => 'sort_url'])</th>
                               <!-- <th title="Remark">@sortablelink('sales_note','Remark',[],[ 'class' => 'sort_url'])</th> -->
                               <th title="Remark">@sortablelink('sales_note','Last Notes',[],[ 'class' => 'sort_url'])</th>
                               <th width="20%">Action</th>
                           </tr>
                           <tr> 
                            <td></td>
                            <td> <input autocomplete="off" type="text" name="sales_enquiry_no" class="search_fields mob" id="sales_enquiry_no" value="{{old('sales_enquiry_no')}}" ></td>
                            <td><input autocomplete="off" type="date" name="created_at" class="search_fields mob" id="created_at" value="{{old('created_at')}}" ></td>
                            <td><input autocomplete="off" type="text" name="sales_enquiry_name" class="search_fields" id="sales_enquiry_name" value="{{old('sales_enquiry_name')}}" ></td>
                            <td><input autocomplete="off" type="text" name="sales_mobile_no" class="search_fields mob" id="sales_mobile_no"  value="{{old('sales_mobile_no')}}" ></td>
                            <td><input autocomplete="off" type="text" name="unitTypes" class="search_fields mob" id="unitTypes"  value="{{old('unitTypes__unit_types_name')}}" ></td>
                            <td><input autocomplete="off" type="text" name="location" class="search_fields mob" id="location"  value="{{old('locations__locations_name')}}" ></td>
                            <td><input autocomplete="off" type="text" name="assigned_person" class="search_fields assigned_person" value="{{old('assignedPerson__employee__employee_name')}}" > </td>
                            <td><input autocomplete="off" type="text" name="sales_note" class="search_fields" id="sales_note" value="{{old('salesNotes__sales_notes')}}" ></td>
                            <td></td>

                        </tr>
                    </thead>
                    <tbody id="enquiry-search">
                       
                        @include('sales::TenantSales.tenant_assigned_list_ajax')	
                        
                    </tbody>
                </table>
                
                
                
            </div>
        </div> 
        <div id="pagination">
            
           {{$assigned_lists->appends(\Request::except(['page','_token']))->links()}}
       </div>    
       <!-- </form>  -->
   </div>
</div>
</div>
</div>
<div class="modal" id="myModal">
    
</div>
<div class="modal" id="myModal1">
    
</div>
@endsection
@section('scripts')
@include('sales::enquiry_search_js')
@include('sales::sales_search_js')
<script>
    $(document).ready(function() {
       

        $("#myModal").on("hidden.bs.modal", function(){
            $("#myModal").html("");
            $(this).removeData('bs.modal');
        });
        $("#myModal1").on("hidden.bs.modal", function(){
            $("#myModal1").html("");
            $(this).removeData('bs.modal');
        });
        /**********************************************************************************/
        $("#show").on("hide.bs.collapse", function(){
            $("#enquirySearch").html('Advance Search <i class="fa fa-caret-down" aria-hidden="true"></i>');
        });
        $("#show").on("show.bs.collapse", function(){
            $("#enquirySearch").html('Advance Search <i class="fa fa-caret-up" aria-hidden="true"></i>');
        });
        /**********************************************************************************/
        $('#master').on('click', function(e) {
            if($(this).is(':checked',true))  
            {
                $(".sub_chk").prop('checked', true);  
            } else {  
                $(".sub_chk").prop('checked',false);  
            }  
        });
        $(".sub_chk").on('click', function(e) {
            $("#master").prop('checked',false);
        });
        $(document).on('click','.re_Assign', function(e) {
           $("#myModal").html(''); 
           var allVals = [];  
           $(".sub_chk:checked").each(function() {  
            allVals.push($(this).attr('value'));
        });  

           if(allVals.length <=0)  
           {  
            alert("Please Select Atleast One Enquiry..!");  
            
            $('#myModal').modal('toggle');
            return false;
        }  else {  


            
            var wrk_flow = $("#wrk_flow").val();
            $('input:hidden[name=enquiryid]').val(allVals);

            $.ajax({
                    method: 'POST', // Type of response and matches what we said in the route
                    url: "{{route('groupReAssignModal')}}", // This is the url we gave in the route
                    data: {'id' : allVals,'wrk_flow' : wrk_flow,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
                    success: function(response){ // What to do if we succeed
                        $("#myModal").html(response);
                    },
                });
            return true;  
        }  
    });
        /**********************************************************************************/
        $(document).on('click','.closed', function(e) {        

            var action_key = $(this).attr('data-id');
            var workflow_id = $(this).attr('datas-id');
            var enquiryid = $(this).attr('datas-enid');
        //alert(workflow_id);
        /*if (confirm('Do you want to Close this Enquiry?')) {*/
            $.ajax({
                method: 'POST', // Type of response and matches what we said in the route
                url: "{{route('approvalAccepts')}}", // This is the url we gave in the route
                data: {'action_key' : action_key,'enquiryid' : enquiryid,'workflow_id' : workflow_id,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
                success: function(response){ // What to do if we succeed
                    $("#myModal1").html(response); 
                },
            });
            return true;
       /* }else {
            return false;
        }*/
        
        
    });

        $(document).on('click','.assignLead', function(e) {        

            /*if (confirm("Are you sure you want to Re-assign ?")) {*/
                var vals = $(this).attr('data-id');
                var workflow = $(this).attr('datas-id');
                $('input:hidden[name=enquiryIds]').val(vals);
                $.ajax({
                method: 'GET', // Type of response and matches what we said in the route
                url: 'leadAssign/re-assign/'+vals+'/'+workflow+'/list', // This is the url we gave in the route
                //data: {'id' : vals,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
                success: function(response){ // What to do if we succeed
                    $("#myModal").html(response); 
                },
            });
                return true;
        /*} else {
            return false;
        }*/  
        
    });
        
    });
</script>
@endsection
