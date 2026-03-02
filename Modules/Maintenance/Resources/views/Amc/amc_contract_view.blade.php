@extends('layouts.plms-app')


@section('content')
<!-- start widget -->
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">AMC Contract View</div>
    </div>
    {{ Breadcrumbs::render('amcContract.show') }}
  </div>
</div>


<div class="row">
  <div class="col-sm-12">
    <div class="card-box">
     <div class="card-head">
      <h4>
        @can('amc_contract_edit') 
        @if(  $amcContract->amc_contract_status == 0 )
        @if(  count($amcContract->schedules) == 0 || count( $amcContract->cancelSchedules) >0 )
        <a title="Edit" href="{{route('amcContract.edit',$amcContract->id)}}" class="btn btn-circle btn-primary  align-right" title="Edit">EDIT 
        </a>  
        @endif    
        @endif                                               
        @endcan
        <div class="clr"></div>
      </h4>
    </div>
    <form action="#" id="form_sample_2" class="form-horizontal">
      <div class="card-body row"> 


       <div class="col-md-6 p-t-10">
        <div class="row">
          <div class="col-md-5"><b> Contract No  </b></div>
          <div class="col-md-1 s-clm">:</div>
          <div class="col-md-6"><span>{{$amcContract->amc_contract_no}}</span></div>
        </div>
      </div>

      <div class="col-md-6 p-t-10">
        <div class="row">
          <div class="col-md-5"><b>Vendor Name  </b></div>
          <div class="col-md-1 s-clm">:</div>
          <div class="col-md-6"><span>{{$amcContract->vendor->vendor_name}}</span></div>
        </div>
      </div> 
      <div class="col-md-6 p-t-10">
        <div class="row">
          <div class="col-md-5"><b>Start Date </b></div>
          <div class="col-md-1 s-clm">:</div>
          <div class="col-md-6"><span>{{$amcContract->amc_contract_period_from->format('d/m/Y')}}</span></div>
        </div>
      </div>
      <div class="col-md-6 p-t-10">
        <div class="row">
          <div class="col-md-5"><b>End Date  </b></div>
          <div class="col-md-1 s-clm">:</div>
          <div class="col-md-6"><span>{{$amcContract->amc_contract_period_to->format('d/m/Y')}}</span></div>
        </div>
      </div>
      <div class="col-md-6 p-t-10">
        <div class="row">
          <div class="col-md-5"><b>Building</b></div>
          <div class="col-md-1 s-clm">:</div>
          <div class="col-md-6"><span>{{$amcContract->building->building_name}}</span></div>
        </div>
      </div>
      <div class="col-md-6 p-t-10">
        <div class="row">
          <div class="col-md-5"><b>Cost </b></div>
          <div class="col-md-1 s-clm">:</div>
          <div class="col-md-6"><span>{{ numberFormat($amcContract->amc_contract_cost) ?? '0'}} OMR </span></div>
        </div>
      </div>
      <div class="col-md-6 p-t-10">
        <div class="row">
          <div class="col-md-5"><b>Frequency  </b></div>
          <div class="col-md-1 s-clm">:</div>
          <div class="col-md-6"><span>{{$amcContract->paymentMethod->payment_method_code}}</span></div>
        </div>
      </div> 
      <div class="col-md-6 p-t-10">
        <div class="row">
          <div class="col-md-5"><b>Status </b></div>
          <div class="col-md-1 s-clm">:</div>
          <div class="col-md-6"><span>@if($amcContract->amc_contract_status==0)Active
            @else Cancelled
            @endif</span></div>
          </div>
        </div>     
      </div>
    </form>
  </div>
</div>
</div>
<div class="row">
 <div class="col-md-12 col-sm-12">
  <div class="card  card-box">

    <div class="card-body ">
      <h4>
        <div id="pagination_info">
         @include('includes.pagination_info',['paginator' => $amcContractAmenities])         
       </div>
       <div class="clr"></div>
       Amenity
     </h4>

     <table class="table display product-overview mb-30" id="dtBasicExample">
      <thead>
        <tr>
          <th>Sl No</th>
          <th>Amenity Code</th>
          <th>Amenity</th>
        </tr>
      </thead>
      <tbody>
        @php
        $i=1;
        @endphp
        @forelse ($amcContractAmenities as $amcContractAmenity)
        @php
        $count=$i++;
        @endphp
        <tr>
          <td>{{$count}}</td>                         
          <td>{{$amcContractAmenity->amenityType->amentity_types_code}}</td>                         
          <td>{{$amcContractAmenity->amenityType->amentity_types_name}}</td>                         
        </tr>  
        @empty 
        <tr>
          <td colspan="8" align="center">
            <p>No Record</p>
          </td>
        </tr>
        @endforelse 
      </tbody>
    </table>

    {{ $amcContractAmenities->links() }}      
  </div>
</div>
</div>
</div>
<div class="modal" id="myModal">

</div>
@endsection
@section('scripts')
@endsection