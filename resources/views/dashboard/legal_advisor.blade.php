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
              <!-- my legal cases (plan 2026-10-13, phase 2) -->
              @php $lcMine = \Modules\Masters\Services\LegalCase::lawyerSummary(\Auth::id()); @endphp
              <div class="row">
                <div class="col-lg-5 col-md-12">
                  <div class="card card-box">
                    <div class="card-head"><header>My active cases by stage ({{ $lcMine['total'] }})</header></div>
                    <div class="card-body">
                      <table class="table table-sm">
                        @forelse($lcMine['byStage'] as $row)
                          <tr><td><a href="{{ route('activeCases', ['lc_stage' => $row->id ?: 'none', 'lc_lawyer' => \Auth::id()]) }}">{{ $row->name }}</a></td><td class="text-right"><b>{{ $row->n }}</b></td></tr>
                        @empty
                          <tr><td class="text-muted">No active cases assigned to you.</td></tr>
                        @endforelse
                      </table>
                    </div>
                  </div>
                </div>
                <div class="col-lg-7 col-md-12">
                  <div class="card card-box">
                    <div class="card-head"><header>Hearings in the next 14 days</header></div>
                    <div class="card-body">
                      <table class="table table-sm">
                        @forelse($lcMine['hearings'] as $h)
                          <tr>
                            <td>{{ \Carbon\Carbon::parse($h->next_hearing_date)->format('d/m/Y') }}</td>
                            <td><a href="{{ route('legal.case', $h->id) }}">{{ $h->tenant_contract_no }}</a> &middot; {{ $h->tenant_name }}</td>
                            <td>{{ $h->building_name }}</td>
                            <td>{{ $h->court_case_no }}</td>
                          </tr>
                        @empty
                          <tr><td class="text-muted">No hearings in the next 14 days.</td></tr>
                        @endforelse
                      </table>
                    </div>
                  </div>
                </div>
              </div>
              
              
              
              