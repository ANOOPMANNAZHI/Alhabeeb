 
    @if(count(\Auth::user()->getRoleNames()) == 1)
        <div class="page-bar">
            <div class="page-title-breadcrumb">
                <div class=" pull-left">
                    <div class="page-title">Facility Manager - Dashboard</div>
                </div>
                {{ Breadcrumbs::render('facility-manager-dashboard')}}  
            </div>
        </div>
    @endif





    <div class="row">

    <div class="col-xl-4 col-md-4 col-12">  
     <div class="info-box bg-blue">
        <a href="{{route('takenOverNotdoneForFacilityManager')}}" >
            <span class="info-box-icon push-bottom infoBoxSmall">
              <i class="material-icons">remove_circle_outline</i>
            </span>
          
        <div class="info-box-content">         
          <span class="info-box-number">{{$facilityManager['takenOverNotdone']}}</span>
          <span class="info-box-text">Taken Over Not Done Even After</span>          
          <div class="progress">
            <div class="progress-bar width-60"></div>
          </div>
           <span class="progress-description">             
            7 Days Of Contract Expiry Date
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
     <a href="{{route('complaint.index')}}" >  
      <div class="info-box bg-orange">        
        <span class="info-box-icon push-bottom">
          <i class="material-icons">person_pin</i>
        </span>      
        <div class="info-box-content">          
           <span class="info-box-number ">{{$facilityManager['vipCallsNotClosed']}}</span>
           <span class="info-box-text">VIP Maintenance Cases</span>         
          <div class="progress">
            <div class="progress-bar width-60"></div>
          </div>
           <span class="progress-description">
              Not Closed Within 2 Days
          </span>          
        </div>       
      </div>
       </a>
    </div>
    <!-- /.col -->



    <div class="col-xl-4 col-md-4 col-12">
       <a href="{{route('keyManagement.index')}}">
      <div class="info-box bg-purple">       
        <span class="info-box-icon push-bottom">
          <i class="material-icons">trending_up</i>
        </span>
       
        <div class="info-box-content">          
           <span class="info-box-number">{{$facilityManager['keysNotYetHandedOver']}}</span>
           <span class="info-box-text"> 
            Keys Hand Over
           </span>         
          <div class="progress">
            <div class="progress-bar width-40"></div>
          </div>
          <span class="progress-description">
              Keys With Takeover Team And Maintenance Team
          </span>
        </div>
      </div>
         </a>
        <!-- /.info-box-content -->
      </div>
       
</div>