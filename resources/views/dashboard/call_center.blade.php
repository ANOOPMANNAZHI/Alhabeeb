 
@if(count(\Auth::user()->getRoleNames()) == 1)
<div class="page-bar">
	<div class="page-title-breadcrumb">
		<div class=" pull-left">
			<div class="page-title">Call Center - Dashboard</div>
		</div>
		{{ Breadcrumbs::render('call-center-dashboard')}}                           
	</div>
</div>
@endif



<div class="state-overview mb-4 one" style="display: none;">
	<div class="row">
		<div class="col-xl-6 col-md-6 col-12">
			<a href="{{ route('tenantEnquiries',['created_at'=>$today,'params'=>$param])}}">
				<div class="info-box bg-blue">
					<span class="info-box-icon push-bottom">
						<i class="fa fa-phone" aria-hidden="true"></i>
					</span>
					<div class="info-box-content">					              
						<span class="info-box-number">{{$callCenter['enquiryTodayCount']}}</span>
						<span class="info-box-text">Calls Received Today! </span>
						<div class="progress">
							<div class="progress-bar width-60"></div>
						</div>
						<span class="progress-description">
							Tenant 
						</span>
					</div>
					<!-- /.info-box-content -->
				</div>
			</a>
			<!-- /.info-box -->
		</div>
		<!-- /.col -->

		<!-- /.col -->
		<div class="col-xl-6 col-md-6 col-12">
		<a href="{{ route('tenantEnquiries',['params'=>$param,'list_by_week'=>true])}}">
				<div class="info-box bg-success">
					<span class="info-box-icon push-bottom"><i class="fa fa-phone" aria-hidden="true"></i></span>
					<div class="info-box-content">
						<span class="info-box-number">{{$callCenter['enquiryWeekCount']}}</span>
						<span class="info-box-text">Calls Received This Week!</span>
						<div class="progress">
							<div class="progress-bar width-60"></div>
						</div>
						<span class="progress-description">
							Tenant 
						</span>
					</div>

				</div>
			</a>

		</div>

	</div>
</div>


<div class="state-overview mb-4 two" style="display: none;">
	<div class="row">
		<div class="col-xl-6 col-md-6 col-12">
			<a href="{{route('landlordEnquiries',['created_at'=>$today,'params'=>$param])}}">
				<div class="info-box bg-blue">
					<span class="info-box-icon push-bottom">
						<i class="fa fa-phone" aria-hidden="true"></i>
					</span>
					<div class="info-box-content">					              
						<span class="info-box-number">{{$callCenter['landlordEnquiryCount']}}</span>
						<span class="info-box-text">Calls Received Today! </span>
						<div class="progress">
							<div class="progress-bar width-60"></div>
						</div>
						<span class="progress-description">
							Landlord 
						</span>
					</div>
					<!-- /.info-box-content -->
				</div>
			</a>
			<!-- /.info-box -->
		</div>
		<!-- /.col -->

		<!-- /.col -->
		<div class="col-xl-6 col-md-6 col-12">
			<a href="{{ route('landlordEnquiries',['params'=>$param,'list_by_week'=>true])}}">
				<div class="info-box bg-success">
					<span class="info-box-icon push-bottom"><i class="fa fa-phone" aria-hidden="true"></i></span>
					<div class="info-box-content">
						<span class="info-box-number">{{$callCenter['landlordEnquiryWeekCount']}}</span>
						<span class="info-box-text">Calls Received This Week!</span>
						<div class="progress">
							<div class="progress-bar width-60"></div>
						</div>
						<span class="progress-description">
							Landlord 
						</span>
					</div>

				</div>
			</a>

		</div>

	</div>
</div>


<div class="state-overview mb-4 three" style="display: none;">
	<div class="row">
		<div class="col-xl-6 col-md-6 col-12">
			<a href="{{route('complaint.index',['created_at'=>$today,'params'=>$param])}}">
				<div class="info-box bg-blue">
					<span class="info-box-icon push-bottom">
						<i class="fa fa-phone" aria-hidden="true"></i>
					</span>
					<div class="info-box-content">					              
						<span class="info-box-number">{{$callCenter['complaintEnquiryCount']}}</span>
						<span class="info-box-text">Calls Received Today! </span>
						<div class="progress">
							<div class="progress-bar width-60"></div>
						</div>
						<span class="progress-description">
							Maintenance 
						</span>
					</div>
					<!-- /.info-box-content -->
				</div>
			</a>
			<!-- /.info-box -->
		</div>
		<!-- /.col -->

		<!-- /.col -->
		<div class="col-xl-6 col-md-6 col-12">
			<a href="{{ route('complaint.index',['params'=>$param,'list_by_week'=>true])}}">
				<div class="info-box bg-success">
					<span class="info-box-icon push-bottom"><i class="fa fa-phone" aria-hidden="true"></i></span>
					<div class="info-box-content">
						<span class="info-box-number">{{$callCenter['complaintEnquiryWeekCount']}}</span>
						<span class="info-box-text">Calls Received This Week!</span>
						<div class="progress">
							<div class="progress-bar width-60"></div>
						</div>
						<span class="progress-description">
							Maintenance 
						</span>
					</div>

				</div>
			</a>

		</div>

	</div>
</div>
@include('sales::enquiry_form')					


