@extends('layouts.plms-app')
@section('css')
<link href="{{ asset('public/css/legal.css') }}" rel="stylesheet">
@endsection

@section('content')
@php $timeline = isset($timeline) ? $timeline : \Modules\Masters\Services\LegalCase::timeline($closedLegalCase->id); @endphp
@can('closed_legal_cases_view')
<div class="lg">
  @component('masters::Legal._detail_head', ['legal' => $closedLegalCase, 'crumb' => 'referBackCases', 'screen' => 'Referred back case'])
    @slot('actions')
      
    @endslot
  @endcomponent
  <div class="lg-grid">
    <div>
      <section class="lg-card" aria-label="Overview"><div class="lg-card__head"><h2 class="lg-card__title">Overview</h2></div>
        <div class="lg-card__body">@include('masters::Legal._case_summary', ['legal' => $closedLegalCase])</div></section>
      @include('masters::Legal._contract_details', ['legal' => $closedLegalCase, 'oldContractList' => $oldContractList, 'dmy' => $dmy, 'owed' => $owed])
      @include('masters::Legal._legal_notes', ['legal' => $closedLegalCase, 'legalNotes' => $legalNotes, 'canAddNote' => !$closedLegalCase->closed_at])
      @include('masters::Legal._documents', ['legal' => $closedLegalCase, 'canUpload' => true])
    </div>
    <aside>
      <section class="lg-card" aria-label="Case timeline">@include('masters::Legal._timeline')</section>
      @include('masters::Legal._stage_notes', ['notes' => $notes])
    </aside>
  </div>
</div>
@endcan
<div class="modal lg lg-modal" id="myModal"></div>
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
    var fieldHTML = '<div class="ro lg-field" style="display:flex;gap:8px;align-items:flex-end;padding-top:6px;"><input type="file" class="legal_documents_file_name lg-input" name="legal_documents_file_name[]" aria-label="Another document" data-rule-extension="pdf|doc|docx" data-msg-extension="Only PDF and Word files"><button type="button" class="lg-btn lg-btn--danger lg-btn--sm remove_doc_button" aria-label="Remove file"><i class="fa fa-times" aria-hidden="true"></i></button></div>'; //New input field html 
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
