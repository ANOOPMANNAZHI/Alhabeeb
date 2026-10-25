                  @if(count(\Auth::user()->getRoleNames()) == 1)
                    <div class="page-bar">
                        <div class="page-title-breadcrumb">
                            <div class=" pull-left">
                                <div class="page-title">Sales Person - Dashboard</div>
                            </div>
                              {{ Breadcrumbs::render('sales-person-dashboard')}}
                        </div>
                    </div>
                     @endif
                   <!-- start widget -->
                   {{-- Card order: row 1 bounces + units to lease, row 2 enquiries
                        to attend + PDC follow-up, row 3 approvals and won.
                        Every card has number, title, bar and a description line, and
                        the links sit inside the card (not around it) so .info-box keeps
                        its full height and all cards in a row line up. --}}
					<div class="state-overview mb-4">
						<div class="row">

                  {{-- ===== Row 1 ===== --}}
                  {{-- new tenant bounced cheques (first contract), highlighted when there are any --}}
                  <div class="col-xl-4 col-md-6 col-12">
                    <div class="info-box {{ $salesPerson['newTenantBounceCount'] > 0 ? 'bg-danger' : 'bg-success' }}">
                      <a href="{{ route('getBouncedCheques') }}" title="Open bounced cheques of new tenants">
                      <span class="info-box-icon push-bottom"><i class="material-icons">money_off</i></span>
                      </a>
                      <div class="info-box-content">
                        <a href="{{ route('getBouncedCheques') }}" title="Open bounced cheques of new tenants">
                        <span class="info-box-number">{{ $salesPerson['newTenantBounceCount'] }}</span>
                        <span class="info-box-text">New Tenant Bounces</span>
                        <div class="progress"><div class="progress-bar width-60"></div></div>
                        </a>
                        <span class="progress-description">{{ $salesPerson['newTenantBounceCount'] > 0 ? 'Cheques bounced on a first contract' : 'No open bounces' }}</span>
                      </div>
                    </div>
                  </div>

                  {{-- units in their sold buildings whose tenant has a termination date in the next 30/60/90 days --}}
                  <div class="col-xl-4 col-md-6 col-12">
                    <div class="info-box bg-orange">
                      <a class="vacating_sp_href" href="{{route('unit.index').'?vaccating_days=30&sold_buildings=1'}}">
                      <span class="info-box-icon push-bottom"><i class="material-icons">exit_to_app</i></span>
                      </a>
                      <div class="info-box-content">
                        <a class="vacating_sp_href" href="{{route('unit.index').'?vaccating_days=30&sold_buildings=1'}}">
                        <span class="info-box-number vacating_sp_count">{{$salesPerson['vacatingUnits'][30]}}</span>
                        <span class="info-box-text">Vacating Units</span>
                        <div class="progress"><div class="progress-bar width-60"></div></div>
                        </a>
                        <span class="progress-description">
                          Next
                          <select id="vacating_sp">
                            @foreach($salesPerson['vacatingUnits'] as $days => $vacatingCount)
                            <option value="{{$days}}" data-count="{{$vacatingCount}}">{{$days}}</option>
                            @endforeach
                          </select>
                          Days.
                        </span>
                      </div>
                    </div>
                  </div>

                  <div class="col-xl-4 col-md-6 col-12">
                    <div class="info-box bg-mint">
                      <a href="{{route('unit.index').'?unit_vaccant_status=0&unit_status=1&sold_buildings=1'}}">
                      <span class="info-box-icon push-bottom"><i class="material-icons">hourglass_empty</i></span>
                      </a>
                      <div class="info-box-content">
                        <a href="{{route('unit.index').'?unit_vaccant_status=0&unit_status=1&sold_buildings=1'}}">
                        <span class="info-box-number">{{$salesPerson['vacantUnits']}}</span>
                        <span class="info-box-text">Vacant Units</span>
                        <div class="progress"><div class="progress-bar width-60"></div></div>
                        </a>
                        <span class="progress-description">Empty units in your buildings</span>
                      </div>
                    </div>
                  </div>

                  {{-- ===== Row 2 ===== --}}
							<div class="col-xl-4 col-md-6 col-12">
                              <div class="info-box bg-danger">
                                <a class="unattenedEnquiry_sp_href" href="{{route('leadAssign.assignedList').'?count_by_day=3'}}" >
                                <span class="info-box-icon push-bottom"><i class="material-icons">remove_circle_outline</i></span>
                                </a>
                                <div class="info-box-content">
                                  <a class="unattenedEnquiry_sp_href" href="{{route('leadAssign.assignedList').'?count_by_day=3'}}" >
                                    <span class="info-box-number unattenedEnquiry_sp_count">{{$salesPerson['unattened_list']}}</span>
                                    <span class="info-box-text">Unattended Enquiry</span>
									<div class="progress"><div class="progress-bar width-60"></div></div>
                                  </a>
                                   <span class="progress-description">
                                    Last
                                    <input type="hidden" value="unattend" class="enitity_count">
                                    <select name="unattened_enquiry"  id="unattenedEnquiry_sp" class="select_count_sp">
                                      <option value="3"> 3 </option>
                                      <option value="7"> 7 </option>
                                      <option value="all"> All </option>
                                    </select>
                                     Days.
                                    </span>
                                </div>
                              </div>
                            </div>

					        <div class="col-xl-4 col-md-6 col-12">
					          <div class="info-box bg-blue">
                                <a class="inprogressEnquiry_sp_href" href="{{route('inprogressList').'?count_within=>_7&firstCall=self'}}" >
					            <span class="info-box-icon push-bottom"><i class="material-icons">person_pin</i></span>
                                </a>
					            <div class="info-box-content">
                                <a class="inprogressEnquiry_sp_href" href="{{route('inprogressList').'?count_within=>_7&firstCall=self'}}" >
                                  <span class="info-box-number inprogressEnquiry_sp_count">{{$salesPerson['inprogress_list']}}</span>
                                  <span class="info-box-text">In Progress</span>
					              <div class="progress"><div class="progress-bar width-60"></div></div>
                                 </a>
					              <span class="progress-description">
                                   <select name="inprogressEnquiry" class="select_count_sp" id ="inprogressEnquiry_sp">
                                      <option value=">_7"> < 7  </option>
                                      <option value="<=_7"> >= 7 </option>
                                      <option value="all"> All </option>
                                    </select>
                                    <select name="inprogressEnquiryFirstCall_sp" class="firstCall" id ="inprogressEnquiryFirstCall_sp">
                                      <option value="self">FirstCall Self</option>
                                      <option value="other">Others </option>
                                    </select>
                                    </span>
					            </div>
					          </div>
					        </div>

                  <div class="col-xl-4 col-md-6 col-12">
                    <div class="info-box bg-info">
                      <a href="{{route('tenant-contract.index').'?pdc_check=2&tenant_contract_status=1'}}">
                      <span class="info-box-icon push-bottom"><i class="material-icons">assignment_late</i></span>
                      </a>
                      <div class="info-box-content">
                        <a href="{{route('tenant-contract.index').'?pdc_check=2&tenant_contract_status=1'}}">
                        <span class="info-box-number">{{$salesPerson['partial_pdc']}}</span>
                        <span class="info-box-text">Partial PDC Collection</span>
                        <div class="progress"><div class="progress-bar width-60"></div></div>
                        </a>
                        <span class="progress-description">Active contracts, PDC not entered</span>
                      </div>
                    </div>
                  </div>

                  {{-- ===== Row 3 ===== --}}
            <div class="col-xl-4 col-md-6 col-12">
              <div class="info-box bg-orange">
                <a class="preapprovalEnquiry_sp_href" href="{{route('preliminaryApprovalList')}}" >
                <span class="info-box-icon push-bottom"><i class="material-icons">trending_up</i></span>
                </a>
                <div class="info-box-content">
                  <a class="preapprovalEnquiry_sp_href" href="{{route('preliminaryApprovalList')}}" >
                  <span class="info-box-number" id="preapproval_enquiry_count">{{$salesPerson['preapprovalEnquiry']}}</span>
                  <span class="info-box-text">Pre-Approval</span>
                  <div class="progress"><div class="progress-bar width-60"></div></div>
                  </a>
                  <span class="progress-description">Preliminary documentation</span>
                </div>
              </div>
            </div>

           <div class="col-xl-4 col-md-6 col-12">
             <div class="info-box bg-purple">
               <a href="{{route('finalDocumentationList')}}">
               <span class="info-box-icon push-bottom"><i class="material-icons">visibility</i></span>
               </a>
               <div class="info-box-content">
                 <a href="{{route('finalDocumentationList')}}">
                 <span class="info-box-number">{{$salesPerson['approvedEnquiry']}}</span>
                 <span class="info-box-text">Approved by HOD</span>
                 <div class="progress"><div class="progress-bar width-60"></div></div>
                 </a>
                 <span class="progress-description">Ready for final documentation</span>
               </div>
             </div>
           </div>

                  <div class="col-xl-4 col-md-6 col-12">
                    <div class="info-box bg-success">
                      <a class="wonEnquiry_sp_href" href="{{route('wonList').'?count_by_month='.date('m')}}">
                      <span class="info-box-icon push-bottom"><i class="material-icons">grade</i></span>
                      </a>
                      <div class="info-box-content">
                        <a class="wonEnquiry_sp_href" href="{{route('wonList').'?count_by_month='.date('m')}}">
                        <span class="info-box-number wonEnquiry_sp_count">{{$salesPerson['wonEnquiry']}}</span>
                        <span class="info-box-text">Won</span>
                        <div class="progress"><div class="progress-bar width-60"></div></div>
                        </a>
                        <span class="progress-description">
                            <input type="hidden" value="unattend" class="enitity_count">
                            <select name="wonEnquiry_enquiry"  id="wonEnquiry_sp" class="select_count_sp">
                             @for($m = 1; $m <=12 ; $m++)
                                <option {{(date('m') == $m)? 'selected':''}} value="{{$m}}">{{date('F', mktime(0, 0, 0, $m, 1))}} </option>
                             @endfor
                            </select>
                        </span>
                      </div>
                    </div>
                  </div>

                  {{-- ===== Row 4 ===== --}}
                  <div class="col-xl-4 col-md-6 col-12">
                  <div class="info-box bg-b-danger">
                    <a href="{{route('handoverAssignedSalesPersonView')}}">
                    <span class="info-box-icon push-bottom"><i class="material-icons extra">assignment_late</i></span>
                    </a>
                    <div class="info-box-content">
                      <a href="{{route('handoverAssignedSalesPersonView')}}">
                      <span class="info-box-number">{{$salesPerson['handoverAssignedCount']}}</span>
                      <span class="info-box-text">Assigned</span>
                      <div class="progress"><div class="progress-bar width-60"></div></div>
                      </a>
                      <span class="progress-description">Handover Assigned</span>
                    </div>
                  </div>
                </div>

					      </div>
						</div>
					<!-- end widget -->
<!-- sales lead window-->



@push('dashboard_scripts')

@include('dashboard.sales_person-js')


@endpush
