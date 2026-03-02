@extends('layouts.plms-app')

@section('css')  
<!-- data tables -->
<link rel="stylesheet" href="{{ asset('public/css/datatables.min.css')}}">
@endsection

@section('content')
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Assigned Enquiries</div>
        </div>
        {{ Breadcrumbs::render('leadAssign.assignedList') }}
    </div>
</div>
<div class="row">
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
           
			<div class="clr"></div></h4>
              	<div class="table-wrap">
                    <div class="table-responsive">
                        <table class="table display product-overview mb-30" id="dtBasicExample">
                            <thead>
                                <tr>
                                   
                                    <th>Enquiry Name</th>
                                    <th>Company</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Enquiry Source</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($assigned_lists as $assigned_list)
                                <tr>
                                    
                                    <td><a class="no-link" href="{{route('leadAssign.nextStage',[$assigned_list->salesEnquiry->id,$assigned_list->salesEnquiry->work_flow_processes_code])}}">{{$assigned_list->salesEnquiry->sales_enquiry_name}}</a></td>
                                    <td><a class="no-link" href="{{route('leadAssign.nextStage',[$assigned_list->salesEnquiry->id,$assigned_list->salesEnquiry->work_flow_processes_code])}}">{{$assigned_list->salesEnquiry->sales_company_name}}</a></td>
                                    <td><a class="no-link" href="{{route('leadAssign.nextStage',[$assigned_list->salesEnquiry->id,$assigned_list->salesEnquiry->work_flow_processes_code])}}">{{$assigned_list->salesEnquiry->sales_email}}</a></td>
                                    <td><a class="no-link" href="{{route('leadAssign.nextStage',[$assigned_list->salesEnquiry->id,$assigned_list->salesEnquiry->work_flow_processes_code])}}">{{$assigned_list->salesEnquiry->sales_mobile_no}}</a></td>
                                    <td><a class="no-link" href="{{route('leadAssign.nextStage',[$assigned_list->salesEnquiry->id,$assigned_list->salesEnquiry->work_flow_processes_code])}}">{{$assigned_list->salesEnquiry->enquirySource->enquiry_sources_name}}</a></td>
                                    <td>
                                       
                                        
                                        <button title="Re-Assign" type="button" class="btn btn-tbl-user btn-xs assignLead" data-toggle="modal" data-target="#myModal" data-id = "{{$assigned_list->salesEnquiry->id}}" datas-id= "{{$assigned_list->salesEnquiry->work_flow_processes_code}}">
                                            <i class="fa fa-user" aria-hidden="true"></i>
                                        </button>
                                        
                                        
                                        
                                    </td>
                                </tr>
                                
                                @empty
                                <tr>
                                    <td colspan="6" align="center">
                                    <p>No records</p>
                                   </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                        {{$assigned_lists->links()}}
                        
                    </div>
                </div> 
            <!-- </form>  -->
            </div>
        </div>
    </div>
</div>
<div class="modal" id="myModal">
    
</div>
@endsection
@section('scripts')
@include('sales::enquiry_search_js')
<script>
$(document).ready(function() {
    $("#leade_search").validate();
    

    $(document).on('click','.assignLead', function(e) {        

        if (confirm("Are you sure you want to Re-assign ?")) {
            var vals = $(this).attr('data-id');
            var workflow = $(this).attr('datas-id');
            $('input:hidden[name=enquiryIds]').val(vals);
            $.ajax({
                method: 'GET', // Type of response and matches what we said in the route
                url: '../leadAssign/re-assign/'+vals+'/'+workflow+'', // This is the url we gave in the route
                //data: {'id' : vals,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
                success: function(response){ // What to do if we succeed
                    $("#myModal").html(response); 
                },
            });
            return true;
        } else {
            return false;
        }  
         
    });
    
});
</script>
@endsection
