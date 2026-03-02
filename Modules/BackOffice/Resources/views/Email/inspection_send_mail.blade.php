@component('mail::message')

Hi {{$tenantName}},
</br></br><br>

This is to inform you that the Your Inspection Report for the Contract No : <b>{{$tenantContract->tenant_contract_no}} </b>.
</br></br>

<div class="col-md-12"> 
	<div class="card card-box salesSearchBox ">
		<!-- ends -->
		<div class="row">
			<div class="col-sm-12">
				<div class="card card-box salesLeadBox">
					<div class="card-head">
						<div class="col"><h4></h4></div>
					</div>
					<div class="card-body">
						<div class="col">
							<div class="row">

								<div class="col leadInformation">
									<div class="table-responsive1">
										<table class="table" border="1" style="border-color: #cfcfcff7;
    border-spacing: 0;">
											<thead>
												<tr style="background: #f5f5f5;">
													<th>Contract No</th>
													<th>Building Name</th>
													<th>Building No</th>
													<th>Unit No</th>
													<th>Unit Type</th>
													<th>Tenant Name</th>
													<th>Mob No</th>
													<th>Location</th>
													<th>Way No</th>
													<th>OutStanding</th>
												</tr>
											</thead>
											<tbody>
												<tr>
													<td style="padding: 0 5px 0 5px;">{{$tenantContract->tenant_contract_no}}</td>
													<td style="padding: 0 5px 0 5px;">{{$tenantContract->building->building_name}}</td>
													<td style="padding: 0 5px 0 5px;">{{$tenantContract->building->building_no}}</td>
													<td style="padding: 0 5px 0 5px;">{{$tenantContract->unit->unit_code}}</td>
													<td style="padding: 0 5px 0 5px;">{{$tenantContract->unit->unit->unit_types_name}}</td>
													<td style="padding: 0 5px 0 5px;">{{$tenantContract->tenant->tenant_name}}</td>
													<td style="padding: 0 5px 0 5px;">{{$tenantContract->tenant->tenant_contact_no}}</td>
													<td style="padding: 0 5px 0 5px;">{{$tenantContract->tenant->location->locations_name??'NA'}}</td>
													<td style="padding: 0 5px 0 5px;">{{$tenantContract->tenant->tenant_contact_address??'NA'}}</td>
													<td style="padding: 0 5px 0 5px;">Yes</td>
												</tr>
											</tbody>
										</table>

									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!--ends -->
		<div class="row">
			<div class="col-sm-12">
				<div class="card card-box salesLeadBox">
					<div class="card-head">
						<div class="col"><h4></h4></div>
					</div>
					<div class="card-body">
						<div class="col">
							<div class="row">

								<div class="col leadInformation">
									<div class="table-responsive1">
										<table class="table" border="1" style="border-color: #cfcfcff7;
    border-spacing: 0;">
											<thead>
												<tr>

													<th>Start Date</th>
													<th>End Date</th>
													<th>Duration</th>
													<th>Last Paid</th>
													<th>OutStanding Rent</th>
													<th>TakeOver Date</th>
													<th>Termination Date</th>
													<th>Remark</th>
													<th>Comment</th>
												</tr>
											</thead>
											<tbody>
												<tr>

													<td style="padding: 0 5px 0 5px;">{{$tenantContract->tenant_contract_start_date->format('d/m/Y')?? "NA"}}</td>

													<td style="padding: 0 5px 0 5px;">{{$tenantContract->tenant_contract_valid_to_date->format('d/m/Y')?? "NA"}}</td>

													<td style="padding: 0 5px 0 5px;">@php 
														$duration = explode('-',$tenantContract->tenant_contract_duration_countdown)
														@endphp
														{{$duration[0]}} Year
														{{$duration[1]}} Month
														{{$duration[2]}} Days
													</td>
													<td style="padding: 0 5px 0 5px;">{{number_format($tenantContract->tenant_contract_last_paid_amt, 3,",","")." OMR"}}</td>
													<td style="padding: 0 5px 0 5px;">{{number_format($tenantContract->tenant_contract_os, 3,",","")." OMR"}}</td>
													<td style="padding: 0 5px 0 5px;">{{$tenantContract->terminationContract->termination_takenover_date->format('d/m/Y')?? "NA"}}</td>
													<td style="padding: 0 5px 0 5px;">{{$tenantContract->terminationContract->termination_date->format('d/m/Y')?? "NA"}}</td>
													<td style="padding: 0 5px 0 5px;">{{$tenantContract->terminationContract->termination_remark?? "NA"}}</td>
													<td style="padding: 0 5px 0 5px;">{{$termination->supervisor_comment?? "NA"}}</td>
												</tr>
											</tbody>
										</table>

									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- ends -->
		<div class="row">
			<div class="col-sm-12">
				<div class="card card-box salesLeadBox">
					<div class="card-head">
						<div class="col"><h4></h4></div>
					</div>
					<div class="card-body">
						<div class="col">
							<div class="row">

								<div class="col leadInformation">
									<div class="table-responsive1">
										<table class="table" border="1" style=" width:70%;border-color: #cfcfcff7;
    border-spacing: 0;">
											<thead>
												<tr>
													<th>Electricity Acc/No</th>
													<th>Closing Reading</th>
													<th>Amount RO </th>
												</tr>
											</thead>
											<tbody>
												<tr>
													<td style="padding: 0 5px 0 5px;">{{$termination->termination_electricity_acc_no}}</td>
													<td style="padding: 0 5px 0 5px;">{{$termination->termination_electricity_close_reading}}</td>
													<td style="padding: 0 5px 0 5px;">{{$termination->termination_electricity_amount}}</td>
												</tr>
											</tbody>
										</table>

									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- ends -->
				<div class="row">
			<div class="col-sm-12">
				<div class="card card-box salesLeadBox">
					<div class="card-head">
						<div class="col"><h4></h4></div>
					</div>
					<div class="card-body">
						<div class="col">
							<div class="row">

								<div class="col leadInformation">
									<div class="table-responsive1">
										<table class="table" border="1" style=" width:70%;border-color: #cfcfcff7;
    border-spacing: 0;" >
											<thead>
												<tr>
													<th>Water Acc/No </th>
													<th>Closing Reading </th>
													<th>Amount RO  </th>
												</tr>
											</thead>
											<tbody>
												<tr>
													<td style="padding: 0 5px 0 5px;">{{$termination->termination_water_acc_no}}</td>
													<td style="padding: 0 5px 0 5px;">{{$termination->termination_water_close_reading}}</td>
													<td style="padding: 0 5px 0 5px;">{{$termination->termination_water_amount}}</td>
												</tr>
											</tbody>
										</table>

									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- ends -->
		@if(count($groupedWork)> 0)

		<div class="row">
			<div class="col-sm-12">
				<div class="card card-box salesLeadBox">
					@foreach($groupedWork as $checklist)
					<div class="card-head">
						<div class="col"><h4></h4></div>
					</div>
					<div class="card-body">
						<div class="col">
							<div class="row">

								<div class="col leadInformation">
									<div class="table-responsive1">
										<table class="table" border="1" style="width:70%;border-color: #cfcfcf99;
    border-spacing: 0;">
											<thead>
												<tr><td colspan="2" style="font-size: 15px;color: #000; font-weight: 700;">{{$checklist->first()->work->works_code}}</td></tr>
												<tr style="background: #f5f5f5;">
													<th>Item</th>
													<th>Amount</th>
												</tr>
											</thead>
											<tbody>
												@forelse ($checklist as $subWork)
												<tr>
													<td style="text-align:left;
    padding: 0 0 0 25px;">{{$subWork->subWorks->sub_work}}</td>
													<td>{{$subWork->termination_amount}}</td>
												</tr>
												@empty 
												<tr>
													<td colspan="3" align="center">
														<p>No Record</p>
													</td>
												</tr>
												@endforelse

											</tbody>
										</table>

									</div>
								</div>
							</div>
						</div>

					</div>
					@endforeach
				</div>
			</div>
		</div>

		@endif
		<div class="row">
			<div class="col-sm-12">
				<div class="card card-box salesLeadBox">
					<div class="card-head">
						<div class="col"><h4></h4></div>
					</div>
					<div class="card-body">
						<div class="col">
							<div class="row">

								<div class="col leadInformation">
									<div class="table-responsive1">
										<table class="table" border="1" style="width:70%;border-color: #cfcfcf99; border-spacing: 0;">
											<thead>
												<tr><td colspan="2" style="font-size: 15px;color: #000; font-weight: 700;">Others</td></tr>
												<tr style="background: #f5f5f5;">
													<th>Item</th>
													<th>Amount</th>
												</tr>
											</thead>
											<tbody>
												@forelse ($terminationChecklistOther as $other)
												<tr>
													<td style="text-align:left;padding: 0 0 0 25px;">{{$other->termination_other_work}}</td>
													<td >{{$other->termination_amount}}</td>
												</tr>
												@empty 
												<tr>
													<td colspan="3" align="center">
														<p>No Record</p>
													</td>
												</tr>
												@endforelse

											</tbody>
										</table>

									</div>
								</div>
							</div>
						</div>

					</div>
				</div>
			</div>
		</div>

		@if(count($tenantContract->terminationChecklistOther)> 0 || count($groupedWork)> 0)

		<div class="dataSearchBox">
			<div class="card-body row">

				<div class="col-lg-6 p-t-20"> 
					<div class = "txt-full-width" style="text-align:left;">
						<h5 class="details"><b>Total Amount :  </b><span>{{numberFormat($termination->termination_total_amount)}} OMR</span></h5>

					</div>
				</div>
				<div class="col-lg-6 p-t-20"> 
					<div class = "txt-full-width" style="text-align:left;">
						<h5 class="details"><b>Total of Electricity and Water  :  </b><span>{{$termination->termination_total_elec_water_amount}}</span></h5>
					</div>
				</div>
				<div class="col-lg-6 p-t-20"> 
					<div class = "txt-full-width" style="text-align:left;">
						<h5 class="details"><b>Discount on Total Maintenance Due:  </b><span>{{$termination->termination_discount_maintenance_due}}</span></h5>
					</div>
				</div>
				<div class="col-lg-6 p-t-20"> 
					<div class = "txt-full-width" style="text-align:left;">
						<h5 class="details"><b>Net Amount:  </b><span>{{numberFormat($termination->termination_net_amount)}} OMR</span></h5>

					</div>
				</div>  
				@if($tenantContract->tenant_contract_os)
				<div class="col-lg-6 p-t-20"> 
					<div class = "txt-full-width" style="text-align:left;">
						<h5 class="details"><b>Total Due:  </b><span>{{numberFormat($tenantContract->tenant_contract_os)}} OMR</span></h5>

					</div>
				</div>
				@endif
			</div>
		</div>
		@endif
	</div>
</div>

Sincerely,</br>
Admin

@endcomponent