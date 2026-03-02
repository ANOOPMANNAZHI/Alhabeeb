<div class="modal-dialog assign">
    <div class="modal-content">
  
    <!-- Modal Header -->
    <div class="modal-header">
      <h4 class="modal-title">Deposit Refund</h4>
      <button type="button" class="close" data-dismiss="modal">&times;</button>
    </div>
    
    <!-- Modal body -->
    <div class="modal-body">
    
   <div class="row">
    <div class="col">
        <div class="card card-box salesSearchBox">
        <form action="{{route('cancelDepositRefundStore')}}" autocomplete="off" method="POST" id="renewal_note_modal" class="form-horizontal" enctype="multipart/form-data" data-toggle="validator">
        {{csrf_field()}}
        <input  type="hidden" class="form-control" name="id" value="{{$id}}">
        <div class="dataSearchBox ">
            
                <div class="row">
                  <div class="col-sm-12">
                    <div class="form-group">
                        <label for="simpleFormCode">Reason </label>
                        <div class="p-relative">
                          <i class="fa fa-file-text-o icn-add" aria-hidden="true"></i>
                          <textarea class="form-control"  id="maintenance_payment_reason_cancel"  placeholder="Enter Note" name="maintenance_payment_reason_cancel"   data-rule-maxlength="200" data-msg-maxlength="Only allows 200 Characters" required></textarea>
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

