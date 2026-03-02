@extends('layouts.plms-app')



@section('content')

 

    <div class="page-bar">
        <div class="page-title-breadcrumb">
            <div class=" pull-left">
                <div class="page-title">Call Center - Dashboard</div>
            </div>
             {{ Breadcrumbs::render('call-center-dashboard')}}                           
        </div>
    </div>




                       <div class="state-overview mb-4">
						<div class="row">
					        <div class="col-xl-6 col-md-6 col-12">
					          <div class="info-box bg-blue">
					            <span class="info-box-icon push-bottom"><i class="material-icons">today</i></span>
					            <div class="info-box-content">					              
					              <span class="info-box-number">{{$enquiry_today}}</span>
                                  <span class="info-box-text">Calls received today! </span>
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
					        <!-- /.col -->
					        
					        <!-- /.col -->
					        <div class="col-xl-6 col-md-6 col-12">
					          <div class="info-box bg-success">
					            <span class="info-box-icon push-bottom"><i class="material-icons">view_week</i></span>
					            <div class="info-box-content">
					               <span class="info-box-number">{{$enquiry_week}}</span>
                                  <span class="info-box-text">Calls received this week!</span>
					              <div class="progress">
					                <div class="progress-bar width-60"></div>
					              </div>
					              <span class="progress-description">
					                    
					                  </span>
					            </div>
					           
					          </div>
					         
					        </div>

					      </div>
						</div>



	 @include('sales::enquiry_form')					



    



  @endsection  