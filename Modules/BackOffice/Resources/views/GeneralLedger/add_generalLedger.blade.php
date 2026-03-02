@extends('layouts.plms-app')
@section('css')

<link rel="stylesheet" href="{{ asset('public/css/jquery-ui.css')}} ">
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">
<link href="{{asset('public/css/formlayout.css')}}" rel="stylesheet" type="text/css" />
@endsection 


@section('content')
	<!-- start widget -->
  <div class="page-bar">
      <div class="page-title-breadcrumb">
          <div class=" pull-left">
              <div class="page-title">{{ (isset($generalLedger))? 'Edit' : 'Add'  }} General Ledger</div>
          </div> 
           {{ (isset($generalLedger))?  
            Breadcrumbs::render('generalLedger.edit',$generalLedger) :  Breadcrumbs::render('generalLedger.create') }}
      </div>
  </div>

<form action="{{ !isset($generalLedger)? route('generalLedger.store'): route('generalLedger.update',$generalLedger->id)}}" method="POST" id="form_sample_2" class="form-horizontal" data-toggle="validator">
  {{csrf_field()}} @if(isset($generalLedger)){{method_field('PUT')}}@endif
<div class="row">
<div class="col">

<div class="card card-box salesSearchBox"> 
<div class="dataSearchBox ">    
        <div class="row">
          <div class="col-sm-6">
            <div class="form-group">
                <label for="voucher_no">Voucher No.:<small class="textRed">*</small></label>
                <div class="p-relative">
                <i class="fa fa-codepen icn-add" aria-hidden="true"></i>
                <input  type="text" class="form-control" id="voucher_no" disabled name="voucher_no" value="{{ isset($generalLedger)? $generalLedger->voucher_no : $nextCode}}" >
              </div>
            </div>
          </div>

        <div class="col-sm-6">
            <div class="form-group">
                <label for="general_ledger_type">Ledger Type:<small class="textRed">*</small></label>
                <div class="p-relative">
                <i class="fa fa-paper-plane-o icn-add" aria-hidden="true"></i>
                <select required class="form-control" id="general_ledger_type" name="general_ledger_type">
                  <option value="">Select Ledger </option>
                  @foreach($ledger_types as $ledger_key => $ledger_type)
                  <option {{(old('general_ledger_type',(isset($generalLedger)? $generalLedger->general_ledger_type : '') ) ==  $ledger_key )? 'selected': ''}}   value="{{$ledger_key}}">{{$ledger_type}}</option>
                  @endforeach
                </select>
              </div>
            </div>
          </div>

         
          <div class="col-sm-6">
            <div class="form-group">
                <label for="jv_refer_no"> JV Ref No.:<small class="textRed">*</small></label>
                <div class="p-relative">
                <i class="fa fa-life-ring icn-add" aria-hidden="true"></i>
                <input required type="text" class="form-control" id="jv_refer_no"  name="jv_refer_no" value="{{ old('jv_refer_no',isset($generalLedger)? $generalLedger->jv_refer_no : '' )}}"  placeholder="Enter JV Ref No" pattern="^[ A-Za-z0-9_@./#&+-]*$" data-msg-pattern="Allowed only Alpha Numeric and Special Characters Values">
              </div>
            </div>
          </div>




        <div class="col-sm-6">
            <div class="form-group">
                <label>Doc Date :<small class="textRed">*</small></label>
                <div class="p-relative">
                <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
                <input required type="date" class="form-control " id="doc_date" name="doc_date" value="{{ old('doc_date', isset($generalLedger)? (\Carbon\Carbon::parse($generalLedger->doc_date)->format('Y-m-d')) : date('Y-m-d',strtotime(today())
               ) )}}"  placeholder="Enter Invoice Date">
                </div>
            </div> 
        </div>


      

        <div class="col-sm-6">
            <div class="form-group">
                <label for="general_ledger_desc"> Description: </label>
                <div class="p-relative">
                <i class="fa fa-pencil-square-o icn-add" aria-hidden="true"></i>
                <input   type="text" class="form-control" id="general_ledger_desc"  name="general_ledger_desc" value="{{ old('general_ledger_desc',isset($generalLedger)? $generalLedger->general_ledger_desc : '' )}}" placeholder="Enter Description" >
              </div>
            </div>
          </div>

         <div class="col-sm-6">
            <div class="form-group">
                <label for="general_ledger_desc"> Bank:<small class="textRed bankValid  " >*</small></label>
                <div class="p-relative">
                <i class="fa fa-university icn-add" aria-hidden="true"></i>
               <select class="form-control" id="bank_id" name="bank_id" {{(isset($generalLedger)?(in_array($generalLedger->general_ledger_type,[1,3])?'Disabled':''):'')}}>
                 <option value="">Select Bank</option>
                 @foreach($banks as  $bank)
                 <option  {{(old('general_ledger_type',(isset($generalLedger)? $generalLedger->bank_id : '') ) ==  $bank->id )? 'selected': ''}} value="{{$bank->id}}">{{$bank->bank_name}}</option>
                 @endforeach
               </select>
              </div>
            </div>
          </div>


         <div class="col-sm-6">
            <div class="form-group">
                <label for="amount"> Amount:<small class="textRed">*</small></label>
                <div class="p-relative">
                <i class="fa fa-money icn-add" aria-hidden="true"></i>
                <input required type="text" class="form-control allownumericwithdecimal text-right" id="amount"  name="amount" value="{{ old('amount',isset($generalLedger)? number_format((float)$generalLedger->amount,3,'.', '') : '' )}}" placeholder="Select Ledger Type" readonly>
              </div>
            </div>
          </div>


          <div class="col-sm-6 agreement_no_div">
            <div class="form-group">
                <label for="agreement_no"> Agreement No:</label>
                <div class="p-relative">
                <i class="fa fa-tags icn-add" aria-hidden="true"></i>
                <input type="text" class="form-control txt_box agreement_no" disabled class="agreement_no" id="agreement_no"  name="agreement_no" value="{{ old('agreement_no',isset($generalLedger)? $generalLedger->agreement_no : '' )}}" placeholder="Enter Agreement No">
                <input type="hidden"  class="agreement_no"  value="{{ old('agreement_no',isset($generalLedger)? $generalLedger->agreement_no :'' )}}" placeholder="Enter Agreement No">
              </div>
            </div>
          </div>


       

        <div class="w-100"></div> 
           
      </div>
    
</div>

  


<div class="clearfix"></div>
    




  <div class="dataSearchBox ">    
        <div class="row">

             <div class="col-sm-6">
            <div class="form-group">
                <label for="ax_batch_id"> AX-Batch ID:</label>
                <div class="p-relative">
                <i class="fa fa-wrench icn-add" aria-hidden="true"></i>
                <input  disabled type="text" class="form-control" id="ax_batch_id"  name="ax_batch_id" value="{{ old('ax_batch_id',isset($generalLedger)? $generalLedger->ax_batch_id : '' )}}" >
              </div>
            </div>
          </div>

              <div class="col-sm-6">
            <div class="form-group">
                <label for="ax_batch_no"> AX - Voucher:</label>
                <div class="p-relative">
                <i class="fa fa-tags icn-add" aria-hidden="true"></i>
                <input disabled  type="text" class="form-control" id="ax_batch_no"  name="ax_batch_no" value="{{ old('ax_batch_no',isset($generalLedger)? $generalLedger->ax_batch_no : '' )}}" >
              </div>
            </div>
          </div>


        </div>
      </div>




<div class="sub-head ">Dimension Details</div>


 <div class="clearfix" style="padding: 10px 20px"></div>

 
 <div class="row">
  <div class="col-sm-12">

<div class="table-wrap ">    

        <div class="table-responsive"> <div>
          <table class="table display product-overview mb-30">
             <thead>
                    <tr>
                      <th>
                        <a  href='#'   id="add_details" class=" add_details"><i class="fa fa-plus" aria-hidden="true"></i>
</a>
                      </th>  
                       <th>Account Code</th>                        
                      <!--  <th>Type</th> -->
                       <th>Bld Code</th>
                       <th>Unit Code</th>
                       <th>JV Desc</th>
                       <th>Dr.Amt</th>
                       <th>Cr.Amt</th>                      
                       <th>Recovery</th>                       
                       <th>Dim1</th>
                       <th>Dim2</th>      
                    </tr>
                </thead>
                <tbody id="tbody_row">

                  @include('backoffice::GeneralLedger.common_distribution')
                    
                </tbody>
                <tr>
                      <td><button class="btn btn-info add_details mr-2">Add more rows <i class="fa fa-plus" aria-hidden="true"></i></button></td>
                      <td colspan="4" ><span class="pull-right" >Total : </span></td>
                      <td ><input type="text" id="debit_amt_total" class="debit_amt_total text-right"  name="debit_amt_total" value="{{ (isset($generalLedger)?  numberFormat($generalLedger->generalLedgerDim->sum('debit_amt')): '0.000'  ) }}" readonly ></td>                      
                      <td colspan="4" class="pull-left"><input type="text" class="credit_amt_total text-right" id="credit_amt_total"  name="credit_amt_total" value="{{ (isset($generalLedger)?  numberFormat($generalLedger->generalLedgerDim->sum('credit_amt')): '0.000'  ) }}" readonly ></td>
                    </tr>
          </table>
        </div>
        </div>
      </div></div>
<div class="sub-head "></div>
<div class="clearfix" ></div>   
        
        <div class="row" style="padding: 10px 20px">
          <div class="col-sm-12">
            <button type="submit" class="btn btn-primary save_form ">Save </button>
          </div>
        </div>
      </div>

      </div>

    </div>



</div>
</div>
</form>

 
               




@endsection


@section('scripts')
<script src="{{ asset('public/js/jquery-ui.js') }} "></script>
<script>
  $(document).ready(function() {
	
	$('.agreement_no').prop('disabled', true);
	$('.agreement_no').val('');
	$('.agreement_no_div').hide();

    @if( (isset($generalLedger) && !in_array($generalLedger->general_ledger_type,[2,4]) ) || !isset($generalLedger) ) 
     $('.bankValid').hide(); 
    @endif




    $("#form_sample_2").validate({
      
      rules: {
      agreement_no: {
                required: {
                    depends: function(element) {
                      return ($("#general_ledger_type").val() == 3);
                    }
                },
              },
      
      bank_id :  {
                required: {
                    depends: function(element) {
                      return (($("#general_ledger_type").val() == 2) || ($("#general_ledger_type").val() == 4));
                    }
                },
              },       

    },
    submitHandler: function(form) {

       var $tr = $('#tbody_row tr[id^="first_row"]:last');
       
       for($i=1;$i <= $('#tbody_row tr').length;$i++){

          if($("#first_row"+$i).find('.account_code').val() =='' ||
            $("#first_row"+$i).find('.building_name').val() ==''){
            alert("Please Fill All the Account Code and Bld Code");
            return false;
          }
          if($("#first_row"+$i).find('.debit_amt').val() && $("#first_row"+$i).find('.credit_amt').val()){
          if($("#first_row"+$i).find('.debit_amt').val().length==0 && $("#first_row"+$i).find('.credit_amt').val().length==0){
              alert("Both Dr.Amt and Cr.Amt Cannot Be Empty In Same Account Code");
             
               return false;
          }
          }
          if($("#first_row"+$i).find('.debit_amt').val() && $("#first_row"+$i).find('.credit_amt').val()){
            
            if($("#first_row"+$i).find('.debit_amt').val().replace(/,/g, '') == 0 &&
              $("#first_row"+$i).find('.credit_amt').val().replace(/,/g, '') == 0){

              alert("Both Dr.Amt and Cr.Amt Cannot Be Empty In Same Account Code");
             
              return false;
            }
          }
      }

      var general_ledger_type = parseInt($('#general_ledger_type').val());
      var amount = 0;
      switch(general_ledger_type){

        case 1 :
            
           
            var debit_amt = credit_amt = 0;

            $('.debit_amt').each(function(){
            if($(this).val() != '')
              debit_amt = debit_amt + parseFloat($(this).val().replace(/,/g, ''));
            });

            $('.credit_amt').each(function(){
            if($(this).val() != '')
             credit_amt = credit_amt + parseFloat($(this).val().replace(/,/g, ''));
            });
            if($('#amount').val() !='')
            var amount = parseFloat($('#amount').val().replace(".", " "));
            
            if((debit_amt - credit_amt) == 0)
            {
              $('.save_form').prop('disabled', true);
              form.submit();
            }else{
             // alert('Total Debit should Equal to Total Credit.');
			  $('.save_form').prop('disabled', true);
              form.submit();
            }

            break;

         case 2:
 
            var debit_amt = credit_amt = 0;

            $('.debit_amt').each(function(){
            if($(this).val() != '')
             debit_amt = debit_amt + parseFloat($(this).val().replace(/,/g, ''));
            });

            $('.credit_amt').each(function(){
            if($(this).val() != '')
             credit_amt = credit_amt + parseFloat($(this).val().replace(/,/g, ''));
            });
            if($('#amount').val() !='')
               amount = parseFloat($('#amount').val().replace(",", " "));

            if(amount == (debit_amt - credit_amt))
            {
             
              $('.save_form').prop('disabled', true);
              form.submit();
            }else{
             // alert('Bank Amount should tally with the Difference of Debit – Credit.');
              //return false;
			  $('.save_form').prop('disabled', true);
              form.submit();
            }

            break;

        case 3 :
            
           
            var debit_amt = credit_amt = 0;

            $('.debit_amt').each(function(){
            if($(this).val() != '')
             debit_amt = debit_amt + parseFloat($(this).val().replace(/,/g, ''));

            });

            $('.credit_amt').each(function(){
            if($(this).val() != '')
              credit_amt = credit_amt + parseFloat($(this).val().replace(/,/g, ''));
            });
           if($('#amount').val() !='')
             amount = parseFloat($('#amount').val().replace(",", " "));

            if((debit_amt - credit_amt) == 0)
            {
              $('.save_form').prop('disabled', true);
              form.submit();
            }else{
             // alert('Total Debit should Equal to Total Credit.');
			 $('.save_form').prop('disabled', true);
              form.submit();
            }

            break;

        case 4:
 
            var debit_amt = credit_amt = 0;

            $('.debit_amt').each(function(){
            if($(this).val() != '')
             debit_amt = debit_amt + parseFloat($(this).val().replace(/,/g, ''));

            });

            $('.credit_amt').each(function(){
            if($(this).val() != '')
              credit_amt = credit_amt + parseFloat($(this).val().replace(/,/g, ''));
            });
            if($('#amount').val() !='')
            var amount = parseFloat($('#amount').val().replace(",", " "));

            if(amount == (debit_amt - credit_amt))
            {
             
              $('.save_form').prop('disabled', true);
              form.submit();
            }else{
             // alert('Bank Amount should tally with the Difference of Debit – Credit.');
             // return false;
			  $('.save_form').prop('disabled', true);
              form.submit();
            }

            break;

        default :
            
            $('.save_form').prop('disabled', true);
            form.submit();
            break;
       }
       

        
      }
    });

	$("input[name^='account_code']").autocomplete({
		  source : '{!!URL::route('accountCodeAutocomplete')!!}',
		  minlenght:2,
		  autoFocus:true,
      change:function(e,ui){
        var parentId = $(this).parent().parent().attr('id');

        if (ui.item == null || ui.item == undefined || parentId =='') {
			$(this).val("");
			$("#"+parentId).find('.account_id').val('');
			$("#"+parentId).find('.account_code').val('');
        }
        else{
         
          $("#"+parentId).find('.account_id').val(ui.item.ids);
        }
      }
		 
	});

  $("#agreement_no").autocomplete({
      source : '{!!URL::route('landlordAgreementAutocomplete')!!}',
      minlenght:2,
      autoFocus:true,
      change:function(e,ui){
        var parentId = $(this).parent().parent().attr('id');
        if (ui.item == null || ui.item == undefined) {
          
            $(".agreement_no").val('');
               
        }
        else{
            $(".agreement_no").val(ui.item.ids);
            $("#agreement_no").val(ui.item.value);
            
            distribution(); 
        }
      }
     
  });
	
	var buildingOption = {
      source : '{!!URL::route('buildingAutocompleteInGl')!!}',
      minlenght:2,
      autoFocus:true,
      change:function(e,ui){
        var parentId = $(this).parent().parent().attr('id');
        if (ui.item == null || ui.item == undefined) {
                $("#"+parentId).find('.building_id').val('');
                $("#"+parentId).find('.building_name').val('');
                $("#"+parentId).find('.dim2_input').val('');
                $("#"+parentId).find('.dim2').val('');
                $("#"+parentId).find('.unit_id').html('<option value="">No Available Units</option>');
                $('.building_name-error').show();
            }else {
                $("#"+parentId).find('.building_id').val(ui.item.ids);
                $("#"+parentId).find('.dim2_input').val(ui.item.value);
                $("#"+parentId).find('.dim2').val(ui.item.ids);
                
                var id = ui.item.ids;
                $.ajax({
                      type: "POST",
                      url: "{{url('/buildingByUnitGL')}}",
                      data: {"id":id,"_token": "{{ csrf_token() }}"},
                      cache: false,
                      dataType: "json",
                      success: function(data)
                      {
                       
                       if(data['buildUnit'].length > 0){
                          $("#"+parentId).find('.unit_id').empty();
                          $("#"+parentId).find('.unit_id').append('<option value="">'+ 'Select Unit' +'</option>')
                            $.each(data['buildUnit'], function(key, value) {
                                $("#"+parentId).find('.unit_id').append('<option value="'+ value['id'] +'">'+ value['unit_code'] +'</option>');
							              });
                        }
                        else{
                            $("#"+parentId).find('.unit_id').html('<option value="">No Available Units</option>');
                        }
                      } 
                  });
                $.ajax({
                      type: "POST",
                      url: "{{url('/ajaxBuildingWithAxDivision')}}",
                      data: {"building_id":id,"_token": "{{ csrf_token() }}"},
                      cache: false,
                      dataType: "json",
                      success: function(data)
                      {
                       
                       if(data['ax_division'].length > 0){
                        if(data['ax_division']=='01')
                          var div = 'HO';
                        else
                          var div = 'PLM';
                          //$("#"+parentId).find('.dim1').empty();
                          $("#"+parentId).find('.dim1').val(data['ax_division']);
                          
                        }
                        else{
                            $("#"+parentId).find('.dim1').val(data['ax_division']);
                        }
                      } 
                  });
              
           }
        
			}
		}; 


$(".building_name").autocomplete(buildingOption);		


		//Account Code 
		$(document).on("change",'.account_id',function(event){
		  des =  $( event.target ).closest('tr').find(".description");
		  var des_val = $('option:selected',this).attr('data_des');
		  des.val(des_val);
		   })

		$(document).on("change",'.building_id',function(event){
		  des =  $( event.target ).closest('tr').find(".dim2");   
		  des.val($(this).val());
		   })



		// List Unit on change Building 
		$(document).on("change",'.building_id',function(event){

		  var building_id = $(this).val();
		  unit =  $( event.target ).closest('tr').find(".unit_id");

		  if(building_id != ''){

			   $.ajax({
				 type: "GET",
				 url: "{{route('buildingUnit')}}/"+building_id,        
				 success: function(data){          
				  var result = $.parseJSON(data);
				  var selected = "";
				  unit.empty();
				  unit.append('<option value="">'+ 'Select Unit' +'</option>')
					$.each(result, function(key, value) { 
					  unit.append('<option value="'+ value['id'] +'" '+ selected +'>'+ value['unit_code'] +'</option>');
				  });
				}
			  }); 


		  }    
    
   });


function amount(){

 var debit_amt_total =   $('#debit_amt_total').val().replace(/,/g, '');
 var credit_amt_total = $('#credit_amt_total').val().replace(/,/g, '');

 var amount = parseFloat(debit_amt_total) - parseFloat(credit_amt_total);
 $('#amount').val(formatNumber(amount.toFixed(3)));
}


function amtCal(className){
  var general_ledger_type = parseInt($('#general_ledger_type').val());
  var total = 0;
  $('.'+className).each(function() {
    var this_val = ($(this).val() !=  '')? ($(this).val().replace(/,/g, '')) : 0;
    total = parseFloat(total) + parseFloat(this_val);  
  });
    //alert(total.toString().replace(".", ","));  
 
  $('#'+className+'_total').val(formatNumber(total.toFixed(3)));  
 
  if(general_ledger_type ===1 || general_ledger_type === 3 )

      amount();
 
 
}



$(document).on("keyup",'.debit_amt,.credit_amt',function (event) {

  var className = $(this).attr('data_name');
  var parentId 	= $(this).parent().parent().attr('id');
  var credit_value = parseFloat($("#"+parentId).find('.credit_amt').val().replace(/,/g, ''));
  //var debit_value = parseFloat($("#"+parentId).find('.debit_amt').val());
  var debit_value = $("#"+parentId).find('.debit_amt').val().replace(/,/g, '');
  //alert(debit_value);
  if(className == 'debit_amt' &&  debit_value > 0 ){
	  
	  $("#"+parentId).find('.credit_amt').val(0);
  }
  else if(className == 'credit_amt' &&  credit_value > 0 ){
	  
	  $("#"+parentId).find('.debit_amt').val(0);
  }
 
  amtCal('debit_amt');
  amtCal('credit_amt');

});



 $('.add_details').click(function(event){
  
	event.preventDefault();
  
  var $tr = $('#tbody_row tr[id^="first_row"]:last');
  for($i=1;$i <= $('#tbody_row tr').length;$i++){

    if($("#first_row"+$i).find('.account_code').val() =='' ||
      $("#first_row"+$i).find('.building_name').val() ==''){
      alert("Please Fill All the Account Code and Bld Code");
      return false;
    }
  }

  var num = parseInt( $tr.prop("id").match(/\d+/g), 10 ) +1;

  var first_row = $tr.clone().prop('id', 'first_row'+num );

	var clone =  $('#first_row').clone();
         for($i=1;$i <= $('#tbody_row tr').length;$i++){
            var selectedValue = $("#first_row"+$i).find('.dim1').val();
            $(first_row).find("option[value = '" + selectedValue + "']").attr("selected", "selected");
          }
  $(first_row).find(".account_code,.txt_box,.debit_amt,.credit_amt").val("").end().appendTo('#tbody_row');

	$('tr').each(function(rowIndex){
    /// find each input with a name attribute inside each row
 
      $(this).find('input[name]').each(function(){
        var name;
        name = $(this).attr('name');
        name = name.replace(/\[[0-9]+\]/g, '['+(rowIndex-1)+']');
        $(this).attr('name',name);
        
        if(name == 'account_code[]'){

            $("input[name^='account_code']").autocomplete({
                source : '{!!URL::route('accountCodeAutocomplete')!!}',
                minlenght:2,
                autoFocus:true,
                change:function(e,ui){
                  var parentId = $(this).parent().parent().attr('id');

                  if (ui.item == null || ui.item == undefined || parentId =='') {
						$(this).val("");
                        $("#"+parentId).find('.account_id').val('');
                        $("#"+parentId).find('.account_code').val('');  
                  }
                  else{
                   
                    $("#"+parentId).find('.account_id').val(ui.item.ids);
                  }
                }
            });
        }
        if(name == 'building_name[]'){
              $(".building_name").autocomplete(buildingOption);
        }
      });

      $(this).find('select[name]').each(function(){
          var name;
          name = $(this).attr('name');
          name = name.replace(/\[[0-9]+\]/g, '['+(rowIndex-1)+']');
          $(this).attr('name',name);
      });
   });

	


 });

 $(document).on("click",".remove_details",function(event) {
    
    event.preventDefault();
     var tr_count =  $(this).closest('tbody').find('tr').length;
      if(tr_count > 1)
         $(this).closest('tr').remove();
      else{

        $(this).find('input[name]').each(function(){
        var name;
        name = $(this).attr('name');
        name = name.replace(/\[[0-9]+\]/g, '['+(rowIndex-1)+']');
        $(this).attr('name',name);
       });

       $(this).find('select[name]').each(function(){
        var name;
        name = $(this).attr('name');
        name = name.replace(/\[[0-9]+\]/g, '['+(rowIndex-1)+']');
        $(this).attr('name',name);
       });

       }

       amtCal('debit_amt');
       amtCal('credit_amt');
      
    });



 function distribution(){

    var ledger_type = $('#general_ledger_type').val();
    var agreement_no = $('#agreement_no').val();

    var agreement_len = agreement_no.length;

    if( (ledger_type == 3 && agreement_no != '' && agreement_len >= 10) || ledger_type != 3 ){

       $.ajax({
        method: "POST",
        url: "{{route('generalLedgerDistribution')}}",
        data: { "_token" : $('meta[name="csrf-token"]').attr('content'),
               'ledger_type': ledger_type, 'agreement_no': agreement_no
            },         
        success: function(data){

            if(data == 'Invaild Agreement'){
            $('#tbody_row').html('');
            alert('Invaild Agreement')
            }
            else{
              $('#tbody_row').html(data);
              $("input[name^='account_code']").autocomplete({
                source : '{!!URL::route('accountCodeAutocomplete')!!}',
                minlenght:2,
                autoFocus:true,
                change:function(e,ui){
                  var parentId = $(this).parent().parent().attr('id');

                  if (ui.item == null || ui.item == undefined || parentId =='') {
						$(this).val("");
                          $("#"+parentId).find('.account_id').val('');
                          $("#"+parentId).find('.account_code').val('');
                  }
                  else{
                   
                    $("#"+parentId).find('.account_id').val(ui.item.ids);
                  }
                }
               
              });
              $(".building_name").autocomplete(buildingOption);
              
            }
            $('#debit_amt_total').val(0); 
            $('#credit_amt_total').val(0); 
           } 
       });

    }

 }



    $(document).on('focusin', '#general_ledger_type', function(){        
          $(this).data('val', $(this).val());

      }).on('change','#general_ledger_type',function(){
         var ledger_type = $(this).val();
         if(ledger_type == 1 ){
            $('#bank_id').val('');
            $('#bank_id').prop('disabled', true);
         }
         if(ledger_type == 3 ){
            $('.agreement_no').prop('disabled', false);
            $('#bank_id').val('');
            $('#bank_id').prop('disabled', true);
            $('.agreement_no_div').show();
            
         }
         else{
            $('.agreement_no').prop('disabled', true);
            $('.agreement_no').val('');
            $('.agreement_no_div').hide();
         }
         if(ledger_type == 2 || ledger_type == 4){
            $('#bank_id').prop('disabled', false);
            $(".building_name").autocomplete(buildingOption);
       
            $('#amount').prop('readonly', false).attr("placeholder", "Enter Amount");
            $('.bankValid').show();
          
         }else{
         
         @if(isset($generalLedger))
             amount();
             $('#amount').prop('readonly', true);
             $('.bankValid').hide();
          @else
            $('#amount').val('').prop('readonly', true).attr("placeholder", "Please Fill Dr.Amt And Cr.Amt");
            $('.bankValid').hide();
          @endif
       }
 
        var prev = $(this).data('val');



       if( (prev == 3 && $(this).val() != 3) ||  (prev != 3 && $(this).val() == 3))        
       distribution(); 

    });


   // $(document).on("change keyup paste",'#agreement_no',function (event) {
      
   // });






  });

</script>
@endsection


