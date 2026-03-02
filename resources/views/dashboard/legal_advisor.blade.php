                  @if(count(\Auth::user()->getRoleNames()) == 1)
                  <div class="page-bar">
                    <div class="page-title-breadcrumb">
                      <div class=" pull-left">
                        <div class="page-title">Legal Advisor - Dashboard</div>
                      </div>
                      {{ Breadcrumbs::render('legal-advisor-dashboard')}}  
                    </div>
                  </div>
                  @endif
                  <!-- start widget -->
                  <div class="state-overview">
                    <div class="row">
                      <div class="col-xl-4 col-md-4 col-12">
                       <a href="{{route('lawyerApproval')}}"> <div class="info-box bg-blue">
                        <span class="info-box-icon push-bottom"><i class="material-icons extra">style</i></span>
                        <div class="info-box-content">
                          <span class="info-box-number">{{$legalAdvisor['openCasesCount']}}</span>
                          <span class="info-box-text">Open Cases Assigned </span>
                          <div class="progress">
                            <div class="progress-bar width-60"></div>
                          </div>
                          <span class="progress-description">
                            To Legal Advisor 
                          </span>
                        </div>
                        <!-- /.info-box-content -->
                      </div></a>
                      <!-- /.info-box -->
                    </div>
                    <!-- /.col -->
                    <div class="col-xl-4 col-md-4 col-12">
                      <a href="{{ route('closedLegalCases')}}">
                        <div class="info-box bg-warning">
                          <span class="info-box-icon push-bottom"><i class="material-icons extra">card_travel</i></span>
                          <div class="info-box-content">
                            <span class="info-box-number">{{$legalAdvisor['closedCasesCount']}}</span>
                            <span class="info-box-text">Closed </span>
                            <div class="progress">
                              <div class="progress-bar width-60"></div>
                            </div>
                            <span class="progress-description">
                              Cases 
                            </span>
                            
                          </div>
                          <!-- /.info-box-content -->
                        </div>
                      </a>
                      <!-- /.info-box -->
                    </div>
                    <!-- /.col -->
                    
                    <!-- /.col -->
                  <div class="col-xl-4 col-md-4 col-12">
                      <a href="{{ route('referBackCases',['param'=>'param'])}}">
                        <div class="info-box bg-success">
                          <span class="info-box-icon push-bottom"><i class="material-icons extra">monetization_on</i></span>
                          <div class="info-box-content">
                            <span class="info-box-number">{{$legalAdvisor['referBackCasesCount']}}</span>
                            <span class="info-box-text">Refer Back </span>
                            <div class="progress">
                             <div class="progress-bar width-60"></div>
                           </div>
                           <span class="progress-description">
                            Cases
                          </span>
                        </div>
                        <!-- /.info-box-content -->
                      </div>
                    </a>
                    <!-- /.info-box -->
                  </div>

                </div>
                <!-- /rwo wnd -->
              </div>
              <!-- end widget -->
              
              
              
              