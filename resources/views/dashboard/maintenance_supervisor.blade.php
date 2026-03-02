                  @if(count(\Auth::user()->getRoleNames()) == 1)
                  <div class="page-bar">
                    <div class="page-title-breadcrumb">
                      <div class=" pull-left">
                        <div class="page-title">Maintenance Supervisor - Dashboard</div>
                      </div>
                      {{ Breadcrumbs::render('maintenance-supervisor-dashboard')}}  
                    </div>
                  </div>
                  @endif
                  <!-- start widget -->
                  <div class="state-overview">
                    <div class="row">
                     <div class="col-xl-4 col-md-4 col-12">
                      <div class="info-box bg-blue">
                       <a href="{{route('complaint.index',['close'=>2])}}">
                         <span class="info-box-icon push-bottom"><i class="material-icons extra">style</i></span>
                       </a>
                       <div class="info-box-content">
                         <span class="info-box-number">{{$maintenanceSupervisor['assignedComplaintCount']}}</span>
                         <span class="info-box-text">Complaints Assigned To </span>
                         <div class="progress">
                           <div class="progress-bar width-60"></div>
                         </div>
                         <span class="progress-description">
                          Maintenance Supervisor
                        </span>
                      </div>
                      <!-- /.info-box-content -->
                    </div>
                    <!-- /.info-box -->
                  </div>
                  <!-- /.col -->
                  <div class="col-xl-4 col-md-4 col-12">
                   <div class="info-box bg-warning">
                     <a href="{{route('complaintSubAssignedList',['time'=>24])}}">
                       <span class="info-box-icon push-bottom"><i class="material-icons extra">card_travel</i></span>
                     </a>
                     <div class="info-box-content">
                       <span class="info-box-number">{{$maintenanceSupervisor['assignedTechnicianCount']}}</span>
                       <span class="info-box-text">Complaints Assigned To Technician</span>
                       <div class="progress">
                        <div class="progress-bar width-60"></div>
                      </div>
                      <span class="progress-description">
                        Pending For More Than 24 Hours
                      </span>
                    </div>
                    <!-- /.info-box-content -->
                  </div>
                  <!-- /.info-box -->
                </div>
                <!-- /.col -->
              </div>
            </div>


