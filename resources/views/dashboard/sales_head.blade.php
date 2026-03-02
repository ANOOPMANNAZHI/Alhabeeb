
     @if(count(\Auth::user()->getRoleNames()) == 1)
                    <div class="page-bar">
                        <div class="page-title-breadcrumb">
                            <div class=" pull-left">
                                <div class="page-title">Sales Head - Dashboard</div>
                            </div>
                            <ol class="breadcrumb page-breadcrumb pull-right">
                                <li><i class="fa fa-home"></i>&nbsp;<a class="parent-item" href="index.html">Home</a>&nbsp;<i class="fa fa-angle-right"></i></li>
                                <li><i class="fa fa-home"></i>&nbsp;<a class="parent-item" href="index.html">Dashboard</a>&nbsp;<i class="fa fa-angle-right"></i></li>
                                <li class="active">Sales Head</li>
                            </ol>
                        </div>
                    </div>
                     @endif
         <div class="state-overview">
           <div class="page-title">Information</div>
             <div class="row">  
                    <div class="col-md-12 col-sm-12 col-12">
                      
          <div class="state-overview">
            <div class="row">
                  <div class="col-xl-4 col-md-6 col-12">
                    <div class="info-box bg-blue">
                      <a class="wonLastDays_href" href="{{route('wonList').'?count_by_day=30'}}" >   
                      <span class="info-box-icon push-bottom"><i class="material-icons">recent_actors</i></span>
                       </a>
                      <div class="info-box-content">
                                
                   
                                  <span class="info-box-text">Won in Last</span>
                                   <span class="info-box-number wonLastDays_count">
                                  {{$salesHead['wonLastDays']}}
                                  </span>
                                 
                        <div class="progress">
                          <div class="progress-bar width-60"></div>
                        </div>
                        <span class="progress-description">
                                    <input type="hidden" value="wonLastDays" class="enitity_count">
                            <select name="wonLastDays" id="wonLastDays" class="select_count">
                                        <option value="30"> 30 </option>
                                        <option value="60"> 60 </option>
                                        <option value="90"> 90 </option>
                                        <option value="YTD"> YTD </option>
                                    </select>   
                            </span>
                      </div>
                      <!-- /.info-box-content -->
                    </div>
                    <!-- /.info-box -->
                  </div>
                  <!-- /.col -->
                  <div class="col-xl-4 col-md-6 col-12">
                    <div class="info-box bg-orange">
                      <a class="vacancyLoss_sc_href" href={{route('vacancy-loss-list',['MTD'])}}>  
                      <span class="info-box-icon push-bottom"><i class="material-icons">card_travel</i></span> </a>
                      <div class="info-box-content">
                        
                        
                          <span class="info-box-text">Vacany Loss</span>
                           <span class="info-box-number  vacancyLoss_sc_count">{{$salesHead['vacancyLoss']}}</span>
                        
                         <div class="progress">
                            <div class="progress-bar width-60"></div>
                          </div>
                           <span class="progress-description">
                          Last 
                          <input type="hidden" value="unattend" class="enitity_count">
                          <select name="vacancy_loss"  id="vacancyLoss_sc" class="select_count">
                              <option value="MTD"> MTD </option>
                              <option value="YTD"> YTD </option>
                          </select>        
                           
                          </span>    
                      </div>
                      <!-- /.info-box-content -->
                    </div>
                    <!-- /.info-box -->
                  </div>
                  <!-- /.col -->
                  <div class="col-xl-4 col-md-6 col-12">
                    <div class="info-box bg-purple">
                      <a  href="{{route('landlordEnquiries').'?not_won_loss=1'}}" > 
                      <span class="info-box-icon push-bottom"><i class="material-icons">local_activity</i></span>
                      </a>          
                      <div class="info-box-content">
                                    
                                        
                        <span class="info-box-text">Landlord Enquiry</span>
                        <span class="info-box-number">{{$salesHead['notInWonLoss']}}</span>
                         
                        <div class="progress">
                          <div class="progress-bar width-80"></div>
                        </div>
                                  
                        <span class="progress-description">
                                    Not In Won/Lost
                        </span>
                      </div>
                                
                      <!-- /.info-box-content -->
                    </div>
                    <!-- /.info-box -->
                  </div>
                  <!-- /.col -->
                  <div class="col-xl-4 col-md-6 col-12">
                    <div class="info-box bg-success">
                       <a href="{{route('leadAssign.index')}}">   
                      <span class="info-box-icon push-bottom"><i class="material-icons">room</i></span></a>
                      <div class="info-box-content">
                               
                        <span class="info-box-text">Unassigned</span>
                         <span class="info-box-number">{{$salesHead['unassignedMoreOneWeek']}}</span>
                      
                        <div class="progress">
                          <div class="progress-bar width-60"></div>
                        </div>
                        <span class="progress-description">
                        More Than One Week
                                  </span>
                      </div>
                      <!-- /.info-box-content -->
                    </div>
                    <!-- /.info-box -->
                  </div>
                  <!-- /.col -->
                            <div class="col-xl-4 col-md-6 col-12">
                              <div class="info-box bg-info">
							   <a href="{{route('tenantEnquiries')}}">
                                <span class="info-box-icon push-bottom"><i class="material-icons">location_searching</i></span>
								</a>
                                <div class="info-box-content">
                                  <span class="info-box-text">Avg Response Time Enquirys</span>
                                    <span class="info-box-number">135</span>
                                  
                                  
                                  <div class="progress">
                                    <div class="progress-bar width-60"></div>
                                  </div>
                                  <span class="progress-description">
                                       Enquiry
                                      </span>
                                </div>
                                <!-- /.info-box-content -->
                              </div>
                              <!-- /.info-box -->
                            </div>
                            

                  </div>
            </div>
                          </div>
           </div>
                                 </div>
          <!-- end widget -->
<!-- sales lead window-->

<div class="row">                       
<!-- activities -->

<div class="col-md-12 col-sm-12 col-12">
                            <div class="card  card-box img-alin">
                                <div class="card-head">
                                    <header>Perfomance Analysis</header>
                 </div> 
                  <div class="card-body">
                    <ul class="list-horizontal">
                        <li>Assigned</li>
                        <li>Inprogress</li>
                        <li>Priliminary Doc</li>
                        <li>Final Doc</li>
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
<div class="col-md-12 col-sm-12 col-12">

<div class="page-title">Action</div>
  <div class="col-xl-4 col-md-6 col-12">
    <div class="row">
      <div class="info-box bg-blue">
        <a href="{{route('preliminaryApprovalList')}}">
        <span class="info-box-icon push-bottom"><i class="material-icons">recent_actors</i></span>
        </a>
        <div class="info-box-content">
          
          
          <span class="info-box-text">
          Pending Document Approval
           </span>
           <span class="info-box-number">
          {{$salesHead['pendingApproval']}}</span>
          <div class="progress">
            <div class="progress-bar width-60"></div>
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
         
<!-- listing -->
    <!-- graph -->          
    
    
    <!-- graph -->
</div>

 @push('dashboard_scripts')

<script> 
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
  $saleList = "'" .implode("', '", $salesHead['sales_person_list']['name'])."'";
  $assigned = implode(", ", $salesHead['sales_person_list']['value']['assigned']);
  $inprogess = implode(", ", $salesHead['sales_person_list']['value']['inprogess']);
  $prel_doc = implode(", ", $salesHead['sales_person_list']['value']['prel_doc']);
  $final_doc = implode(", ", $salesHead['sales_person_list']['value']['final_doc']);
@endphp
var ctx = document.getElementById("Chart1");
var myChart = new Chart(ctx, {
    type: 'horizontalBar',
    data: {
        labels: [{!! $saleList!!}],        
        datasets: [{
            data: [{!!$assigned!!}],
            backgroundColor: "rgba(91,156,214,1)",
            hoverBackgroundColor: "rgba(62,133,197,1)"
        },{
            data: [{!!$inprogess!!}],
            backgroundColor: "rgba(112,173,70,1)",
            hoverBackgroundColor: "rgba(96,155,55,1)"
        },{
            data: [{!!$prel_doc!!}],
            backgroundColor: "rgba(237,125,49,1)",
            hoverBackgroundColor: "rgba(213,103,28,1)"
        },{
            data: [{!!$final_doc!!}],
            backgroundColor: "rgba(255,192,0,1)",
            hoverBackgroundColor: "rgba(217,165,4,1)"
        }]
    },

    options: barOptions_stacked,
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
             @foreach($salesHead['vacantUnits']['comprehensive'] as $comprehensive)
              '{{$comprehensive}}' ,
             @endforeach
             ]
         }, {
             label: 'Normal',
             backgroundColor: 'rgb(255, 99, 132)',
             borderColor: 'rgb(255, 99, 132)',
             borderWidth: 1,
             data: [
              @foreach($salesHead['vacantUnits']['normal'] as $normal)
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

