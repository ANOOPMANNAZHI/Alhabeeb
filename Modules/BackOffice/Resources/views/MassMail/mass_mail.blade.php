@extends('layouts.plms-app')
@section('css')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">
<link href="{{asset('public/css/formlayout.css')}}" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="{{ asset('public/css/tokenize2.min.css')}}">
<script src="{{ asset('public/js/tinymce.js') }}" ></script>

    <script>
      tinymce.init({
        selector: '#mytextarea',
        menubar :false,
        statusbar : false,
        branding :false,
        setup: function (editor) {
          editor.on('change', function () {
            $("#mytextarea").val(editor.getBody().innerHTML);
            $("#mytextarea").valid();
          });
        }
      });
    </script>

@endsection 
@section('content')
<!-- start widget -->
<div class="page-bar">
  <div class="page-title-breadcrumb">
      <div class=" pull-left">
          <div class="page-title">Compose Mail</div>
      </div>
        {{ Breadcrumbs::render('massMail.create') }}
  </div>
</div>
<div class="row">
    <div class="col">
        <div class="card card-box salesSearchBox">
            <form action="{{route('massMail.store')}}" method="POST" id="form_sample_2" class="form-horizontal" enctype="multipart/form-data" data-toggle="validator">
                {{csrf_field()}} 
                <!-- <div class="sub-head">Building Type Details</div> -->
                <div class="dataSearchBox ">



                   <div class="row">
                      <div class="col-sm-12">
                          <div class="form-group autocomplete-cls">
                            <label>To<small class="textRed">*</small></label>


                            <div class="p-relative">
                              <i class="fa fa-envelope icn-add" aria-hidden="true"></i>
                              <select id="user_id" required name="user_id[]" class="tokenize-remote-demo1 form-group" multiple>      
                              </select>
                              <a href="#" id="myelement" class="align-right">Cc</a>
                          </div>
                          <label id="user_id-error" class="error" for="user_id" style=""></label>
                      </div>
                  </div> 

                   <div class="col-sm-12 inputcc" id="another-element" style=" display: none;">
                          <div class="form-group autocomplete-cls">
                            <label>Cc<small class="textRed">*</small></label>


                            <div class="p-relative">
                              <i class="fa fa-envelope icn-add" aria-hidden="true"></i>
                              <select id="users_id"  name="users_id[]" class="tokenize-remote-demo2 form-group" multiple>      
                              </select>
                          </div>
                      </div>
                  </div> 
               <div class="w-100"></div>
               <div class="col-sm-12">
                <div class="form-group">
                    <label for="subject">Subject<small class="textRed">*</small></label>
                    <div class="p-relative">
                       <i class="fa fa-font-awesome icn-add" aria-hidden="true"></i>
                       <input  type="text" class="form-control" id="subject"  placeholder="Enter Subject" name="subject" required patten="^[a-zA-Z0-9]+$" value="" data-rule-maxlength="25" data-msg-maxlength="Only allowes 25 Characters">
                   </div>
               </div>
           </div>
           <div class="w-100"></div>
           <div class="col-sm-12">
            <div class="form-group">
                <label>Content</label>
                <div class="p-relative">
                   <i class="fa fa-file-text-o icn-add" aria-hidden="true"></i>
                   <textarea name="content" id="mytextarea" class="form-control" rows="15" placeholder="Enter Content" required></textarea>
                    <label id="mytextarea-error" class="error" for="mytextarea" ></label>
                  
               </div>

           </div>

       </div>


       <div class="col">
          <div class="w-100"></div>
          <button type="submit" id ="btn" class="btn btn-primary">Save</button>
      </div>

  </div>

</div>
<div class="clearfix"></div>
</form>

</div>
</div>
</div>
@endsection
@section('scripts')
<script src="{{ asset('public/js/tokenize2.min.js') }}" ></script>

<script>
  $(document).ready(function() {
	
    $("#form_sample_2").validate({
       ignore:[],
       rules: {     
        "user_id[]": {
          required: true
        },
        "subject": {
          required: true
        },
        "content": {
          required: true
        },
      },
      messages: {
        "user_id[]": {
          required: "Field is required."
        },
        "subject": {
          required: "Field is required."
        },
        "content": {
          required: "Field is required."
        },
      },
      submitHandler: function(form) { 
        $("#btn").attr('disabled','disabled');
        form.submit();
      }
    })





    /**********************************************************************/
    $('.tokenize-remote-demo1').tokenize2({

      placeholder: " &nbsp;&nbsp; Type The Letter For Preferred Email",
      dataSource: function(term, object){
        $.ajax('{{route("emailAutocomplete")}}', {
          data: { search: term, start: 0 },
          dataType: 'json',
          success: function(data){
            var $items = [];
            $.each(data, function(k, v){
              $items.push(v);
          });
            object.trigger('tokenize:dropdown:fill', [$items]);

        }
    });
    }
});

    /*************************************************************************/
    $('.tokenize-remote-demo1').on("tokenize:tokens:add", function (event, value, text){

      if(value){

        $("#user_id-error").hide();
     }


 });
    /*************************************************************************/
    $('.tokenize-remote-demo1').on("tokenize:tokens:remove", function (event, value, text){
        $('#user_id').valid();
   });

        /**********************************************************************/
    $('.tokenize-remote-demo2').tokenize2({

      placeholder: " &nbsp;&nbsp; Type The Letter For Preferred Email",
      dataSource: function(term, object){
        $.ajax('{{route("emailCcAutocomplete")}}', {
          data: { search: term, start: 0 },
          dataType: 'json',
          success: function(data){
            var $items = [];
            $.each(data, function(k, v){
              $items.push(v);
          });
            object.trigger('tokenize:dropdown:fill', [$items]);

        }
    });
    }
});

    /*************************************************************************/
    $('.tokenize-remote-demo2').on("tokenize:tokens:add", function (event, value, text){

      if(value){

        $("#users_id-error").hide();
     }


 });
    /*************************************************************************/
    $('.tokenize-remote-demo2').on("tokenize:tokens:remove", function (event, value, text){
        $('#users_id').valid();
   });
    /*************************************************************************/
    $( "#myelement" ).click(function() {     
    if($('#another-element:visible').length)
        $('#another-element').hide();
    else
        $('#another-element').show();        
});
});

</script>
@endsection
