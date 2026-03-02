<div class="modal-dialog assign  modal-lg">
    <div class="modal-content">
  
    <!-- Modal Header -->
    <div class="modal-header">
      <h4 class="modal-title">   @if($type != 'normal')  Send for Approval @else Normal Renewal @endif </h4>
      <button type="button" class="close" data-dismiss="modal">&times;</button>
    </div>
    
    <!-- Modal body -->
    <div class="modal-body">
       
   <div class="row">
    <div class="col">
        <div class="card card-box salesSearchBox">
        <form action="{{route('contractUnderRenewalTypeStore',$tenant_contract->id)}}" autocomplete="off" method="POST" id="renewal_note_modal" class="form-horizontal" data-toggle="validator">
        {{csrf_field()}}       
        <div class="dataSearchBox ">            
                <div class="row">
                @if($type != 'normal')  
                <div class="col-sm-12">
                    <div class="form-group">
                        <label for="simpleFormCode">Type of the Contract</label>
                        <div class="p-relative">
                          <i class="fa fa-file-text-o icn-add" aria-hidden="true"></i>
                           <select class="form-control" name="contract_renewal_type_id" id="discussion_category_id" required>
                            <option value="">Select Category</option>
                            @foreach($renewal_types as $renewal_type) 
                            <option value="{{$renewal_type->id}}">{{$renewal_type->type}}</option>
                            @endforeach 
                          </select>
                        </div>
                    </div>
                  </div>
               @else
               <input type="hidden" name="contract_renewal_type_id" value="1">
               @endif   

                  <div class="col-sm-12">
                    <div class="form-group">
                        <label for="simpleFormCode">Comment <small class="textRed">*</small></label>
                        <div class="p-relative">
                          <i class="fa fa-file-text-o icn-add" aria-hidden="true"></i>
                          <textarea required class="form-control"   placeholder="Enter Comment" name="notes"   data-rule-maxlength="200" data-msg-maxlength="Only allows 200 Characters"></textarea>
                        </div>
                    </div>
                  </div>
                  
               <div class="w-100"></div>
                <div class="col">
                   <div class="w-100"></div>
                      <button type="submit" name="submit" value="submit" class="btn btn-primary">Save</button>                
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
    
    <!-- Modal footer -->
    <div class="modal-footer">
      
    </div>

    </div>
</div>

