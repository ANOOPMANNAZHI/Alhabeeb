                  @if(count(\Auth::user()->getRoleNames()) == 1)
                  <div class="page-bar">
                    <div class="page-title-breadcrumb">
                      <div class=" pull-left">
                      <div class="page-title">ARE Team Lead - Dashboard</div>
                      </div>
                      {{ Breadcrumbs::render('are-team-lead-dashboard')}}  
                    </div>
                  </div>
                  @endif
                  <!-- start widget -->
                  <div class="state-overview">
                    <div class="row">
                     <div class="col-xl-4 col-md-4 col-12">
                       <div class="info-box bg-blue">
                         <a href="{{route('tenant-contract.index',['grace_period'=>30])}}">
                           <span class="info-box-icon push-bottom"><i class="material-icons extra">style</i></span>
                         </a>
                         <div class="info-box-content">
                           <span class="info-box-number">{{$areTeamLead['gracePeriodCount']}}</span>
                           <span class="info-box-text">Contracts Expired</span>
                           <div class="progress">
                             <div class="progress-bar width-60"></div>
                           </div>
                           <span class="progress-description">
                            Within Grace Period
                          </span>
                        </div>
                        <!-- /.info-box-content -->
                      </div>
                      <!-- /.info-box -->
                    </div>
                    <!-- /.col -->
                    <div class="col-xl-4 col-md-4 col-12">
                     <div class="info-box bg-warning">
                      <a href="{{route('tenant-contract.index',['under_penalty'=>30])}}">
                       <span class="info-box-icon push-bottom"><i class="material-icons extra">card_travel</i></span>
                     </a>
                     <div class="info-box-content">
                       <span class="info-box-number">{{$areTeamLead['underPenaltyCount']}}</span>
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
                  <a href="{{route('tenantRenewedContract',['no_days'=>10])}}">
                    <span class="info-box-icon push-bottom"><i class="material-icons extra">contact_mail</i></span>
                  </a>
                  <div class="info-box-content">
                    <span class="info-box-number">{{$areTeamLead['notRegisteredInMunicipalityCount']}}</span>
                    <span class="info-box-text">Contracts Renewed</span>
                    <div class="progress">
                     <div class="progress-bar width-60"></div>
                   </div>
                   <span class="progress-description">
                    Not registered with Municipality
                  </span>
                </div>
                <!-- /.info-box-content -->
              </div>
              <!-- /.info-box -->
            </div>
            <div class="col-xl-4 col-md-4 col-12">
             <div class="info-box bg-purple">
               <a class="link" href="">
                 <span class="info-box-icon push-bottom"><i class="material-icons extra">style</i></span>
               </a>
               <div class="info-box-content">
                 <span class="info-box-number"><p class="total_receivables" id="total_receivables"></p></span>
                 <span class="info-box-text">Total Receivables</span>
                 <div class="progress">
                   <div class="progress-bar width-60"></div>
                 </div>
                 <select name="days"  class="form-control" id="days" required>
                   <option selected value="30">30 Days</option> 
                   <option value="60">60 Days</option> 
                   <option value="90">90 Days</option> 
                 </select> 
                       <!--  <span class="progress-description">
                              Vacating in 30 days.
                            </span> -->
                          </div>
                          <!-- /.info-box-content -->
                        </div>
                        <!-- /.info-box -->
                      </div>

                    </div>
                    <!-- /rwo wnd -->
                  </div>


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

                   });
                 </script>

