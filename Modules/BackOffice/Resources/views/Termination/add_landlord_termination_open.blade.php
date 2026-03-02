@extends('layouts.plms-app')



@section('content')



<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">{{ isset($landlordTermination)? 'Edit' : 'Create' }} Early Termination Request</div>
    </div>
    {{ (isset($landlordTermination))?   Breadcrumbs::render('landlordTermination.edit',$landlordTermination,Session::get('current')) :  Breadcrumbs::render('landlordTermination.create') }} 

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
</style>

<div class="row">  
  <div class="col-md-12 col-sm-12 dashboardtab">
    <div class="panel tab-border card-box">
      <div class="panel-body">
        <div class="tab-content">                                     

          <!-- --------------------------  Div Starts ----------------------------------- -->
          <div class="tab-pane active" id="maintenance">
            <div class="clearfix"></div>
            <div class="dataSearchBox">
              <div class="row">
                <div class="col-sm-6">
                  <div class="form-group">
                    <label for="landlord_contract_no">Agreement No<small class="textRed">*</small></label>
                    <div class="p-relative">
                     <i class="fa fa-eercast icn-add" aria-hidden="true"></i>
                     <input required type="text" class="form-control" id="landlord_contract_no"  name="landlord_contract_no" value="{{isset($landlordTermination)? $landlordTermination->landlordContract->landlord_contract_no : ''}}"  placeholder="Enter Agreement No">

                     <input type="hidden"  name="landlord_contract_id" id="landlord_contract_id" value="{{isset($landlordTermination)? $landlordTermination->contract_id : ''}}">
                   </div>
                 </div>
               </div>
               <div class="col-sm-6">
                <div class="form-group">
                  <label for="building_id">Building Name <small class="textRed">*</small></label>
                  <div class="p-relative">
                   <i class="icon icon-building" aria-hidden="true"></i>
                   <input required type="text" class="form-control" id="building_id"  name="building_id" value="{{isset($landlordTermination)? $landlordTermination->landlordContract->buildingInfo->building_name : ''}}"   placeholder="Enter Building Name">
                 </div>
               </div>
             </div>
             <div class="col-sm-6">
              <div class="form-group">
                <label for="vendor_id">Landlord Name<small class="textRed">*</small></label>
                <div class="p-relative">
                 <i class="icon icon-landlord" aria-hidden="true"></i>
                 <input required type="text" class="form-control" id="vendor_id"  name="vendor_id" value="{{isset($landlordTermination)? $landlordTermination->landlordContract->vendorInfo->vendor_name : ''}}"  placeholder="Enter Landlord Name">
               </div>
             </div>
           </div>
         </div>
         <form method="post" autocomplete="off" id="termination-form" action="{{isset($landlordTermination)? route( 'landlordTermination.update',$landlordTermination->id) : route( 'landlordTermination.store')}} " data-toggle="validator">
          {{csrf_field()}} @if(isset($landlordTermination)){{method_field('PUT')}}@endif
          <div id="landlorAgreementDetail"></div>
        </form>
      </div>

    </div>
  </div>
</div>
</div>
</div>           

</div>
<div class="modal" id="myModal">

</div>
@section('scripts')
<script src="{{ asset('public/js/jquery-ui.js') }} "></script>
<script type="text/javascript">
  $(document).ready(function() { 
  //edit 
  var contractNo = $('#landlord_contract_id').val();
  if(contractNo){
    $.ajax({
      type: "POST",
      url: "{{route('landlordAgreementDetail')}}",
      data: {"contractNo":contractNo,"_token": "{{ csrf_token() }}"},
      cache: false,
      success: function(data)
      {
        //alert(data);
        $("#landlorAgreementDetail").html(data);

      } 
    });
  }
  /****************************************************************************/

  $('#landlord_contract_no').autocomplete({
    source : '{!!URL::route('landlordAgreementAutocomplete')!!}',
    minlenght:2,
    autoFocus:true,
    change:function(e,ui){
       var contractNo = ui.item.ids;
       $(document).on('change keyup paste','#landlord_contract_no,#building_id,#vendor_id',function(){ 
         
          var contractNum = $("#landlord_contract_no").val();
          var building = $("#building_id").val();
          var vendor = $("#vendor_id").val();

          if(contractNum =="" && building=="" && vendor==""){
            $("#landlorAgreementDetail").empty();
          }else{
            $("#landlorAgreementDetail").show();
          }

        });



      if(ui.item.ids != null){
        $('#landlord_contract_id').val(ui.item.ids);
        if(contractNo){
          $.ajax({
            type: "POST",
            url: "{{route('landlordAgreementDetail')}}",
            data: {"contractNo":contractNo,"_token": "{{ csrf_token() }}"},
            cache: false,
            success: function(data)
            {
              $("#landlorAgreementDetail").html(data);
              $("#building_id").val("");
              $("#vendor_id").val("");
              $("#building_id").val("");
            //  $("#landlord_contract_no").val("");
          } 
        });

        }
      }

    }
  });
  /****************************************************************************/
  $('#building_id').autocomplete({
    source : '{!!URL::route('landlordBuildingAutocomplete')!!}',
    minlenght:2,
    autoFocus:true,
    change:function(e,ui){
      if(ui.item.ids != null){
        //$('#building_id').val(ui.item.ids);
        var building_id = ui.item.ids;
        if(building_id){
          $.ajax
          ({
            type: "POST",
            url: "{{route('landlordAgreementDetail')}}",
            data: {"building_id":building_id,"_token": "{{ csrf_token() }}"},
            cache: false,
            success: function(data)
            {
              $("#landlorAgreementDetail").html(data);
              $("#landlord_contract_no").val("");
             // $("#building_id").val("");
             $("#vendor_id").val("");
           }
         });
        }
      }
    }
  });
  /****************************************************************************/
  $('#vendor_id').autocomplete({
    source : '{!!URL::route('landlordVendorAutocomplete')!!}',
    minlenght:2,
    autoFocus:true,
    change:function(e,ui){
      if(ui.item.ids != null){
        //$('#building_id').val(ui.item.ids);
        var vendor_id = ui.item.ids;
        if(vendor_id){
          $.ajax
          ({
            type: "POST",
            url: "{{route('landlordAgreementDetail')}}",
            data: {"vendor_id":vendor_id,"_token": "{{ csrf_token() }}"},
            cache: false,
            success: function(data)
            {
              $("#landlorAgreementDetail").html(data);
              $("#building_id").val("");
             // $("#vendor_id").val("");
             $("#landlord_contract_no").val("");
           }
         });
        }
      }
    }
  });
  /****************************************************************************/
});
</script>
@endsection





@endsection
