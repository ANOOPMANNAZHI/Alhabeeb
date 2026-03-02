                  @if(count(\Auth::user()->getRoleNames()) == 1)
                  <div class="page-bar">
                    <div class="page-title-breadcrumb">
                      <div class=" pull-left">
                        <div class="page-title">Take over Coordinator - Dashboard</div>
                      </div>
                      {{ Breadcrumbs::render('take-over-coordinator-dashboard')}}  
                    </div>
                  </div>
                  @endif
                  <!-- start widget -->
                   <div class="state-overview">
                    <div class="row">
                     <div class="col-xl-4 col-md-4 col-12">
                       <div class="info-box bg-blue">
                         <a href="{{route('handoverUnassigned')}}">
                           <span class="info-box-icon push-bottom"><i class="material-icons extra">style</i></span>
                         </a>
                         <div class="info-box-content">
                           <span class="info-box-number">{{$takeOverSupervisor['handoverUnassignedCount']}}</span>
                           <span class="info-box-text">Take Over Requests</span>
                           <div class="progress">
                             <div class="progress-bar width-60"></div>
                           </div>
                           <span class="progress-description">
                            Received From BackOffice
                          </span>
                        </div>
                        <!-- /.info-box-content -->
                      </div>
                      <!-- /.info-box -->
                    </div>
                    <!-- /.col -->
                    <div class="col-xl-4 col-md-4 col-12">
                     <div class="info-box bg-warning">
                      <a href="{{route('keyManagement.index',['params'=>'takenover'])}}">
                       <span class="info-box-icon push-bottom"><i class="material-icons extra">card_travel</i></span>
                       </a>
                       <div class="info-box-content">
                         <span class="info-box-number">{{$takeOverSupervisor['takeoverTeamCount']}}</span>
                         <span class="info-box-text">Key Pending</span>
                         <div class="progress">
                          <div class="progress-bar width-60"></div>
                        </div>
                        <span class="progress-description">
                         Taken Over Less Acknowledged By Maintenance
                       </span>
                     </div>
                     <!-- /.info-box-content -->
                   </div>
                   <!-- /.info-box -->
                 </div>
                 <!-- /.col -->

                 <div class="col-xl-4 col-md-4 col-12">
                  <div class="info-box bg-b-danger">
                  <a href="{{route('handoverAssigned')}}">
                    <span class="info-box-icon push-bottom"><i class="material-icons extra">assignment_late</i></span>
                    </a>
                    <div class="info-box-content">
                      <span class="info-box-number">{{$takeOverSupervisor['handoverAssignedCount']}}</span>
                      <span class="info-box-text">Assigned</span>
                      <div class="progress">
                       <div class="progress-bar width-60"></div>
                     </div>
                     <span class="progress-description">
                      Handover Assigned
                    </span>
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


