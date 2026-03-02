@extends('layouts.plms-app')



@section('content')



<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">AMC Contract {{ (isset($amcContract))? 'Edit' : 'Add'}}</div>
    </div>
    {{ (isset($amcContract))?   Breadcrumbs::render('amcContract.edit',$amcContract,Session::get('current')) :  Breadcrumbs::render('amcContract.create') }} 



  </div>
</div>


<link rel="stylesheet" href="{{ asset('public/css/jquery-ui.css')}} "><!-- //code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css -->



<style>
  body {counter-reset:section 0 sec 0;}
  .counters:before
  {
    counter-increment:section;
    content:counter(section);
  }
  .countn:before
  {
    counter-increment:sec;
    content:counter(sec);
  }
  
input[type=date]::-webkit-inner-spin-button {
    -webkit-appearance: none;
    display: none;
}
</style>

<div class="row">  

  <!-- activities -->
  <div class="col-md-12 col-sm-12 dashboardtab">
    <div class="panel tab-border card-box">
      <div class="panel-body">
        <div class="tab-content">                                     

          <!-- -------------------------- Amc Contract  Div ----------------------------------- -->
          {{-- @if(isset($amcContract)) --}}
          <div class="tab-pane active" id="maintenance">
            <form method="post" autocomplete="off" id="contract-form" action="{{isset($amcContract)? route( 'amcContract.update',$amcContract->id) : route( 'amcContract.store')}}" data-toggle="validator">
              @csrf  @if(isset($amcContract)){{method_field('PUT')}}@endif
              <div class="clearfix"></div>
              <div class="dataSearchBox">
                <input type="hidden" name="editStatus" id="editStatus" value="{{ old('editStatus', isset($amcContract)? $amcContract->id : '' )}}">
                <div class=" tenantStatus">

                </div>
                
                <div class="row">
                  <div class="col-sm-6">
                    <div class="form-group">
                      <label for="amc_contract_no">Contract No<small class="textRed">*</small></label>
                      <div class="p-relative">
                       <i class="fa fa-eercast icn-add" aria-hidden="true"></i>
                       <input required type="text" class="form-control" id="amc_contract_no" readonly name="amc_contract_no" value="{{ old('amc_contract_no', isset($amcContract)? $amcContract->amc_contract_no : $nextCode )}}"  placeholder="Enter Comp No">
                     </div>
                   </div>
                 </div>



                 <div class="col-sm-6">
                  <div class="form-group">
                    <label for="vendor_name">Contractor<small class="textRed">*</small></label>
                    <div class="p-relative">
                     <i class="fa fa-id-badge icn-add" aria-hidden="true"></i>
                     <input required type="text" class="form-control read" id="vendor_name"  name="vendor_name" value="{{ old('vendor_name', isset($amcContract)? $amcContract->vendor->vendor_name : '' )}}"  placeholder="Enter Contractor Name">
                   </div>
                 </div>
               </div>
               <input type="hidden" class="form-control" id="vendor_code" name="vendor_id" placeholder="Enter Landlord Code" value="{{ old('vendor_id', isset($amcContract)? $amcContract->vendor_id : '' )}}" readonly>
               <div class="col-sm-4">
                <div class="form-group">
                  <label for="building_name">Building<small class="textRed">*</small></label>
                  <div class="p-relative">
                   <i class="fa fa-id-badge icn-add" aria-hidden="true"></i>
                   <input required type="text" class="form-control read" id="building_name"  name="building_name" value="{{ old('building_name', isset($amcContract)? $amcContract->building->building_name.'-'.$amcContract->building->building_code : '' )}}"  placeholder="Enter Building Name">
                 </div>
               </div>
             </div>
             <input type="hidden" name="building_idd" id="building_idd" value="{{ old('building_id', isset($amcContract)? $amcContract->building_id : '' )}}" >
             <input type="hidden" name="building_id" id="building_id" value="{{ old('building_id', isset($amcContract)? $amcContract->building_id : '' )}}" >
             <div class="col-sm-4">
              <div class="form-group">
                <label for="amc_contract_period_from">Start Date<small class="textRed">*</small></label>
                <div class="p-relative">
                 <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
                 <input required type="date" class="form-control date_range" id="amc_contract_period_from" name="amc_contract_period_from" value="{{ old('amc_contract_period_from', isset($amcContract)? $amcContract->amc_contract_period_from->format('Y-m-d') : ''  )}}"  placeholder="Enter Start Date">
               </div>
             </div>
           </div>
           <div class="col-sm-4">
            <div class="form-group">
              <label for="amc_contract_period_to">End Date<small class="textRed">*</small></label>
              <div class="p-relative">
               <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
               <input required type="date" class="form-control date_range" id="amc_contract_period_to" name="amc_contract_period_to" value="{{ old('amc_contract_period_to', isset($amcContract)? $amcContract->amc_contract_period_to->format('Y-m-d') : ''  )}}"  placeholder="Enter End Date">
             </div>
           </div>
         </div>
         <input type="hidden" name="no_days" id="no_days" value="">
         <input type="hidden" name="method" id="method" value="{{ old('method', isset($amcContract)? $amcContract->payment_method_id : '' )}}">

         <input type="hidden" name="method_text" id="method_text" value="{{ old('method_text', isset($amcContract)? $amcContract->paymentMethod->payment_method_code : '' )}}">
         <div class="col-sm-6 building_select">
          <div class="form-group">
            <label>Frequency <small class="textRed">*</small></label>
            <div class="p-relative">
             <i class="fa fa-building icn-add" aria-hidden="true"></i>
             <select class="form-control" id="payment_method_id" name="payment_method_id" required>
             <!-- <option value="{{ isset($amcContract) ? old('payment_method_id',$amcContract->payment_method_id): '' }}">{{ isset($amcContract)?  old('payment_method_id',$amcContract->paymentMethod->payment_method_code): 'Select' }}</option> -->
            </select> 
          </div>
        </div> 
      </div>
      <div class="col-sm-6">
        <div class="form-group">
          <label for="amc_contract_cost">Cost</label>
          <div class="p-relative">
           <i class="fa fa-id-badge icn-add" aria-hidden="true"></i>
           <input  type="text" class="form-control read" id="amc_contract_cost"  name="amc_contract_cost" value="{{ old('amc_contract_cost', isset($amcContract)? numberFormat($amcContract->amc_contract_cost) : '' )}}"  placeholder="Enter Cost" onkeyup="FormatCurrency(this)"   maxlength="20" minlength="2"  data-rule-pattern="^-?(?:\d+|\d{1,3}(?:,\d{3})+)(?:(\.|,)\d+)?$" data-msg-pattern="Allowed only Numeric and Decimal Values">
         </div>
       </div>
     </div>

   </div>
 </div>
 <div class="clearfix"></div>
 <div class="dataSearchBox panel-heading-lightblue">
  <h4>Select  Amenity</h4>
  <div class="row bb-1 mb-3">
    <div class="col-sm-4">
      <label for="simpleFormEmail">Amenity<small class="textRed">*</small></label>
      <select class="form-control" id="amentity_types_id" name="amentity_types_id" >
        <option value="">Select Amenity </option>
      </select>
    </div>

    <div class="col-sm-4">
      <div class="dataSearchLabel w-100"></div>
      <button type="button" id="addedAmenity" class="btn btn-primary dataSearchLabel add_amenity btnDisable">Add</button>
    </div>
  </div>


</div>

<div class="clearfix"></div>
<div class="dataSearchBox panel-heading-lightblue">
  <div class="col-md-12 col-sm-12">
    <div class="card  card-box">
      <div class="card-body ">
        <div class="table-wrap">
          <div class="table-responsive">
            <table class="table display product-overview mb-30" id="support_table5">
              <thead>
                <tr>
                  <th>Serial No</th>
                  <th>Amenity</th>
                  <th>Amenity Code</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody id="amenity_form">
               @if(isset($amcContract))
               @forelse ($amcContractAmenities as $amcContractAmenity)
               <tr class="tr" id="{{$loop->iteration}}"><input type="hidden" name="amc_contract_aminities_id[]" value="{{$amcContractAmenity->id}}">
                <td id="no{{$loop->iteration}}" class="counters"></td>
                <td id="work_id{{$loop->iteration}}">{{$amcContractAmenity->amenityType->amentity_types_name}}<input type="hidden" name="amentity_types_tech_id[]" id="amentity_types_tech_id" value="{{$amcContractAmenity->amenityType->id}}" class="amentity_types_tech_id"></td>
                <td id="work_id{{$loop->iteration}}">{{$amcContractAmenity->amenityType->amentity_types_code}}<input type="hidden" name="amenities_type_idss[]" value="{{$amcContractAmenity->amenityType->id}}"></td>
                <td id="action{{$loop->iteration}}">
                 <!--  <a href="{{route('amcContract.destroy',$amcContractAmenity->id)}}" class="btn btn-tbl-delete btn-xs remove_amenity" type="button">
                    <i class="fa fa-trash-o "></i>
                  </a> -->
                  <button class="btn btn-tbl-delete btn-xs remove_amenity" type="button">
                      <i class="fa fa-trash-o "></i>
                  </button>
                </td>                            

              </tr>  
              @empty
              <tr>
                <td colspan="4" align="center">
                  <p>No record</p>
                </td>
              </tr>
              @endforelse  
              @endif 


            </tbody>
          </table>
        </div>
      </div>  
    </div>
  </div>
</div>
</div>
<div class="col"><button type="submit" class="btn btn-primary submitBtn">Save</button> </div>
</form>
</div>
{{-- @endif --}}
</div>
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
@section('scripts')
@include('sales::add_sub_complaint_js')
<script src="{{ asset('public/js/jquery-ui.js') }} "></script><!-- https://code.jquery.com/ui/1.12.1/jquery-ui.js -->
<script>
/************************************************************/ 
$('#amc_contract_cost').keypress(function(e){ 
   if (this.value.length == 0 && e.which == 48 ){
      return false;
   }
});
/************************************************************/ 
function isNumber(evt,id) {
    evt = (evt) ? evt : window.event;
    var charCode = (evt.which) ? evt.which : evt.keyCode;
    if (charCode > 31 && (charCode < 48 || charCode > 57)) {
        return false;
    }
    var input = $("#"+id).val();
    if(input.search(/^0/) != -1){
         alert("you have started with a 0"); 
         $("#"+id).val("");
    }
    var point='#'+id;
    jQuery(point).keyup(function () { 
        this.value = this.value.replace(/[^0-9\.]/g,'');
    var arr = this.value.split('.');
         var len = arr.length;
         if(len > 2)
        this.value="";


    if(this.value == '.')
      this.value='0.';
    if(this.value == '00.')
      this.value='0.';
    if(this.value == '00')
      this.value=''; 
    if(this.value == '0')
      this.value='';  
    var arrs = this.value.split('0');
    var leng = arr.length;
    if (this.value.length == 0){
        return false;
     }     
         //alert(len);
         if(leng > 2)
         {
        this.value="";
         }
         
         
    });
    return true;
}
/************************************************************/ 
$(document).ready(function(){
    
  /************************************************************/ 
   $("#contract-form").on('submit',function(e){
  
var el = $(this);
    el.prop('disabled', true);
    setTimeout(function(){el.prop('disabled', false); }, 3000);
        var amenity_form = $('#amenity_form tr').length;
        var td = $('#amenity_form').children('tr').children('td').length;
          if(td == 1 ){          
            alert("Please add Amenity");
             return false;
          }

      });
  /************************************************************/ 
  $.validator.addMethod("greaterThan", 
    function(value, element, params) {
      
        if (!/Invalid|NaN/.test(new Date(value))) {
            return new Date(value) > new Date($(params).val());
        }

        return isNaN(value) && isNaN($(params).val()) 
            || (Number(value) > Number($(params).val())); 
    },'Must be greater than End Date.');
    $("#contract-form").validate({
            rules: {
                amc_contract_period_to: { greaterThan: "#amc_contract_period_from" ,
                
            }
              

        }
    });
});

//AutoComplete For Vendor Name
/************************************************************/ 
$('#vendor_name').autocomplete({
  source : '{!!URL::route('contractorAutocompleteCode')!!}',
  minlenght:2,
  autoFocus:true,
  change:function(e,ui){
    if (ui.item == null || ui.item == undefined) {
      $("#vendor_name").val('');
      $('#create_build_span').hide();
      $('#vendor_name-error').show();
    }else {

      $('#vendor_code').val(ui.item.ids);       
      $('#create_build_span').show();       
    }

  }
});
/************************************************************/  

//Autocomplete for Building
$('#building_name').autocomplete({
     // source : '{!!URL::route('buildingAutocompleteCode')!!}',
     source: function(request, response) {
      $.getJSON("{!!URL::route('buildingAmcAutocompleteCode')!!}", { vendor: $('#vendor_code').val(),building:$('#building_name').val() }, 
        response);
    },
    minlenght:2,
    autoFocus:true,
    change:function(e,ui){
      if (ui.item == null || ui.item == undefined) {
        $("#building_name").val('');  
        $('#building_name-error').show();
      }else {
        $('#building_id').val(ui.item.ids); 
        //var units_id = $('#unit_text_id').val();
        $.ajax({
         type: "GET",
         url: "{!!URL::route('buildingAmenityInContract')!!}",
         data:'building_id='+ $('#building_id').val(),
         success: function(data){
          var selected = "";
          var result = $.parseJSON(data);
          //$("#amentity_types_id").html(data);
          $('#amentity_types_id').empty();
            $('#amentity_types_id').append('<option value="">'+ 'Select Amenity' +'</option>')
              $.each(result[0], function(key, value) {
                  $('#amentity_types_id').append('<option value="'+ value['id'] +'" '+ selected +'>'+ value['name'] +'</option>');
            });
          if(result[1] != ""){
            location.reload();
          }else{
            var data = "<tr><td colspan='4'><p>No Record</p></td></tr>";
            $('#amenity_form').html(data);
            
          }
          
          
        }
      });      
      }

    }


  });

/************************************************************/ 


$(document).on('click','.add_amenity',function(){ 
  //event.preventDefault();
/*$(".btnDisable").attr("disabled", true);*/
    var el = $(this);
    el.prop('disabled', true);
    setTimeout(function(){el.prop('disabled', false); }, 3000);
  var amentity_types_id = $("#amentity_types_id").val();
  var building_id = $("#building_id").val();
  var editStatus = $("#editStatus").val();
  var no = $('#complaint_form tr').length+1;
  var td = $('#amenity_form').children('tr').children('td').length;
  var x=[];
  
  var inputs = document.getElementsByClassName( 'amentity_types_tech_id' ),
  names  = [].map.call(inputs, function( input ) {
    x.push(input.value);
      return input.value;
  }).join( ',' );
  
  if(x.indexOf(amentity_types_id) != -1){
    alert('Amenity Exists');

  }else{
    if(amentity_types_id !=""){
      $.ajax({
        method: "POST",
        url: "{{route('checkContractExist')}}",
        data: {amentity_types_id:amentity_types_id,editStatus:editStatus,building_id:building_id, "_token" : $('meta[name="csrf-token"]').attr('content')},
        success: function(data){                            
             if(data == 0){
              $.ajax({
                method: "POST",
                url: "{{route('addAmcAmenity')}}",
                data: {no:no,amentity_types_id:amentity_types_id,building_id:building_id, "_token" : $('meta[name="csrf-token"]').attr('content')},
                success: function(data){                            
                  if(data != 0){
                    
                    if(td == 1){
                      $('#amenity_form').html(data);
                      $('#amentity_types_id').val("");
                      
                      
                    }else{
                      $('#amenity_form').append(data); 
                      $('#amentity_types_id').val("");
                      
                    }
                                              
                  }               
                }           
              });
             }else{
                alert("Contract Already Exist For Selected Building Amenity!");
             }         
        }           
      });

      
    }else{
      alert("Please Select Amenity");
    }
  }             
});
/***************************************************************************/ 

/***************************************************************************/
//date range
$('.date_range').change(function(event){
  var d1 = $('#amc_contract_period_from').val();
  var d2 = $('#amc_contract_period_to').val();

  var date1 = new Date(d1);
  var date2 = new Date(d2);

  var date1_ms = date1.getTime();
  var date2_ms = date2.getTime();

  var diff = date2_ms-date1_ms;

          // get days
          var days = diff/1000/60/60/24;
          var no_days=days + 1;
          $('#no_days').val(no_days); 

          if(no_days>=30)
          {
            $('#payment_method_id').empty().append('<option value="">'+ 'Select Frequency' +'</option>')
           $('#payment_method_id').append('<option value="6">'+ 'Monthly' +'</option>')
         }
         if(no_days>=90)
         {
          $('#payment_method_id').empty().append('<option value="">'+ 'Select Frequency' +'</option>')
          $('#payment_method_id').append('<option value="6">'+ 'Monthly' +'</option>')
          $('#payment_method_id').append('<option value="1">'+ 'Quarterly' +'</option>')
        }
        if(no_days>=180)
        {
          $('#payment_method_id').empty().append('<option value="">'+ 'Select Frequency' +'</option>')
          $('#payment_method_id').append('<option value="6">'+ 'Monthly' +'</option>')
          $('#payment_method_id').append('<option value="1">'+ 'Quarterly' +'</option>')
          $('#payment_method_id').append('<option value="2">'+ 'Half-yearly' +'</option>')
        } 
        if(no_days>=360)
        {
          $('#payment_method_id').empty().append('<option value="">'+ 'Select Frequency' +'</option>')
         $('#payment_method_id').append('<option value="6">'+ 'Monthly' +'</option>')
         $('#payment_method_id').append('<option value="1">'+ 'Quarterly' +'</option>')
         $('#payment_method_id').append('<option value="2">'+ 'Half-yearly' +'</option>')
         $('#payment_method_id').append('<option value="7">'+ 'Yearly' +'</option>')
       }
       if(no_days<30)
       {
        alert('No Of days must be atleast 30 days From Start Date To End Date!');
		$('#amc_contract_period_to').empty();
        $('#amc_contract_period_to').val("");
       //event.preventDefault();
      }

    });
//remove amenity-
/***************************************************************************/
$(document).on('click','.remove_amenity',function(){
      var row = $(this).closest('tr').attr('id'); // Or continue to use the invalid ID selector: '#'+id

      var siblings =  $(this).closest('td').siblings('td.selected').text();
      $(this).closest('tbody .tr').remove();
      var td = $('#amenity_form').children('tr').children('td').length;
      if(td == 0){
        var data = "<tr><td colspan='4'><p>No Record</p></td></tr>";
        $('#amenity_form').html(data);
      }
      
      //$('#amenity_form').html(data);

        //$(this).closest('tbody .tr').remove();
        
      });
/***************************************************************************/
//fetching amenity for edit 
   $( document ).ready(function() {
        $.ajax({
         type: "GET",
         url: "{!!URL::route('buildingAmenityInContract')!!}",
         data:'building_id='+ $('#building_idd').val(),
         success: function(data){
          var result = $.parseJSON(data);
          var selected = "";
          $('#amentity_types_id').empty();
          $('#amentity_types_id').append('<option value="">'+ 'Select Amenity' +'</option>')
            $.each(result[0], function(key, value) {
                $('#amentity_types_id').append('<option value="'+ value['id'] +'" '+ selected +'>'+ value['name'] +'</option>');
          });
        }
      });  
        /***************************************************************************/
  
  var d1 = $('#amc_contract_period_from').val();
  var d2 = $('#amc_contract_period_to').val();
  var pname = $('#method_text').val();
  var pid = $('#method').val();
  
  
  var date1 = new Date(d1);
  var date2 = new Date(d2);

  var date1_ms = date1.getTime();
  var date2_ms = date2.getTime();

  var diff = date2_ms-date1_ms;

          // get days
          var days = diff/1000/60/60/24;
          var no_days=days + 1;
          $('#no_days').val(no_days); 
          if(no_days>=30)
          {
           $('#payment_method_id').empty().append('<option value="6" id="monthly">'+ 'Monthly' +'</option>')
         }
         if(no_days>=90)
         {
          /*$('#payment_method_id').empty().append('<option value='+ pid +' selected >'+ pname +'</option>')*/
          $('#payment_method_id').empty().append('<option value="6" id="monthly">'+ 'Monthly' +'</option>')
          $('#payment_method_id').append('<option value="1" id="quarterly">'+ 'Quarterly' +'</option>')
        }
        if(no_days>=180)
        {
          /*$('#payment_method_id').empty().append('<option value='+ pid +' selected>'+ pname +'</option>')*/
          $('#payment_method_id').empty().append('<option value="6" id="monthly">'+ 'Monthly' +'</option>')
          $('#payment_method_id').append('<option value="1" id="quarterly">'+ 'Quarterly' +'</option>')
          $('#payment_method_id').append('<option value="2" id="HalfYearly">'+ 'Half-Yearly' +'</option>')
        } 
        if(no_days>=360)
        {
         /*$('#payment_method_id').empty().append('<option value='+ pid +' selected>'+ pname +'</option>')*/
         $('#payment_method_id').empty().append('<option value="6" id="monthly">'+ 'Monthly' +'</option>')
         $('#payment_method_id').append('<option value="1" id="quarterly">'+ 'Quarterly' +'</option>')
         $('#payment_method_id').append('<option value="2" id="HalfYearly">'+ 'Half-Yearly' +'</option>')
         $('#payment_method_id').append('<option value="7" id="Yearly">'+ 'Yearly' +'</option>')
       }
       if(no_days<30)
       {
        alert('No Of days must be atleast 30 days From Start Date To End Date!');
        $('#amc_contract_period_to').empty();
        $('#amc_contract_period_to').val("");
      }
      if(pid == 6){
        $("#payment_method_id option[value="+6+"]").prop('selected', true);
      
      }else if(pid == 1){
        $("#payment_method_id option[value="+1+"]").prop('selected', true);
      }else if(pid == 2){
        $("#payment_method_id option[value="+2+"]").prop('selected', true);
      }else if(pid == 7){
        $("#payment_method_id option[value="+7+"]").prop('selected', true);
      }
    });    
/***************************************************************************/
//delete schedule task
jQuery('.delete_type').click(function (event) {
                var action = $(this).attr("href");
                event.preventDefault();
                if (confirm('Do you want to Delete this Amenity?')) {
                    jQuery("#delete-form").attr('action', action);
                    jQuery("#delete-form").submit();
                } else {
                    return false;
                }
            });

/***************************************************************************/

</script>
@endsection





@endsection
