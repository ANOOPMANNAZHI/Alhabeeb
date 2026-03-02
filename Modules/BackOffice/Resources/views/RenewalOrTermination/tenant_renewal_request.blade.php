@extends('layouts.plms-app')

@section('css')  
<!-- data tables -->
<link rel="stylesheet" href="{{ asset('public/css/datatables.min.css')}}">
<link href="{{asset('public/css/formlayout.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">
@endsection

@section('content')
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Tenant Renewal Request</div>
        </div>
        {{ (isset($tenantRenewal))?   Breadcrumbs::render('tenantRenewal.edit',$tenantRenewal) :  Breadcrumbs::render('tenantRenewal.create') }}
    </div>
</div>
<div class="row">
<div class="col">
<div class="card card-box salesSearchBox">
<form action="{{ !isset($tenantRenewal)? route('tenantRenewal.store'): route('tenantRenewal.update',$tenantRenewal->id)}}" method="POST" id="tenant_renewal_termination_form"" class="form-horizontal" enctype="multipart/form-data" data-toggle="validator">
        {{csrf_field()}} @if(isset($tenantRenewal)){{method_field('PUT')}}@endif
        
        <input  type="hidden" class="form-control" name="work_flow_process_code" value="{{ !isset($tenantRenewal)? '': $tenantRenewal->work_flow_process_code}}">
<div class="dataSearchBox ">

        <div class="row">
             <div class="col-sm-6">
            <div class="form-group">
                <label for="tenant_contract_id">Tenant Contract</label>
                <select class="form-control" name="tenant_contract_id" required>
                    <option value="">Select Contract</option>                            
                    @foreach($contracts as $contract)                           
                    <option {{ isset($tenantRenewal)? ((old('tenant_contract_id',$tenantRenewal->tenant_contract_id) == $contract->id)? 'selected' : '') : ''}} value="{{$contract->id}}" >{{$contract->tenant_contract_no}}</option>
                    @endforeach
                
                </select>
            </div>
        </div>

       <div class="w-100"></div>
         <div class="col-sm-12">
            <div class="form-group">
                <label for="renewal_or_termination_request_note">Note</label>
                <textarea name="renewal_or_termination_request_note" id="renewal_or_termination_request_note" class="form-control" placeholder="Enter Renewal Notes"  >{{ isset($tenantRenewal)?  old('renewal_or_termination_request_note',$tenantRenewal->renewal_or_termination_request_note): old('renewal_or_termination_request_note')}} </textarea>
            </div>
        </div>
        <div class="w-100"></div>        
           
        </div>

</div>

<div class="clearfix"></div>
<div class="col-sm-12">
            <div class="row">
                <div class="col no-padding">
                    <button type="submit" class="btn btn-primary "><i class="fa fa-floppy-o fa-fw" aria-hidden="true"></i>Save</button> 
                   <!--  <button type="submit" class="btn btn-primary "><i class="fa fa-trash fa-fw" aria-hidden="true"></i>Delete</button> 
                    <button type="submit" class="btn btn-primary "><i class="fa fa-print fa-fw" aria-hidden="true"></i>Print</button> 
                    <button type="submit" class="btn btn-primary "><i class="fa fa-share-square fa-fw" aria-hidden="true"></i>Post</button>  -->
                    <!-- <button type="submit" class="btn btn-primary "><i class="fa fa-paperclip fa-fw" aria-hidden="true"></i>Attachment</button> -->
                </div>
            </div>
            <!-- <div class="row">
                <div class="col mt-5 mb-3">
                    <p><a href="#">Generate Invoice</a> | <a href="#">PDC (Post Dated Cheques)</a></p>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <button type="submit" class="btn btn-warning">Renew Contract</button>
                    <button type="submit" class="btn btn-warning">Premature Termination of Contract</button>
                    <button type="submit" class="btn btn-warning">Approve Contract</button>
                    <button type="submit" class="btn btn-warning">Normal Termination of Contract</button>
                    <button type="submit" class="btn btn-warning">Reject</button>
                </div>
            </div> -->
        </div>
</form>

<div class="clearfix"></div>

    
</div>
</div>
</div>


@endsection
@section('scripts')
<script>
$(document).ready(function() {
    $("#tenant_renewal_termination_form").validate();
});

</script>
@endsection