 
    @if(count(\Auth::user()->getRoleNames()) == 1)
        <div class="page-bar">
            <div class="page-title-breadcrumb">
                <div class=" pull-left">
                    <div class="page-title">CEO - Dashboard</div>
                </div>
                {{ Breadcrumbs::render('ceo-dashboard')}}  
            </div>
        </div>
    @endif
                   <!-- start widget -->
<div class="row">
  <div class="page-bar page-title-breadcrumb pull-left">
     <div class="page-title">Sales</div>
  </div>
</div>

<div class="row">

    <div class="col-xl-4 col-md-4 col-12">  
     <div class="info-box bg-blue">
        <a href="{{route('wonList').'?count_by_month='.date('m')}}" >
            <span class="info-box-icon push-bottom infoBoxSmall">
              <i class="material-icons">remove_circle_outline</i>
            </span>
          
        <div class="info-box-content">         
          <span class="info-box-number">{{$ceo['wonDealsCurrentMonth']}}</span>
          <span class="info-box-text">Concluded Deals</span>          
          <div class="progress">
            <div class="progress-bar width-60"></div>
          </div>
           <span class="progress-description">
             Current Month
          </span>          
        </div>
      </a>
        <!-- /.info-box-content -->
     </div>
      <!-- /.info-box -->
    </div>
    <!-- /.col -->

    <!-- /.col -->
    <div class="col-xl-4 col-md-4 col-12">   
     <a href="{{route('wonList').'?count_by_month='.date('m',strtotime('-1 month'))}}" >  
      <div class="info-box bg-orange">        
        <span class="info-box-icon push-bottom">
          <i class="material-icons">person_pin</i>
        </span>      
        <div class="info-box-content">          
           <span class="info-box-number ">{{$ceo['wonDealsLastMonth']}}</span>
           <span class="info-box-text">Concluded Deals</span>         
          <div class="progress">
            <div class="progress-bar width-60"></div>
          </div>
           <span class="progress-description">
              Last Month
          </span>          
        </div>       
      </div>
       </a>
    </div>
    <!-- /.col -->



    <div class="col-xl-4 col-md-4 col-12">
       <a href="{{route('preliminaryApprovalList').'?count_by_assignday=5'}}">
      <div class="info-box bg-purple">       
        <span class="info-box-icon push-bottom">
          <i class="material-icons">trending_up</i>
        </span>
       
        <div class="info-box-content">          
           <span class="info-box-number">{{$ceo['approvalsPendingSalesManager']}}</span>
           <span class="info-box-text"> 
            Approvals Pending With
           </span>         
          <div class="progress">
            <div class="progress-bar width-40"></div>
          </div>
          <span class="progress-description">
              Sales Manager
          </span>
        </div>
      </div>
         </a>
        <!-- /.info-box-content -->
      </div>
       

    
              
    <!-- /.col -->  
      <div class="col-xl-4 col-md-4 col-12">
        <a href="{{route('tenantContractApprovedRevoke')}}">       
      <div class="info-box bg-success">                      
                      <span class="info-box-icon push-bottom infoBoxSmall">
                        <i class="material-icons">grade</i>
                      </span>                      
                      
                      <div class="info-box-content">      
                        <span class="info-box-number">{{$ceo['approvalsPendingBackOfficeManager']}}</span>
                        <span class="info-box-text">Approvals Pending With </span>

                         <div class="progress">
                          <div class="progress-bar width-40"></div>
                         </div>

                        <span class="progress-description">
                          Back Office Manager 
                        </span> 

                      </div>                 
                      <!-- /.info-box-content -->
                    </div>
         </a>
      </div>

        <!-- /.info-box -->
   
<!-- /.col -->
</div>
           

<div class="row">
  <div class="page-bar page-title-breadcrumb pull-left">
     <div class="page-title">Legal</div>
  </div>
</div>




  <div class="row">
    <!-- .col -->
                 
   <div class="col-xl-4 col-md-4 col-12">  
    <a class="selfAssignedEnquiry_sc_href"  href="{{route('activeCases',['param'=>'param'])}}">     
      <div class="info-box bg-danger">       
        <span class="info-box-icon push-bottom"><i class="material-icons">assignment_return</i></span>       
        <div class="info-box-content">
           <span class="info-box-number  selfAssignedEnquiry_sc_count">{{$ceo['legalCases']}}</span>
           <span class="info-box-text">Legal Cases</span>
          
          <div class="progress">
            <div class="progress-bar width-40"></div>
          </div>

          <span class="progress-description">
              Pending Legal Cases.
          </span>          

        </div>
        <!-- /.info-box-content -->
      </div>       
      </a>
      <!-- /.info-box -->
    </div>


    </div>



<div class="row">
  <div class="page-bar page-title-breadcrumb pull-left">
     <div class="page-title">Maintenance</div>
  </div>
</div>


  <div class="row">
     <div class="col-xl-4 col-md-4 col-12">
       <a class="lostEnquiry_sc_href"  href="{{route('complaint.index').'?not_closeed=true'}}">
          <div class="info-box bg-watermelon">
           
            <span class="info-box-icon push-bottom infoBoxSmall">
              <i class="material-icons">card_travel</i>
            </span>          
            
            <div class="info-box-content">                                               
              <span class="info-box-number">{{$ceo['maintenance']}}</span>
              <span class="info-box-text">Maintenance </span>              

              <div class="progress">
                <div class="progress-bar width-40"></div>
              </div>

              <span class="progress-description">Pending Beyond 7 days </span> 

            </div>                 
            <!-- /.info-box-content -->
          </div>
            </a>
          <!-- /.info-box -->
        </div>

      </div>

 <div class="row">
    <div class="page-bar page-title-breadcrumb pull-left">
         <div class="page-title">Back Office</div>
    </div>
  </div>


  <div class="row">


           <div class="col-xl-4 col-md-4 col-12">
                <a href="{{route('tenant-contract.index').'?expiredContracts=true'}}">
                <div class="info-box bg-lime">
                  <span class="info-box-icon push-bottom infoBoxSmall"><i class="material-icons">business</i></span>
                  <div class="info-box-content">              
                    <span class="info-box-number">{{$ceo['expiredContracts']}}</span>
                    <span class="info-box-text">Expired Contracts</span>

                     <div class="progress">
                      <div class="progress-bar width-40"></div>
                    </div>

                    <span class="progress-description"> Not Renewed As on Date </span> 
                        </div>
                  <!-- /.info-box-content -->
                </div>
              </a>
                <!-- /.info-box -->
             </div>

                 <!-- /.col -->
                  <div class="col-xl-4 col-md-4 col-12">
                   
                    <div class="info-box bg-mint">
                       <a class="receivables_ceo_href" href="{{route('unit.index').'?unit_vaccant_status=0'}}">
                      <span class="info-box-icon push-bottom infoBoxSmall"><i class="material-icons">hourglass_empty</i></span>
                    </a>
                      <div class="info-box-content" style="margin-left: 80px">
                      <span class="info-box-number  receivables_ceo_count">{{$ceo['receivables']}}</span>
                          <span class="info-box-text">Receivables</span>
         
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


                   <!-- /.col -->
                  <div class="col-xl-4 col-md-4 col-12">
                  
                    <div class="info-box bg-pink">
                      <a class="vacancyLoss_ceo" href="{{route('vacancy-loss-list','MTD')}}">
                      <span class="info-box-icon push-bottom infoBoxSmall">
                        <i class="material-icons">assignment_turned_in</i>
                      </span>
                      </a>

                      <div class="info-box-content">
                         <span class="info-box-number  vacancyLoss_ceo_count">{{$ceo['vacancyLoss']}}</span>
                         <span class="info-box-text">Vacancy Loss</span> 

                    <div class="progress">
                      <div class="progress-bar width-40"></div>
                    </div>
					{{--
                    <span class="progress-description">
                      <select  name="vacancyLoss_ceo"  id="vacancyLoss_ceo" class="ceo_select_count">                       
                      <option value="MTD">MTD</option>
                      <option value="YTD">YTD</option>                        
                      </select>                       
					</span>--}}
                      </div>                     
                    </div> 
                    </a>                  
                  </div>
                  <!-- /.col --> 

                </div>


<div class="row">
    <div class="col-md-12 col-sm-12 col-12">
          <div class="graph_container">
            <canvas id="Chart2"></canvas>
          </div>          
    </div> 
</div>


             
          <!-- end widget -->
<!-- sales lead window-->


@push('dashboard_scripts')

<script> 

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



$(document).ready(function() {  

     
     var barChartData = {
         labels: ["30", "60", "90"],
         datasets: [{
             label: 'Comprehensive',
             backgroundColor: 'rgb(100,149,237)',
             borderColor: 'rgb(100,149,237)',
             borderWidth: 1,
             data: [
             @foreach($ceo['vacantUnits']['comprehensive'] as $comprehensive)
              '{{$comprehensive}}' ,
             @endforeach
             ]
         }, {
             label: 'Normal',
             backgroundColor: 'rgb(255, 99, 132)',
             borderColor: 'rgb(255, 99, 132)',
             borderWidth: 1,
             data: [
              @foreach($ceo['vacantUnits']['normal'] as $normal)
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






</script>



@endpush         
