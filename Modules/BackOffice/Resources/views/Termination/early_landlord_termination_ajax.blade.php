<div class="card card-box salesSearchBox " id="agdiv">
@if(!empty($landlordContract)) 
<div class="dataSearchBox">
<div class="card-body row">

  <div class="col-lg-6 p-t-20"> 
      <div class = "txt-full-width">
         <h5 class="details"><b>Agreement No :  </b><span>{{$landlordContract->landlord_contract_no}}</span></h5>
          <input type="hidden"  name="contract_id" id="contract_id" value="{{$landlordContract->id}}">
      </div>
    </div>
   <div class="col-lg-6 p-t-20"> 
      <div class = "txt-full-width">
         <h5 class="details"><b>Agreement Date :  </b><span>{{$landlordContract->created_at->format('d/m/Y')}}</span></h5>
          <input type="hidden"  name="contract_id" id="contract_id" value="{{$landlordContract->id}}">
      </div>
    </div>
    <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
             <h5 class="details"><b>Building :  </b><span>{{$landlordContract->buildingInfo->building_name}}</span></h5>
          </div>
        </div>
    <div class="col-lg-6 p-t-20"> 
      <div class = "txt-full-width">
         <h5 class="details"><b>Vendor :  </b><span>{{$landlordContract->vendorInfo->vendor_name}}</span></h5>
      </div>
    </div>
     <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
             <h5 class="details"><b>Management Type :  </b><span>{{$landlordContract->managementTypeInfo->management_types_name}}</span></h5>
          </div>
        </div>
     <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
             <h5 class="details"><b>Duration Type :  </b><span>@if($landlordContract->landlord_contract_duration == 1)
                Open
              @else
                Perpetual
              @endif</span></h5>
          </div>
        </div>
     <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
             <h5 class="details"><b>Payment Type :  </b><span>{{$landlordContract->paymentMethodInfo->payment_method_code}}</span></h5>
          </div>
        </div>
     <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
             <h5 class="details"><b>Management Fee :  </b><span>{{$landlordContract->landlord_contract_management_fee}}</span></h5>
             <h5 class="details"><b>Cleaning Charges :  </b><span>{{ ($landlordContract->cleaning_charge_method ?? null) == 1 ? ($landlordContract->landlord_contract_cleaning_charge ?? '').' %' : numberFormat($landlordContract->landlord_contract_cleaning_charge ?? 0).' OMR' }}</span></h5>
          </div>
        </div>
  @if($landlordContract->landlord_marketing_executive)
        <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
             <h5 class="details"><b>Marketing Executive :  </b><span>{{$landlordContract->marketExecutiveEmployeeInfo->employee_name}}</span></h5>
          </div>
        </div>
        @endif
  @if($landlordContract->landlord_contract_valid_from_date)
        <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
             <h5 class="details"><b>Valid From :  </b><span>{{$landlordContract->landlord_contract_valid_from_date->format('d/m/Y')}}</span></h5>
          </div>
        </div>
        @endif
        @if($landlordContract->end_date)
        <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
             <h5 class="details"><b>End Date :  </b><span>{{$landlordContract->end_date->format('d/m/Y')}}</span></h5>
          </div>
        </div>
        @endif
        
  
</div>
<div class="col">
  <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
             <div class="form-group">
              <label> <b>Contract To Date </b></label>
              @if($landlordContract->end_date)
              <input autocomplete="off" type="date" name="end_date" value="{{$landlordContract->end_date->format('d/m/Y')}}" class="contract_search_field created_at" id="end_date" >
              @else
              <input autocomplete="off" type="date" name="end_date" class="contract_search_field created_at" id="end_date" >
              @endif
            </div>
          </div>
        </div>
 
  <button type="submit" class="btn btn-primary">Save</button> 
</div>
</div>
@endif  
</div>