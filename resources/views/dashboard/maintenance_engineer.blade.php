 
@if(count(\Auth::user()->getRoleNames()) == 1)
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">Maintenance Engineer - Dashboard</div>
    </div>
    {{ Breadcrumbs::render('maintenance-engineer-dashboard')}}  
  </div>
</div>
@endif
<!-- start widget -->
<div class="state-overview mb-4">
  <div class="row">
    <div class="col-xl-4 col-md-4 col-12">
      <a href="{{route('leadAssign.index')}}" >
        <div class="info-box bg-blue">
          <a class="link" href="">
          <span class="info-box-icon push-bottom"><i class="material-icons">remove_circle_outline</i></span>
          </a>
          <div class="info-box-content">                                
            <span class="info-box-number"><p class="open_assigned" id="open_assigned"></p></span>
            <span class="info-box-text">Assigned Complaints – Open</span>
            <div class="progress">
              <div class="progress-bar width-60"></div>
            </div>
            <select name="hours"  class="form-control" id="hours" required>
             <option selected value="24">24 Hours</option> 
             <option value="48">48 Hours</option> 
             <option value="all">All</option> 
           </select>
         </div>
         <!-- /.info-box-content -->
       </div>
     </a>

     <!-- /.info-box -->
   </div>
    <!-- /.col -->

    <!-- /.col -->
    <div class="col-xl-4 col-md-4 col-12">
      <div class="info-box bg-orange">
        <a href="{{route('complaintAssignedList',['vip_status'=>2])}}">
          <span class="info-box-icon push-bottom"><i class="material-icons">person_pin</i></span>
        </a>
        <div class="info-box-content">
         <span class="info-box-number">{{$maintenanceEngineer['vipOpenAssignedCount']}}</span>
         <span class="info-box-text">VIP</span>
         <div class="progress">
          <div class="progress-bar width-60"></div>
        </div>
        <span class="progress-description">
         Open Complaints
       </span>
     </div>

   </div>

 </div>
 <!-- /.col -->
 <div class="col-xl-4 col-md-4 col-12">
    <div class="info-box bg-purple">
  <a href="{{route('keyManagement.index',['params'=>'takenover'])}}">
      <span class="info-box-icon push-bottom"><i class="material-icons">trending_up</i></span>
      </a>
      <div class="info-box-content">              
        <span class="info-box-number">{{$maintenanceEngineer['takeoverTeamCount']}}</span>
        <span class="info-box-text">Keys Pending</span>
        <div class="progress">
          <div class="progress-bar width-40"></div>
        </div>
        <span class="progress-description">
          With Takeover Team
        </span>
      </div>
      <!-- /.info-box-content -->
    </div>

  <!-- /.info-box -->
</div>
<!-- /.col -->
</div>
<div class="row">
<div class="col-xl-4 col-md-4 col-12">
   <div class="info-box bg-watermelon">
   <a href="{{route('keyManagement.index',['param'=>'engineer'])}}">
     <span class="info-box-icon push-bottom infoBoxSmall"><i class="material-icons">cloud_upload</i></span>
     </a>
     <div class="info-box-content">                       
       <span class="info-box-number">{{$maintenanceEngineer['maintenanceEngineerCount']}}</span>
       <span class="info-box-text">Keys </span>
       <div class="progress">
        <div class="progress-bar width-60"></div>
      </div>
      <span class="progress-description">
       To Be Handed Over To Sales Team
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
 <a href="{{route('complaintReviewList',['complaint_assign_status'=>5])}}">
   <span class="info-box-icon push-bottom infoBoxSmall"><i class="material-icons">grade</i></span>
   </a>
   <div class="info-box-content">
    <span class="info-box-number">{{$maintenanceEngineer['landlordApprovalPendingCount']}}</span>
    <span class="info-box-text">Landlord Approval Pending</span>
    <div class="progress">
      <div class="progress-bar width-60"></div>
    </div>
    <span class="progress-description">
   <br>
    </span>
  </div>
</div>                   
</div>
<!-- /.col -->        
</div>
</div>
<!-- end widget -->
<!-- sales lead window-->
<script>
      $(document).ready(function() {
       /*******Assigned Complaints – Open – More than 24 hrs./48 hrs./All*****/
       $('#hours').on('change', function(e) {   
         var hours = $('#hours').val();
         var href =   "{{ route('complaintAssignedList')}}";
         $.ajax({
           type: "GET",
           url: "{!!URL::route('openAssignedComplaint')!!}",
           data:'hours='+ $('#hours').val(),
           success: function(data){
            var result = $.parseJSON(data);
            $('#open_assigned').text(result[0].count);
            href = href+'?hours='+hours;
            $(".link").attr('href', href);
          }
        });
       });
       /*******Assigned Complaints – Open – More than 24 hrs./48 hrs./All*****/
       var hours = 24;
         var href =   "{{ route('complaintAssignedList',['hours' => '24'])}}";
         $.ajax({
           type: "GET",
           url: "{!!URL::route('openAssignedComplaint')!!}",
           data:'hours='+ $('#hours').val(),
           success: function(data){
            var result = $.parseJSON(data);
            $('#open_assigned').text(result[0].count);
            href = href.replace(':hours', hours);
           // alert( href);
            $(".link").attr('href', href);
          }
        });
       /*******Assigned Complaints – Open – More than 24 hrs./48 hrs./All*****/
       });
       </script>




