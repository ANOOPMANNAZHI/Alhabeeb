@extends('layouts.plms-app')
@section('css')  
<!-- data tables -->
<!-- <link rel="stylesheet" href="{{ asset('public/css/datatables.min.css')}}"> -->
<link href="{{asset('public/css/formlayout.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">
<style type="text/css">
  /* Some CSS styling */
  #sketchpadapp {
    /* Prevent nearby text being highlighted when accidentally dragging mouse outside confines of the canvas */
    -webkit-touch-callout: none;
    -webkit-user-select: none;
    -khtml-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
  }
  .leftside {
    float:left;
    width:220px;
    height:285px;
    background-color:#def;
    padding:10px;
    border-radius:4px;
  }
  .rightside {
    float:left;
    margin-left:10px;
  }
  #sketchpad {
    float:left;
    border:2px solid #888;
    border-radius:4px;
    position:relative; /* Necessary for correct mouse co-ords in Firefox */
  }
  #clearbutton {
    font-size: 15px;
    padding: 10px;
    -webkit-appearance: none;
    background: #eee;
    border: 1px solid #888;
  }

</style>
@endsection

@section('content')
<!-- start widget -->
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">Service Report</div>
    </div>
    {{ Breadcrumbs::render('technicianServiceReportClosed',$ticket,$ticket->sub_assign_to) }}
  </div>
</div>

<div class="row" >
  <div class="col-sm-12">
    <div class="card-box">
     <div class="card-head">
      <h4>
        @if($ticket->complaintServiceReport->complaint_assign_status < 3)
        <a title="Attended" href="{{route('serviceReportStatusButtonUpdate',[$ticket->complaint_service_report_id,2])}}" class="btn btn-circle btn-primary float-right" >Attended</a>

        <a title="Completed" href="{{route('serviceReportStatusButtonUpdate',[$ticket->complaint_service_report_id,3])}}" class="btn btn-circle btn-primary float-right" >Completed</a>

        @unlessrole('technician')
        <a title="Closed" href="{{route('serviceReportStatusButtonUpdate',[$ticket->complaint_service_report_id,4])}}" class="btn btn-circle btn-primary float-right" >Closed</a>
        @endunlessrole
        @endif
        <div class="clr"></div>
      </h4>
    </div>
    <form action="#" id="form_sample_2" class="form-horizontal">
      <div class="card-body row"> 

        <div class="col-md-6 p-t-10">
          <div class="row">
            <div class="col-md-5"><b> Complaint No  </b></div>
            <div class="col-md-1 s-clm">:</div>
            <div class="col-md-6"><span>{{$ticket->complaintChecklist->complaintEnquiry->complaint_no}}</span></div>
          </div>
        </div>


        <div class="col-md-6 p-t-10">
          <div class="row">
            <div class="col-md-5"><b>Assigned to  </b></div>
            <div class="col-md-1 s-clm">:</div>
            <div class="col-md-6"><span>
              @if($ticket->complaintChecklist->sub_assigned_to)
              {{$ticket->complaintChecklist->subAssignedPerson->username}}
              @endif
            </span></div>
          </div>
        </div> 
        <div class="col-md-6 p-t-10">
          <div class="row">
            <div class="col-md-5"><b>Unit  </b></div>
            <div class="col-md-1 s-clm">:</div>
            <div class="col-md-6"><span>{{$ticket->complaintChecklist->complaintEnquiry->Unit->unit_no??''}}</span></div>
          </div>
        </div>
        <div class="col-md-6 p-t-10">
          <div class="row">
            <div class="col-md-5"><b>Complaint Date</b></div>
            <div class="col-md-1 s-clm">:</div>
            <div class="col-md-6"><span>{{$ticket->complaintChecklist->complaintEnquiry->complaint_date->format('d/m/Y')}}</span></div>
          </div>
        </div>
        <div class="col-md-6 p-t-10">
          <div class="row">
            <div class="col-md-5"><b>Location </b></div>
            <div class="col-md-1 s-clm">:</div>
            <div class="col-md-6"><span>{{$ticket->complaintChecklist->complaintEnquiry->location->locations_name}}</span></div>
          </div>
        </div>
        <div class="col-md-6 p-t-10">
          <div class="row">
            <div class="col-md-5"><b>Building</b></div>
            <div class="col-md-1 s-clm">:</div>
            <div class="col-md-6"><span>{{$ticket->complaintChecklist->complaintEnquiry->building->building_name}}</span></div>
          </div>
        </div>
		<div class="col-md-6 p-t-10">
          <div class="row">
            <div class="col-md-5"><b>Service Report No</b></div>
            <div class="col-md-1 s-clm">:</div>
            <div class="col-md-6"><span>{{$ticket->complaintServiceReport->service_report_no}}
              </span></div>
          </div>
        </div>
        <div class="col-md-6 p-t-10">
          <div class="row">
            <div class="col-md-5"><b>Status</b></div>
            <div class="col-md-1 s-clm">:</div>
            <div class="col-md-6"><span>{{$ticket->complaintServiceReport->ServiceReportStatusName}}</span></div>
          </div>
        </div>
        <div class="col-md-6 p-t-10">
          <div class="row">
            <div class="col-md-5"><b>Description</b></div>
            <div class="col-md-1 s-clm">:</div>
            <div class="col-md-6"><span>{{$ticket->complaintServiceReport->complaint_assign_note}}</span></div>

          </div>
          <div class="row float-right">
            <button type="button" class="btn btn-circle btn-primary align-right updateStatus" title="Edit" data-toggle="modal" data-target="#myModal" data-id="{{$ticket->complaint_service_report_id}}" datas-id = "{{$ticket->complaintChecklist->complaintEnquiry->id}}" dataa-id="note"><i class="fa fa-pencil"></i></button>
          </div>
        </div>

      </div>
    </form>
  </div>
</div>

<!-- ticket tabkle-->
<div class="col-md-12">
  <div class="card-box">
   <div class="card-head">
    <header>Tickets</header>
  </div>
  <div class="card-body">
    <table class="table display product-overview mb-30" id="">
      <thead>
        <tr>
          <th>Ticket</th>
          <th>Category</th>
          <th>Description</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($checklists as $checklist)

        <tr>
          <td>{{$checklist->complaint_ticket_no}}</td>
          <td>{{$checklist->work->works_code}} </td>
          <td>{{$checklist->checklist_desc}}</td>

        </tr>                     
        @empty
        <tr>
          <td colspan="3" >
            <p  align="center">No Record</p>
          </td>
        </tr>
        @endforelse    
      </tbody>
    </table>
  </div>
</div>
</div>
<!--ends -->
<!-- start note -->
<div class="col-sm-12">
  <div class="card card-box salesLeadBox">
    <div class="card-head">
     <div class="col"><h4>Note</h4></div>
   </div>
   <div class="card-body">
    <div class="dataSearchBox">
      <div class="add-note-section">

       <form  id="sales_note-form" autocomplete="off" action="{{route('storeServiceReportNote')}}" method="POST" enctype="multipart/form-data" data-toggle="validator">
         {{csrf_field()}}
         <div class="col-sm-12">
           <div class="form-group">
            <label for="simpleFormEmail">Note</label>
            <textarea class="form-control" rows="2" required name="report_notes" placeholder="Enter Note"></textarea>
            <input type="hidden" name="service_report_id" id="service_report_id" value="{{$ticket->complaint_service_report_id}}">

          </div>
        </div>
        <div class="col-sm-2"><button type="submit" class="btn btn-primary">Save</button></div>

      </form>
       <div class="clr"></div>
      <div class="col-sm-12"></div>

</div>
</div>
<!-- -->
      
       <div class="table-responsive1">
        <table class="table" id="note_datatable">
         <thead>
          <tr style="background: #f5f5f5;">
           <th>Note</th>
           <th>Stage</th>
           <th>Created By</th>
           <th>Date & Time</th>

         </tr>
       </thead>
       <tbody>

         @forelse ($ServiceReportNotes as $note)

         <tr>
          <td>{{$note->desc}} </td>
          <td>{{$note->ServiceReportStageName}} </td>
          <td>{{$note->createdBy->username}} </td>
          <td>{{$note->created_at->format('d/m/Y h:i:s')}}</td>
        </tr>                     
        @empty
        <tr>
          <td colspan="4" >
            <p  align="center">No Record</p>
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>

<!-- -->
</div>
</div>
</div>

<!-- end note-->
<!--complaint category -->
<div class="col-md-12">
  <div class="card-box">
   <div class="card-body">
    <div class="dataSearchBox ">
      <form autocomplete="off" action="{{route('complaintStage.store')}}" method="POST" id="item_form"" class="form-horizontal" enctype="multipart/form-data" data-toggle="validator">
        {{csrf_field()}}
        <div><h4>Item</h4></div>
        <div class="row">
          <div class="col-sm-4">
            <div class="form-group">
              <label for="fieldName">Item Name</label>
              <div class="p-relative">
               <i class="fa fa-address-book-o icn-add" aria-hidden="true"></i>
               <select class="form-control" required name="item" required>
                <option value="">Select Item</option>
                @foreach($inventory_items as $item)
                <option value="{{$item->id}}">{{$item->inventories_name}}</option>
                @endforeach
              </select>
            </div>
          </div>
        </div>
        <div class="col-sm-2">
          <div class="form-group">
            <label for="quantity"> Quantity/Weight</label>
            <div class="p-relative">
             <i class="fa fa-align-left icn-add" aria-hidden="true"></i>
             <input required type="text" class="form-control" name="quantity" id="quantity" placeholder="Enter Quantity/Weight" value="" data-rule-pattern="\d{1,9}(\.\d{0,3})?" data-msg-pattern="Allowed only Numeric and Decimal Values">
           </div>
         </div>
       </div>
       <div class="col-sm-3">
            <div class="form-group">
              <label for="fieldName">Category</label>
              <div class="p-relative">
               <!--   <i class="fa fa-address-book-o icn-add" aria-hidden="true"></i> -->
               <select class="form-control" required name="checklist" required>
                
                @foreach($checklists as $item)
                <option value="{{$item->id}}">{{$item->work->works_code}}</option>
                @endforeach
              </select>
            </div>
          </div>
        </div>
        <div class="col-sm-3">
          <div class="form-group">
            <label for="quantity"> Material Charge</label>
            <div class="p-relative">
             <i class="fa fa-align-left icn-add" aria-hidden="true"></i>
             <input required type="text" class="form-control" name="material_charge" id="material_charge" placeholder="Enter Material Charge" value="" data-rule-pattern="\d{1,9}(\.\d{0,3})?" data-msg-pattern="Allowed only Numeric and Decimal Values">
           </div>
         </div>
       </div>
       <div class="col-sm-4">
          <div class="form-group">
            <label for="quantity"> Labour Charge</label>
            <div class="p-relative">
             <i class="fa fa-align-left icn-add" aria-hidden="true"></i>
             <input required type="text" class="form-control" name="labour_charge" id="labour_charge" placeholder="Enter Labour Charge" value="" data-rule-pattern="\d{1,9}(\.\d{0,3})?" data-msg-pattern="Allowed only Numeric and Decimal Values">
           </div>
         </div>
       </div>
       <div class="col-sm-2">
          <div class="form-group">
            <label for="tax_percentage_display">Tax %</label>
            <div class="p-relative">
             <i class="fa fa-percent icn-add" aria-hidden="true"></i>
             <input type="text" class="form-control" id="tax_percentage_display" value="{{numberFormat($taxPercentage)}}" readonly>
           </div>
         </div>
       </div>
       <div class="col-sm-2">
          <div class="form-group">
            <label for="total_charge_display">Total (with Tax)</label>
            <div class="p-relative">
             <i class="fa fa-money icn-add" aria-hidden="true"></i>
             <input type="text" class="form-control" id="total_charge_display" value="0.000" readonly>
           </div>
         </div>
       </div>

       <div class="col-sm-1">
        <input type="hidden" name="complaint_service_report_id" id="complaint_service_report_id" value="{{$ticket->complaint_service_report_id}}">
        <div class="dataSearchLabel w-100 margin" style="margin-bottom: 31px;"></div>
        <button type="submit" class="btn btn-primary">Add</button>
      </div>
    </div>
  </form>
</div>
</div>
</div>
</div>
<!-- ends-->
<script>
  (function() {
    var taxPercentage = parseFloat(@json($taxPercentage)) || 0;

    function recalcItemTotal() {
      var material = parseFloat(document.getElementById('material_charge').value) || 0;
      var labour = parseFloat(document.getElementById('labour_charge').value) || 0;
      var taxAmount = (material + labour) * taxPercentage / 100;
      var total = material + labour + taxAmount;
      document.getElementById('total_charge_display').value = total.toFixed(3);
    }

    document.getElementById('material_charge').addEventListener('keyup', recalcItemTotal);
    document.getElementById('labour_charge').addEventListener('keyup', recalcItemTotal);
  })();
</script>
<!-- ticket tabkle-->
<div class="col-md-12">
  <div class="card-box">
    <div class="card-body">
      <table class="table display product-overview mb-30" id="">
        <thead>
          <tr>
            <th>Item Name</th>
            <th>Quantity</th>
            <th>Material Charge</th>
            <th>Labour Charge</th>
            <th>Tax</th>
            <th>Total (with Tax)</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>

         @forelse ($complaintServiceReportInv as $item)

         <tr>
          <td>{{$item->inventory->inventories_name}} </td>
          <td>{{$item->quantity}}</td>
          <td>{{numberFormat($item->material_charge)}} OMR</td>
          <td>{{numberFormat($item->labour_charge)}} OMR</td>
          <td>{{numberFormat($item->tax_amount)}} OMR ({{numberFormat($item->tax_percentage)}}%)</td>
          <td>{{numberFormat($item->total_charge)}} OMR</td>
          <td> 

            <button type="button" class="btn btn btn-tbl-edit btn-xs itemEdit" title="Close" data-toggle="modal" data-target="#myModal" data-id="{{$item->id}}"><i class="fa fa-pencil"></i></button>

            <a href="{{route('complaintStage.destroy',$item->id)}}" title="Delete" class="btn btn-tbl-delete btn-xs delete_type">
              <i class="fa fa-trash-o "></i>
            </a>
          </td>
        </tr>                     
        @empty
        <tr>
          <td colspan="7" >
            <p  align="center">No Record</p>
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
</div>
<!--ends -->
<!-- Image upload-->
<div class="col-md-12">
  <div class="card-box">
    <div class="card-body">
      <div class="sub-head">Image Upload (Max : {{$upload_size/1000000}} MB)</div>
      <div class="dataSearchBox ">
        <form autocomplete="off" action="{{route('reportImageUpload')}}" method="POST" id="img_form"" class="form-horizontal" enctype="multipart/form-data" data-toggle="validator">
          {{csrf_field()}}
          <div class="field_wrapper">
          <div class="row">
           <div class="col-sm-6">
            <div class="form-group">
				<div class="p-relative">
				<div class="control-group input-group increment" id="1">
					
					<i class="fa fa-paperclip icn-add" aria-hidden="true"></i>
					<input type="file" name="report_image_file_name[1]" id="report_image_file_name[1]" class="form-control upload report_image_file_name" style="width: 70%;">
					<div class="input-group-btn" style="display: none;"> 
                        <button class="btn btn-danger remove_doc_button" type="button"><i class="fa fa-trash-o "></i></button>
                    </div>
                    <label id="report_image_file_name[1]-error" class="error" for="report_image_file_name[1]"></label>
                </div>
              </div>
              </div>
              </div>
              <div class="input-group-btn"> 
                    <button class="btn btn-default btn-reset" type="button"><i class="glyphicon glyphicon-remove"></i>Clear</button>
                   <!-- <button class="btn btn-success" type="button"><i class="glyphicon glyphicon-plus"></i>Add</button> -->
				</div>
				</div>
                          
                <input type="hidden" name="serviceReport_id" id="serviceReport_id" value="{{$ticket->complaint_service_report_id}}">
                <input type="hidden" name="report_stage" id="report_stage" value="{{ $ticket->complaintServiceReport->complaint_assign_status}}">

            </div>
        
          <button type="submit" class="btn btn-primary">Upload</button>
        </div>
	
       
      </form>
    </div>
  </div>
</div>
</div>
@if($images->count()>0)
<div class="col-sm-12">
  <div class="card-box">
    <div class="card-head">
      <header>Gallery</header>
    </div>
    <div class="card-body row">
      <div id="aniimated-thumbnials" class="list-unstyled  clearfix">
        @foreach ($images as $image) 
       @php $ext = strtolower(pathinfo($image->image_path_thumbnail, PATHINFO_EXTENSION)); @endphp
        @if(in_array($ext, $supported_image))
        <div class="balance m-b-20 field_wrapper ro"> 
          <a href="{{asset('storage/app/'.$image->image_path_file_name)}}" target="_blank"  data-sub-html="Doc, PDF, Docx">
			<img class="img-fluid img-thumbnail" src="{{asset('public/img/doc_download.png')}}" alt="" title =""> </a> 
            <input type="hidden" name="img_path_id" id="img_path_id" value="{{$image->id}}"> 
            <button type="button" class=" btn btn-danger remove_image" style="margin-top: 5px">Remove</button>
        </div>
        @else
        <div class="balance m-b-20 field_wrapper ro"> 
          <a href="{{asset('storage/app/'.$image->image_path_file_name)}}" data-sub-html="Images" target="_blank">
           <img class="img-fluid img-thumbnail" src="{{asset('storage/app/'.$image->image_path_thumbnail)}}" alt="" title =""> </a> 
            <input type="hidden" name="img_path_id" id="img_path_id" value="{{$image->id}}"> 
                 <button type="button" class=" btn btn-danger remove_image" style="margin-top: 5px">Remove</button>
        </div>
        
        @endif
        @endforeach    
         </div>
       </div>
     </div>
   </div>
   @endif
 </div>

 <div class="modal" id="myModal">

 </div>
 <form id="delete-form" action="" method="POST">
  {{ method_field('DELETE') }}  {{csrf_field()}}
  <input value="delete" style="display: none;" type="submit">
</form>
@endsection
@section('scripts')
<!-- <script src="https://cdn.jsdelivr.net/npm/signature_pad@2.3.2/dist/signature_pad.min.js"></script> -->

<!-- <script type="text/javascript" href="{{ asset('public/js/signature_pad.umd.js')}}"></script>
--><!-- @include('maintenance::complaint_js') -->
<!-- <link rel="stylesheet" type="text/css" href="{{ asset('public/css/jquery.signaturepad.css')}}" media="screen">
--><!-- <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.min.js"></script> -->
<!-- <script type="text/javascript" src="{{ asset('public/js/jquery.signaturepad.min.js')}}"></script> -->
<script>


  $(document).ready(function() {


    $("#item_form").validate();
    $("#service_report_note_form").validate();
    $("#img_form").validate();
    jQuery('.delete_type').click(function (event) {
      var action = $(this).attr("href");
      event.preventDefault();
      /*if (confirm('Do you want to Delete this Currency?')) {*/
        jQuery("#delete-form").attr('action', action);
        jQuery("#delete-form").submit();
      /*} else {
          return false;
        }*/
      });
    /***********************************************************/
    $("#myModal").on("hidden.bs.modal", function(){
      $("#myModal").html("");
      $(this).removeData('bs.modal');
    });
    /***********************************************************/
    $(document).on('click','.itemEdit',function(){

      var item_id = $(this).attr('data-id');
      $.ajax({
            method: 'GET', // Type of response and matches what we said in the route
            //url: '../../tenantContract/'+tenantContract_id+'/edit', // This is the url we gave in the 
           //url: "{{route('checklistEdit')}}",
           url: '../../../complaintStage/'+item_id+'/edit',
          //data: {'id' : item_id,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
            success: function(response){ // What to do if we succeed
              $("#myModal").html(response); 
            },
          });
      return true;


    });
    /***********************************************************/
    $(document).on('click','.updateStatus',function(){

      var service_report_id = $(this).attr('data-id');
      var complaint_id = $(this).attr('datas-id');
      var symbol = $(this).attr('dataa-id');
      var work_flow = $(this).attr('dataaw-id');

      $.ajax({
            method: 'POST', // Type of response and matches what we said in the route
            //url: '../../tenantContract/'+tenantContract_id+'/edit', // This is the url we gave in the 
            url: "{{route('ServiceReportStatusUpdate')}}",
           //url: '../../complaintStage/'+item_id+'/edit',
          data: {'id' : service_report_id,'complaint_id' : complaint_id,'work_flow':work_flow,'symbol':symbol,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
            success: function(response){ // What to do if we succeed
              $("#myModal").html(response); 
            },
          });
      return true;


    });

    /**********************************************************************/
    var maxField = 100; //Input fields increment limitation
    var addButton = $('.add_button'); //Add button selector
    var wrapper	 = $('.field_wrapper'); //Input field wrapper
        
    //Once remove button is clicked
    $(wrapper).on('click', '.remove_button', function(e){
      e.preventDefault();
        /*$(this).parent('div').remove();*/ //Remove field html
        var img_path_id =$(this).siblings('input').val();
        //$(this).closest('.rows').remove();
        if(confirm('Sure want to Delete this Document')) {
         $.ajax({
          headers : {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
          url: '{{ url('tenant-contract') }}' + '/' + img_path_id,
          type: "DELETE",
          data: {  "_method": 'DELETE', 'img_path_id': img_path_id }
        });
         $(this).closest('.ro').remove();
            // Remove the file preview.
           // _this.removeFile(file);
         }

        x--; //Decrement field counter
      });

    $(wrapper).on('click', '.remove_doc_button', function(e){
      e.preventDefault();

      $(this).closest('.increment').remove();
      $("#report_image_file_name-error").hide();

        x--; //Decrement field counter
      });
    /************************************************************************************/
    var wrapper = $('.field_wrapper');
  //Once remove button is clicked
    $(wrapper).on('click', '.remove_image', function(e){
        e.preventDefault();
        /*$(this).parent('div').remove();*/ //Remove field html
        var img_path_id =$(this).siblings('input').val();
        //$(this).closest('.rows').remove();
        if(confirm('Are You Sure want to Delete this Image')) {
             $.ajax({
              headers : {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
              url: '{{ url('serviceDestroyImage') }}' + '/' + img_path_id,
              type: "DELETE",
              data: {  "_method": 'DELETE', 'img_path_id': img_path_id }
              });
              $(this).closest('.ro').remove();
            // Remove the file preview.
           // _this.removeFile(file);
          }
        
         //Decrement field counter
    });
    /************************************************************************************/
    $(document).on("change",".upload",function(){
            
            fileUpload($(this));
      });
      $(".btn-reset").click(function(){ 
            
            $(".increment").first().find('.upload').val(''); 
            $(".upload").valid();
 
       
      });
  });
  function fileUpload(file){
   
		var currentRowId    = parseInt(file.closest('.increment').attr('id'));
		var lastRowId       = parseInt($(".increment").first().attr("id"));
		
		$(".form-group .input-group").first().find('.input-group-btn').hide();
		$('.upload').each(function() {
			$(this).rules("add", 
				{
					extension:"Pdf|Doc|Docx|Jpeg|Jpg",
					filesize: {{$upload_size}},
					messages: {
					   extension: "Support Only Following File type : Pdf|Doc|Docx|Jpeg|Jpg",
					   filesize: "File Must Be Less Than {{$upload_size/1000000}}MB",
					}
				});
		});
	   
	   $.validator.addMethod('filesize', function(value, element, param) {
		// param = size (in bytes) 
		// element = element to validate (<input>)
		// value = value of the element (file name)
		return this.optional(element) || (element.files[0].size <= param) 
		});
	   $(".upload").valid();
	   var ext = file.val().split('.').pop().toLowerCase();

	   if(ext !='' && $.inArray(ext, ['pdf','docx', 'doc', 'jpeg', 'jpg']) == -1) {
			alert('Invalid Extension!');
			return false;
	   }
	   else if(fileUpload =='' && currentRowId == lastRowId){
		   alert('Please Upload The File');
		   return false;    
	   }

		if($(".upload").valid() == 1 && currentRowId == lastRowId  ){
		// Next Row Id
			var cont = lastRowId + 1;
			//var fieldHTML = '<div class="row ro"><div class="col-sm-6"><div class="form-group"><div class="p-relative"><i class="fa fa-paperclip icn-add" aria-hidden="true"></i><input type="file" class="report_image_file_name form-control"  id="report_image_file_name"  name="report_image_file_name[]" data-rule-extension="jpg|jpeg|png" data-msg-extension="Only allowes jpg,jpeg and png" required></div></div></div><div class="col-sm-2"><div class="dataSearchLabel w-100" style="margin-top: 36px"></div><button type="button" class="btn btn-warning remove_doc_button"><i class="fa fa-trash-o "></i></button></div></div>'; //New input field html 
			var html = '<div class="control-group input-group increment" id="'+cont +'"style="margin-bottom:10px"><i class="fa fa-paperclip icn-add" aria-hidden="true"></i><input type="file" name="report_image_file_name['+cont +']" id="report_image_file_name['+cont +']" class="report_image_file_name form-control upload" style="width: 70%;"><div class="input-group-btn" style="display:none"><button class="btn btn-danger remove_doc_button" type="button"><i class="fa fa-trash-o "></i></button></div><label id="report_image_file_name['+cont +']-error" class="error" for="report_image_file_name['+cont +']"></label></div>';
			//html.find('.btn-danger').hide();
			$(".increment").first().before(html);
			
			//alert($(".form-group .input-group").length)
			$(".form-group .input-group:nth-child(2)").find('.input-group-btn').show();
		}
		

	}
</script>
@endsection
