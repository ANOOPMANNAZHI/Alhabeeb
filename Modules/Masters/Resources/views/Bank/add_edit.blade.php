@extends('layouts.plms-app')
@section('css')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">
<link href="{{asset('public/css/formlayout.css')}}" rel="stylesheet" type="text/css" />
@endsection 
@section('content')
	<!-- start widget -->
  <div class="page-bar">
      <div class="page-title-breadcrumb">
          <div class=" pull-left">
              <div class="page-title">Bank</div>
          </div>
           {{ (isset($bank))?   Breadcrumbs::render('bank.edit',$bank,$backUrlBreadCrumb,$backIdBreadCrumb) :  Breadcrumbs::render('bank.create') }}
      </div>
  </div>
<div class="row">
<div class="col">
<div class="card card-box salesSearchBox">
<form action="{{ !isset($bank)? route('bank.store'): route('bank.update',$bank->id)}}" method="POST" id="form_sample_2" class="form-horizontal" enctype="multipart/form-data" data-toggle="validator">
{{csrf_field()}} @if(isset($bank)){{method_field('PUT')}}@endif

<!-- <div class="sub-head">Building Type Details</div> -->
<div class="dataSearchBox ">
    
        <div class="row">
          <div class="col-sm-6">
            <div class="form-group">
                <label for="simpleFormCode">Code<small class="textRed">*</small></label>
                <div class="p-relative">
                   <i class="fa fa-terminal icn-add" aria-hidden="true"></i>
                <input  type="text" class="form-control" id="simpleFormCode"  placeholder="Enter Bank Code" name="bank_code" required patten="[ A-Za-z_@./#&+-]+" value="{{ isset($bank)?  old('bank_code',$bank->bank_code): old('bank_code')}}" data-rule-maxlength="25" data-msg-maxlength="Only allowes 25 Characters">
              </div>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="form-group">
                <label for="simpleFormName">Name<small class="textRed">*</small></label>
                <div class="p-relative">
                   <i class="fa fa-university icn-add" aria-hidden="true"></i>
                <input  type="text" class="form-control" id="simpleFormName"  placeholder="Enter Bank Name" name="bank_name" required patten="[ A-Za-z_@./#&+-]+" value="{{ isset($bank)?  old('bank_name',$bank->bank_name): old('bank_name')}}" data-rule-maxlength="25" data-msg-maxlength="Only allowes 25 Characters">
              </div>
            </div>
          </div>
       <div class="w-100"></div>
         <div class="col-sm-6">
            <div class="form-group">
                <label for="simpleFormBranch">Branch<small class="textRed">*</small></label>
                <div class="p-relative">
                   <i class="fa fa-font-awesome icn-add" aria-hidden="true"></i>
                <input  type="text" class="form-control" id="simpleFormBranch"  placeholder="Enter Bank Branch" name="bank_branch" required patten="[ A-Za-z_@./#&+-]+" value="{{ isset($bank)?  old('bank_branch',$bank->bank_branch): old('bank_branch')}}" data-rule-maxlength="25" data-msg-maxlength="Only allowes 25 Characters">
              </div>
            </div>
          </div>
		  <div class="col-sm-6">
            <div class="form-group">
                <label for="simpleFormBranch">Bank Cheque-book Id<small class="textRed">*</small></label>
                <div class="p-relative">
                   <i class="fa fa-font-awesome icn-add" aria-hidden="true"></i>
                <input  type="text" class="form-control" id="bank_chequebook_id"  placeholder="Enter Bank Cheque-Book id" name="bank_chequebook_id" required  value="{{ isset($bank)?  old('bank_chequebook_id',$bank->bank_chequebook_id): old('bank_chequebook_id')}}" data-rule-maxlength="25" data-msg-maxlength="Only allowes 25 Characters">
              </div>
            </div>
          </div>
         <div class="w-100"></div>
		 <div class="col-sm-6">
            <div class="form-group">
                <label for="simpleFormBranch">Division <small class="textRed">*</small></label>
                <div class="p-relative">
                <i class="fa fa-font-awesome icn-add" aria-hidden="true"></i>
                <select class="form-control" id="dim1Value"  name="dim1Value" required>
                  <option {{(old('bank_chequebook_id', isset($bank)? $bank->dim1Value :'00') == '02') ? 'selected' : '' }} value="02">PLM</option>
                 
                   <option  {{(old('bank_chequebook_id', isset($bank)?  $bank->dim1Value :'00') == '01') ? 'selected' : '' }} value="01">HO</option>
                                 
                </select> 
              </div>
            </div>
          </div>
		  <div class="col-sm-6">
            <div class="form-group">
                <label for="simpleFormBranch">Accounts <small class="textRed">*</small></label>
                <div class="p-relative">
                <i class="fa fa-font-awesome icn-add" aria-hidden="true"></i>
                <select class="form-control" id="dim1Value"  name="dim1Value" required>
                  <option {{(old('accounts_bank', isset($bank)? $bank->accounts_bank :'') == '0') ? 'selected' : '' }} value="0">No</option>
                 
                   <option  {{(old('accounts_bank', isset($bank)?  $bank->accounts_bank :'') == '1') ? 'selected' : '' }} value="1">Yes</option>
                                 
                </select> 
              </div>
            </div>
          </div>
		    <div class="w-100"></div>
          <div class="col-sm-12">
            <div class="form-group">
                <label for="simpleFormRemark">Remark</label>
                <div class="p-relative">
                   <i class="fa fa-file-text-o icn-add" aria-hidden="true"></i>
                <textarea name="bank_remark" id="simpleFormRemark" class="form-control" placeholder="Enter Bank Remark">{{ isset($bank)?  old('bank_remark',$bank->bank_remark): old('bank_remark')}}</textarea>
              </div>
                
            </div>
          </div>
       
        
        <div class="col">
          <div class="w-100"></div>
              <button type="submit" class="btn btn-primary">Save</button>
        </div>
           
      </div>
    
</div>
<div class="clearfix"></div>
</form>
    
</div>
</div>
</div>
@endsection
@section('scripts')
<script>
  $(document).ready(function() {
    $("#form_sample_2").validate()
  });
</script>
@endsection
