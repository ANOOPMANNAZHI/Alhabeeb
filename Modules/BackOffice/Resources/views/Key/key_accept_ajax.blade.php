<form method="post" autocomplete="off" id="key-accept-form" action="{{route( 'keyManagement.store')}}" data-toggle="validator">
    {{csrf_field()}}


<div class="sub-head">Unit Details</div>

  <div class="dataSearchBox">    
      <div class="card-body row">
     
          <div class="col-lg-6 p-t-20"> 
              <div class = "txt-full-width">
                  <h5 class="details"><b>Building Name :  </b><span>{{$unit->building->building_name}}</span></h5>
                  <input type="hidden" name="building_id" value="{{$unit->building_id}}">
              </div>
          </div>
          <div class="col-lg-6 p-t-20"> 
              <div class = "txt-full-width">
                  <h5 class="details"><b>Unit :  </b><span>{{$unit->unit_code}}</span></h5>
                  <input type="hidden" name="unit_id" value="{{$unit->id}}">
                  <input type="hidden" name="user_id" value="{{\Auth::user()->id}}">
              </div>
          </div>
          <div class="col-lg-6 p-t-20"> 
              <div class = "txt-full-width">
                  <h5 class="details"><b>Tenant Name :  </b><span>{{isset($contract)?$contract->tenant->tenant_name:""}}</span></h5>
              </div>
          </div>
          <div class="col-lg-6 p-t-20"> 
              <div class = "txt-full-width">
                  <h5 class="details"><b>Occupant Name :  </b><span>{{isset($contract)?$contract->tenant->tenant_name:""}}</span></h5>
              </div>
          </div>
      </div>
  </div>
  <div class="col"><button type="submit" class="btn btn-primary">Save</button> </div>
</form>