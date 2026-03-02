@extends('layouts.plms-app')
@section('css')  
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">
<!-- data tables -->
<link rel="stylesheet" href="{{ asset('public/css/datatables.min.css')}}">
@endsection

@section('search_url', route('tenantContract.underRenewal'))
@section('search_reset', route('tenantContract.underRenewal'))

@section('content')
    <!-- start widget -->
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Tenant - Contract Under Renewal</div>
        </div>
         {{ Breadcrumbs::render('tenantContract.underRenewal') }}
    </div>
</div>
<a  class=" align-right advSearch" href="#" id="enquiry_div" data-toggle="collapse" data-target="#show">
<i class="fa fa-search" aria-hidden="true"></i>  <span id="enquirySearch"> Advance Search <i class="fa fa-caret-down" aria-hidden="true"></i></span>
</a>
<div class="clearfix"></div>
<div class="row collapse @if(old('fieldName')) show @endif" id="show"  >
    <div class="col-md-12 col-sm-12 dashboardtab">
        <div class="panel tab-border card-box">
           
           @include('backoffice::RenewalOrTermination.tenant_search')  
 
        </div>
    </div>
</div>
 <div class="row">
   <div class="col-md-12 col-sm-12">
        <div class="card  card-box">
            
            <div class="card-body ">
            <h4>
            <div id="pagination_info">
                     @include('includes.pagination_info',['paginator' => $dueForRenewals])         
                 </div>
             <div class="clr"></div>
            </h4>
                          <div class="table-wrap">
     <div class="table-responsive"> 
              <table class="table display product-overview mb-30" id="dtBasicExample">
                <thead>
                    <tr>
                        <th>@sortablelink('tenant_contract_no','Contract',[],[ 'class' => 'sort_url' ])</th>
                        <th>@sortablelink('building.building_name','Building',[],[ 'class' => 'sort_url' ])</th>
                        <th>@sortablelink('unit.unit_code','Unit No',[],[ 'class' => 'sort_url' ])</th>
                        <th>@sortablelink('tenant_contract_start_date','Start Date',[],[ 'class' => 'sort_url' ])</th>
                        <th>@sortablelink('tenant_contract_valid_to_date','End Date',[],[ 'class' => 'sort_url' ])</th>
                        <th>@sortablelink('tenant_contract_rent','Rent',[],[ 'class' => 'sort_url' ])</th>
                        <th>@sortablelink('tenant_id','Tenant',[],[ 'class' => 'sort_url' ])</th>
                         
                        <th>@sortablelink('catgeory','Discussion Type',[],[ 'class' => 'sort_url' ])</th>
                        <th>Action</th>
                    </tr>
                    <tr>
                        <td><input type="text" name="contract_no" id="contract_no" class="search_fields Contract mob"  value="{{old('tenant_contract_no')}}" >
                        </td>
                        <td><input type="text" name="building_id" id="building_id" class="search_fields Building mob"  value="{{old('building_name')}}" >
                        </td> 
                        <td><input type="text" name="unit_id"  id="unit_id" class="search_fields Unit mob"  value="{{old('unit_code')}}" >
                        </td> 
                        <td><input type="date" name="tenant_contract_start_date" id="tenant_contract_start_date" class="search_fields StartDate mob"  value="{{old('tenant_contract_start_date')}}" >
                        </td> 
                        <td><input type="date" name="tenant_contract_valid_to_date" id="tenant_contract_valid_to_date" class="search_fields EndDate mob"  value="{{old('tenant_contract_valid_to_date')}}" >
                        </td>
                        <td><input type="text" name="tenant_contract_rent" id="tenant_contract_rent" class="search_fields Tenant mob"  value="{{old('tenant_contract_rent')}}" >
                        </td>
                        <td><input type="text" name="tenant_id" id="tenant_id" class="search_fields Tenant mob"  value="{{old('tenant_name')}}" >
                        </td>
                        <td><input type="text" name="category" id="category" class="search_fields Tenant mob"  value="{{old('category')}}" ><input autocomplete="off" type="hidden" name="url_route" id="url_route"  value="{{route('tenantRenewal.tenantRenewalRequestSearch')}}" >
                        </td>
                         
                        <td></td>
                    </tr>
                </thead>

                    <tbody id="enquiry-search">                                                             
                     @include('backoffice::RenewalOrTermination.tenant_contract_underRenewal_list_ajax')                                 
                    </tbody>
                   
              </table>
                 </div>
</div>
			 <div id="pagination">                  
                        
                        {{$dueForRenewals->appends(\Request::except('page'))->links()}}               
                    </div>  
            </div>
        </div>
    </div>
</div>
<form id="delete-form" action="" method="POST">
    {{ method_field('DELETE') }}  {{csrf_field()}}
    <input value="delete" style="display: none;" type="submit">
</form>



<div class="modal" id="myModal">

</div>
@endsection
@section('scripts')    
 
@include('backoffice::RenewalOrTermination.tenant_search_js') 
@include('backoffice::RenewalOrTermination.tenant_quick_search_view_js') 
<script> 

$(document).ready(function(){

   $(document).on('click','.delete_type',function(event){
      var action = $(this).attr("href");
        event.preventDefault();
        if (confirm('Do you want to Delete this Request Type?')) {
            jQuery("#delete-form").attr('action', action);
            jQuery("#delete-form").submit();
        } else {
            return false;
        }
   });




    $(document).on('click','.emailTemplate,.pdfTemplate', function(e){
        var tenant_contract_id =  $(this).attr('data-tenant_contract_id');
   
        $.ajax({
            method: 'POST',  
            url: "{{route('renewalTemplatePdf')}}",  
            data: {'tenant_contract_id':tenant_contract_id,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
            success: function(response){  
                $("#myModal").html(response);
                 
           },
       });
    })

});

</script>


@endsection
