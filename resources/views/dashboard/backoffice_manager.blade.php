                  @if(count(\Auth::user()->getRoleNames()) == 1)
                   <div class="page-bar">
            <div class="page-title-breadcrumb">
                <div class=" pull-left">
                    <div class="page-title">BackOffice Manager - Dashboard</div>
                </div>
                {{ Breadcrumbs::render('backoffice-manager-dashboard')}}  
            </div>
        </div>
                  @endif
                  <!-- start widget -->
                  <div class="state-overview">
                    <div class="page-title">Information</div>
                    <div class="row">

                      <div class="col-xl-4 col-md-4 col-12">
                        <div class="info-box bg-mint">
                          <a href="" class="link1" id="link1">
                            <span class="info-box-icon push-bottom"><i class="material-icons extra">monetization_on</i></span>
                          </a>
                          <div class="info-box-content">
                            <span class="info-box-number"><p class="tenant_vacating" id="tenant_vacating"></p></span>
                            <span class="info-box-text">Tenant Vacating</span>
                            <div class="progress">
                             <div class="progress-bar width-60"></div>
                           </div>
                           <select name="vaccatingdays"  class="form-control" id="vaccatingdays" required>
                             <option selected value="30">30 Days</option> 
                             <option value="60">60 Days</option> 
                             <option value="90">90 Days</option> 
                           </select> 
                         </div>
                         <!-- /.info-box-content -->
                       </div>
                       <!-- /.info-box -->
                     </div>
                     <!-- /.col -->
                     <div class="col-xl-4 col-md-4 col-12">
                       <div class="info-box bg-warning">
                         <a href="{{route('tenant-contract.index',['grace_period'=>30])}}">
                           <span class="info-box-icon push-bottom"><i class="material-icons extra">card_travel</i></span>
                         </a>
                         <div class="info-box-content">
                           <span class="info-box-number">{{$backofficeManager['gracePeriodCount']}}</span>
                           <span class="info-box-text">Contracts Expired</span>
                           <div class="progress">
                            <div class="progress-bar width-60"></div>
                          </div>
                          <span class="progress-description">
                            Within Grace Period.
                          </span>
                        </div>
                        <!-- /.info-box-content -->
                      </div>
                      <!-- /.info-box -->
                    </div>
                    <!-- /.col -->
                    <div class="col-xl-4 col-md-4 col-12">
                      <div class="info-box bg-success">
                       <a href="{{route('tenant-contract.index',['expdays'=>30])}}">
                        <span class="info-box-icon push-bottom"><i class="material-icons extra">monetization_on</i></span>
                      </a>
                      <div class="info-box-content">
                        <span class="info-box-number">{{$backofficeManager['expiringContractsCount']}}</span>
                        <span class="info-box-text">Contracts Expiring</span>
                        <div class="progress">
                         <div class="progress-bar width-60"></div>
                       </div>
                       <span class="progress-description">
                        In 30 days.
                      </span>
                    </div>
                    <!-- /.info-box-content -->
                  </div>
                  <!-- /.info-box -->
                </div>
                <div class="col-xl-4 col-md-4 col-12">
                  <div class="info-box bg-danger">
                   <a href="{{route('tenant-contract.index',['under_penalty'=>30])}}">
                    <span class="info-box-icon push-bottom"><i class="material-icons extra">fiber_new</i></span>
                  </a>
                  <div class="info-box-content">
                    <span class="info-box-number">{{$backofficeManager['underPenaltyCount']}}</span>
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

             <div class="col-xl-4 col-md-4 col-12">
               <div class="info-box bg-blue">
                <a href="{{route('tenantRenewedContract',['not_registered'=>true])}}">
                 <span class="info-box-icon push-bottom"><i class="material-icons extra">style</i></span>
               </a>
               <div class="info-box-content">
                 <span class="info-box-number">{{$backofficeManager['notRegisteredInMunicipalityCount']}}</span>
                 <span class="info-box-text">Contracts Renewed</span>
                 <div class="progress">
                   <div class="progress-bar width-60"></div>
                 </div>
                 <span class="progress-description">
                  Not Registered
                </span>
              </div>
              <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
          </div>
          <!-- /.col -->


        <div class="col-xl-4 col-md-4 col-12">
            <div class="info-box bg-b-danger">
              <a href="{{ route('tenant-contract.index',['tenant_contract_is_reg_municipality'=>0])}}">
                <span class="info-box-icon push-bottom"><i class="material-icons extra">assignment_late</i></span>
              </a>
              <div class="info-box-content">
                <span class="info-box-number">{{$backofficeManager['unregisteredContractsCount']}}</span>
                <span class="info-box-text">Unregistered  </span>
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
           <div class="info-box bg-blue">
            <a href="{{route('takenOverNotdoneForFacilityManager')}}" >
              <span class="info-box-icon push-bottom infoBoxSmall">
                <i class="material-icons">remove_circle_outline</i>
              </span>
              
              <div class="info-box-content">         
                <span class="info-box-number">{{$backofficeManager['takenOverNotdone']}}</span>
                <span class="info-box-text">Taken Over Not Done Even After</span>          
                <div class="progress">
                  <div class="progress-bar width-60"></div>
                </div>
                <span class="progress-description">             
                  7 days of Contract Expiry Date
                </span>          
              </div>
            </a>
            <!-- /.info-box-content -->
          </div>
          <!-- /.info-box -->
        </div>

        <div class="col-xl-4 col-md-4 col-12">
          <div class="info-box bg-b-purple">
            <a href="{{route('tenantContract.underRenewal',['referback'=>'yes'])}}">
              <span class="info-box-icon push-bottom"><i class="material-icons extra">assistant</i></span>
            </a>
            <div class="info-box-content">
              <span class="info-box-number">{{$backofficeManager['referBackContractsCount']}}</span>
              <span class="info-box-text">Referred Cases</span>
              <div class="progress">
               <div class="progress-bar width-60"></div>
             </div>
             <span class="progress-description">
              by takeover
            </span>
          </div>
          <!-- /.info-box-content -->
        </div>
        <!-- /.info-box -->
      </div>
      

      <div class="col-xl-4 col-md-4 col-12">
        <div class="info-box bg-purple">
          <a href="{{route('receivables',['days'=>60])}}">
            <span class="info-box-icon push-bottom"><i class="material-icons extra">compare</i></span>
          </a>
          <div class="info-box-content">
            <span class="info-box-number">{{$backofficeManager['sumOfReceiptAmount']}}</span>
            <span class="info-box-text">Receivables </span>
            <div class="progress">
              <div class="progress-bar width-60"></div>
            </div>
            <span class="progress-description">
              Beyond 60 Days
            </span>
          </div>
          <!-- /.info-box-content -->
        </div>
        <!-- /.info-box -->
      </div>

      <!-- /.col -->

      <div class="col-xl-4 col-md-4 col-12">
        <div class="info-box bg-mint">
          <a href="{{route('getBouncedCheques')}}">
            <span class="info-box-icon push-bottom"><i class="material-icons extra">fiber_new</i></span>
          </a>
          <div class="info-box-content">
            <span class="info-box-number">{{$backofficeManager['bouncedChequeCount']}}</span>
            <span class="info-box-text">Bounce Cheque</span>
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
      <div class="col-xl-4 col-md-4 col-12">
        <div class="info-box bg-orange">
         <a href="{{route('landlord-contract.index',['expdays'=>90])}}">
          <span class="info-box-icon push-bottom"><i class="material-icons extra">fiber_new</i></span>
        </a>
        <div class="info-box-content">
          <span class="info-box-number">{{$backofficeManager['landlordContractCount']}}</span>
          <span class="info-box-text">Landlord Management Agreement</span>
          <div class="progress">
            <div class="progress-bar width-60"></div>
          </div>
          <span class="progress-description">
           Expiring Within 90 days
         </span>
       </div>
       <!-- /.info-box-content -->
     </div>
     <!-- /.info-box -->
   </div>
 </div>
 <div class="page-title">Action</div>
 <div class="row">

  <!-- /.col -->
  <div class="col-xl-4 col-md-4 col-12">
    <div class="info-box bg-mint">
     <a class="link" href="{{route('renewalContractApproval')}}">
      <span class="info-box-icon push-bottom"><i class="material-icons extra">fiber_new</i></span>
    </a>
    <div class="info-box-content">
      <span class="info-box-number">{{$backofficeManager['approvalCount']}}</span>
      <span class="info-box-text">Approved</span>
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
</div>
<div class="page-title">Legal</div>
<div class="row">
 <div class="col-xl-4 col-md-4 col-12">
   <div class="info-box bg-warning">
     <a class="link" href="{{route('plmsApproval')}}">
       <span class="info-box-icon push-bottom"><i class="material-icons extra">card_travel</i></span>
     </a>
     <div class="info-box-content">
       <span class="info-box-number">{{$backofficeManager['plmsApprovalCount']}}</span>
       <span class="info-box-text">Number Of Referrals</span>
       <div class="progress">
        <div class="progress-bar width-60"></div>
      </div>
      <span class="progress-description">
       ARE's To Take To Legal
     </span>
   </div>
   <!-- /.info-box-content -->
 </div>
 <!-- /.info-box -->
</div>
<!-- /.col -->
<div class="col-xl-4 col-md-4 col-12">
  <div class="info-box bg-success">
   <a class="link" href="{{route('activeCases')}}">
    <span class="info-box-icon push-bottom"><i class="material-icons extra">monetization_on</i></span>
  </a>
  <div class="info-box-content">
    <span class="info-box-number">{{$backofficeManager['activeCasesCount']}}</span>
    <span class="info-box-text">Cases Accepted</span>
    <div class="progress">
     <div class="progress-bar width-60"></div>
   </div>
   <span class="progress-description">
     By Legal
   </span>
 </div>
 <!-- /.info-box-content -->
</div>
<!-- /.info-box -->
</div>
<div class="col-xl-4 col-md-4 col-12">
  <div class="info-box bg-danger">
    <a class="link" href="{{route('legalCase.index',['are_status'=>1])}}">
      <span class="info-box-icon push-bottom"><i class="material-icons extra">fiber_new</i></span>
    </a>
    <div class="info-box-content">
      <span class="info-box-number">{{$backofficeManager['referBackCount']}}</span>
      <span class="info-box-text">Cases Referred Back</span>
      <div class="progress">
        <div class="progress-bar width-60"></div>
      </div>
      <span class="progress-description">
       By Legal
     </span>
   </div>
   <!-- /.info-box-content -->
 </div>
 <!-- /.info-box -->
</div>

<div class="col-xl-4 col-md-4 col-12">
 <div class="info-box bg-blue">
   <a href="{{ route('activeCases',['params'=>'params']) }}">
     <span class="info-box-icon push-bottom"><i class="material-icons extra">style</i></span>
   </a>
   <div class="info-box-content">
     <span class="info-box-number">{{$backofficeManager['closedCasesCount']}}</span>
     <span class="info-box-text">Cases Closed</span>
     <div class="progress">
       <div class="progress-bar width-60"></div>
     </div>
     <span class="progress-description">
      By Legal
    </span>
  </div>
  <!-- /.info-box-content -->
</div>
<!-- /.info-box -->
</div>

</div>
</div>    
<script>
 $(document).ready(function() {
   /***********Contracts Expiring starts**************/
   $('#vaccatingdays').on('change', function(e) {   
     var vaccatingdays = $('#vaccatingdays').val();
     var href1 =   "{{ route('tenant-contract.index')}}";
     $.ajax({
       type: "GET",
       url: "{!!URL::route('tenantVaccating')!!}",
       data:'vaccatingdays='+ $('#vaccatingdays').val(),
       success: function(data){
        var result = $.parseJSON(data);
        $('#tenant_vacating').text(result[0].count);
        href1 = href1+'?vaccatingdays='+vaccatingdays;
        $(".link1").attr('href', href1);
      }
    });
   });
   /***********Contracts Expiring ends**************/
   var vaccatingdays = 30;
   var href1 =   "{{ route('tenant-contract.index')}}";
   $.ajax({
     type: "GET",
     url: "{!!URL::route('tenantVaccating')!!}",
     data:'vaccatingdays='+ 30,
     success: function(data){
      var result = $.parseJSON(data);
      $('#tenant_vacating').text(result[0].count);
      href1 = href1+'?vaccatingdays='+vaccatingdays;
      $(".link1").attr('href', href1);
    }
  });
   /***********Contracts Expiring ends**************/  
 }); 
</script>

