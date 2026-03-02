
@if(count(\Auth::user()->getRoleNames()) == 1)

 <div class="page-bar">
            <div class="page-title-breadcrumb">
                <div class=" pull-left">
                    <div class="page-title">ARE - Dashboard</div>
                </div>
                {{ Breadcrumbs::render('are-dashboard')}}  
            </div>
        </div>

@endif           
<!-- start widget -->
<div class="state-overview">
  <div class="row">
   <div class="col-xl-4 col-md-4 col-12">
     <div class="info-box bg-blue">
       <a class="link" href="">
         <span class="info-box-icon push-bottom"><i class="material-icons extra">style</i></span>
       </a>
       <div class="info-box-content">
         <span class="info-box-number"><p class="total_receivables" id="total_receivables"></p></span><br/>
         <span class="info-box-text">Total Receivables</span>
         <div class="progress">
           <div class="progress-bar width-60"></div>
         </div>
         <select name="days"  class="form-control" id="days" required>
           <option selected value="30">30 Days</option>
           <option value="60">60 Days</option>
           <option value="90">90 Days</option>
         </select>
                          </div>
                        </div>
                      </div>
                      <!-- /.col -->
                   <div class="col-xl-4 col-md-4 col-12">
                       <div class="info-box bg-warning">
                       <a href="{{route('averageReceivableUnit',['today'=>1])}}">
                         <span class="info-box-icon push-bottom"><i class="material-icons extra">card_travel</i></span>
                         </a>
                         <div class="info-box-content">
                           <span class="info-box-number">{{$are['averageReceivables']}}</span>
                           <span class="info-box-text">Average Receivables Per Unit</span>
                           <div class="progress">
                            <div class="progress-bar width-60"></div>
                          </div>
                          <span class="progress-description">
                            Without Taking Legal Units
                          </span>
                        </div>
                      </div>
                    </div>

                    <!-- /.col -->
                    
                    <!-- /.col -->
                    <div class="col-xl-4 col-md-4 col-12">
                      <div class="info-box bg-mint">
                      <a href="" class="link2" id="link2">
                          <span class="info-box-icon push-bottom"><i class="material-icons extra">monetization_on</i></span>
                        </a>
                        <div class="info-box-content">
                          <span class="info-box-number"><p class="pending_approvals" id="pending_approvals"></p></span>
                          <span class="info-box-text">Pending Approvals from HOD</span>
                          <div class="progress">
                           <div class="progress-bar width-60"></div>
                         </div>
                         <select name="renewal_days"  class="form-control" id="renewal_days" required>
                           <option selected value="3">3 Days</option> 
                           <option value="6">6 Days</option> 
                           <option value="9">9 Days</option> 
                         </select> 
                       </div>
                       <!-- /.info-box-content -->
                     </div>
                     <!-- /.info-box -->
                   </div>
                   <div class="col-xl-4 col-md-4 col-12">
                    <div class="info-box bg-b-danger">
                      <a href="{{route('getBouncedCheques')}}">
                        <span class="info-box-icon push-bottom"><i class="material-icons extra">assignment_late</i></span>
                      </a>
                      <div class="info-box-content">
                        <span class="info-box-number">{{$are['bouncedChequeCount']}}</span>
                        <span class="info-box-text">Bounced Cheques</span>
                        <div class="progress">
                         <div class="progress-bar width-60"></div>
                       </div>
                       <span class="progress-description">
                         No Of Cheques
                       </span>
                     </div>
                     <!-- /.info-box-content -->
                   </div>
                   <!-- /.info-box -->
                 </div>

                 <div class="col-xl-4 col-md-4 col-12">
                  <div class="info-box bg-b-purple">
                    <a href="{{route('complaint.index',['days'=>'7'])}}">
                      <span class="info-box-icon push-bottom"><i class="material-icons extra">assistant</i></span>
                    </a>
                    <div class="info-box-content">
                      <span class="info-box-number">{{$are['maintenancePendingCount']}}</span>
                      <span class="info-box-text">Maintenance Pending Cases</span>
                      <div class="progress">
                       <div class="progress-bar width-60"></div>
                     </div>
                     <span class="progress-description">
                    More than One Week
                    </span>
                  </div>
                  <!-- /.info-box-content -->
                </div>
                <!-- /.info-box -->
              </div>
              <div class="col-xl-4 col-md-4 col-12">
                <div class="info-box bg-b-blue">
                  <a class="link1" href="">
                    <span class="info-box-icon push-bottom"><i class="material-icons extra">beenhere</i></span>
                  </a>
                  <div class="info-box-content">
                    <span class="info-box-number"><p id="expiring_contracts" class="expiring_contracts"></p></span>
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
            <div class="info-box bg-success">
            <a href="{{route('tenant-contract.index',['grace_period'=>30,'role'=>'are'])}}">
              <span class="info-box-icon push-bottom"><i class="material-icons extra">battery_unknown</i></span>
              </a>
              <div class="info-box-content">
                <span class="info-box-number">{{$are['gracePeriodCount']}}</span>
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
             
             <div class="col-xl-4 col-md-4 col-12">
              <div class="info-box bg-danger">
              <a href="{{route('tenant-contract.index',['under_penalty'=>30])}}">
                <span class="info-box-icon push-bottom">
                  <i class="material-icons extra">signal_cellular_off</i></span>
                </a>
                <div class="info-box-content">
                  <span class="info-box-number">{{$are['underPenaltyCount']}}</span>
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
            <div class="col-xl-4 col-md-4 col-12">
              <div class="info-box bg-primary">
              <a href="{{route('tenantRenewedContract',['no_days'=>5])}}">
                <span class="info-box-icon push-bottom"><i class="material-icons extra">contact_mail</i></span>
                </a>
                <div class="info-box-content">
                  <span class="info-box-number">{{$are['notRegisteredInMunicipalityCount']}}</span>
                  <span class="info-box-text">Contracts Renewed -Not registered</span>
                  <div class="progress">
                   <div class="progress-bar width-60"></div>
                 </div>
                 <span class="progress-description">
                 After 5 Days Of Renewal
                </span>
              </div>
              <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
          </div>
          <!-- /.col -->
          
          <!-- /.col -->

          <div class="col-xl-6 col-md-4 col-12">
            <div class="info-box bg-orange">
              <a href="{{route('unit.index',['vaccating_days=15'])}}">
                <span class="info-box-icon push-bottom"><i class="material-icons extra">code</i></span>
              </a>
              <div class="info-box-content">
                <span class="info-box-number">{{$are['vaccatingUnitsCount']}}</span>
                <span class="info-box-text">Number Of Units</span>
                <div class="progress">
                  <div class="progress-bar width-60"></div>
                </div>
                <span class="progress-description">
                 Vacating In 15 Days
               </span>
             </div>
             <!-- /.info-box-content -->
           </div>
           <!-- /.info-box -->
         </div>
         <div class="col-xl-6 col-md-4 col-12">
          <div class="info-box bg-purple">
          <a href="{{route('tenantContract.underRenewal',['referback'=>'yes'])}}">
            <span class="info-box-icon push-bottom"><i class="material-icons extra">cached</i></span>
            </a>
            <div class="info-box-content">
              <span class="info-box-number">{{$are['referBackContractsCount']}}</span>
              <span class="info-box-text">Referred Back</span>
              <div class="progress">
                <div class="progress-bar width-60"></div>
              </div>
              <span class="progress-description">
                Maintenance Take Over Team
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
       /**********Total Receivables Starts*********/
       $('#days').on('change', function(e) {   
         var days = $('#days').val();
         var href =   "{{ route('receivables',':days')}}";
         $.ajax({
           type: "GET",
           url: "{!!URL::route('totalReceivables')!!}",
           data:'days='+ $('#days').val(),
           success: function(data){
            var result = $.parseJSON(data);
            $('#total_receivables').text(result[0].amount);
            href = href.replace(':days', days);
            $(".link").attr('href', href);
          }
        });
       });
       /**********Total Receivables ends*********/
       var days = 30;
       var href =   "{{ route('receivables',':days')}}";
       $.ajax({
         type: "GET",
         url: "{!!URL::route('totalReceivables')!!}",
         data:'days='+ 30,
         success: function(data){
          var result = $.parseJSON(data);
          $('#total_receivables').text(result[0].amount);
          href = href.replace(':days', days);
          $(".link").attr('href', href);
        }
      });
       /**********Total Receivables ends*********/
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
        /***********Pending approvals from HOD starts**************/
       $('#renewal_days').on('change', function(e) {   
         var renewal_days = $('#renewal_days').val();
         var href2 =   "{{ route('renewalContractApproval')}}";
         $.ajax({
           type: "GET",
           url: "{!!URL::route('pendingApprovals')!!}",
           data:'renewal_days='+ $('#renewal_days').val(),
           success: function(data){
            var result = $.parseJSON(data);
            $('#pending_approvals').text(result[0].count);
            href2 = href2+'?renewal_days='+renewal_days;
            $(".link2").attr('href', href2);
          }
        });
       });
       /***********Pending approvals from HOD ends**************/
       var renewal_days = 3;
       var href2 =   "{{ route('renewalContractApproval')}}";
       $.ajax({
         type: "GET",
         url: "{!!URL::route('pendingApprovals')!!}",
         data:'renewal_days='+ 3,
         success: function(data){
          var result = $.parseJSON(data);
          $('#pending_approvals').text(result[0].count);
          href2 = href2+'?renewal_days='+renewal_days;
          $(".link2").attr('href', href2);
        }
      });
       /***********Pending approvals from HOD ends**************/
     });
   </script>