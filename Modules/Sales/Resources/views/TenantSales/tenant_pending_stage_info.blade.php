@extends('layouts.plms-app')

@section('css')  
<!-- data tables -->
<link rel="stylesheet" href="{{ asset('public/css/datatables.min.css')}}">
<link href="{{asset('public/css/formlayout.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">
<link href="https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css" rel="stylesheet">

@endsection

@section('content')
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title"> Final Documentation View</div>
        </div>

        {{ Breadcrumbs::render('tenantPendingStageInfo', $details,$details->salesEnquiry->work_flow_processes_code) }}
    </div>
</div>
<div class="row">
<div class="col">
<div class="card card-box salesLeadBox">
    <div class="card-head">
        <div class="col">
        <h4>
            {{$details->salesEnquiry->sales_enquiry_name}}
           
        </h4>
        </div>
    </div>
    <div class="card-body">
        
        @if($details->salesEnquiry->work_flow_processes_code >= 105)
        <div class="col-sm-6 p-0">
            <dl>
                <dt>Enquiry Owner</dt>
                <dd>{{$details->salesEnquiry->enquiryOwner->username??'NA'}}</dd>
                <dt>Email</dt>
                <dd><a href="mailto:{{$details->salesEnquiry->sales_email}}">{{$details->salesEnquiry->sales_email?? 'NA'}}</a></dd>
                <dt>Phone</dt>
                <dd>{{$details->salesEnquiry->sales_mobile_no}}</dd>
                
            </dl> 
        </div>
        @endif
        <div class="clearfix"></div>
        <div class="w-100 mt-4"></div>
        <a class="btn btn-circle btn-success align-right href="#" data-toggle="collapse" data-target="#show">
			<span id="enquiry_info">Show Enquiry Detail</span>
		</a>
        <div class="clearfix"></div>
        <div id="show" class="collapse">
            <div class="col">
                <div class="row">
                    <h3>Enquiry Information</h3>
                    <div class="w-100"></div>      
                    <div class="col leadInformation">
                        <ul>
                            <li class="bld">Enquiry Owner</li><li>{{$details->salesEnquiry->enquiryOwner->username??'NA'}}</li>
                            <li class="bld">Enquiry Type</li>
                            <li>@if($details->salesEnquiry->tenant_type_id){{$details->salesEnquiry->tenantType->tenant_types_name}}@else {{'NA'}}@endif</li>
                            
                            <li class="bld">Enquiry No</li>
                            <li>{{$details->salesEnquiry->sales_enquiry_no ??  'NA'}}</li>
                            <li class="bld">Referred By</li>
                            <li>{{$details->salesEnquiry->sales_referred_by??  'NA'}}</li>
                            <li class="bld">Price Range</li>
                            <li>@if($details->salesEnquiry->priceRanges->count()>0){{$details->salesEnquiry->priceRanges->implode('price_ranges_name',', ')?? 'NA'}}  @else {{'NA'}} @endif </li>
                            <li class="bld">Location</li>
                            <li>@if($details->salesEnquiry->locations->count()>0){{$details->salesEnquiry->locations->implode('locations_name',', ') ?? 'NA'}}@else {{'NA'}}@endif</li>
                            <li class="bld">Remark</li><li>{{$details->salesEnquiry->sales_note ??  'NA'}}</li>
                            <li class="bld">Alternative No</li><li>{{$details->salesEnquiry->alternative_no ??  'NA'}}</li>
                        </ul>
                    </div>
                    <div class="col leadInformation">
                        <ul>
                            <li class="bld">Unit Type</li>
                            <li>@if($details->salesEnquiry->unitTypes->count()>0){{$details->salesEnquiry->unitTypes->implode('unit_types_name',', ') ?? 'NA'}}@else {{'NA'}} @endif</li>
                            <li class="bld">Status</li>
                            <li>{{$details->salesEnquiry->workFlowProcess->work_flow_processes_name ??  'NA'}}</li>
                            <li class="bld">Move In Date</li>
                            <li>{{$details->salesEnquiry->sales_move_in_date->format('m/Y')}}</li>
                            <li class="bld">Enquiry Date</li>
                            <li>{{$details->salesEnquiry->created_at->format('d/m/Y') ?? 'NA'}}</li>
                            <li class="bld">Company Name</li>
                            <li>{{$details->salesEnquiry->sales_company_name ??  'NA'}}</li>
                            <li class="bld">Square Meter</li>
                            <li>{{$details->salesEnquiry->sales_size ??  'NA'}}</li>
                            <li class="bld">No Of Units</li>
                            <li>{{$details->salesEnquiry->sales_no_of_unit ??  'NA' }}</li>
                            <li class="bld">Source</li>
                            <li>{{$details->salesEnquiry->enquirySource->enquiry_sources_name ??  'NA'}}</li>
                            
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card card-box salesLeadBox">
		<div class="card-head">
			<div class="col"><h4>Enquiry Stage Note</h4></div>
		</div>
		 <div class="card-body">
		 <div class="col">
                <div class="row">
          
                    <div class="col leadInformation">
                         <div class="table-responsive1">
                            <table class="table">
                                <thead>
                                    <tr style="background: #f5f5f5;">
                                        <th>Stage</th>
                                        <th>Note</th>
                                        <th>Created By</th>
                                        <th>Created At</th>
                                    </tr>
                                </thead>
                                <tbody>
								
                                   @forelse ($allNotes as $note)
                                 
                                   @if($note->sales_notes!="")
                                    <tr>
                                        <td>{{$note->workFlowProcess->work_flow_processes_name}} </td>
                                        <td>{{$note->sales_notes}}</td>
                                        <td>{{$note->createdBy->username}} </td>
                                        <td>{{$note->created_at->format('d/m/Y h:i:s')}} </td>
                                    </tr> 
                                    @endif                       
                                    @empty
                                    <tr>
                                        <td colspan="4" align="center">
                                        No Record
                                       </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>

                        </div>
                    </div>
                </div>
            </div>
       
		</div>
	</div>
<div class="card card-box salesLeadBox">
    <div class="card-body">
        @if($details->salesEnquiry->work_flow_processes_code >= 105 && $details->salesEnquiry->work_flow_processes_code <= 106)
        <div class="clr"></div><h4>Documentation @if($details->salesEnquiry->work_flow_processes_code == 106 ) {{'(Add Contract Details In Each Document)'}}@endif</h4>
        
        <div class="table-responsive1">
            <table class="table">
                <thead>
                    <tr style="background: #f5f5f5;">
                        <th>Building Name</th>
                        <th>Unit No</th>
                        <th>Unit Usage</th>
                        <th>Duration</th>
                        <th>Occupant ID</th>
                        
                        
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tenantContracts as $tenantContract)
                    <tr>
                        <td>{{$tenantContract->building->building_name}}</td>
                        <td>{{$tenantContract->unit->unit_code}}</td>
                        <td>{{$tenantContract->unit_usage}}</td>
                        <td>{{$tenantContract->tenant_contract_duration}} {{$tenantContract->tenant_contract_duration_type}}</td>
                        <td>@foreach($tenantContract->tenantDocument as $documents)
                        
                            @if(!empty($documents->tenant_documents_name)) <a target="_blank" href="{{asset('storage/app/'.$documents->tenant_documents_file_name)}}">{{$documents->tenant_documents_name}}</a>  @endif
                            @endforeach
                        </td>
                       
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" align="center">
                        <p>No Record</p>
                       </td>
                    </tr>
                    @endforelse
                   
                </tbody>
            </table>
        </div>
        <div class="clearfix"></div>
        @elseif($details->salesEnquiry->work_flow_processes_code >= 107)
        <div class="clr"></div><h4>Tenant Contract</h4>
        
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr style="background: #f5f5f5;">
                        <th>Contract No</th>
                        <th>Tenant Name</th>
                        <th>Municipality Agr No</th>
                        <th>Rent Amount</th>
                        <th>Action</th>
                        
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tenantContracts as $tenantContract)
                    <tr>
                        <td>{{$tenantContract->tenant_contract_no}}</td>
                        <td>{{$tenantContract->tenant->tenant_name}}</td>
                        <td>{{$tenantContract->tenant_contract_muncipality_agr_no}}</td>
                        <td>{{number_format($tenantContract->tenant_contract_rent,3)}} OMR</td>
                         <td>
                           <!--  <button type="submit" class="btn btn-tbl-edit btn-xs editDocumentation" data-toggle="modal" data-target="#myModal" data-id = "{{$tenantContract->id}}">
                            <i class="fa fa-pencil-square-o"></i> 
                            </button>  -->
                            <button type="submit" class="btn btn-tbl-view btn-xs viewDocumentation" data-toggle="modal" data-target="#myModal" data-id = "{{$tenantContract->id}}">
                                <i class="fa fa-eye"></i>
                            </button>                            

                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" align="center">
                        <p>No Record</p>
                       </td>
                    </tr>
                    @endforelse
                   
                </tbody>
            </table>
        </div>
        @endif
        <div class="clearfix"></div>
        
        @if($details->salesEnquiry->work_flow_processes_code >= 103 && count($salesNotes)>0)
        <div class="add-note-section">
           
            <div class="col-md-12">
                <div class="col p-0">
            <h4><strong>Sales Note</strong><div class="clr"></div></h4>
           
            <div class="table-responsive1">
                <table class="table" id="sale_note_datatable">
                    <thead>
                        <tr style="background: #f5f5f5;">
                            <th>Progress</th>
                            <th>Date & time</th>
                            <th>Created By</th>
                        </tr>
                    </thead>
                    <tbody>
                       @forelse ($salesNotes as $salesNote)
                        <tr>
                            <td>{{$salesNote->sales_notes_note}} </td>
                            <td class="d-t">{{$salesNote->created_at->format('d/m/Y h:m A')}}</td>
                            <td>{{$salesNote->createdBy->username}} </td>
                           
                        </tr>                        
                        @empty
                        <tr>
                            <td colspan="3" align="center">
                            <p>No Record</p>
                           </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

            </div>
        </div>
            </div>
        </div>
        <div class="w-100 mt-5 mb-5"></div>
        
      @endif
    
    
    </div> 
                   
</div>

</div>
</div>
<form id="delete-form" action="" method="POST">
    {{ method_field('DELETE') }}  {{csrf_field()}}
    <input value="delete" style="display: none;" type="submit">
</form>        <!-- The Modal -->
<div class="modal" id="myModal">
    
</div>
<div class="modal" id="myModal1">
    
</div>
@endsection
@section('scripts')
<script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script> 
<script>
$(function() {
    var ar_status = <?php echo json_encode($ar_status)?>;
    if(ar_status== false){
        $("#accept").show();
        $("#reject").hide(); 
    }else{
        $("#reject").show(); 
        $("#accept").hide();
    }
    $('.toggle-event').change(function() {
        var checked = $(this).prop('checked');
        var value = $(this).val();
        var enquiryid = $("#enquiryid").val();
        if(checked == false){
            $.ajax({
                method: 'POST', // Type of response and matches what we said in the route
                url: "{{route('accept-reject')}}", // This is the url we gave in the route
                data: {'id' : value,'status' : 2,'enqId':enquiryid,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
                success: function(response){// What to do if we succeed
                    if(response=='false'){
                        $("#reject").hide(); 
                        $("#accept").show();
                    }else {
                        $("#reject").show();
                        $("#accept").hide();
                    }
                },
            });
        }else{
            $.ajax({
                method: 'POST', // Type of response and matches what we said in the route
                url: "{{route('accept-reject')}}", // This is the url we gave in the route
                data: {'id' : value,'status' : 1,'enqId':enquiryid,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
                success: function(response){ // What to do if we succeed
                    if(response == 'false'){
                        $("#reject").hide(); 
                        $("#accept").show();
                    }else {
                        $("#reject").show();
                        $("#accept").hide();
                    }
                        
                    
                    
                },
            });
        }
        
    })
  })
$(document).ready(function() {
    $("#leade_search").validate();
    $("#sales_note_modal").validate();
    @if(count($salesNotes)>0)
    $('#sale_note_datatable').DataTable({
        "bPaginate": false,
        "bInfo" : false,
        "ordering": false
    });
    @endif


   
    
    $('.viewDocumentation').on('click', function(e) {        

        var tenant_id = $(this).attr('data-id');
        $.ajax({
            method: 'GET', // Type of response and matches what we said in the route
            url:"{{ url('/') }}/tenantContract/"+tenant_id+"", // This is the url we gave in the route
            //data: {'id' : activity_id,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
            success: function(response){ // What to do if we succeed
                $("#myModal").html(response); 
            },
        });
        return true;
        
         
    });
   
   
    $("#myModal").on("hidden.bs.modal", function(){
        $("#myModal").html("");
        $(this).removeData('bs.modal');
    });
    $("#myModal1").on("hidden.bs.modal", function(){
        $("#myModal1").html("");
        $(this).removeData('bs.modal');
    });
    /****************************************************************************88888*/  
    $("#show").on("hide.bs.collapse", function(){
			$("#enquiry_info").html('Show Enquiry Details');
	});
	$("#show").on("show.bs.collapse", function(){
		$("#enquiry_info").html('Hide Enquiry Details');
	});
});
</script>
@endsection
