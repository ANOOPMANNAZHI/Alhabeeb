        <!-- The Modal -->
<div class="modal-dialog assign">
    <div class="modal-content">
  
    <!-- Modal Header -->
    <div class="modal-header">
      <h4 class="modal-title">Key Handover</h4>
      <button type="button" class="close" data-dismiss="modal">&times;</button>
    </div>
    
    <!-- Modal body -->
    <div class="modal-body">
    
        <form method="post" autocomplete="off" id="key-accept-form" action="{{route( 'keyAcceptAginstLandlordTenantStore')}}" data-toggle="validator">
    {{csrf_field()}}
        <input type="hidden" name="building_id" value="{{$key_details->building_id}}">
        <input type="hidden" name="unit_id" value="{{$key_details->unit_id}}">
        <input type="hidden" name="type" value="{{$type}}">
        
         @if($type == "Tenant")
         <input type="hidden" name="tenant_id" id="tenant_id" value="{{isset($key_details->unit->tenantContract)? $key_details->unit->tenantContract->tenant_id:''}}">
          <div class="dataSearchBox">    
              <div class="card-body row">
                @if($key_details->unit->tenantContract)
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                              <h5 class="details"><b>Tenant :  </b><span>{{$key_details->unit->tenantContract->tenant->tenant_name}}</span></h5>
                              
                      </div>
                    </div>
                @else
                   <div class="col-sm-6">
                    <div class="form-group">
                        <label for="simpleFormCode">Tenant </label>
                        <div class="p-relative">
                          <i class="fa fa-file-text-o icn-add" aria-hidden="true"></i>
                          <input type="text" placeholder="Enter Tenant Name" required name="tenant_name" class="form-control tenant_name tenant" id="tenant_name" value="">
                         
                        </div>
                    </div>
                  </div>
                @endif
                 
              </div>
          </div>
          @else
          <input type="hidden" name="vendor_id" id="vendor_id" value="{{isset($key_details->building->vendor_id)? $key_details->building->vendor_id:''}}">
          <div class="dataSearchBox">    
              <div class="card-body row">
                @if($key_details->building->vendor_id)
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                              <h5 class="details"><b>Landlord :  </b><span>{{$key_details->building->vendor->vendor_name}}</span></h5>
                              
                      </div>
                    </div>
                @else
                   <div class="col-sm-6">
                    <div class="form-group">
                        <label for="simpleFormCode">Landlord </label>
                        <div class="p-relative">
                          <i class="fa fa-file-text-o icn-add" aria-hidden="true"></i>
                          <input type="text" placeholder="Enter Landlord Name" required name="landlord_name" class="form-control landlord_name landlord" id="vendor_name" value="">
                         
                        </div>
                    </div>
                  </div>
                @endif
                 
              </div>
          </div>
          @endif
          <div class="col"><button type="submit" class="btn btn-primary">Save</button> </div>
    </form>

    </div>
    
    <!-- Modal footer -->
    <div class="modal-footer">
      <!-- <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button> -->
    </div>

    </div>
</div>
<script type="text/javascript">
$(document).ready(function(){
  $('#tenant_name').autocomplete({
      source : '{!!URL::route('tenantActiveAutocomplete')!!}',
      minlenght:2,
      appendTo: "#myModal",
      autoFocus:true,
      select:function(e,ui){
            if (ui.item == null || ui.item == undefined) {
                
              $('#user_id').val("");

            }else {
                var id = ui.item.ids;
                $('#user_id').val(id);
            }
        }
    });
/***********************************************************************/
$('#vendor_name').autocomplete({
      source : '{!!URL::route('landlordAutocompleteCode')!!}',
      minlenght:2,
      autoFocus:true,
      select:function(e,ui){
        if (ui.item == null || ui.item == undefined) {
                $("#user_id").val('');
            }else {
                $('#user_id').val(ui.item.ids);              
           }
        
        }
    });
/************************************************************/
});
</script>