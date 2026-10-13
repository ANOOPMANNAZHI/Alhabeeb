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
              <!-- my legal cases (plan 2026-10-13, phase 2; design-system/plms-legal) -->
              @php $lcMine = \Modules\Masters\Services\LegalCase::lawyerSummary(\Auth::id()); @endphp
              <link href="{{ asset('public/css/legal.css') }}" rel="stylesheet">
              <div class="lg" style="margin-top:8px;">
                <div class="lg-grid" style="grid-template-columns:minmax(0,1fr) minmax(0,1.4fr);">
                  <section class="lg-card" aria-labelledby="lgd_stage">
                    <div class="lg-card__head"><h2 class="lg-card__title" id="lgd_stage">My active cases <span class="lg-count">{{ $lcMine['total'] }}</span></h2>
                      <a class="lg-btn lg-btn--ghost lg-btn--sm" href="{{ route('activeCases', ['lc_lawyer' => \Auth::id()]) }}">All</a></div>
                    <div class="lg-table-wrap"><table class="lg-table">
                      <thead><tr><th scope="col">Stage</th><th scope="col" class="num">Cases</th></tr></thead>
                      <tbody>
                      @forelse($lcMine['byStage'] as $row)
                        <tr><td><a class="lg-row-link" href="{{ route('activeCases', ['lc_stage' => $row->id ?: 'none', 'lc_lawyer' => \Auth::id()]) }}">{{ $row->name }}</a></td><td class="num">{{ $row->n }}</td></tr>
                      @empty
                        <tr><td colspan="2"><div class="lg-empty">No active cases assigned to you.</div></td></tr>
                      @endforelse
                      </tbody></table></div>
                  </section>
                  <section class="lg-card" aria-labelledby="lgd_hearings">
                    <div class="lg-card__head"><h2 class="lg-card__title" id="lgd_hearings">Hearings in the next 14 days <span class="lg-count">{{ count($lcMine['hearings']) }}</span></h2></div>
                    <div class="lg-table-wrap"><table class="lg-table">
                      <thead><tr><th scope="col">Date</th><th scope="col">Case</th><th scope="col">Building</th><th scope="col">Court case</th></tr></thead>
                      <tbody>
                      @forelse($lcMine['hearings'] as $h)
                        <tr><td class="tnum">{{ \Carbon\Carbon::parse($h->next_hearing_date)->format('d/m/Y') }}</td>
                          <td><a class="lg-row-link" href="{{ route('legal.case', $h->id) }}">{{ $h->tenant_contract_no }}</a><div class="muted" style="font-size:12px;">{{ $h->tenant_name }}</div></td>
                          <td>{{ $h->building_name }}</td><td>{{ $h->court_case_no }}</td></tr>
                      @empty
                        <tr><td colspan="4"><div class="lg-empty">No hearings in the next 14 days.</div></td></tr>
                      @endforelse
                      </tbody></table></div>
                  </section>
                </div>
              </div>
