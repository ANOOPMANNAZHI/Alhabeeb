
          @if(count(\Auth::user()->getRoleNames()) == 1)
                    <div class="page-bar">
                        <div class="page-title-breadcrumb">
                            <div class=" pull-left">
                                <div class="page-title">Dashboard</div>
                            </div>
                            <ol class="breadcrumb page-breadcrumb pull-right">
                                <li class="active">Dashboard</li>
                            </ol>
                        </div>
                    </div>
          @endif          
                   <!-- start widget -->
					<div class="state-overview">
						<div class="row">
					        <div class="col-xl-4 col-md-4 col-12">
					         <a href="{{route('role.index')}}"> <div class="info-box bg-blue">
					            <span class="info-box-icon push-bottom"><i class="fa fa-user" aria-hidden="true"></i>
</span>
					            <div class="info-box-content">
					              <span class="info-box-text">Roles</span>
					              <span class="info-box-number">{{$roles}}</span>
					              <div class="progress">
					                <div class="progress-bar width-60"></div>
					              </div>					              
					            </div>
					            <!-- /.info-box-content -->
					          </div></a>
					          <!-- /.info-box -->
					        </div>
					        <!-- /.col -->				    
					       

					        
					        <!-- /.col -->

                   <div class="col-xl-4 col-md-4 col-12">
                   <a href="{{route('employee.index')}}"> <div class="info-box bg-purple">
                      <span class="info-box-icon push-bottom"><i class="fa fa-users" aria-hidden="true"></i>
</span>
                      <div class="info-box-content">
                        <span class="info-box-text">Employee</span>
                        <span class="info-box-number">{{$employee}}</span>
                        <div class="progress">
                          <div class="progress-bar width-60"></div>
                        </div>                        
                      </div>
                      <!-- /.info-box-content -->
                    </div></a>
                    <!-- /.info-box -->
                  </div>



                   <div class="col-xl-4 col-md-4 col-12">
                   <a href="{{route('workFlowProcess.index')}}"> <div class="info-box bg-danger">
                      <span class="info-box-icon push-bottom"><i class="fa fa-cogs" aria-hidden="true"></i></span>
                      <div class="info-box-content">
                        <span class="info-box-text">WorkFlow</span>
                        <span class="info-box-number">{{$workFlowProcess}}</span>
                        <div class="progress">
                          <div class="progress-bar width-60"></div>
                        </div>                        
                      </div>
                      <!-- /.info-box-content -->
                    </div></a>
                    <!-- /.info-box -->
                  </div>
					       
                          

					      </div>
                          <!-- /rwo wnd -->
						</div>



            <div>
                <div class="row">

                   <div class="col-md-12 col-sm-12">
        <div class="card  card-box">
            
            <div class="card-body ">
            
             <div class="card-head">
					<header>Employee</header>
					<div class="clr"></div>
						<a href="{{route('employee.index')}}" class="btn btn-circle btn-primary align-right">
							View All
						</a>
						<!--<a class="t-collapse btn-color fa fa-chevron-down" href="javascript:;"></a>
						<a class="t-close btn-color fa fa-times" href="javascript:;"></a> -->
					
			</div>
             <div class="clr"></div>
           
              <div class="table-responsive1">
              <table class="table display product-overview mb-30" id="dtBasicExample">
                <thead>
                    <tr>
                        <th>Sl No.</th>
                        <th>Employee Code</th>
                        <th>Employee Name</th>
                        <th>Employee Designation</th>
                        <th>Employee Mob</th>                        
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($employees as $employee)
                    <tr>
                        <td>{{$loop->iteration}}</td>
                        <td>{{$employee->employee_code}}</td>
                        <td>{{$employee->employee_name}}</td>
                         <td>{{$employee->designations->designation_name}}</td>
                        <td>{{$employee->employee_contact_no}}</td>
                        
                        <td>
                        <a href="{{route('employee.show',$employee->id)}}" title="View" class="btn btn-tbl-view btn-xs">
                            <i class="fa fa-eye "></i>
                        </a>          
                        </td>
                    </tr>  
                    @empty
                    <tr class="no-record">
                        <td colspan="2">
                        <p>No Records</p>
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

                     
                  
