         
<div class="modal-dialog modal-lg assign">
    <div class="modal-content">
  
    <!-- Modal Header -->
    <div class="modal-header">
      <h4 class="modal-title">Edit Item </h4>
      <button type="button" class="close" data-dismiss="modal">&times;</button>
    </div>
    
    <!-- Modal body -->
        <div class="modal-body">
            <div class="dataSearchBox panel-heading-lightblue">
                <form autocomplete="off" action="{{ !isset($reportInventory)? route('complaintStage.store'): route('complaintStage.update',$reportInventory->id)}}" method="POST" id="item_form"" class="form-horizontal" enctype="multipart/form-data" data-toggle="validator">
                {{csrf_field()}} @if(isset($reportInventory)){{method_field('PUT')}}@endif   
                <div class="row bb-1 mb-3">
                    <div class="col-sm-6">
                        <label for="simpleFormEmail">Item Name</label>
                            <select class="form-control" required name="item" required>
                              <option value="">Select Item</option>
                                @foreach($inventory_items as $item)
                                <option {{ isset($reportInventory)? ((old('item',$reportInventory->inventory_id) == $item->id)? 'selected' : '') : 'selected'}} value="{{$item->id}}">{{$item->inventories_name}} </option>
                                @endforeach
                            </select>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="simpleFormEmail">Quantity</label>
                            <input required type="text" class="form-control" name="quantity" id="quantity" placeholder="Enter Quantity" value="{{ isset($reportInventory)?  old('quantity',$reportInventory->quantity): old('quantity')}}" data-rule-pattern="\d{1,9}(\.\d{0,3})?" data-msg-pattern="Allowed only Numeric and Decimal Values">

                        </div>
                    </div>
                    <div class="clr"></div>
                    <div class="col-sm-6">
                      <div class="form-group">
                        <label for="quantity"> Material Charge</label>
                        <div class="p-relative">
                         <i class="fa fa-align-left icn-add" aria-hidden="true"></i>
                         <input required type="text" class="form-control" name="material_charge" id="material_charge" placeholder="Enter Material Charge" value="{{ isset($reportInventory)?  old('material_charge',$reportInventory->material_charge): old('material_charge')}}" data-rule-pattern="\d{1,9}(\.\d{0,3})?" data-msg-pattern="Allowed only Numeric and Decimal Values">
                       </div>
                     </div>
                   </div>
                   <div class="col-sm-6">
                      <div class="form-group">
                        <label for="quantity"> Labour Charge</label>
                        <div class="p-relative">
                         <i class="fa fa-align-left icn-add" aria-hidden="true"></i>
                         <input required type="text" class="form-control" name="labour_charge" id="labour_charge" placeholder="Enter Labour Charge" value="{{ isset($reportInventory)?  old('labour_charge',$reportInventory->labour_charge): old('quantity')}}" data-rule-pattern="\d{1,9}(\.\d{0,3})?" data-msg-pattern="Allowed only Numeric and Decimal Values">
                       </div>
                     </div>
                   </div>
                   <div class="col-sm-6">
                      <div class="form-group">
                        <label for="tax_percentage_display">Tax %</label>
                        <div class="p-relative">
                         <i class="fa fa-percent icn-add" aria-hidden="true"></i>
                         <input type="text" class="form-control" id="tax_percentage_display" value="{{numberFormat($taxPercentage)}}" readonly>
                       </div>
                     </div>
                   </div>
                    <div class="col-sm-4">
                        <div class="dataSearchLabel w-100"></div>
                            <input type="hidden" name="url" id="url" value="{{ isset($reportInventory)? old('url',$nowUrl):old('url')}}">
                            <button type="Submit" class="btn btn-primary dataSearchLabel">Update</button>
                    </div>
                </div>
                </form>  

            </div>
        </div>
    </div>
</div>