  @extends('layouts.plms-app')



@section('content')

                    <div class="page-bar">
                        <div class="page-title-breadcrumb">
                            <div class=" pull-left">
                                <div class="page-title">Sales Cordinator - Dashboard</div>
                            </div>
                            {{ Breadcrumbs::render('sales-cordinator-dashboard')}}  
                        </div>
                    </div>
                   <!-- start widget -->
					<div class="state-overview mb-4">
<div class="row">
    <div class="col-xl-4 col-md-4 col-12">
  <a href="{{route('leadAssign.index')}}" >
      <div class="info-box bg-blue">
        <span class="info-box-icon push-bottom"><i class="material-icons">remove_circle_outline</i></span>
        <div class="info-box-content">                                
          <span class="info-box-number">{{$enquiries_unassigned}}</span>
          <span class="info-box-text">Un assigned Enquiry</span>
          <div class="progress">
            <div class="progress-bar width-60"></div>
          </div>
          <span class="progress-description">
                Last 90 Days.
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
     <a href="{{route('leadAssign.assignedList')}}">
      <div class="info-box bg-orange">
        <span class="info-box-icon push-bottom"><i class="material-icons">person_pin</i></span>
        <div class="info-box-content">
           <span class="info-box-number">{{$enquiries_assigned}}</span>
          <span class="info-box-text">Assigned Enquiry</span>
          <div class="progress">
            <div class="progress-bar width-60"></div>
          </div>
          <span class="progress-description">
                 Last 90 Days.
              </span>
        </div>
       
      </div>
     </a>
     
    </div>
    <!-- /.col -->
    <div class="col-xl-4 col-md-4 col-12">
      <a href="{{route('inprogressList')}}">
      <div class="info-box bg-purple">
        <span class="info-box-icon push-bottom"><i class="material-icons">trending_up</i></span>
        <div class="info-box-content">              
          <span class="info-box-number">{{$enquiries_inprogress}}</span>
          <span class="info-box-text"> In Progress</span>
          <div class="progress">
            <div class="progress-bar width-40"></div>
          </div>
          <span class="progress-description">
                Last 90 Days.
           </span>
        </div>
        <!-- /.info-box-content -->
      </div>
      </a>

      <!-- /.info-box -->
    </div>
<!-- /.col -->
</div>
						<div class="row">
					        <div class="col-xl-4 col-md-4 col-12">
					          <div class="info-box bg-watermelon">
					            <span class="info-box-icon push-bottom infoBoxSmall"><i class="material-icons">cloud_upload</i></span>
					            <div class="info-box-content">					              
					              <span class="info-box-number">450</span>
                                  <span class="info-box-text">Documentation </span>
					            </div>
					            <!-- /.info-box-content -->
					          </div>
					          <!-- /.info-box -->
					        </div>
					        <!-- /.col -->
					        
					        <!-- /.col -->
					        <div class="col-xl-4 col-md-4 col-12">
					          <div class="info-box bg-mint">
					            <span class="info-box-icon push-bottom infoBoxSmall"><i class="material-icons">grade</i></span>
					            <div class="info-box-content">
					               <span class="info-box-number">15</span>
                                  <span class="info-box-text">Win</span>
			
					            </div>
					           
					          </div>
					         
					        </div>
					        <!-- /.col -->
                           
                            <!-- /.col -->
                            <div class="col-xl-4 col-md-4 col-12">
                              <div class="info-box bg-lime">
                                <span class="info-box-icon push-bottom infoBoxSmall"><i class="material-icons">business</i></span>
                                <div class="info-box-content">              
                                  <span class="info-box-number">15</span>
                                  <span class="info-box-text">Flat/Villa/Commercial</span>
                                </div>
                                <!-- /.info-box-content -->
                              </div>
                              <!-- /.info-box -->
                            </div>
					      </div>



						</div>
					<!-- end widget -->
<!-- sales lead window-->

<div class="row">                       
<!-- activities -->
<div class="col-md-4 col-sm-12 col-12">
   <div class="card  card-box">
                                <div class="card-head">
                                    <header>Vacant Units</header>
                                    <div class="tools">
                                        <a class="fa fa-repeat btn-color box-refresh" href="javascript:;"></a>
                                        <a class="t-collapse btn-color fa fa-chevron-down" href="javascript:;"></a>
                                        <a class="t-close btn-color fa fa-times" href="javascript:;"></a>
                                    </div>
                                </div>
                                <div class="card-body no-padding height-9">
                                    <div class="row text-center">
                                        <div class="col-sm-4 col-6">
                                            <h4 class="margin-0">100</h4>
                                            <p class="text-muted"> Total</p>
                                        </div>
                                        <div class="col-sm-4 col-6">
                                            <h4 class="margin-0">75 </h4>
                                            <p class="text-muted">Comprehensive</p>
                                        </div>
                                        <div class="col-sm-4 col-6">
                                            <h4 class="margin-0">25 </h4>
                                            <p class="text-muted">Normal</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div id="donut_chart" class="width-100 height-250"></div>
                                    </div>
                                </div>
                            </div>
</div>


                        
                        <div class="col-md-8 col-sm-12 col-12">
                            <div class="card  card-box">
                                <div class="card-head">
                                    <header>Notifications</header>
                                    <div class="tools">
                                        <a class="fa fa-repeat btn-color box-refresh" href="javascript:;"></a>
                                        <a class="t-collapse btn-color fa fa-chevron-down" href="javascript:;"></a>
                                        <a class="t-close btn-color fa fa-times" href="javascript:;"></a>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="noti-information notification-menu">
                                            <div class="notification-list mail-list not-list small-slimscroll-style">
                                                <a href="javascript:;" class="single-mail"> <span class="icon bg-primary"> <i class="fa fa-user-o"></i>
                                                </span> <span class="text-purple">Abhay Jani</span> Added as new Enquiry
                                                    <span class="notificationtime">
                                                        <small>Just Now</small>
                                                    </span>
                                                </a>
                                                <a href="javascript:;" class="single-mail"> <span class="icon blue-bgcolor"> <i class="fa fa-envelope-o"></i>
                                                </span> <span class="text-purple">John Doe</span> moved to documentation stage.
                                                    <span class="notificationtime">
                                                        <small>Just Now</small>
                                                    </span>
                                                </a>
                                                <a href="javascript:;" class="single-mail"> <span class="icon bg-success"> <i class="fa fa-check-square-o"></i>
                                                </span> <span class="text-purple">Abhay Jani</span> deal is WON!
                                                    <span class="notificationtime">
                                                        <small>Just Now</small>
                                                    </span>
                                                </a>
                                                
                                                <a href="javascript:;" class="single-mail"> <span class="icon bg-warning"> <i class="fa fa-warning"></i>
                                                </span>  <span class="text-purple">John Doe</span> Waiting for documentation approval.
                                                    <span class="notificationtime">
                                                       <small>1 Week Ago</small>
                                                    </span>
                                                </a>


                                                
                                                
                                                
                                            </div>
                                            <div class="full-width text-center p-t-10" >
                                                <button type="button" class="btn purple btn-outline btn-circle margin-0">View All</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>                          
<!-- activities -->


                        <!-- graph -->
                        <div class="col-md-12 col-sm-12">
                            <div class="card  card-box">
                                <div class="card-head">
                                    <header></header>
                                    <div class="tools">
                                        <a class="fa fa-repeat btn-color box-refresh" href="javascript:;"></a>
                                        <a class="t-collapse btn-color fa fa-chevron-down" href="javascript:;"></a>
                                        <a class="t-close btn-color fa fa-times" href="javascript:;"></a>
                                    </div>
                                </div>
                                <div class="card-body ">
                                  <div class="table-wrap">
                                        <div class="table-responsive">
                                            <table class="table display product-overview mb-30" id="support_table5">
                                                <thead>
                                                    <tr>
                                                        <th>No</th>
                                                        <th>Enquiry Name</th>
                                                        <th>Company</th>
                                                        <th>Email</th>
                                                        <th>Phone</th>
                                                        <th>Enquiry Source</th>
                                                        <th>Edit</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td>1</td>
                                                        <td>Jens Brincker</td>
                                                        <td>Edward s Kats</td>
                                                        <td>info@edward.com</td>
                                                        <td>+91 9XXX XXX XXX</td>
                                                        <td>Trade Show</td>
                                                        <td>
                                                            <a href="edit_booking.html" class="btn btn-tbl-edit btn-xs">
                                                                <i class="fa fa-eye"></i>
                                                            </a>
                                                            <button class="btn btn-tbl-delete btn-xs">
                                                                <i class="fa fa-trash-o "></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                   
                                                    
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>  
                                </div>
                            </div>
                        </div>
                        <!-- graph -->
                    </div>
               

            @endsection