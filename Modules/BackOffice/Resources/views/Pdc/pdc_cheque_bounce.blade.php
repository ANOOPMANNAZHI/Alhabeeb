@extends('layouts.plms-app')



@section('content')
<style>
  input[type=date]::-webkit-inner-spin-button {
    -webkit-appearance: none;
    display: none;
  }
</style>
<!-- start widget -->
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">Cheque Bounce</div>
    </div> 
    {{ Breadcrumbs::render('tenantPdcBounce') }} 
  </div>
</div>

<div class="row">
  <div class="col-md-12 col-sm-12 dashboardtab">
    <div class="panel tab-border card-box">
      @include('backoffice::Pdc.cheque_bounce_search')   
    </div>
  </div>
</div>

@if(!empty($request->all()))

<div class="row">
 <div class="col-md-12 col-sm-12">
  <div class="card  card-box">

    <div class="card-body ">

      <div style="overflow-x:auto;">


        <table class="table display product-overview mb-30" id="">
          <thead>
            <tr>

              <!--<th>Sl No.</th> -->
              <th>Cheque No</th>
              <th>Cheque Date</th>
              <th>Bank</th>
              <th>Building Name</th>
              <th>Building Code</th>
              <th>Unit No</th>
              <th>Tenant Name</th>
              <th>Tenant Code</th>
              <th>Agr No</th>
              <th>Cheque Status</th>
              <th>Receipt No</th>
              <th>Receipt Status</th>
            </tr>
          </thead>
          <tbody>
            @php $count = 1; @endphp
            <input type="hidden" name="search_count" value="{{count($pdc)}}" >
            <input type="hidden" name="row_collection" value="0" id="row_collection">
            @forelse ($pdc as $key=>$pdcData) 
            <tr id="tr_{{$pdcData->id}}">

              <!--<td>{{$pdc->perPage()*($pdc->currentPage()-1)+$count}}</td> -->
              <td>{{ $pdcData->pdc_check_no}}
                <input type="hidden" name="pdc_check_no_{{$pdcData->id}}" id="pdc_check_no_{{$pdcData->id}}" value="{{$pdcData->id}}" >
                <input type="hidden" name="contract_id_{{$pdcData->id}}" id="contract_id_{{$pdcData->id}}" value="{{$pdcData->tenant_contract_id}}" >
              </td>
              <td>{{ $pdcData->pdc_check_date->format('d/m/Y') ?? ''}}</td>
              <td>
               {{ $pdcData->bankInfo->bank_name}}
             </td>
             <td>{{ $pdcData->tenantContractInfo->building->building_name}}</td>
             <td>{{ $pdcData->tenantContractInfo->building->building_code}}</td> 
             <td>{{ $pdcData->tenantContractInfo->Unit->unit_no}}</td>
             <td>{{ $pdcData->tenantContractInfo->tenant->tenant_name}}</td>
             <td>{{ $pdcData->tenantContractInfo->tenant->tenant_code}}</td> 
             <td>{{ $pdcData->tenantContractInfo->tenant_contract_no}}</td>
             <td>
              <select name="pdc_cancel_reason_{{$pdcData->id}}" class="pdc_cancel_reason" id="{{$pdcData->id}}">
                <option value="">Select</option>
                <option value="1" {{($pdcData->pdc_cancel_reason == 1)?'selected':''}}>Bounce</option>
                <option value="2" {{($pdcData->pdc_cancel_reason == 2)?'selected':''}}>Exchange</option>
              </select>

            </td>
            <td>{{$pdcData->pdc_receipt_no}}
            </td>
            <td>
              @if($pdcData->pdc_is_posted==1)
              Posted
              @else
              Not Posted
              @endif

            </td>
          </tr>  


          @empty 
          <tr>
            <td colspan="12" align="center">
              <p>No Record</p>
            </td>
          </tr>
          @endforelse 

        </tbody>
      </table>
      @php $count++; @endphp
	  {{$pdc->withPath($route)->appends(\Request::except(['page','_token']))->links()}}
    </div>

    @php
    /*
    $sort =  app('request')->input('sort') ;
    if(!empty($sort)){
    $direction =  app('request')->input('direction') ;
    $pdc->appends(['sort' => $sort, 'direction' => $direction ]);                   
  }  */                             
  @endphp 
  {{--$pdc->appends(\Request::except(['page','_token']))->links()--}} 
  @endif 
</div>
</div>
</div>
</div>
<!-- -------------------- Exchange Modal ---------------------------------------------->
 <div class="modal fade" id="exchange_id" tabindex="-2" role="dialog1">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
     <div class="modal-header">
      <h4 class="modal-title">PDC Exchange</h4>
      <button type="button" class="close" data-dismiss="modal">&times;</button>
    </div>
    <div class="modal-body">
     <div class="row">
      <div class="col">
        <div class="card card-box salesSearchBox">
          <input type="hidden" name="popId" id="popId" value=""/>    
          <div class="dataSearchBox ">
            <div class="row">
              <div class="col-sm-12">
                <div class="form-group">
                  <label for="tenant_contract_deposit_amt">Cheque No</label>
                  <div class="p-relative">
                    <i class="fa fa-money icn-add" aria-hidden="true"></i>
                    <input type="texbox" required name="pdc_check_no" id="pdc_check_no" value="" class="form-control  data-rule-pattern="\d{1,9}(\d{0,3})?" data-msg-pattern="Allowed only Numeric ">                    
                  </div>
                </div>
              </div>
              <div class="col-sm-12">
                <div class="form-group">
                  <label for="tenant_contract_deposit_amt">Cheque Dt</label>
                  <div class="p-relative">
                    <i class="fa fa-money icn-add" aria-hidden="true"></i>
                    <input type="date" required class="form-control  name="pdc_check_date" id="pdc_check_date" value="">                    
                  </div>
                </div>
              </div>
              <div class="col-sm-12">
                <div class="form-group">
                  <label for="tenant_contract_deposit_amt">Amount</label>
                  <div class="p-relative">
                    <i class="fa fa-money icn-add" aria-hidden="true"></i>
                    <input type="number" required class="form-control name="pdc_amt" id="pdc_amt" value="" pattern="^\d{1,4}(,\d{4})*(\.\d+)?$"  data-type="currency">                  
                  </div>
                </div>
              </div>
              <div class="col-sm-12">
                <div class="form-group">
                  <label for="tenant_contract_deposit_amt">Stage</label>
                  <div class="p-relative">
                    <i class="fa fa-money icn-add" aria-hidden="true"></i>
                    <input type="number" required class="form-control name="pdc_stage id="pdc_stage" value="" min="1">                 
                  </div>
                </div>
              </div>
              <div class="col-sm-12">
                <div class="form-group">
                  <label for="tenant_contract_deposit_amt">Bank Name</label>
                  <div class="p-relative">
                    <i class="fa fa-money icn-add" aria-hidden="true"></i>
                    <select name="bank_id_popup" id="bank_id_popup" class="form-control" required>
                      <option value="">Select</option> 
                      @foreach($banks as $name)
                      <option value="{{$name->id}}">{{$name->bank_name}}</option>
                      @endforeach
                    </select>                 
                  </div>
                </div>
              </div>
              <div class="col-sm-12">
                <div class="form-group">
                  <label for="tenant_contract_deposit_amt">Rec Dt</label>
                  <div class="p-relative">
                    <i class="fa fa-money icn-add" aria-hidden="true"></i>
                    <input type="date" required class="form-control  name="pdc_recieve_date id="pdc_recieve_date" value="">               
                  </div>
                </div>
              </div>
              <div class="w-100"></div>
              <div class="col">
                <div class="w-100"></div>
                <button type="button" name="submit" value="exchange" class="btn btn-primary" id="exchange">Exchange</button>
                <button type="button" data-dismiss="modal"  value="skip"  name="skip" id="skip"  class="btn btn-warning">Skip</button>
              </div>

            </div>
            
          </div>
          <div class="col-sm-12 text-right">
          </div>
          <div class="clearfix"></div>
        </form>

      </div>
    </div>
  </div> 

</div>
</div>
<div class="modal-footer"></div> 
</div>
</div>
</div>
<!-- -------------------------- Exchange End ----------------------------------------->
<!---------------------------- Bounce Popup ------------------------->
<div class="modal fade" id="bounce_id" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
       <div class="modal-header">
      <h4 class="modal-title">PDC Bounce</h4>
      <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
       <div class="row">
        <div class="col">
        <div class="card card-box salesSearchBox">
        <input type="hidden" name="popId" id="popId" value=""/>     
        <!-- <div class="sub-head">Building Type Details</div> -->
        <div class="dataSearchBox ">
          <div class="row">
            <div class="col-sm-12">
              <div class="form-group">
                <input type="hidden" name="form_pdc_id" id="form_pdc_id" value="">
                <label for="tenant_contract_deposit_amt">Bounce Reason</label>
                <div class="p-relative">
                  <i class="fa fa-money icn-add" aria-hidden="true"></i>
                  <select name="pdc_bounce_reason" class="form-control" required id="pdc_bounce_reason">
                    <option value="">Select</option>
                    <option value="1" >Insufficient Funds</option>
                    <option value="2" >Signature Missing</option>
                    <option value="3" >Signature Mismatch</option> 
                    <option value="4" >Word in amount and figure differ</option>
                    <option value="5" >Stop Payment</option>
                    <option value="6" >Refer to Drawer</option>
                    <option value="7" >Correction</option>  
                    <option value="8" >Stale Cheque (Beyond six months)</option>
                    <option value="9" >Misc</option>
                  </select>                
                </div>
              </div>
            </div>

            <div class="w-100"></div>
            <div class="col" id="loadingCls" style="display: none;color:red">Loading...</div>
            <div class="col" id="onsubmitCls">
              <div class="w-100"></div>
              <button type="button" name="submit" value="exchange" class="btn btn-primary" id="bounce">Save</button>
              <button type="button" data-dismiss="modal"  value="skip"  name="skip" id="skip"  class="btn btn-warning">Skip</button>
            </div>

          </div>

        </div>
        <div class="col-sm-12 text-right">
          <!-- <button type="submit" value="skip"  name="skip" class="btn btn-warning">SKIP</button> -->
        </div>
        <div class="clearfix"></div>
      </form>

    </div>
  </div>
</div> 

</div>
</div>
<div class="modal-footer"></div>
</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
</div>
<!------------------------------ Bounce Popup End ------------------------------>
<div class="modal" id="myModal">

</div>
@endsection
@section('scripts')  
<script>
  $(document).ready(function() {

    $(document).on("click",'#exchange', function(){

      var pdc_id              = $('#form_pdc_id').val(); 
      //alert(pdc_id);
      var tenant_contract     = $('#contract_id_'+pdc_id).val();
      var pdc_check_no        = parseInt($('#pdc_check_no').val());
      var pdc_check_date      = $('#pdc_check_date').val(); 
      var pdc_amt             = $('#pdc_amt').val()?parseInt($('#pdc_amt').val()):'';
      var pdc_stage           = parseInt($('#pdc_stage').val());
      var bank_id             = parseInt($('#bank_id_popup').val());
      var pdc_recieve_date    = $('#pdc_recieve_date').val();
      var pdc_reference       = pdc_id;
      if($("#pdc_check_no").val()=='' || $("#pdc_check_date").val()=='' || $("#pdc_amt").val()=='' || $("#pdc_stage").val()=='' || $("#pdc_recieve_date").val()==''){

        alert("Please fill following field- Cheque No, Cheque Date, Stage , Bank Name, Received Date");
        return false;
      }
      else if(tenant_contract ==''){

        alert("Something Wrong in Server");
        return false;
      }
      else{

        $.ajax({
                    method: 'POST', // Type of response and matches what we said in the route
                    url: "{{route('tenantAjaxPdcExchange')}}", // This is the url we gave in the route
                    data: {'tenant_contract' : tenant_contract, 'pdc_check_no':pdc_check_no,'pdc_check_date' : pdc_check_date, 'pdc_amt':pdc_amt, 'pdc_stage':pdc_stage,'bank_id':bank_id,'pdc_recieve_date':pdc_recieve_date,'pdc_reference':pdc_reference,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
                    success: function(data){ // What to do if we succeed
                      // console.log(data);
                      if(data.success){
                        $("#tr_"+pdc_id).remove();

                        $('#exchange_id').modal('toggle');
                        alert('Cheque Status Changed to Exchange successfully !');
                      }
                      else{

                        $('#exchange_id').modal('toggle');
                        alert('Failed to Exchange the Cheque !')
                      }
                    },
                  });
      }

    });
    $(document).on("change",'.check_dt,.check_no, #bank_id', function(){

      var idElement = $(this).attr('id');
      if(idElement=='fromdate' || idElement=='todate'){
       $(".check_no").val('');
       $("#bank_id").prop('selectedIndex',0);
     }
     if(idElement=='fromcheque_no' || idElement=='tocheque_no'){
       $(".check_dt").val('');
       $("#bank_id").prop('selectedIndex',0);
     }
     if(idElement=='bank_id' ){
       $(".check_no").val('');
       $(".check_dt").val('');
     }

   });
    $(document).on("click",'#bounce', function(){

      var pdc_id              = $('#form_pdc_id').val(); 
      var pdc_bounce_reason   = $('#pdc_bounce_reason').val();
      $("#bounce").attr("disabled", true);
	  $('#pdc_bounce_reason').val('');
      if(pdc_check_no){
        $.ajax({
                    method: 'POST', // Type of response and matches what we said in the route
                    url: "{{route('tenantAjaxPdcBounceSearch')}}", // This is the url we gave in the route
                    data: {'id' : pdc_id, 'pdc_bounce_reason':pdc_bounce_reason,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
                    beforeSend: function(){
                    // Show image container 
                    $('#onsubmitCls').hide();               
                    $('#loadingCls').show();
                    
                    },
                    success: function(data){ // What to do if we succeed
                      // console.log(data);
                      if(data.success){
                        $("#tr_"+pdc_id).remove();

                        $('#bounce_id').modal('toggle');
                        $("#bounce").attr("disabled", false);
                        $('#onsubmitCls').show();               
                        $('#loadingCls').hide();
                        alert('Cheque Status Changed to Bounce successfully !');
                      }
                      else{

                        $('#bounce_id').modal('toggle');
                        $("#bounce").attr("disabled", false);
                        $('#onsubmitCls').show();               
                        $('#loadingCls').hide();
                        alert('Failed to Bounce the Cheque !')
                      }
                    },
                  });
      }

    });


    $(document).on("change",'.pdc_cancel_reason', function(event){

      var pdc_cancel_reason = $(this).val();
      var pdc_id = $(this).attr('id');
      //alert(pdc_cancel_reason);

          if(pdc_cancel_reason == 2){ // Exchange

            $('#pdc_bounce_reason_'+pdc_id).prop('selectedIndex',0);
            $('#pdc_bounce_reason_'+pdc_id).prop("disabled", true);
            if($('#pdc_check_no_'+pdc_id).val()==''){
              alert("Please enter the check No");
              return false;
            }
            else{

              var pdc_check_no = $('#pdc_check_no_'+pdc_id).val();
              /*reshma */
              /*alert(pdc_id);
              $.ajax({
            method: 'POST', 
   
            url: "{{route('getPdcExchangeDetails')}}",
           
            data: {'pdc_id' : pdc_id,"_token": "{{ csrf_token() }}"}, 
            success: function(response){ 
              $("#myModal").html(response); 
              $('#myModal').modal('show');
            },
          });
              return true;*/
              /*reshma */

              $('#pdc_check_no_'+pdc_id).prop("readOnly", true);
              $('#exchange').prop("readOnly", false);
              $('#popId').val( pdc_check_no );
              event.preventDefault();  
                 // $("#tr_"+idName).append('input type="hidden" name="pdc_reference" id="pdc_reference" value="'+pdc_check_no+'"');
                 $("#form_pdc_id").val(pdc_id);
                 $('#exchange_id').modal('show').find("input,textarea,select")
                 .val('').end();

                 return false;

               }

             }
          else if(pdc_cancel_reason == 1){ // Bounce
            $('#pdc_check_no_'+pdc_id).prop("readOnly", false);
            $('#pdc_bounce_reason_'+pdc_id). prop("disabled", false);
            $('#bounce_id').modal('show').find("#form_pdc_id").val(pdc_id).end();

          } 
          $('#pdc_bounce_reason_'+pdc_id).prop('selectedIndex',0);
          return false;


        });

    // EXchange and bounce popup back to select
    $('.modal').on('hidden.bs.modal', function () {
      var pdc_check_no = $('#form_pdc_id').val();
      $('#'+pdc_check_no).prop('selectedIndex',0);

    });


    $('.pdcPost').on('click', function(e) {  

      var allVals = []; 
      $(".sub_chk:checked").each(function() {  
        allVals.push($(this).attr('value'));
            //alert(allVals);
          });
      if(allVals.length <=0)  
      {  
        alert("Please Select Atleast One PDC..!"); return false;  

      }

    });
    /*********************************************************************/
    $('.pdc_search').click(function(){
     var fromdate = $('#fromdate').val();
     var todate = $('#todate').val();
     if(fromdate != '' && todate != ''){
      $.validator.addMethod("greaterThan", 
        function(value, element, params) {

          if (!/Invalid|NaN/.test(new Date(value))) {
            return new Date(value) >= new Date($(params).val());
          }

          return isNaN(value) && isNaN($(params).val()) 
          || (Number(value) > Number($(params).val())); 
        },'Must be greater than End Date.');
      $("#search_form").validate({
        rules: {
          todate: { greaterThan: "#fromdate" ,

        }


      }
    });
    }
  });
  });
</script>  
@endsection
