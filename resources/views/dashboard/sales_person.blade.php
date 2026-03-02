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
					<div class="state-overview mb-4">
						<div class="row">
							<div class="col-xl-4 col-md-6 col-12">
                              <div class="info-box bg-danger">
                             
                                <a class="unattenedEnquiry_sp_href" href="{{route('leadAssign.assignedList').'?count_by_day=3'}}" >
                                <span class="info-box-icon push-bottom"><i class="material-icons">remove_circle_outline</i></span>
                                </a>

                                <div class="info-box-content">
                                 
                                  <a class="unattenedEnquiry_sp_href" href="{{route('leadAssign.assignedList').'?count_by_day=3'}}" >
                                    <span class="info-box-number unattenedEnquiry_sp_count">
                                      {{$salesPerson['unattened_list']}}  
                                    </span>
                                    <span class="info-box-text">
									Unattended Enquiry
									</span>
									<div class="progress">
										<div class="progress-bar width-60"></div>
                                    </div>
                                  </a>

                                   <span class="progress-description">
                                    Last 
                                    <input type="hidden" value="unattend" class="enitity_count">
                                    <select name="unattened_enquiry"  id="unattenedEnquiry_sp" class="select_count_sp   ">          
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
					            <div class="info-box-content" style="margin-left: 78px !important;">					 

                                <a class="inprogressEnquiry_sp_href" href="{{route('inprogressList').'?count_within=>_7&firstCall=self'}}" > 
                                    <span class="info-box-number  inprogressEnquiry_sp_count">
                                     {{$salesPerson['inprogress_list']}}  </span>
                                  <span class="info-box-text">In Progress</span>
					              <div class="progress">
					                <div class="progress-bar width-60"></div>
					              </div>
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
					            <!-- /.info-box-content -->
					          </div>
					          <!-- /.info-box -->
					        </div>
					        <!-- /.col -->
					        
					        <!-- /.col -->
                            


                                <div class="col-xl-4 col-md-4 col-12">
                              <div class="info-box bg-success">
                      <a class="wonEnquiry_sp_href"  href="{{route('wonList').'?count_by_month='.date('m')}}">
                                <span class="info-box-icon push-bottom infoBoxSmall"><i class="material-icons">grade</i></span>
                      </a>
                      
                                <div class="info-box-content">

                        <a class="wonEnquiry_sp_href"  href="{{route('wonList').'?count_by_month='.date('m')}}">                               
                                  <span class="info-box-number wonEnquiry_sp_count">{{$salesPerson['wonEnquiry']}}</span>
                        <span class="info-box-text">Won </span>
                        </a>

                         <div class="progress">
                          <div class="progress-bar width-40"></div>
                        </div>


                        <span class="progress-description">
                         
                            <input type="hidden" value="unattend" class="enitity_count">
                            <select name="wonEnquiry_enquiry"  id="wonEnquiry_sp" class="select_count_sp ">                                
                             @for($m = 1; $m <=12 ; $m++)              
                                <option {{(date('m') == $m)? 'selected':''}} value="{{$m}}">{{date('F', mktime(0, 0, 0, $m, 1))}} </option>                               
                             @endfor   
                            </select>        
                              
                        </span>
                       </div>
                                <!-- /.info-box-content -->
                              </div>
                              <!-- /.info-box -->
                            </div>

	        <!-- /.col -->
            <div class="col-xl-4 col-md-6 col-12">
             <a class="preapprovalEnquiry_sp_href" href="{{route('preliminaryApprovalList')}}" >
              <div class="info-box bg-orange">
                <span class="info-box-icon push-bottom infoBoxSmall">
                    <i class="material-icons">trending_up</i>
                </span>
                <div class="info-box-content">              
                  <span class="info-box-number" id="preapproval_enquiry_count"> {{$salesPerson['preapprovalEnquiry']}}</span>
                  <span class="info-box-text">Pre-Approval</span>                 
                </div>                             
                <!-- /.info-box-content -->
              </div>  </a>
              <!-- /.info-box -->
            </div>




           <div class="col-xl-4 col-md-4 col-12">
             <a href="{{route('finalDocumentationList')}}">
               <div class="info-box bg-purple">
                      <span class="info-box-icon push-bottom infoBoxSmall"><i class="material-icons">visibility</i></span>
                      <div class="info-box-content">
                         <span class="info-box-number">{{$salesPerson['approvedEnquiry']}}</span>
                          <span class="info-box-text">Approved by HOD</span>
      
                      </div>
                     
                    </div>
              </a>
           </div>
                        




<!-- /.col -->
                  <div class="col-xl-4 col-md-4 col-12">
                    <a href="{{route('unit.index').'?unit_vaccant_status=0&unit_status=1'}}">
                    <div class="info-box bg-mint">
                      <span class="info-box-icon push-bottom infoBoxSmall"><i class="material-icons">hourglass_empty</i></span>
                      <div class="info-box-content">
                         <span class="info-box-number">{{$salesPerson['vacantUnits']}}</span>
                                  <span class="info-box-text">Vacant Units</span>
      
                      </div>
                     
                    </div>
                   </a>
                  </div>
                  <!-- /.col -->




                   <!-- /.col -->
                  <div class="col-xl-4 col-md-4 col-12">
                    <a href="{{route('tenant-contract.index').'?pdc_check=2&tenant_contract_status=1'}}">
                    <div class="info-box bg-info">
                      <span class="info-box-icon push-bottom infoBoxSmall"><i class="material-icons">assignment_late</i></span>
                      <div class="info-box-content">
                         <span class="info-box-number">{{$salesPerson['partial_pdc']}}</span>
                         <span class="info-box-text">Partial PDC Collection </span>      
                      </div>                     
                    </div>   
                    </a>                
                  </div>
                  <!-- /.col --> 
                  <div class="col-xl-4 col-md-4 col-12">
                  <div class="info-box bg-b-danger">
                  <a href="{{route('handoverAssignedSalesPersonView')}}">
                    <span class="info-box-icon push-bottom"><i class="material-icons extra">assignment_late</i></span>
                    </a>
                    <div class="info-box-content">
                      <span class="info-box-number">{{$salesPerson['handoverAssignedCount']}}</span>
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
						</div>
					<!-- end widget -->
<!-- sales lead window-->



@push('dashboard_scripts')

@include('dashboard.sales_person-js')


@endpush  
