 
    @if(count(\Auth::user()->getRoleNames()) == 1)
        <div class="page-bar">
            <div class="page-title-breadcrumb">
                <div class=" pull-left">
                    <div class="page-title">Sales Coordinator - Dashboard</div>
                </div>
                {{ Breadcrumbs::render('sales-coordinator-dashboard')}}  
            </div>
        </div>
    @endif
                   <!-- start widget -->
<div class="state-overview mb-4">
<div class="row">
    <div class="col-xl-4 col-md-4 col-12">
  
      <div class="info-box bg-blue">
          <a class="unassignedEnquiry_sc_href" href="{{route('leadAssign.index').'?count_by_day=1'}}" >
            <span class="info-box-icon push-bottom"><i class="material-icons">remove_circle_outline</i></span>
          </a>
        <div class="info-box-content">  
         <a class="unassignedEnquiry_sc_href" href="{{route('leadAssign.index').'?count_by_day=1'}}" >                              
          <span class="info-box-number unassignedEnquiry_sc_count">{{$salesCoordinator['unassigned_list']}}</span>
          <span class="info-box-text">Unassigned Enquiry</span>
         </a>
          <div class="progress">
            <div class="progress-bar width-60"></div>
          </div>
           <span class="progress-description">
          Last 
          <input type="hidden" value="unattend" class="enitity_count">
          <select name="unassigned_enquiry"  id="unassignedEnquiry_sc" class="select_count   ">
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

    <!-- /.col -->
    <div class="col-xl-4 col-md-4 col-12">
     
      <div class="info-box bg-orange">
        <a class="assignedEnquiry_sc_href" href="{{route('leadAssign.assignedList').'?count_by_assignday=3'}}">
        <span class="info-box-icon push-bottom"><i class="material-icons">person_pin</i></span>
       </a>
        <div class="info-box-content">
          <a class="assignedEnquiry_sc_href" href="{{route('leadAssign.assignedList').'?count_by_assignday=3'}}">
           <span class="info-box-number  assignedEnquiry_sc_count">{{$salesCoordinator['assigned_list']}}</span>
           <span class="info-box-text">Assigned Enquiry</span>
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
    <!-- /.col -->
    <div class="col-xl-4 col-md-4 col-12">
       
      <div class="info-box bg-purple">
        <a class="inprogressEnquiry_sc_href"  href="{{route('inprogressList').'?count_by_assignday=5'}}">
        <span class="info-box-icon push-bottom"><i class="material-icons">trending_up</i></span>
        </a>
        <div class="info-box-content">  
          <a class="inprogressEnquiry_sc_href"  href="{{route('inprogressList').'?count_by_assignday=5'}}">
           <span class="info-box-number  inprogressEnquiry_sc_count">{{$salesCoordinator['inprogress_list']}}</span>
           <span class="info-box-text"> In Progress</span>
          </a>
          <div class="progress">
            <div class="progress-bar width-40"></div>
          </div>

          <span class="progress-description">
              Last 
            <input type="hidden" value="unattend" class="enitity_count">
            <select name="inprogress_enquiry"  id="inprogressEnquiry_sc" class="select_count   ">               
                <option value="5"> 5 </option>
                <option value="10"> 10 </option>
                <option value="15"> 15 </option>
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
</div>
						<div class="row">
					        <div class="col-xl-4 col-md-4 col-12">
					          <div class="info-box bg-success">
                      <a class="wonEnquiry_sc_href"  href="{{route('wonList').'?count_by_month='.date('m')}}">
					            <span class="info-box-icon push-bottom infoBoxSmall"><i class="material-icons">grade</i></span>
                      </a>
                      
					            <div class="info-box-content">

                        <a class="wonEnquiry_sc_href"  href="{{route('wonList').'?count_by_month='.date('m')}}">					              
					              <span class="info-box-number  wonEnquiry_sc_count">{{$salesCoordinator['wonEnquiry']}}</span>
                        <span class="info-box-text">Won </span>
                        </a>

                         <div class="progress">
                          <div class="progress-bar width-40"></div>
                        </div>


                        <span class="progress-description">
                         
                            <input type="hidden" value="unattend" class="enitity_count">
                            <select name="wonEnquiry_enquiry"  id="wonEnquiry_sc" class="select_count ">                                
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
					       
                   
                  <!-- .col -->
                 
   <div class="col-xl-4 col-md-4 col-12">
       
      <div class="info-box bg-danger">
        <a class="selfAssignedEnquiry_sc_href"  href="{{route('inprogressList').'?firstCall=self&count_by_assignday=1'}}">
        <span class="info-box-icon push-bottom"><i class="material-icons">assignment_return</i></span>
        </a>
        <div class="info-box-content">  
          <a class="selfAssignedEnquiry_sc_href"  href="{{route('inprogressList').'?firstCall=self&count_by_assignday=1'}}">
           <span class="info-box-number  selfAssignedEnquiry_sc_count">{{$salesCoordinator['selfAssignedEnquiry']}}</span>
           <span class="info-box-text"> Self-Assigned</span>
          </a>
          <div class="progress">
            <div class="progress-bar width-40"></div>
          </div>

          <span class="progress-description">
              Last 
            <input type="hidden" value="unattend" class="enitity_count">
            <select name="selfAssignedEnquiry_enquiry"  id="selfAssignedEnquiry_sc" class="select_count   ">               
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



     <div class="col-xl-4 col-md-4 col-12">
                    <div class="info-box bg-watermelon">
                      <a class="lostEnquiry_sc_href"  href="{{route('closedList').'?count_by_month='.date('m')}}">
                      <span class="info-box-icon push-bottom infoBoxSmall"><i class="material-icons">card_travel</i></span>
                      </a>
                      
                      <div class="info-box-content">

                        <a class="lostEnquiry_sc_href"  href="{{route('closedList').'?count_by_month='.date('m')}}">                       
                        <span class="info-box-number lostEnquiry_sc_count">{{$salesCoordinator['lostEnquiry']}}</span>
                        <span class="info-box-text">Lost Enquiries </span>
                        </a>

                         <div class="progress">
                          <div class="progress-bar width-40"></div>
                        </div>


                        <span class="progress-description">
                         
                            <input type="hidden" value="unattend" class="enitity_count">
                            <select name="lostEnquiry_enquiry"  id="lostEnquiry_sc" class="select_count ">                                
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



                            <!-- /.col -->
                            <div class="col-xl-4 col-md-4 col-12">
                              <a href="{{route('tenantEnquiries',['open_enquiry'=>'Yes'])}}">
                              <div class="info-box bg-lime">
                                <span class="info-box-icon push-bottom infoBoxSmall"><i class="material-icons">business</i></span>
                                <div class="info-box-content">              
                                  <span class="info-box-number">{{$salesCoordinator['allOpenEnquiry']}}</span>
                                  <span class="info-box-text">Flat/Villa/Commercial</span>
                                </div>
                                <!-- /.info-box-content -->
                              </div>
                            </a>
                              <!-- /.info-box -->
                            </div>


                 <!-- /.col -->
                  <div class="col-xl-4 col-md-4 col-12">
                    <a href="{{route('unit.index').'?unit_vaccant_status=0&unit_status=1'}}">
                    <div class="info-box bg-mint">
                      <span class="info-box-icon push-bottom infoBoxSmall"><i class="material-icons">hourglass_empty</i></span>
                      <div class="info-box-content">
                         <span class="info-box-number">{{$salesCoordinator['vacantUnits']}}</span>
                                  <span class="info-box-text">Vacant Units</span>
      
                      </div>
                     
                    </div>
                   </a>
                  </div>
                  <!-- /.col -->


                   <!-- /.col -->
                  <div class="col-xl-4 col-md-4 col-12">
                    <a href="{{route('preliminaryApprovalList')}}">
                    <div class="info-box bg-pink">
                      <span class="info-box-icon push-bottom infoBoxSmall"><i class="material-icons">assignment_turned_in</i></span>
                      <div class="info-box-content">
                         <span class="info-box-number">{{$salesCoordinator['preapproval']}}</span>
                                  <span class="info-box-text">Pending Preapproval Documentation </span>      
                      </div>                     
                    </div> 
                    </a>                  
                  </div>
                  <!-- /.col -->


                  
                  <!-- /.col -->     


                   <!-- /.col -->
                  <div class="col-xl-4 col-md-4 col-12">
                    <a href="{{route('tenant-contract.index').'?pdc_check=2&tenant_contract_status=1'}}">
                    <div class="info-box bg-info">
                      <span class="info-box-icon push-bottom infoBoxSmall"><i class="material-icons">assignment_late</i></span>
                      <div class="info-box-content">
                         <span class="info-box-number">{{$salesCoordinator['partial_pdc']}}</span>
                         <span class="info-box-text">Partial PDC Collection </span>      
                      </div>                     
                    </div>   
                    </a>                
                  </div>
                  <!-- /.col --> 


					      </div>



						</div>
					<!-- end widget -->
<!-- sales lead window-->


@push('dashboard_scripts')

@include('dashboard.sales_coordinator-js')

<!-- end page content -->
<!-- start chat sidebar -->
<script>
 $(document).ready(function() {
  /***********Contracts Expiring starts**************/
       $('#expdays').on('change', function(e) {   
         var expdays = $('#expdays').val();
         
         var href =   "{{ route('expiring',':expdays')}}";
         $.ajax({
           type: "GET",
           url: "{!!URL::route('contractsExpiring')!!}",
           data:'expdays='+ $('#expdays').val(),
           success: function(data){
            var result = $.parseJSON(data);
            $('#expiring_contracts').text(result[0].count);
            href = href.replace(':expdays', expdays);
            $(".link1").attr('href', href);
          }
        });
       });
       /***********Contracts Expiring ends**************/
     });
</script>


@endpush         
