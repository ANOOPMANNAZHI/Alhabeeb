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
            <div class="page-title">Landlord Renewal Request</div>
        </div>
        {{ (isset($landlordRenewal))?   Breadcrumbs::render('landlordRenewal.edit',$landlordRenewal) :  Breadcrumbs::render('landlordRenewal.create') }}
    </div>
</div>
<div class="row">
<div class="col">
<div class="card card-box salesSearchBox">
<form action="{{ !isset($landlordRenewal)? route('landlordRenewal.store'): route('landlordRenewal.update',$landlordRenewal->id)}}" method="POST" id="landlord_renewal_termination_form"" class="form-horizontal" enctype="multipart/form-data" data-toggle="validator">
        {{csrf_field()}} @if(isset($landlordRenewal)){{method_field('PUT')}}@endif
        
<div class="dataSearchBox ">

        <div class="row">
             <div class="col-sm-6">
            <div class="form-group">
                <label for="landlord_contract_id">Landlord Contract</label>
                <div class="p-relative">
                    <i class="fa fa-external-link icn-add" aria-hidden="true"></i>
                <select class="form-control" name="landlord_contract_id" required>
                    <option value="">Select Contract</option>                            
                    @foreach($contracts as $contract)                           
                    <option {{ isset($landlordRenewal)? ((old('landlord_contract_id',$landlordRenewal->landlord_contract_id) == $contract->id)? 'selected' : '') : ''}} value="{{$contract->id}}" >{{$contract->landlord_contract_no}}</option>
                    @endforeach
                </select>
                </div>
            </div>
        </div>

       <div class="w-100"></div>
         <div class="col-sm-12">
            <div class="form-group">
                <label for="renewal_or_termination_request_note">Note</label>
                <div class="p-relative">
                    <i class="fa fa-file-text-o icn-add" aria-hidden="true"></i>
                <textarea name="renewal_or_termination_request_note" id="renewal_or_termination_request_note" class="form-control" placeholder="Enter Renewal Notes"  >{{ isset($landlordRenewal)?  old('renewal_or_termination_request_note',$landlordRenewal->renewal_or_termination_request_note): old('renewal_or_termination_request_note')}} </textarea>
            </div>
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
    $("#landlord_renewal_termination_form").validate();
});

</script>
@endsection