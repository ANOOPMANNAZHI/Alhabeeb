         
<div class="modal-dialog modal-lg assign">
  <div class="modal-content">

    <!-- Modal Header -->
    <div class="modal-header">
      <h4 class="modal-title">PDC Exchange</h4>
      <button type="button" class="close" data-dismiss="modal">&times;</button>
    </div>

    <!-- Modal body -->
    <div class="modal-body">
      <div class="dataSearchBox panel-heading-lightblue">
        <form method="post" autocomplete="off" id="update-amenity-form" action="" data-toggle="validator">

         <div class="row bb-1 mb-3">
           <div class="col-sm-12">
            <div class="form-group">             

              <label for="pdc_check_no">Cheque No<small class="textRed">*</small></label>
              <div class="p-relative">
               <i class="fa fa-money icn-add" aria-hidden="true"></i>
               <input type="texbox" required name="pdc_check_no" id="pdc_check_no" value="{{ old('pdc_check_no', isset($pdc)? $pdc->pdc_check_no : '' )}}" class="form-control  data-rule-pattern="\d{1,9}(\d{0,3})?" data-msg-pattern="Allowed only Numeric ">

             </div>
           </div>
         </div>
         <div class="col-sm-12">
          <div class="form-group">             

            <label for="pdc_check_date">Cheque Dt<small class="textRed">*</small></label>
            <div class="p-relative">
              <i class="fa fa-money icn-add" aria-hidden="true"></i>
              <input type="date" required class="form-control  name="pdc_check_date" id="pdc_check_date" value="{{ old('pdc_check_date', isset($pdc)? $pdc->pdc_check_date->format('Y-m-d') : '' )}}"> 

            </div>
          </div>
        </div>
        <div class="col-sm-12">
          <div class="form-group">             

            <label for="pdc_stage">Stage<small class="textRed">*</small></label>
            <div class="p-relative">
              <i class="fa fa-money icn-add" aria-hidden="true"></i>
              <input type="number" required class="form-control name="pdc_stage id="pdc_stage" value="{{ old('pdc_stage', isset($pdc)? $pdc->pdc_stage : '' )}}" min="1">     

            </div>
          </div>
        </div>
        <div class="col-sm-12">
          <div class="form-group">             

            <label for="pdc_amt">Amount<small class="textRed">*</small></label>
            <div class="p-relative">
              <i class="fa fa-money icn-add" aria-hidden="true"></i>
              <input type="number" required class="form-control name="pdc_amt" id="pdc_amt" value="{{ old('pdc_amt', isset($pdc)? $pdc->pdc_amt : '' )}}" pattern="^\d{1,4}(,\d{4})*(\.\d+)?$"  data-type="currency">

            </div>
          </div>
        </div>
        <div class="col-sm-12">
          <div class="form-group">             

            <label for="bank_id_popup">Bank Name<small class="textRed">*</small></label>
            <div class="p-relative">
              <i class="fa fa-money icn-add" aria-hidden="true"></i>
              <select name="bank_id_popup" id="bank_id_popup" class="form-control" required>
                <option value="">Select</option> 
                @foreach($banks as $name)
                <option {{(old('bank_id', isset($pdc)?  $pdc->bank_id : 0) == $name->id) ? 'selected' : '' }}  value="{{$name->id}}">{{$name->bank_name}}</option>
                @endforeach
              </select>    
            </div>
          </div>
        </div>
        <div class="col-sm-12">
          <div class="form-group">             

            <label for="pdc_recieve_date">Rec Dt<small class="textRed">*</small></label>
            <div class="p-relative">
              <i class="fa fa-money icn-add" aria-hidden="true"></i>
              <input type="date" required class="form-control  name="pdc_recieve_date id="pdc_recieve_date" value="{{ old('pdc_recieve_date', isset($pdc)? $pdc->pdc_recieve_date->format('Y-m-d') : '' )}}" >  

            </div>
          </div>
        </div>
        
        <input type="hidden" name="url" id="url" value="{{ isset($pdc)? old('url',$nowUrl):$nowUrl}}">
        <div class="col">
                <div class="w-100"></div>
                <button type="button" name="submit" value="exchange" class="btn btn-primary" id="exchange">Exchange</button>
                <button type="button" data-dismiss="modal"  value="skip"  name="skip" id="skip"  class="btn btn-warning">Skip</button>
              </div>
      </div>
    </form>

  </div>
</div>
</div>
</div>

















