
<div class="modal-dialog assign">
	<div class="modal-content">

		<!-- Modal Header -->
		<div class="modal-header">
			<h4 class="modal-title"> Municipality Details </h4>
			<button type="button" class="close" data-dismiss="modal">&times;</button>
		</div>

		<!-- Modal body -->
		<div class="modal-body">

			<div class="row">
				<div class="col">
					<div class="card card-box salesSearchBox">
						<form action="{{route('tenantContractAddMunicipalityStore')}}" autocomplete="off" method="POST" id="renewal_note_modal" class="form-horizontal" enctype="multipart/form-data" data-toggle="validator">
							{{csrf_field()}}
							<input  type="hidden" class="form-control" name="tenant_contract_id" value="{{$tenant_contract_id}}">
							<div class="dataSearchBox ">

								<div class="row">

									<div class="col-sm-12">
										<div class="form-group">
											<label for="tenant_contract_muncipality_agr_no">Municipality Agr. No<small class="textRed">*</small></label>
											<div class="p-relative">
												<i class="fa fa-list-ol icn-add" aria-hidden="true"></i>
												<input type="text" class="form-control" id="tenant_contract_muncipality_agr_no" placeholder="Enter Municipality Agr. No" name="tenant_contract_muncipality_agr_no"  value="{{ isset($tenantContractInfo)?  old('tenant_contract_muncipality_agr_no',$tenantContractInfo->tenant_contract_muncipality_agr_no): old('tenant_contract_muncipality_agr_no')}}" required>
											</div>
										</div>
									</div> 

									
									<div class="col-sm-12">
										<div class="form-group">
											<label for="tenant_contract_registered_in">Contract Registered In<small class="textRed">*</small></label>
											<div class="p-relative">
												<i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
												<select class="form-control" name="tenant_contract_registered_in" required>
													<option {{ isset($tenantContractInfo)? ((old('tenant_contract_registered_in',$tenantContractInfo->tenant_contract_registered_in) == '1')? 'selected' : '') : ''}} value="1" >Muscat</option>
													<option {{ isset($tenantContractInfo)? ((old('tenant_contract_registered_in',$tenantContractInfo->tenant_contract_registered_in) == '2')? 'selected' : '') : ''}} value="2" >Not in Muscat</option>
												</select>
											</div>
										</div>
									</div>

									<div class="col-sm-12">
										<div class="form-group">
											<label for="tenant_contract_registered_date">Contract Registered Date<small class="textRed">*</small></label>
											<div class="p-relative">
												<i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
												<input type="date" class="form-control" id="tenant_contract_registered_date" placeholder="Enter Contract Registered Date" name="tenant_contract_registered_date"  value="{{old('tenant_contract_registered_date',isset($tenantContractInfo->tenant_contract_registered_date)? $tenantContractInfo->tenant_contract_registered_date->format('Y-m-d') : '')}}" required>
											</div>
										</div>
									</div>
									<div class="col-sm-12">
										<div class="form-group">
											<label for="tenant_contract_note">Guarantee cheque </label>
											<div class="p-relative">
												<i class="fa fa-file-text-o icn-add" aria-hidden="true"></i>
												<input type="text" class="form-control" id="tenant_contract_guarantee_cheque_details" placeholder="Enter Guarantee Cheque" name="tenant_contract_guarantee_cheque_details"  value="{{ isset($tenantContractInfo)?  old('tenant_contract_guarantee_cheque_details',$tenantContractInfo->tenant_contract_guarantee_cheque_details): old('tenant_contract_guarantee_cheque_details','')}}" data-rule-pattern="^[a-zA-Z0-9]+$" data-msg-pattern="Allowed only Alpha Numeric Values" maxlength="100">

											</div>
										</div>
									</div>
									<div class="col-sm-1"> 
									    <div class="form-group">
									        <label for="pdc_check">PDC </label>
									    </div>
									</div>
									<div class="col-sm-11">

									    <label class="radio-inline"><input type="radio" value="1" name="pdc_check" id="pdc1" {{ isset($tenantContractInfo)?(($tenantContractInfo->pdc_check==1)?'checked':''):''}}> Full</label>
									    <label class="radio-inline"><input type="radio" value="2" name="pdc_check" id="pdc2" {{ isset($tenantContractInfo)?(($tenantContractInfo->pdc_check==2)?'checked':''):''}}> Partial</label>
									    <textarea style="width: 95%; display: none;" rows="4" name="partial_comment" id="partial_comment"></textarea>

									</div>
									<div class="col-sm-12">
								    <div class="form-group">
								        <label for="tenant_contract_note">Deposit</label>

								        <input type="checkbox" value="1" name="deposit_check"  {{ isset($tenantContractInfo)?(($tenantContractInfo->deposit_check==1)?'checked':''):''}}>

								    </div>
								</div>
									<div class="col-sm-12">
										<div class="form-group">
											<label for="tenant_contract_note">Remark</label>
											<div class="p-relative">
												<i class="fa fa-file-text-o icn-add" aria-hidden="true"></i>
												<textarea name="tenant_contract_note" id="tenant_contract_note" class="form-control" placeholder="Enter Note"  >{{ isset($tenantContractInfo)?  old('tenant_contract_note',$tenantContractInfo->tenant_contract_note): old('tenant_contract_note','')}}</textarea>

											</div>
										</div>
									</div>
									<div class="col-sm-12">
									
										<div class="form-group">
											<label>Upload Municipality Agreement</label>
											<?php 

											if(isset($tenantContractInfo)){
												$agreements = array();
											      if(isset($tenantContractInfo->tenant_muncipality_agreement)){
											        $agreements = explode ("/", $tenantContractInfo->tenant_muncipality_agreement); ?>
											

											<span><a href="../storage/app/{{$tenantContractInfo->tenant_muncipality_agreement}}" target="_blank">{{$agreements[2]}}</a></span><br>

										<?php } } ?>


								            <div class="control-group input-group increment mt-3" id="1">
								              <input type="file" name="tenant_document_file_name[1]" id="tenant_document_file_name[1]" class="form-control upload" style="width: 70%;">
								              <div class="input-group-btn" style="display: none;"> 
								                <button class="btn btn-danger" type="button"><i class="fa fa-trash-o "></i></button>
								            </div>
								            <label id="tenant_document_file_name[1]-error" class="error" for="tenant_document_file_name[1]"></label>

								            

								        </div>


								    </div>
									</div>


									<div class="w-100"></div>
									<div class="col">
										<div class="w-100"></div>
										<button type="submit" name="sub" value="submit" class="btn btn-primary">Save</button>
										<!--     <button type="submit" value="skip"  name="skip" id="skip" class="btn btn-warning">Skip</button> -->
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

<script type="text/javascript">

	$(document).on("change",".upload",function(){

        fileUpload($(this));
    });

	 function fileUpload(file){


        $(".form-group .input-group").first().find('.input-group-btn').hide();
        $('.upload').each(function() {
            $(this).rules("add", 
            {
                extension:"Pdf|Doc|Docx|Jpeg|Jpg",
                filesize: 1000000,
                messages: {
                 extension: "Support Only Following File type : Pdf|Doc|Docx|Jpeg|Jpg",
                 filesize: "File Must Be Less Than {{1000000/1000000}}MB",
             }
         });
        });

        $.validator.addMethod('filesize', function(value, element, param) {
          return this.optional(element) || (element.files[0].size <= param) 
		});

        $(".upload").valid();
        var ext = file.val().split('.').pop().toLowerCase();

        if(ext !='' && $.inArray(ext, ['pdf','docx', 'doc', 'jpeg', 'jpg']) == -1) {
            alert('Invalid Extension!');
            return false;
        }
        else if(fileUpload =='' && currentRowId == lastRowId){
         alert('Please Upload The File');
         return false;    
     }

     if($(".upload").valid() == 1 && currentRowId == lastRowId  ){
    // Next Row Id
    var cont = lastRowId + 1;

    var html = '<div class="control-group input-group increment" id="'+cont +'"style="margin-bottom:10px"><input type="file" name="tenant_document_file_name['+cont +']" class="form-control upload" style="width: 70%;"><div class="input-group-btn" style="display:none"><button class="btn btn-danger" type="button"><i class="fa fa-trash-o "></i></button></div><label id="tenant_document_file_name['+cont +']-error" class="error" for="tenant_document_file_name['+cont +']"></label></div>';
        //html.find('.btn-danger').hide();
        $(".increment").first().before(html);
        
        //alert($(".form-group .input-group").length)
        $(".form-group .input-group:nth-child(2)").find('.input-group-btn').show();
    }
    

}
</script>

