@extends('layouts.plms-app')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">


@section('content')
<!-- start widget -->
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">Accepted Legal Case Details</div>
    </div>

    {{ Breadcrumbs::render('activeCasesShow',$activeCase) }}

  </div>
</div>

<div class="row">
  <div class="col">
    <div class="card card-box salesSearchBox">
      <div class="sub-head">Building Details</div>
      <div class="dataSearchBox">    
        <div class="card-body row">

          @if(isset($activeCase->building_id))
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
             <h5 class="details"><b>Building Name :  </b><span>{{$activeCase->building->building_name}}</span></h5>
           </div>
         </div> 
         @endif
         @if(isset($activeCase->building_id))
         <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
           <h5 class="details"><b>Building Code :  </b><span>{{$activeCase->building->building_code}}</span></h5>
         </div>
       </div>
       @endif
       @if(isset($activeCase->building_id))
       <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
         <h5 class="details"><b>Landlord :  </b><span>{{$activeCase->building->vendor->vendor_name}}</span></h5>
       </div>
     </div>
     @endif
     @if(isset($activeCase->building_id))
     <div class="col-lg-6 p-t-20"> 
      <div class = "txt-full-width">
       <h5 class="details"><b>ARE :  </b><span>{{$activeCase->building->vendor->vendor_name}}</span></h5>
     </div>
   </div>
   @endif
   @if(isset($activeCase->building_id))
   <div class="col-lg-6 p-t-20"> 
    <div class = "txt-full-width">
     <h5 class="details"><b>Way No :  </b><span>{{$activeCase->building->plot_no}}</span></h5>
   </div>
 </div>
 @endif
 @if(isset($activeCase->building_id))
 <div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Block No :  </b><span>{{$activeCase->building->block_number}}</span></h5>
 </div>
</div>
@endif
@if(isset($activeCase->building_id))
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Plot No :  </b><span>{{$activeCase->building->plot_no}}</span></h5>
 </div>
</div>
@endif
@if(isset($activeCase->building_id))
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Location :  </b><span>{{$activeCase->building->location->locations_name}}</span></h5>
 </div>
</div>
@endif
@if(isset($activeCase->building_id))
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Location No :  </b><span>{{$activeCase->building->location->locations_code}}</span></h5>
 </div>
</div>
@endif
</div>
</div>
<div class="sub-head">Unit Details</div>
<div class="dataSearchBox">    
  <div class="card-body row">
    @if(isset($activeCase->unit_id))
    <div class="col-lg-6 p-t-20"> 
      <div class = "txt-full-width">
       <h5 class="details"><b>Unit No :  </b><span>{{$activeCase->unit->unit_no}}</span></h5>
     </div>
   </div>
   @endif
   @if(isset($activeCase->unit_id))
   <div class="col-lg-6 p-t-20"> 
    <div class = "txt-full-width">
     <h5 class="details"><b>Unit Type :  </b><span>{{$activeCase->unit->unit->unit_types_name}}</span></h5>
   </div>
 </div>
 @endif
 @if(isset($activeCase->unit_id))
 <div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Floor No :  </b><span>{{$activeCase->unit->unit_floor}}</span></h5>
 </div>
</div>
@endif
@if(isset($activeCase->tenantContract->unit_usage))
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Unit Usage :  </b><span>{{$activeCase->tenantContract->unit_usage}}</span></h5>
 </div>
</div>
@endif
@if(isset($activeCase->tenantContract->tenant_id))
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Tenant Name :  </b><span>{{$activeCase->tenantContract->tenant->tenant_name}}</span></h5>
 </div>
</div> 
@endif
@if(isset($activeCase->tenantContract->tenant_id))
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Tenant Code :  </b><span>{{$activeCase->tenantContract->tenant->tenant_code}}</span></h5>
 </div>
</div>
@endif
@if(isset($activeCase->tenantContract->occupant_id))
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Occupant Name :  </b><span>{{$activeCase->tenantContract->occupant->occupant_name}}</span></h5>
 </div>
</div> 
@endif
@if(isset($activeCase->tenantContract->occupant_id))
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Occupant Mob No :  </b><span>{{$activeCase->tenantContract->occupant->occupant_primary_contact_no}}</span></h5>
 </div>
</div>
@endif
@if(isset($activeCase->tenantContract->occupant_id))
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Occupant Email :  </b><span>{{$activeCase->tenantContract->occupant->occupant_email}}</span></h5>
 </div>
</div> 
@endif
</div>
</div>
<div class="sub-head">Current Contract Details</div>
<div class="dataSearchBox">    
  <div class="card-body row">

    @if(isset($activeCase->tenantContract->tenant_contract_start_date))
    <div class="col-lg-6 p-t-20"> 
      <div class = "txt-full-width">
       <h5 class="details"><b>Start Date :  </b><span>{{$activeCase->tenantContract->tenant_contract_start_date->format('d/m/Y')}}</span></h5>
     </div>
   </div> 
   @endif
   @if(isset($activeCase->tenantContract->tenant_contract_effective_date))
   <div class="col-lg-6 p-t-20"> 
    <div class = "txt-full-width">
     <h5 class="details"><b>Effective Date :  </b><span>{{$activeCase->tenantContract->tenant_contract_effective_date->format('d/m/Y')}}</span></h5>
   </div>
 </div>
 @endif 
 @if(isset($activeCase->tenantContract->tenant_contract_valid_to_date))
 <div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>End Date :  </b><span>{{$activeCase->tenantContract->tenant_contract_valid_to_date->format('d/m/Y')}}</span></h5>
 </div>
</div>
@endif
@if(isset($activeCase->tenantContract->tenant_contract_value))
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Contract Value :  </b><span>{{ numberFormat($activeCase->tenantContract->tenant_contract_value) }} OMR</span></h5>
 </div>
</div> 
@endif
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Rented From :  </b><span>{{ $dmy }}</span></h5>
 </div>
</div> 
@if(isset($paidDays))
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Over Due Rent Period :  </b><span>{{$overDueFrom->format('d/m/Y')}}, {{date('d/m/Y')}} ,{{ $paidDays }} Days</span></h5>
 </div>
</div> 
@endif
@if(isset($rentAmount))
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Over Due Rent Amount:  </b><span>{{ numberFormat($rentAmount) }} OMR</span></h5>
 </div>
</div> 
@endif

</div>
</div>
</div>    

<div class="card card-box salesLeadBox">
  <div class="card-head">
    <div class="col"><h4>Old Contract Details</h4></div>
  </div>
  <div class="card-body">
   <div class="col">
    <div class="row">

      <div class="col leadInformation">
       <div class="table-responsive1">
        <table class="table" >
          <thead>
            <tr style="background: #f5f5f5;">
              <th>Old Contract No</th>
              <th>From Date</th>
              <th>To Date</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>

           @forelse ($oldContractList as $oldContract)

           @if($oldContract->tenant_contract_no!="") 
           <tr>
            <td>{{$oldContract->tenant_contract_no}} </td>
            <td>{{$oldContract->tenant_contract_start_date->format('d/m/Y')}}</td>
            <td>{{$oldContract->tenant_contract_valid_to_date->format('d/m/Y')}}</td>
            <td>
              <a href="{{route('legaltenantContractShow',$oldContract->id)}}" title="View" class="btn btn-tbl-view btn-xs">
                <i class="fa fa-eye "></i>
              </a>
              <a href="{{route('legalPdcShow',$oldContract->id)}}" title="PDC" class="btn btn-tbl-general btn-xs">
                <i class="fa fa-pie-chart "></i>
              </a>
            </td>
          </tr> 
          @endif                       
          @empty 
          <tr>
            <td colspan="4" align="center">
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

</div>
</div>
<div class="card card-box salesLeadBox">
  <div class="card-head">
    <div class="col"><h4>Note</h4></div>
  </div>
  <div class="card-body">
   <div class="col">
    <div class="row">

      <div class="col leadInformation">
       <div class="table-responsive1">
        <table class="table" >
          <thead>
            <tr style="background: #f5f5f5;">
              <th>Note</th>
              <th>Stage</th>
              <th>User</th>
              <th>Date Time</th>
            </tr>
          </thead>
          <tbody>

            @forelse ($notes as $note)

            @if($note->note!="") 
            <tr>
              <td> {{$note->note}}</td>
              <td>{{$note->workFlowProcess->work_flow_processes_name}}</td>
              <td>{{$note->createdBy->username}}</td>
              <td>{{$note->created_at->format('d/m/Y h:m A')}}</td>
            </tr> 
            @endif                       
            @empty 
            <tr>
              <td colspan="4" align="center">
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

</div>
</div>
<!-- ends--> 
<div class="card card-box salesLeadBox">
  <div class="card-head">
    <div class="clearfix"></div>
 
    <div class="col"><h4>Note</h4></div>

     <form action="{{route('legalNoteStore')}}" method="POST" id="legal_note_form"" class="form-horizontal" enctype="multipart/form-data" data-toggle="validator">
     {{csrf_field()}}
     <div class="dataSearchBox ">
      <div class="row">
       <div class="col-sm-6">
        <div class="form-group">
                <label>Status<small class="textRed">*</small></label>
                <input type="hidden" name="legal_id" value="{{$activeCase->id}}">
                <input type="hidden" name="current_url" value="{{url()->current()}}">
                <div class="p-relative">
                  <select class="form-control" name="legal_notes_status" id="legal_notes_status" required>
                    <option value="">--Select--</option>
                    <option value="1">Start</option>
                    <option value="2">Middle</option>
                    <option value="0">End</option>
                  </select>
                </div>
              </div>
      </div>
      <div class="col-sm-6">
        <div class="form-group">
                <label>Note<small class="textRed">*</small></label>
                <div class="p-relative">
                 <textarea class="form-control"  id="legal_notes_note"  placeholder="Enter Note" name="legal_notes_note"   data-rule-maxlength="200" data-msg-maxlength="Only allows 200 Characters" required></textarea>
               </div>
             </div> 
      </div>
      <div class="w-100"></div>
    </div>     
</div>
<div class="col-sm-12">
  <div class="row">
    <div class="col no-padding">
      <button type="submit" class="btn btn-primary ">Save</button> 

    </div>
  </div>

</div>  
</form> 
</div>
</div>
<!-- ends-->
<div class="card card-box salesLeadBox">
  <div class="card-body">
   <div class="col">
    <div class="row">

      <div class="col leadInformation">
       <div class="table-responsive1">
        <table class="table" >
          <thead>
            <tr style="background: #f5f5f5;">
              <th>Status</th>
              <th>Date and Time</th>
              <th>Notes</th>
            </tr>
          </thead>
          <tbody>

            @forelse ($legalNotes as $note)

            @if(isset($note->legal_notes_status)) 
            <tr>
              <td> {{$note->legal_notes_status_name}}</td>
              <td>{{$note->created_at->format('d/m/Y h:m A')}}</td>
              <td>{{$note->legal_notes_note}}</td>
            </tr> 
            @endif                       
            @empty 
            <tr>
              <td colspan="4" align="center">
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

</div>
</div>  
<div class="card card-box salesLeadBox">
  <div class="card-head">
    <div class="clearfix"></div>
    <div class="sub-head">Document Upload</div>
    <form action="{{route('legalDocumentStore')}}" method="POST" id="legal_document_form"" class="form-horizontal" enctype="multipart/form-data" data-toggle="validator">
     {{csrf_field()}}
     <div class="dataSearchBox ">
      <div class="row">
       <div class="col-sm-6">
        <div class="form-group">
          <label for="legal_documents_file_name">Document</label>
          <div class="p-relative">
            <i class="fa fa-paperclip icn-add" aria-hidden="true"></i>
            <input type="hidden" name="legal_id" value="{{$activeCase->id}}">
            <input type="hidden" name="current_url" value="{{url()->current()}}">
            <input type="file" class="legal_documents_file_name form-control"  id="legal_documents_file_name"  name="legal_documents_file_name[]" data-rule-extension="pdf|doc" data-msg-extension="Only allows pdf and doc">
          </div>
        </div>
      </div>
      <div class="col-sm-2">
        <div class="dataSearchLabel w-100"></div>
        <button type="button" class="btn btn-primary doc-button add_button">Add</button>
      </div>
      <div class="w-100"></div>
    </div>
    <div class="field_wrapper">
      @if(!empty($activeCase->legalDocument)) 
      @foreach ($activeCase->legalDocument  as $doc) 
      <div class="row ro ">
       <div class="col-sm-6">
        <div class="form-group doc-border">
         <a target="_blank" href="{{asset('storage/app/'.$doc->legal_documents_file_name)}}">
          {{$doc->legal_documents_name}}  </a>
        </div>
      </div>
      <div class="col-sm-2">
        <div class="dataSearchLabel w-100"></div>
        <input type="hidden" name="doc_path_id" value="{{$doc->id}}"><!-- img-closed -->
        <button type="button" title="Delete" class="btn btn-warning remove_button "><i class="fa fa-trash-o "></i></button>
      </div>
      <div class="w-100"></div>
    </div>
    @endforeach    
    @endif
  </div>     
</div>
<div class="col-sm-12">
  <div class="row">
    <div class="col no-padding">
      <button type="submit" class="btn btn-primary ">Save</button> 

    </div>
  </div>

</div>  
</form> 
</div>
</div>
<!-- end-->
</div> 

<div class="modal" id="myModal">

</div>
@endsection
@section('scripts')
<script>
  $(document).ready(function() {
    $("#legal_note_form").validate();
    $("#legal_document_form").validate();
  });
  /**********************************************************************/
    var maxField = 10; //Input fields increment limitation
    var addButton = $('.add_button'); //Add button selector
    var wrapper = $('.field_wrapper'); //Input field wrapper
    var fieldHTML = '<div class="row ro"><div class="col-sm-6"><div class="form-group"><label for="legal_documents_file_name"></label><div class="p-relative"><i class="fa fa-paperclip icn-add" aria-hidden="true"></i><input type="file" class="legal_documents_file_name form-control"  id="legal_documents_file_name"  name="legal_documents_file_name[]" data-rule-extension="pdf|doc" data-msg-extension="Only allowes pdf and doc"></div></div></div><div class="col-sm-2"><div class="dataSearchLabel w-100" style="margin-top: 28px"></div><button type="button" class="btn btn-warning remove_doc_button"><i class="fa fa-trash-o "></i></button></div></div>'; //New input field html 
    var x = 1; //Initial field counter is 1
    
    //Once add button is clicked
    $(addButton).click(function(){
      var values = $("input[name='legal_documents_file_name[]']")
      .map(function(){
        if($(this).val())return $(this).val();}).get();

      var len = $("input[name='legal_documents_file_name[]']").length;
      if (($( ".legal_documents_file_name" ).is( ".legal_documents_file_name.form-control.error" )) || ( values.length != len )) {


      }else{
        //Check maximum number of input fields
        if(x < maxField){ 
                x++; //Increment field counter
                $(wrapper).append(fieldHTML); //Add field html
              }
            }
          });
    
    //Once remove button is clicked
    $(wrapper).on('click', '.remove_button', function(e){
      e.preventDefault();
        /*$(this).parent('div').remove();*/ //Remove field html
        var img_path_id =$(this).siblings('input').val();
        //$(this).closest('.rows').remove();
        if(confirm('Are You Sure You want to Delete this Document ?')) {
         $.ajax({
          headers : {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
          url: '{{ url('legalCase') }}' + '/' + img_path_id,
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

      $(this).closest('.ro').remove();


        x--; //Decrement field counter
      });
    /************************************************************************************/
  </script>
  @endsection
