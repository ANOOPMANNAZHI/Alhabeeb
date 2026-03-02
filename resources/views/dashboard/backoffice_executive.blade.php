
@if(count(\Auth::user()->getRoleNames()) == 1)

<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">BackOffice Executive - Dashboard</div>
    </div>
    {{ Breadcrumbs::render('backoffice-executive-dashboard')}}                           
  </div>
</div>

@endif           
<!-- start widget -->
<div class="state-overview">
  <div class="row">
   <div class="col-xl-4 col-md-4 col-12">
     <div class="info-box bg-success">
       <a href="{{route('tenant-contract.index',['grace_period'=>30])}}">
         <span class="info-box-icon push-bottom"><i class="material-icons extra">style</i></span>
       </a>
       <div class="info-box-content">
         <span class="info-box-number">{{$backofficeExecutive['gracePeriodCount']}}</span>
         <span class="info-box-text">Contracts Expired</span>
         <div class="progress">
           <div class="progress-bar width-60"></div>
         </div>
         <span class="progress-description">
           Grace Period 30 Days
         </span>
       </div>
       <!-- /.info-box-content -->
     </div>
     <!-- /.info-box -->
   </div>
   <!-- /.col -->
   <div class="col-xl-4 col-md-4 col-12">
     <div class="info-box bg-danger">
       <a href="{{route('tenant-contract.index',['under_penalty'=>30])}}">
         <span class="info-box-icon push-bottom"><i class="material-icons extra">card_travel</i></span>
       </a>
       <div class="info-box-content">
         <span class="info-box-number">{{$backofficeExecutive['underPenaltyCount']}}</span>
         <span class="info-box-text">Contracts Expired</span>
         <div class="progress">
          <div class="progress-bar width-60"></div>
        </div>
        <span class="progress-description">
          Under Penalty
        </span>
      </div>
      <!-- /.info-box-content -->
    </div>
    <!-- /.info-box -->
  </div>
  <!-- /.col -->

  <!-- /.col -->
  <div class="col-xl-4 col-md-4 col-12">
    <div class="info-box bg-success">
      <a class="link1" href="">
        <span class="info-box-icon push-bottom"><i class="material-icons extra">monetization_on</i></span>
      </a>
      <div class="info-box-content">
        <span id="expiring_contracts" class="info-box-number expiring_contracts" ></span>
        <span class="info-box-text">Contracts Expiring</span>
        <div class="progress">
         <div class="progress-bar width-60"></div>
       </div>
       <select name="expdays"  class="form-control" id="expdays" required>
         <option selected value="30">30 Days</option> 
         <option value="60">60 Days</option> 
         <option value="90">90 Days</option> 
         <option value="120">120 Days</option> 
       </select>
     </div>
     <!-- /.info-box-content -->
   </div>
   <!-- /.info-box -->
 </div>
 <div class="col-xl-4 col-md-4 col-12">
  <div class="info-box bg-b-purple">
    <a href="{{ route('tenant-contract.index',['tenant_contract_is_reg_municipality'=>0])}}">
      <span class="info-box-icon push-bottom"><i class="material-icons extra">assistant</i></span>
    </a>
    <div class="info-box-content">
      <span class="info-box-number">{{$backofficeExecutive['unregisteredContractsCount']}}</span>
      <span class="info-box-text">Unregistered Contracts</span>
      <div class="progress">
       <div class="progress-bar width-60"></div>
     </div>
     <span class="progress-description">
     Contracts
    </span>
  </div>
</div>
</div>
<div class="col-xl-4 col-md-4 col-12">
  <div class="info-box bg-b-blue">
    <a href="{{route('finalDocumentationList')}}">
      <span class="info-box-icon push-bottom"><i class="material-icons extra">beenhere</i></span>
    </a>
    <div class="info-box-content">
      <span class="info-box-number">{{$backofficeExecutive['finalDocumentationCount']}}</span>
      <span class="info-box-text">Final Documentation</span>
      <div class="progress">
       <div class="progress-bar width-60"></div>
     </div>
      <span class="progress-description">
      Approved By HOD
    </span>
   </div>
   <!-- /.info-box-content -->
 </div>
 <!-- /.info-box -->
</div>

<div class="col-xl-4 col-md-4 col-12">
  <div class="info-box bg-purple">
    <a href="{{route('tenantRenewalContract')}}">
      <span class="info-box-icon push-bottom"><i class="material-icons extra">compare</i></span>
    </a>
    <div class="info-box-content">
      <span class="info-box-number">{{$backofficeExecutive['renewalsCount']}}</span>
      <span class="info-box-text">Renewals</span>
      <div class="progress">
        <div class="progress-bar width-60"></div>
      </div>
      <span class="progress-description">
        
      </span>
    </div>
    <!-- /.info-box-content -->
  </div>
  <!-- /.info-box -->
</div>
<!-- /.col -->
<div class="col-xl-4 col-md-4 col-12">
  <div class="info-box bg-primary">
    <a href="{{route('tenantTermination.index')}}">
      <span class="info-box-icon push-bottom"><i class="material-icons extra">contact_mail</i></span>
    </a>
    <div class="info-box-content">
      <span class="info-box-number">{{$backofficeExecutive['openTerminationCount']}}</span>
      <span class="info-box-text">Take Over </span>
      <div class="progress">
       <div class="progress-bar width-60"></div>
     </div>
     <span class="progress-description">
      Requested By ARE</a>
    </span>
  </div>
  <!-- /.info-box-content -->
</div>
<!-- /.info-box -->
</div>
<!-- /.col -->

<!-- /.col -->

<div class="col-xl-4 col-md-4 col-12">
  <div class="info-box bg-mint">
    <a href="{{route('takeoverForTermination')}}">
      <span class="info-box-icon push-bottom"><i class="material-icons extra">eject</i></span>
    </a>
    <div class="info-box-content">
      <span class="info-box-number">{{$backofficeExecutive['terminatedCount']}}</span>
      <span class="info-box-text">Contracts To Be Terminated</span>
      <div class="progress">
        <div class="progress-bar width-60"></div>
      </div>
      <span class="progress-description">
        Cleared From Takeover Team
      </span>
    </div>
    <!-- /.info-box-content -->
  </div>
  <!-- /.info-box -->
</div>
<div class="col-xl-4 col-md-4 col-12">
  <div class="info-box bg-orange">
    <a href="{{route('tenantContract.underRenewal',['referback'=>'yes'])}}">
      <span class="info-box-icon push-bottom"><i class="material-icons extra">cached</i></span>
    </a>
    <div class="info-box-content">
      <span class="info-box-number">{{$backofficeExecutive['referBackContractsCount']}}</span>
      <span class="info-box-text"> Maintenance Take Over Team</span>
      <div class="progress">
        <div class="progress-bar width-60"></div>
      </div>
      <span class="progress-description">
       Referred Back 
     </span>
   </div>
   <!-- /.info-box-content -->
 </div>
 <!-- /.info-box -->
</div>
<div class="col-xl-4 col-md-4 col-12">
  <div class="info-box bg-danger">
    <a href="{{route('activeCases')}}">
      <span class="info-box-icon push-bottom"><i class="material-icons extra">card_travel</i></span>
    </a>
    <div class="info-box-content">
      <span class="info-box-number">{{$backofficeExecutive['activeCasesCount']}}</span>
      <span class="info-box-text">Legal Cases </span>
      <div class="progress">
        <div class="progress-bar width-60"></div>
      </div>
      <span class="progress-description">
        View Only
      </span>
    </div>
    <!-- /.info-box-content -->
  </div>
  <!-- /.info-box -->
</div>




</div>
<!-- /rwo wnd -->
</div>
<div>
  <div class="row">


  </div>
</div>
<!-- end widget -->



<!-- end page content -->
<!-- start chat sidebar -->
<script>
 $(document).ready(function() {
   /***********Contracts Expiring starts**************/
   $('#expdays').on('change', function(e) {   
     var expdays = $('#expdays').val();
     var href1 =   "{{ route('tenant-contract.index')}}";
     $.ajax({
       type: "GET",
       url: "{!!URL::route('contractsExpiring')!!}",
       data:'expdays='+ $('#expdays').val(),
       success: function(data){
        var result = $.parseJSON(data);
        $('#expiring_contracts').text(result[0].count);
        href1 = href1+'?expdays='+expdays;
        $(".link1").attr('href', href1);
      }
    });
   });
   /***********Contracts Expiring ends**************/
   var expdays = 30;
   var href1 =   "{{ route('tenant-contract.index')}}";
   $.ajax({
     type: "GET",
     url: "{!!URL::route('contractsExpiring')!!}",
     data:'expdays='+ 30,
     success: function(data){
      var result = $.parseJSON(data);
      $('#expiring_contracts').text(result[0].count);
      href1 = href1+'?expdays='+expdays;
      $(".link1").attr('href', href1);
    }
  });
   /***********Contracts Expiring ends**************/
 });
</script>


