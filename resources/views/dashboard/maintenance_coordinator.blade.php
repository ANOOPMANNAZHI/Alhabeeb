                  @if(count(\Auth::user()->getRoleNames()) == 1)
                  <div class="page-bar">
                    <div class="page-title-breadcrumb">
                      <div class=" pull-left">
                        <div class="page-title">Maintenance Coordinator - Dashboard</div>
                      </div>
                      {{ Breadcrumbs::render('maintenance-coordinator-dashboard')}}  
                    </div>
                  </div>
                  @endif
                  <!-- start widget -->
                  <div class="state-overview">
                    <div class="row">
                     <div class="col-xl-4 col-md-4 col-12">
                       <div class="info-box bg-blue">
                         <a class="link2" href="">
                           <span class="info-box-icon push-bottom"><i class="material-icons extra">style</i></span>
                         </a>
                         <div class="info-box-content">
                           <span class="info-box-number"><p class="open_assigned_subcontractor" id="open_assigned_subcontractor"></p></span>
                           <span class="info-box-text">Sub Contractors – Open Cases</span>
                           <div class="progress">
                             <div class="progress-bar width-60"></div>
                           </div>
                           <select name="sub_open_days"  class="form-control" id="sub_open_days" required>
                             <option selected value="1">1 Day</option> 
                             <option value="3">3 Days</option> 
                             <option value="5">5 Days</option> 
                             <option value="all">All</option> 
                           </select>
                         </div>
                         <!-- /.info-box-content -->
                       </div>
                       <!-- /.info-box -->
                     </div>
                     <!-- /.col -->
                     <div class="col-xl-4 col-md-4 col-12">
                       <div class="info-box bg-warning">
                        <a class="link3" href="">
                         <span class="info-box-icon push-bottom"><i class="material-icons extra">card_travel</i></span>
                         </a>
                         <div class="info-box-content">
                           <span class="info-box-number"><p class="land_review" id="land_review"></p></span>
                           <span class="info-box-text">Landlord Approval –Open Cases </span>
                           <div class="progress">
                            <div class="progress-bar width-60"></div>
                          </div>
                          <select name="review_days"  class="form-control" id="review_days" required>
                           <option selected value="7">7 Days</option> 
                           <option value="15">15 Days</option> 
                           <option value="all">All</option> 
                         </select>
                       </div>
                       <!-- /.info-box-content -->
                     </div>
                     <!-- /.info-box -->
                   </div>
                   <!-- /.col -->

                   <!-- /.col -->
                   <div class="col-xl-4 col-md-4 col-12">
                    <div class="info-box bg-success">
                      <a href="{{route('complaintStage.index')}}">
                        <span class="info-box-icon push-bottom"><i class="material-icons extra">monetization_on</i></span>
                      </a>
                      <div class="info-box-content">
                        <span class="info-box-number">{{$maintenanceCoordinator['unAssignedCaseCount']}}</span>
                        <span class="info-box-text">Unassigned </span>
                        <div class="progress">
                         <div class="progress-bar width-60"></div>
                       </div>
                       <span class="progress-description">
                        Cases
                      </span>
                    </div>
                    <!-- /.info-box-content -->
                  </div>
                  <!-- /.info-box -->
                </div>
                <div class="col-xl-4 col-md-4 col-12">
                  <div class="info-box bg-b-danger">
                   <a class="link1" href="">
                    <span class="info-box-icon push-bottom"><i class="material-icons extra">assignment_late</i></span>
                  </a>
                  <div class="info-box-content">
                    <span class="info-box-number"><p class="open_assigned_supervisor" id="open_assigned_supervisor"></p></span>
                    <span class="info-box-text">Assigned Tickets </span>
                    <div class="progress">
                     <div class="progress-bar width-60"></div>
                   </div>
                   <select name="time"  class="form-control" id="time" required>
                     <option selected value="24">24 Hours</option> 
                     <option value="48">48 Hours</option> 
                     <option value="all">All</option> 
                   </select>
                 </div>
                 <!-- /.info-box-content -->
               </div>
               <!-- /.info-box -->
             </div>

             <div class="col-xl-4 col-md-4 col-12">
              <div class="info-box bg-b-purple">
                <a href="{{route('complaint.index',['vip_status'=>2])}}">
                  <span class="info-box-icon push-bottom"><i class="material-icons extra">assistant</i></span>
                </a>
                <div class="info-box-content">
                  <span class="info-box-number">{{$maintenanceCoordinator['vipOpenCount']}}</span>
                  <span class="info-box-text">VIP</span>
                  <div class="progress">
                   <div class="progress-bar width-60"></div>
                 </div>
                 <span class="progress-description">
                 Open Complaints
                </span>
              </div>
              <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
          </div>
          <div class="col-xl-4 col-md-4 col-12">
            <div class="info-box bg-b-blue">
             <a href="{{route('complaint.index',['maintained_status'=>2])}}">
              <span class="info-box-icon push-bottom"><i class="material-icons extra">beenhere</i></span>
            </a>
            <div class="info-box-content">
              <span class="info-box-number">{{$maintenanceCoordinator['landlordCasesCount']}}</span>
              <span class="info-box-text">Cases Maintained</span>
              <div class="progress">
               <div class="progress-bar width-60"></div>
             </div>
             <span class="progress-description">
                 by Landlord
                </span>
             
           </div>
           <!-- /.info-box-content -->
         </div>
         <!-- /.info-box -->
       </div>

       <div class="col-xl-4 col-md-4 col-12">
        <div class="info-box bg-purple">
          <a href="{{route('complaintClosedList')}}">
            <span class="info-box-icon push-bottom"><i class="material-icons extra">compare</i></span></a>
            <div class="info-box-content">
              <span class="info-box-number">{{$maintenanceCoordinator['completedCasesCount']}}</span>
              <span class="info-box-text">Cases  </span>
              <div class="progress">
                <div class="progress-bar width-60"></div>
              </div>
              <span class="progress-description">
                Completed
              </span>
            </div>
            <!-- /.info-box-content -->
          </div>
          <!-- /.info-box -->
        </div>
      </div>
    </div>

    <!-- end widget -->
    <script>
      $(document).ready(function() {
       /*******Assigned Complaints – Open – More than 24 hrs./48 hrs./All*****/
       $('#time').on('change', function(e) {   
         var time = $('#time').val();
         var href =   "{{ route('complaintAssignedList')}}";
         $.ajax({
           type: "GET",
           url: "{!!URL::route('openAssignedComplaintSupervisor')!!}",
           data:'time='+ $('#time').val(),
           success: function(data){
            var result = $.parseJSON(data);
            $('#open_assigned_supervisor').text(result[0].count);
            href = href+'?time='+time;
            $(".link1").attr('href', href);
          }
        });
       });
       /*******Assigned Complaints – Open – More than 24 hrs./48 hrs./All*****/
       var time = 24;
       var href =   "{{ route('complaintAssignedList',['time' => '24'])}}";
       $.ajax({
         type: "GET",
         url: "{!!URL::route('openAssignedComplaintSupervisor')!!}",
         data:'time='+ $('#time').val(),
         success: function(data){
          var result = $.parseJSON(data);
          $('#open_assigned_supervisor').text(result[0].count);
          href = href.replace(':time', time);
           // alert( href);
           $(".link1").attr('href', href);
         }
       });
       /*******Assigned Complaints – Open – More than 24 hrs./48 hrs./All*****/

       /****Ticket with Sub contractors – Open cases – more than 1 day/3 days/5 days/All -***/

       $('#sub_open_days').on('change', function(e) {   
         var sub_open_days = $('#sub_open_days').val();
         var href1 =   "{{ route('complaintAssignedList')}}";
         $.ajax({
           type: "GET",
           url: "{!!URL::route('openAssignedComplaintSubcontractor')!!}",
           data:'sub_open_days='+ $('#sub_open_days').val(),
           success: function(data){
            var result = $.parseJSON(data);
            $('#open_assigned_subcontractor').text(result[0].count);
            href1 = href1+'?sub_open_days='+sub_open_days;
            $(".link2").attr('href', href1);
          }
        });
       });
       /*******Assigned Complaints – Open – More than 24 hrs./48 hrs./All*****/
       var sub_open_days = 1;
       var href1 =   "{{ route('complaintAssignedList',['sub_open_days' => '1'])}}";
       $.ajax({
         type: "GET",
         url: "{!!URL::route('openAssignedComplaintSubcontractor')!!}",
         data:'sub_open_days='+ $('#sub_open_days').val(),
         success: function(data){
          var result = $.parseJSON(data);
          $('#open_assigned_subcontractor').text(result[0].count);
          href1 = href1.replace(':sub_open_days', sub_open_days);
           // alert( href);
           $(".link2").attr('href', href1);
         }
       });

       /****Ticket with Sub contractors – Open cases – more than 1 day/3 days/5 days/All -***/

       
       /****Ticket waiting landlord approval –Open cases more than 7 days/15 days/All-***/

       $('#review_days').on('change', function(e) {   
         var review_days = $('#review_days').val();
         var href2 =   "{{ route('complaintReviewList')}}";
         $.ajax({
           type: "GET",
           url: "{!!URL::route('complaintReviewCount')!!}",
           data:'review_days='+ $('#review_days').val(),
           success: function(data){
            var result = $.parseJSON(data);
            $('#land_review').text(result[0].count);
            href2 = href2+'?review_days='+review_days;
            $(".link3").attr('href', href2);
          }
        });
       });
       /*******Ticket waiting landlord approval –Open cases more than 7 days/15 days/All*****/
       var review_days = 1;
       var href2 =   "{{ route('complaintReviewList',['review_days' => '7'])}}";
       $.ajax({
         type: "GET",
         url: "{!!URL::route('complaintReviewCount')!!}",
         data:'review_days='+ $('#review_days').val(),
         success: function(data){
          var result = $.parseJSON(data);
          $('#land_review').text(result[0].count);
          href2 = href2.replace(':review_days', review_days);
           // alert( href);
           $(".link3").attr('href', href2);
         }
       });

       /****Ticket waiting landlord approval –Open cases more than 7 days/15 days/All***/
     });
   </script>



