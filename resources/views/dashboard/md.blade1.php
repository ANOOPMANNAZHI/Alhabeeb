<head>
    <meta charset="utf-8">
    <script src="https://www.google.com/jsapi"></script>
    <style>
        .pie-chart {
            width: 600px;
            height: 400px;
            margin: 0 auto;
        }
        .text-center{
            text-align: center; 
        }
    </style>
</head>
  
     @if(count(\Auth::user()->getRoleNames()) == 1)

                    <div class="page-bar">
                        <div class="page-title-breadcrumb">
                            <div class=" pull-left">
                                <div class="page-title">Managing Director - Dashboard</div>
                            </div>
                            <ol class="breadcrumb page-breadcrumb pull-right">
                                <li><i class="fa fa-home"></i>&nbsp;<a class="parent-item" href="index.html">Home</a>&nbsp;<i class="fa fa-angle-right"></i></li>
                                <li><i class="fa fa-home"></i>&nbsp;<a class="parent-item" href="index.html">Dashboard</a>&nbsp;<i class="fa fa-angle-right"></i></li>
                                <li class="active">Managing Director</li>
                            </ol>
                        </div>
                    </div>
                     @endif
         <div class="state-overview">
           <div class="page-title">Sales</div>
             <!-- <div class="row">  
                    <div class="col-md-12 col-sm-12 col-12"> -->
          </div>         
          <div class="state-overview">
            <div class="row">

                <!-- 26-12-2020 starts new updations-->
                 <!-- /.col -->
                  <div class="col-xl-4 col-md-6 col-12">
                    <div class="info-box bg-purple">
                      <a  href="{{ url('building') }}" > 
                      <span class="info-box-icon push-bottom"><i class="material-icons">local_activity</i></span>
                      </a>          
                      <div class="info-box-content">
                                    
                                        
                        <span class="info-box-text">Total count of buildings</span>
                        <span class="info-box-number">{{$managingDirector['totalBuildings']}}</span>
                         
                        <div class="progress">
                          <div class="progress-bar width-80"></div>
                        </div>
                                  
                        <span class="progress-description">
                                    
                        </span>
                      </div>
                                
                      <!-- /.info-box-content -->
                    </div>
                    <!-- /.info-box -->
                  </div>

                  <!-- /.col -->
                   <div class="col-xl-4 col-md-6 col-12">
                    <div class="info-box bg-success">
                      <a  href="{{ route('normalUnits') }}" > 
                      <span class="info-box-icon push-bottom"><i class="material-icons">local_activity</i></span>
                      </a>          
                      <div class="info-box-content">
                                    
                                        
                        <span class="info-box-text">Number Of Units(Normal)</span>
                        <span class="info-box-number">{{$managingDirector['normalUnits']}}</span>
                         
                        <div class="progress">
                          <div class="progress-bar width-80"></div>
                        </div>
                                  
                        <span class="progress-description">
                                    
                        </span>
                      </div>
                                
                      <!-- /.info-box-content -->
                    </div>
                    <!-- /.info-box -->
                  </div>

                  <!-- /.col -->
                   <div class="col-xl-4 col-md-6 col-12">
                    <div class="info-box bg-b-purple">
                      <a  href="{{ route('comprehensiveUnits') }}" > 
                      <span class="info-box-icon push-bottom"><i class="material-icons">local_activity</i></span>
                      </a>          
                      <div class="info-box-content">
                                    
                                        
                        <span class="info-box-text">Number Of Units(Comprehensive)</span>
                        <span class="info-box-number">{{$managingDirector['comprehensiveUnits']}}</span>
                         
                        <div class="progress">
                          <div class="progress-bar width-80"></div>
                        </div>
                                  
                        <span class="progress-description">
                                    
                        </span>
                      </div>
                                
                      <!-- /.info-box-content -->
                    </div>
                    <!-- /.info-box -->
                  </div>

                   <!-- /.col -->

                  

                  <div class="col-xl-4 col-md-4 col-12">
  
                  <div class="info-box bg-danger">
                      <!-- <a class="unassignedEnquiry_sc_href" href="{{route('leadAssign.index').'?count_by_day=1'}}" > -->
                      <a class="unassignedEnquiry_sc_href" href="{{ route('unassignedEnquiry') }}" >
                        <span class="info-box-icon push-bottom"><i class="material-icons">remove_circle_outline</i></span>
                      </a>
                    <div class="info-box-content">  
                     <a class="unassignedEnquiry_sc_href" href="{{route('leadAssign.index').'?count_by_day=1'}}" >   <span class="info-box-text">Unassigned Enquiry</span>                           
                      <span class="info-box-number unassignedEnquiry_sc_count">{{$managingDirector['unassigned_list']}}</span>
                     
                     </a>
                      <div class="progress">
                        <div class="progress-bar width-60"></div>
                      </div>
                       <span class="progress-description">
                      Last 
                      <input type="hidden" value="unattend" class="enitity_count">
                      <select name="unassigned_enquiry"  id="unassignedEnquiry_sc" class="select_count">
                          <option value="1"> 1 </option>
                          <option value="3"> 3 </option>
                          <option value="5"> 5 </option>
                          <option value="all"> All </option>
                      </select>        
                       Days.
                      </span>          
                    </div>
                    <!-- /.info-box-content -->
                  </div>
               

                  <!-- /.info-box -->
                </div>


                  <!-- /.col -->
              <div class="col-xl-4 col-md-4 col-12">
               
                <div class="info-box bg-orange">
                  <a class="assignedEnquiry_sc_href" href="{{route('leadAssign.assignedList').'?count_by_assignday=3'}}">
                  <span class="info-box-icon push-bottom"><i class="material-icons">person_pin</i></span>
                 </a>
                  <div class="info-box-content">
                    <a class="assignedEnquiry_sc_href" href="{{route('leadAssign.assignedList').'?count_by_assignday=3'}}">
                      <span class="info-box-text">Assigned Enquiry</span>
                     <span class="info-box-number  assignedEnquiry_sc_count">{{$managingDirector['assigned_lists']}}</span>
                     
                    </a>
                    <div class="progress">
                      <div class="progress-bar width-60"></div>
                    </div>
                     <span class="progress-description">
                        Last 
                      <input type="hidden" value="unattend" class="enitity_count">
                      <select name="assigned_enquiry"  id="assignedEnquiry_sc" class="select_count   ">               
                          <option value="3"> 3 </option>
                          <option value="5"> 5 </option>
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
                      <a  href="" > 
                      <span class="info-box-icon push-bottom"><i class="material-icons">local_activity</i></span>
                      </a>          
                      <div class="info-box-content">
                                    
                                        
                        <span class="info-box-text">Total Hit Ratio</span>
                        <span class="info-box-number">{{$managingDirector['wonEnquiries']}} / {{$managingDirector['assignedEnquiries']}}  ({{$managingDirector['hitRatio']}}%)</span>
                         
                        <div class="progress">
                          <div class="progress-bar width-80"></div>
                        </div>
                                  
                        <span class="progress-description">
                                    
                        </span>
                      </div>
                                
                      <!-- /.info-box-content -->
                    </div>
                    <!-- /.info-box -->
                  </div>

              </div>
              </div>
              <div class="state-overview">
               <div class="page-title">Legal</div>
                 
              </div>         
              <div class="state-overview">
                <div class="row">
              <div class="col-xl-4 col-md-4 col-12">
                 <div class="info-box bg-warning">
                   <a class="link" href="{{route('plmsApproval')}}">
                     <span class="info-box-icon push-bottom"><i class="material-icons extra">card_travel</i></span>
                   </a>
                   <div class="info-box-content">
                    <span class="info-box-text">Legal Cases movement</span>
                     <span class="info-box-number" id="legalMovCount">{{$managingDirector['plmsApprovalCount']}}</span>
                     
                     <div class="progress">
                      <div class="progress-bar width-60"></div>
                    </div>
                    <span class="progress-description"> Cases
                     <select name="legalCaseMovement" id="legalCaseMovement" class="legalCaseMovement">  
                        <option value="open"> Open </option>
                        <option value="close"> Close </option>
                        <option value="reject"> Reject </option>
                      </select>  
                   </span>
                 </div>
                 <!-- /.info-box-content -->
               </div>
               <!-- /.info-box -->
              </div>
              <div class="col-xl-4 col-md-6 col-12">
                    <div class="info-box bg-mint">
                      <a  href="{{ route('caseStatusAccordingToLawyer') }}" > 
                      <span class="info-box-icon push-bottom"><i class="material-icons">local_activity</i></span>
                      </a>          
                      <div class="info-box-content">
                                    
                                        
                        <span class="info-box-text">Legal Cases Status according to lawyer</span>
                        <span class="info-box-number" id="legalStatusCount">{{$managingDirector['plmsLegalStatus']}}</span>
                        <div class="progress">
                          <div class="progress-bar width-80"></div>
                        </div>
                                  
                        <span class="progress-description">
                             Cases
                          <select name="legalCaseStatus" id="legalCaseStatus" class="legalCaseStatus">  
                            <option value="won"> Won </option>
                            <option value="loss"> Loss </option>
                            <option value="reject"> Reject </option>
                            <option value="inprogress"> Inprogress </option>
                          </select>         
                        </span>
                      </div>
                                
                      <!-- /.info-box-content -->
                    </div>
                    <!-- /.info-box -->
                  </div>
                </div>
              </div>
              <div class="state-overview">
               <div class="page-title">Maintenance</div>
                 
              </div>         
              <div class="state-overview">
                <div class="row">
               <div class="col-xl-4 col-md-4 col-12">
                 <div class="info-box bg-b-danger">
                   <a class="link" href="{{route('complaint.index').'?days=4'}}">
                     <span class="info-box-icon push-bottom"><i class="material-icons extra">card_travel</i></span>
                   </a>
                   <div class="info-box-content">
                    <span class="info-box-text">Maintenance Pending beyond 4 days</span>
                     <span class="info-box-number">{{$managingDirector['maintenancePendingCount']}}</span>
                     
                     <div class="progress">
                      <div class="progress-bar width-60"></div>
                    </div>
                    <span class="progress-description">
                     <!-- ARE's To Take To Legal -->
                   </span>
                 </div>
                 <!-- /.info-box-content -->
               </div>
               <!-- /.info-box -->
              </div>
                  </div>
              </div>
              <div class="state-overview">
               <div class="page-title">Back Office</div>
                 
              </div>         
              <div class="state-overview">
                <div class="row">
                 <div class="col-xl-4 col-md-6 col-12">
                    <div class="info-box bg-blue">
                      <a  href="{{route('getBouncedChequesMTD').'?not_won_loss=1'}}" > 
                      <span class="info-box-icon push-bottom"><i class="material-icons">local_activity</i></span>
                      </a>          
                      <div class="info-box-content">
                                    
                                        
                        <span class="info-box-text">Bounced Cheques MTD</span>
                        <span class="info-box-number">{{$managingDirector['bouncedChequeCount']}}</span>
                         
                        <div class="progress">
                          <div class="progress-bar width-80"></div>
                        </div>
                                  
                        <span class="progress-description">
                                    
                        </span>
                      </div>
                                
                      <!-- /.info-box-content -->
                    </div>
                    <!-- /.info-box -->
                  </div>
                <div class="col-xl-4 col-md-4 col-12">
                 <div class="info-box bg-purple">
                   <a href="{{route('tenant-contract.index').'?expiredContracts=true'}}">
                     <span class="info-box-icon push-bottom"><i class="material-icons extra">card_travel</i></span>
                   </a>
                   <div class="info-box-content">
                    <span class="info-box-text">Tenant Contracts Expired</span>
                    <span class="info-box-number">{{$managingDirector['gracePeriodCount']}}</span>
                     <span class="progress-description"> 
                      <select style="width: 62px;" name="tenantAre"  id="tenantAre" class="tenantAre">
                        @foreach($areUsers as $areUser)
                        <option value="{{$areUser->user_id}}">{{$areUser->areUser->employee->employee_name}}</option>
                        @endforeach
                      </select> 
                     
                    </span> 
                     <div class="progress">
                      <div class="progress-bar width-60"></div>
                    </div>
                    <span class="progress-description">
                     <!-- ARE's To Take To Legal -->
                   </span>
                 </div>
                 <!-- /.info-box-content -->
               </div>
               <!-- /.info-box -->
              </div>

                <div class="col-xl-4 col-md-4 col-12">
                 <div class="info-box bg-purple">
                   <a href="{{route('landlord-contract.index')}}">
                     <span class="info-box-icon push-bottom"><i class="material-icons extra">card_travel</i></span>
                   </a>
                   <div class="info-box-content">
                    <span class="info-box-text">Expired Landlord Contracts(Comprehensive)</span>
                     <span class="info-box-number">{{$managingDirector['landlordContractsExpired']}}</span>
                     
                     <div class="progress">
                      <div class="progress-bar width-60"></div>
                    </div>
                    <span class="progress-description">
                     <!-- ARE's To Take To Legal -->
                   </span>
                 </div>
                 <!-- /.info-box-content -->
               </div>
               <!-- /.info-box -->
              </div>


               <div class="col-xl-4 col-md-4 col-12">
                        <div class="info-box bg-mint">
                          <a href="" class="link1" id="link1">
                            <span class="info-box-icon push-bottom"><i class="material-icons extra">monetization_on</i></span>
                          </a>
                          <div class="info-box-content">
                            <span class="info-box-text">Vacated units MTD</span>
                            <span class="info-box-number"><p class="tenant_vacating" id="tenant_vacating"></p></span>
                            
                            <div class="progress">
                             <div class="progress-bar width-60"></div>
                           </div>
                       
                         </div>
                      
                       </div>
                  
                </div>

                <!-- /.col -->
                  <div class="col-xl-4 col-md-4 col-12">
                   
                    <div class="info-box bg-success">
                       <a class="receivables_ceo_href" href="{{route('unit.index').'?unit_vaccant_status=0'}}">
                      <span class="info-box-icon push-bottom infoBoxSmall"><i class="material-icons">hourglass_empty</i></span>
                    </a>
                      <div class="info-box-content" style="margin-left: 80px">
                      <span class="info-box-text">Receivables</span>
                      <span class="info-box-number  receivables_ceo_count">{{$managingDirector['receivables']}}</span>
                          
         
                    <div class="progress">
                      <div class="progress-bar width-40"></div>
                    </div>

                    <span class="progress-description"> 
                      <select style="width: 62px;" name="receivables_ceo"  id="receivables_ceo" class="ceo_select_count">
                        @foreach($areUsers as $areUser)
                        <option value="{{$areUser->user_id}}">{{$areUser->areUser->employee->employee_name}}</option>
                        @endforeach
                      </select> 

                    <input type="date" class="receivable_date" id="receivable_date" value="{{date('Y-m-d')}}">
                     
                    </span> 

                     </div>
                     
                    </div>
                   </a>
                  </div>
                  <!-- /.col -->


                  <div class="col-xl-4 col-md-4 col-12">
                   
                    <div class="info-box bg-danger">
                       <a class="collection_MTD__href" href="">
                      <span class="info-box-icon push-bottom infoBoxSmall"><i class="material-icons">hourglass_empty</i></span>
                    </a>
                      <div class="info-box-content" style="margin-left: 80px">
                      <span class="info-box-text">Collection MTD</span>
                      <span class="info-box-number  collection_MTD_count">{{$managingDirector['collectionMTD']}}</span>
                          
         
                    <div class="progress">
                      <div class="progress-bar width-40"></div>
                    </div>

                    <span class="progress-description"> 
                      <select style="width: 62px;" name=""  id="" class="collection_MTD_count">
                        @foreach($areUsers as $areUser)
                        <option value="{{$areUser->user_id}}">{{$areUser->areUser->employee->employee_name}}</option>
                        @endforeach
                      </select> 

                    <input type="date" class="collection_MTD_date" id="collection_MTD_date" value="{{date('Y-m-d')}}">
                     
                    </span> 

                     </div>
                     
                    </div>
                 
                  </div>

				<!--
                  <div class="col-xl-4 col-md-6 col-12">
                    <div class="info-box bg-success">
                      <a  href="" > 
                      <span class="info-box-icon push-bottom"><i class="material-icons">local_activity</i></span>
                      </a>          
                      <div class="info-box-content">
                                    
                                        
                        <span class="info-box-text">Vacant Normal Units</span>
                        <span class="info-box-number">{{$managingDirector['vacantNormalUnits']}} / {{$managingDirector['normalUnits']}}</span>
                         
                        <div class="progress">
                          <div class="progress-bar width-80"></div>
                        </div>
                                  
                        <span class="progress-description">
                                    
                        </span>
                      </div>
                                
                     
                    </div>
                    
                  </div> -->

                  <!-- <div class="col-xl-4 col-md-6 col-12">
                    <div class="info-box bg-watermelon">
                      <a  href="" > 
                      <span class="info-box-icon push-bottom"><i class="material-icons">local_activity</i></span>
                      </a>          
                      <div class="info-box-content">
                                    
                                        
                        <span class="info-box-text">Vacant Comprehensive Units</span>
                        <span class="info-box-number">{{$managingDirector['vacantComprehensiveUnits']}} / {{$managingDirector['comprehensiveUnits']}}</span>
                         
                        <div class="progress">
                          <div class="progress-bar width-80"></div>
                        </div>
                                  
                        <span class="progress-description">
                                    
                        </span>
                      </div>
                                
                      
                    </div>
                    
                  </div> -->


                  


                  <div class="col-xl-4 col-md-4 col-12">
                   
                    <div class="info-box bg-orange">
                       <a href="{{route('wonList').'?count_by_month='.date('m')}}" >
                      <span class="info-box-icon push-bottom infoBoxSmall"><i class="material-icons">hourglass_empty</i></span>
                    </a>
                      <div class="info-box-content" style="margin-left: 80px">
                      <span class="info-box-text">Number of units rented MTD</span>
                      <span class="info-box-number">{{$managingDirector['wonDealsMTD']}}</span>
                          
         
                    <div class="progress">
                      <div class="progress-bar width-40"></div>
                    </div>

                  <!--   <span class="progress-description"> 
                      <select style="width: 62px;" name=""  id="" class="collection_MTD_count">
                        @foreach($areUsers as $areUser)
                        <option value="{{$areUser->user_id}}">{{$areUser->areUser->employee->employee_name}}</option>
                        @endforeach
                      </select> 

                    <input type="date" class="" id="" value="{{date('Y-m-d')}}">
                     
                    </span>  -->

                     </div>
                     
                    </div>
                 
                  </div>


                <div class="col-xl-4 col-md-6 col-12">
                    <div class="info-box bg-b-danger">
                      <a  href="" > 
                      <span class="info-box-icon push-bottom"><i class="material-icons">local_activity</i></span>
                      </a>          
                      <div class="info-box-content">

                        <span class="info-box-text">Number of early termination requests</span>
                        <span class="info-box-number">{{$managingDirector['tenantTerminations']}}</span>
                         
                        <div class="progress">
                          <div class="progress-bar width-80"></div>
                        </div>
                                  
                        <span class="progress-description">
                                    
                        </span>
                      </div>
                                
                      <!-- /.info-box-content -->
                    </div>
                    <!-- /.info-box -->
                </div>


                  <!-- 26-12-2020 ends new updations-->
              <!-- New card in dashbaord :2021-08-12 -->
              <div class="col-xl-4 col-md-4 col-12">
               <div class="info-box bg-info">
                 <a class="vacantUnit_sc_href">
                 <span class="info-box-icon push-bottom"><i class="material-icons">person_pin</i></span>
                </a>
                 <div class="info-box-content">
                   <a class="vacantUnit_sc_href">
                     <span class="info-box-text">Vacant Unit Movement</span>
                     <span class="info-box-number  vacantUnit_sc_count">{{$managingDirector['vacantBuildingsSc']}} / {{$managingDirector['vacantUnitsSc']}}</span>
                   </a>
                   <div class="progress">
                     <div class="progress-bar width-60"></div>
                   </div>
                    <span class="progress-description"> 
                     <!-- <input type="hidden" value="unattend" class="enitity_count"> -->
                     <select name="vacantUnit"  id="vacantUnit_sc" class="vacantUnit">               
                         <option value="M"> Monthly </option>
                         <option value="Y"> Yearly </option>
                     </select>        
                   </span>          
                 </div> 
               </div> 
             </div>
                <!-- card ends -->

                 
                  
                
                
                         
                            

                  </div>
            </div>
                          <!-- </div> -->
          <!--  </div>
                                 </div> -->
          <!-- end widget -->
<!-- sales lead window-->



<div class="state-overview">
<div class="row">                       
<!-- activities -->

<div class="col-md-12 col-sm-12 col-12">
    <div class="card  card-box img-alin">
                <div class="card-head">
                    <header>Hit Ratio Per Sales person</header>
                </div> 
                  <div class="card-body">
                    <ul class="list-horizontal">
                        <li>Assigned</li>
                        <li>Won</li>
                    </ul>
                    <div class="graph_container">
                      <canvas id="Chart1"></canvas>
                    </div>
                  </div>
    </div>
                           
</div>

<div class="col-md-12 col-sm-12 col-12">
  <div class="row">
      <div class="col-md-12 col-sm-12 col-12">
            <div class="graph_container">
              <canvas id="Chart2"></canvas>
            </div>          
      </div> 
  </div>
                           
</div>  

<!-- Number Of Units In Each Location -->
<div class="col-md-12 col-sm-12 col-12">
    <div class="card  card-box img-alin">
                <div class="card-head">
                    <header>Number Of Units In Each Location</header>
                </div> 
                  <div class="card-body">
                    <ul class="list-horizontal-new">
                        <li style="">Number Of Units</li>
                    </ul>
                    <div class="graph_container">
                      <canvas id="Chart3"></canvas>
                    </div>
                  </div>
    </div>
                           
</div>

<!--Number of vacant normal units piechart -->
<div class="col-md-6 col-sm-6 col-6">
    <div class="card  card-box img-alin">
                <div class="card-head">
                    <header>No. of Vacant Units</header>
                </div> 
                  <div class="card-body">
                    <ul class="list-horizontal-2">
                        <li style="">No. of Vacant Units - Normal</li>
                    </ul>
                    <div class="graph_container" style="height:400px!important;width:400px!important;">
                      <canvas id="pieChart"></canvas>
                    </div>
                  </div>
</div>
                           
</div>
<!--Number of vacant comprehensive units piechart  -->
<div class="col-md-6 col-sm-6 col-6">
    <div class="card  card-box img-alin">
                <div class="card-head">
                    <header>No. of Vacant Units</header>
                </div> 
                  <div class="card-body">
                    <ul class="list-horizontal-2">
                        <li style="">No. of Vacant Units - Comprehensive</li>
                    </ul>
                    <div class="graph_container" style="height:400px!important;width:400px!important;">
                      <canvas id="pieChart2"></canvas>
                    </div>
                  </div>
    </div>
                           
</div>

<!-- Buildings Per ARE -->
<div class="col-md-12 col-sm-12 col-12">
    <div class="card  card-box img-alin">
                <div class="card-head">
                    <header>Buildings Per ARE</header>
                </div> 
                  <div class="card-body">
                    <ul class="list-horizontal-2">
                        <li style="">Number Of Buildings</li>
                    </ul>
                    <div class="graph_container">
                      <canvas id="Chart4"></canvas>
                    </div>
                  </div>
    </div>
                           
</div>
<!-- Buildings Per ARE -->

<!-- Units Per ARE -->
<div class="col-md-12 col-sm-12 col-12">
    <div class="card  card-box img-alin">
                <div class="card-head">
                    <header>Units Per ARE</header>
                </div> 
                  <div class="card-body">
                    <ul class="list-horizontal-2">
                        <li style="">Number Of Units</li>
                    </ul>
                    <div class="graph_container">
                      <canvas id="Chart4a"></canvas>
                    </div>
                  </div>
    </div>
                           
</div>

<!-- Units Per ARE -->

<div class="col-md-12 col-sm-12 col-12">
    <div class="card  card-box img-alin">
                <div class="card-head">
                    <header>Sources Of Enquiry</header>
                </div> 
                  <div class="card-body">
                    <ul class="list-horizontal-3">
                        <li style="">Sources</li>
                    </ul>
                    <div class="graph_container">
                      <canvas id="Chart5"></canvas>
                    </div>
                  </div>
    </div>
                           
</div>

<!-- Average Rent in Geographical area -->
<div class="col-md-12 col-sm-12 col-12">
    <div class="card  card-box img-alin">
                <div class="card-head">
                    <header>Average Rent in Geographical area</header>
                </div> 
                  <div class="card-body">
                    <ul class="list-horizontal-4">
                        <li style="">Areas</li>
                    </ul>
                    <div class="graph_container">
                      <canvas id="Chart6"></canvas>
                    </div>
                  </div>
    </div>
                           
</div>



<!-- listing -->
    <!-- graph -->          
    
    
    <!-- graph -->
</div>
</div>

 @push('dashboard_scripts')

<script> 
	
	 /******************** Legal Movement ***********************/
     $('#legalCaseMovement').change(function (event) {
        var selected_status = $(this).val();
        
        $.ajax({
                type: "POST",
                url: "{{route('md-dashboard')}}",
                data:{"_token":"{{ csrf_token() }}","count_type":"legalCaseMovement","selected_status": selected_status},
                success: function(data){                    
                    $("#legalMovCount").html(data.count)            
                }
         }); 
        
     }); 
    /******************** End Legal Movement ***********************/    
    /******************** Legal Status ***********************/
     $('#legalCaseStatus').change(function (event) {
        var selected_status = $(this).val();
        
        $.ajax({
                type: "POST",
                url: "{{route('md-dashboard')}}",
                data:{"_token":"{{ csrf_token() }}","count_type":"legalCaseStatus","selected_status": selected_status},
                success: function(data){                    
                    $("#legalStatusCount").html(data.count)            
                }
         }); 
        
     }); 
    /******************** End Legal Status ***********************/    
     $('.select_count').change(function (event) {
  
            var id = $(this).attr('id');
            var select_value = $(this).val();

            var  data_details = {};
             
            data_details['count_type'] = id;
 
            if(select_value != 'YTD')
              data_details['count_val'] = select_value;
            else
              data_details['count_val'] = select_value;

            data_details['_token'] =  "{{ csrf_token() }}";
            //console.log(data_details)
             $.ajax({
                   type: "POST",
                   url: "{{route('sales-head-dashboard')}}",
                //   data: {count_type :id ,  count_val : select_value},
                   data: data_details,
                   success: function(data){

                    
                    $('.'+id+'_count').html(data.count)
                    $("."+id+"_href").attr('href', data.href);      
                   
                  }
                });
        });

        $('#vacantUnit_sc').change(function (event) {
             
              var reportType = $('#vacantUnit_sc').find(":selected").val();
              console.log(reportType)
               $.ajax({
                     type: "GET",
                     url: "{{route('vacantUnitMovementSc')}}"+'/'+reportType,
                     success: function(data){  
                      var data = JSON.parse(data);
                      var frameVacContent = data.vacantBuildingsSc + ' / ' + data.vacantUnitsSc;
                      $('.vacantUnit_sc_count').html(frameVacContent)
                    }
                  });
          });


   
    var barOptions_stacked = {
    tooltips: {
        enabled: false
    },
    hover :{
        animationDuration:0
    },
    scales: {
        xAxes: [{
            ticks: {
                beginAtZero:true,
                fontFamily: "'Open Sans Bold', sans-serif",
                fontSize:11
            },
            scaleLabel:{
                display:false
            },
            gridLines: {
            }, 
            stacked: true
        }],
        yAxes: [{
            gridLines: {
                display:false,
                color: "#fff",
                zeroLineColor: "#fff",
                zeroLineWidth: 0
            },
            ticks: {
                fontFamily: "'Open Sans Bold', sans-serif",
                fontSize:11
            },
            stacked: true
        }]
    },
    legend:{
        display:false
    },
    
    animation: {
        onComplete: function () {
            var chartInstance = this.chart;
            var ctx = chartInstance.ctx;
            ctx.textAlign = "left";
            ctx.font = "9px Open Sans";
            ctx.fillStyle = "#fff";

            Chart.helpers.each(this.data.datasets.forEach(function (dataset, i) {
                var meta = chartInstance.controller.getDatasetMeta(i);
                Chart.helpers.each(meta.data.forEach(function (bar, index) {
                    data = dataset.data[index];
                    if(i==0){
                        ctx.fillText(data, 50, bar._model.y+4);
                    } else {
                        ctx.fillText(data, bar._model.x-25, bar._model.y+4);
                    }
                }),this)
            }),this);
        }
    },
    pointLabelFontFamily : "Quadon Extra Bold",
    scaleFontFamily : "Quadon Extra Bold",
};
@php 
  $saleList = "'" .implode("', '", $managingDirector['sales_person_list']['name'])."'";
  $assigned = implode(", ", $managingDirector['sales_person_list']['value']['assigned']);
  $won = implode(", ", $managingDirector['sales_person_list']['value']['won']);
  $null_value = implode(", ", $managingDirector['sales_person_list']['value']['null_value']);
@endphp
var ctx = document.getElementById("Chart1");
var myChart = new Chart(ctx, {
    type: 'horizontalBar',
    data: {
        labels: [{!! $saleList!!}],  

        datasets: [{
            data: [{!!$null_value!!}],
            backgroundColor: "rgba(91,156,214,1)",
            hoverBackgroundColor: "rgba(62,133,197,1)"
        },{
           data: [{!!$assigned!!}],
           backgroundColor: "rgba(91,156,214,1)",
            hoverBackgroundColor: "rgba(62,133,197,1)"
        },{
           data: [{!!$won!!}],
             backgroundColor: "rgba(112,173,70,1)",
            hoverBackgroundColor: "rgba(96,155,55,1)"
        }]


    },

    options: barOptions_stacked,
});


// chart3(NUmber of Units In Each Location)
@php 
  $locationList = "'" .implode("', '", $managingDirector['location_list']['location'])."'";
  $unit_count = implode(", ", $managingDirector['location_list']['value']['unit_count']);
  $null_value = implode(", ", $managingDirector['location_list']['value']['null_value']);
@endphp
var ctx = document.getElementById("Chart3");
var myChart = new Chart(ctx, {
    type: 'horizontalBar',
    data: {
        labels: [{!! $locationList!!}],  

        datasets: [{
            data: [{!!$null_value!!}],
            backgroundColor: "rgba(91,156,214,1)",
            hoverBackgroundColor: "rgba(62,133,197,1)"
        },{
           data: [{!!$unit_count!!}],
             backgroundColor: "rgba(237,125,49,1)",
            hoverBackgroundColor: "rgba(213,103,28,1)"
        }]


    },

    options: barOptions_stacked,
});

//piechart
@php 
  $vacantNormalTotal = $managingDirector['vacantNormalUnits'] + $managingDirector['normalUnits'];
 // $vacantNormalTotal = $managingDirector['vacantNormalUnits'] + $managingDirector['normalUnits'];
@endphp

var ctx2 = document.getElementById("pieChart").getContext('2d');
  var pieChart = new Chart(ctx2, {
    type: 'pie',
    data: {
        datasets: [{
          data: [{!! $managingDirector['vacantNormalUnits']!!},{!! $managingDirector['normalUnits'] !!}],
          backgroundColor: [
            'rgba(255,99,132,1)',
            'rgba(54, 162, 235, 1)'
            
          ],
          label: 'Vacant Units - Normal'
        }],
        labels: [
          "Vacant Units - Normal",
          "Total"
        ]
      },
      options: {
        responsive: true
      }
   
  });
//piechart2
@php 
  $vacantCompTotal = $managingDirector['vacantComprehensiveUnits'] + $managingDirector['comprehensiveUnits'];
 // $vacantNormalTotal = $managingDirector['vacantNormalUnits'] + $managingDirector['normalUnits'];
@endphp

var ctx2ap = document.getElementById("pieChart2").getContext('2d');
  var pieChart2 = new Chart(ctx2ap, {
    type: 'pie',
    data: {
        datasets: [{
          data: [{!!  $managingDirector['vacantComprehensiveUnits']!!},{!! $managingDirector['comprehensiveUnits']!!}],
          backgroundColor: [
            'rgba(255,99,132,1)',
            'rgba(54, 162, 235, 1)'
            
          ],
          label: 'Vacant Units - Comprehensive'
        }],
        labels: [
          "Vacant Units - Comprehensive",
          "Total"
        ]
      },
      options: {
        responsive: true
      }
   
  });
//
// chart4(Buildings Per ARE)
@php 
  $areList = "'" .implode("', '", $managingDirector['buildingsPerARE']['are'])."'";
  $building_count = implode(", ", $managingDirector['buildingsPerARE']['value']['building_count']);
  $null_value = implode(", ", $managingDirector['buildingsPerARE']['value']['null_value']);
@endphp
var ctx = document.getElementById("Chart4");
var myChart = new Chart(ctx, {
    type: 'horizontalBar',
    data: {
        labels: [{!! $areList!!}],  

        datasets: [{
            data: [{!!$null_value!!}],
            backgroundColor: "rgba(91,156,214,1)",
            hoverBackgroundColor: "rgba(62,133,197,1)"
        },{
           data: [{!!$building_count!!}],
             backgroundColor: "#B04833",
            hoverBackgroundColor: "#9C341F"
        }]


    },

    options: barOptions_stacked,
});

// chart4aUnits(Buildings Per ARE)
@php 
  $areList_c4a = "'" .implode("', '", $managingDirector['unitsPerARE']['are'])."'";
  $unit_count_are = implode(", ", $managingDirector['unitsPerARE']['value']['unit_count']);
  $null_value_are = implode(", ", $managingDirector['unitsPerARE']['value']['null_value']);
@endphp
var ctx_c4n = document.getElementById("Chart4a");
var myChart_c4a = new Chart(ctx_c4n, {
    type: 'horizontalBar',
    data: {
        labels: [{!! $areList_c4a!!}],  

        datasets: [{
            data: [{!!$null_value_are!!}],
            backgroundColor: "rgba(91,156,214,1)",
            hoverBackgroundColor: "rgba(62,133,197,1)"
        },{
           data: [{!!$unit_count_are!!}],
             backgroundColor: "#B04833",
            hoverBackgroundColor: "#9C341F"
        }]


    },

    options: barOptions_stacked,
});

// chart5(Sources Of Enquiry)
@php 
  $enquiry_sourceList = "'" .implode("', '", $managingDirector['enquirySource']['enquiry_source'])."'";
  $enquiry_count = implode(", ", $managingDirector['enquirySource']['value']['enquiry_source_count']);
  $null_value = implode(", ", $managingDirector['enquirySource']['value']['null_value']);
@endphp
var ctx = document.getElementById("Chart5");
var myChart = new Chart(ctx, {
    type: 'horizontalBar',
    data: {
        labels: [{!! $enquiry_sourceList!!}],  

        datasets: [{
            data: [{!!$null_value!!}],
            backgroundColor: "rgba(199, 0, 57 ,1)",
            hoverBackgroundColor: "rgba(62,133,197,1)"
        },{
           data: [{!!$enquiry_count!!}],
             backgroundColor: "rgba(199, 0, 57 ,1)",
            hoverBackgroundColor: "#D00357   "
        }]


    },

    options: barOptions_stacked,
});


// chart6(Average Rent in Geographical area)
@php 
  $area_List = "'" .implode("', '", $managingDirector['rent']['area'])."'";
  $avg_rent = implode(", ", $managingDirector['rent']['value']['averageReceivables']);
  $null_value = implode(", ", $managingDirector['rent']['value']['null_value']);
@endphp
var ctx = document.getElementById("Chart6");
var myChart = new Chart(ctx, {
    type: 'horizontalBar',
    data: {
        labels: [{!! $area_List!!}],  

        datasets: [{
            data: [{!!$null_value!!}],
            backgroundColor: "rgba(199, 0, 57 ,1)",
            hoverBackgroundColor: "rgba(62,133,197,1)"
        },{
           data: [{!!$avg_rent!!}],
             backgroundColor: "#808000",
            hoverBackgroundColor: "#444400"
        }]


    },

    options: barOptions_stacked,
});


$(document).ready(function() {  

     
      var barChartData = {
         labels: ["30", "60", "90","120+"],
         datasets: [{
             label: 'Comprehensive',
             backgroundColor: 'rgb(100,149,237)',
             borderColor: 'rgb(100,149,237)',
             borderWidth: 1,
             data: [
             @foreach($managingDirector['vacantUnits']['comprehensive'] as $comprehensive)
              '{{$comprehensive}}' ,
             @endforeach
             ]
         }, {
             label: 'Normal',
             backgroundColor: 'rgb(255, 99, 132)',
             borderColor: 'rgb(255, 99, 132)',
             borderWidth: 1,
             data: [
              @foreach($managingDirector['vacantUnits']['normal'] as $normal)
              '{{$normal}}' ,
             @endforeach
             ]
         }]

     };



         var ctx = document.getElementById("Chart2").getContext("2d");
         var myBarChart   = new Chart(ctx, {
             type: 'bar',
             data: barChartData,
             options: {
                 responsive: true,
                 legend: {
                     position: 'top',
                 },
                 title: {
                     display: true,
                     text: 'Vacant Unit'
                 },
                 scales: {
                  yAxes: [{
                      ticks: {
                          beginAtZero: true
                      }
                  }]
                 }
                 //onClick : clickHandler()
             },
             
         });


$("#Chart2").click(function(evt) {
    var firstPoint = myBarChart.getElementAtEvent(evt)[0];

       if (firstPoint) {
        var label = myBarChart.data.labels[firstPoint._index];
        var management_id = firstPoint._datasetIndex + 1;
        var vacant_days =  label;

        window.location = '{{route("unit.index")}}'+'?building__management_id='+management_id+'&vacantUnits_byDays='+vacant_days;
        



       // var value = myBarChart.data.datasets[firstPoint._datasetIndex].data[firstPoint._index];      
      //   alert(management_id);         
       //   alert(vacant_days);
       //  alert(label+' '+value+ ' '+firstPoint._datasetIndex)
      }
      
     }
    );

  });


     $(document).ready(function() {
       /***********Contracts Expiring starts**************/
       var vaccatingdays = 30;
       var href1 =   "{{ route('vacatedUnitsMTDList')}}";
       $.ajax({
         type: "GET",
         url: "{!!URL::route('vacatedUnitsMTDCount')!!}",
       
         success: function(data){
          var result = $.parseJSON(data);
          $('#tenant_vacating').text(result[0].count);
          href1 = href1;
          $(".link1").attr('href', href1);
        }
      });
       /***********Contracts Expiring ends**************/  
     }); 


     $(document).ready(function(){
       $( "#receivables_ceo" ).trigger( "change" );
      });

      $('#receivable_date').change(function (event) {
        $( "#receivables_ceo" ).trigger( "change" );
      });



      $('#receivable_date').attr('min','{{date("Y-m-d")}}');


    $('.ceo_select_count').change(function (event) {
     
        var id = $(this).attr('id');
        var select_value = $(this).val();

        var  data_details = {};
         
        data_details['count_type'] = id;

        if(select_value != 'all')
        data_details['count_val'] = select_value;

        data_details['_token'] =  "{{ csrf_token() }}";

        if(id == 'receivables_ceo'){
         if($('#receivable_date').val() != '') 
         data_details['receivable_date'] = $('#receivable_date').val();  
      }
        
         $.ajax({
               type: "POST",
               url: "{{route('ceo_dashboard')}}",        
               data: data_details,
               success: function(data){            
                $('.'+id+'_count').html(data.count)
                $("."+id+"_href").attr('href', data.href); 
              }
            });
       
    });

</script>

@endpush  



