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
					         <a href="#"> <div class="info-box bg-blue">
					            <span class="info-box-icon push-bottom"><i class="material-icons extra">style</i></span>
					            <div class="info-box-content">
					              <span class="info-box-text">Tenant</span>
					              <span class="info-box-number">450</span>
					              <div class="progress">
					                <div class="progress-bar width-60"></div>
					              </div>
					              <span class="progress-description">
					                    Vacating in 30 days.
					                  </span>
					            </div>
					            <!-- /.info-box-content -->
					          </div></a>
					          <!-- /.info-box -->
					        </div>
					        <!-- /.col -->
					        <div class="col-xl-4 col-md-4 col-12">
					          <div class="info-box bg-warning">
					            <span class="info-box-icon push-bottom"><i class="material-icons extra">card_travel</i></span>
					            <div class="info-box-content">
					              <span class="info-box-text">Contracts Expired</span>
					              <span class="info-box-number">155</span>
					              <div class="progress">
                                    <div class="progress-bar width-60"></div>
					              </div>
					              <span class="progress-description">
					                   Within grace periode.
					                  </span>
					            </div>
					            <!-- /.info-box-content -->
					          </div>
					          <!-- /.info-box -->
					        </div>
					        <!-- /.col -->
					        
					        <!-- /.col -->
					        <div class="col-xl-4 col-md-4 col-12">
                              <div class="info-box bg-success">
                                <span class="info-box-icon push-bottom"><i class="material-icons extra">monetization_on</i></span>
                                <div class="info-box-content">
                                  <span class="info-box-text">Contracts Expiring</span>
                                  <span class="info-box-number">13</span>
                                  <div class="progress">
                                   <div class="progress-bar width-60"></div>
                                  </div>
                                  <span class="progress-description">
                                        In 30 days.
                                      </span>
                                </div>
                                <!-- /.info-box-content -->
                              </div>
                              <!-- /.info-box -->
                            </div>
                            <div class="col-xl-4 col-md-4 col-12">
                              <div class="info-box bg-b-danger">
                                <span class="info-box-icon push-bottom"><i class="material-icons extra">assignment_late</i></span>
                                <div class="info-box-content">
                                  <span class="info-box-text">Contracts Expired </span>
                                  <span class="info-box-number">52</span>
                                  <div class="progress">
                                   <div class="progress-bar width-60"></div>
                                  </div>
                                  <span class="progress-description">
                                       Beyond grace period
                                      </span>
                                </div>
                                <!-- /.info-box-content -->
                              </div>
                              <!-- /.info-box -->
                            </div>

                            <div class="col-xl-4 col-md-4 col-12">
                              <div class="info-box bg-b-purple">
                                <span class="info-box-icon push-bottom"><i class="material-icons extra">assistant</i></span>
                                <div class="info-box-content">
                                  <span class="info-box-text">Contracts Renewed</span>
                                  <span class="info-box-number">52</span>
                                  <div class="progress">
                                   <div class="progress-bar width-60"></div>
                                  </div>
                                  <span class="progress-description">
                                        Not Registered.
                                      </span>
                                </div>
                                <!-- /.info-box-content -->
                              </div>
                              <!-- /.info-box -->
                            </div>
                         <div class="col-xl-4 col-md-4 col-12">
                              <div class="info-box bg-b-blue">
                                <span class="info-box-icon push-bottom"><i class="material-icons extra">beenhere</i></span>
                                <div class="info-box-content">
                                  <span class="info-box-text">Take-Over Pending</span>
                                  <span class="info-box-number">13</span>
                                  <div class="progress">
                                   <div class="progress-bar width-60"></div>
                                  </div>
                                  <span class="progress-description">
                                       <a class="tooltips add-link" href="email_inbox.html" data-placement="top" data-original-title="Expired. But not takeover after the grace periode of 7 days."> Expired -  After grace periode of 7 days.</a>
                                      </span>
                                </div>
                                <!-- /.info-box-content -->
                              </div>
                              <!-- /.info-box -->
                            </div>
                            
					        <div class="col-xl-4 col-md-4 col-12">
                              <div class="info-box bg-purple">
                                <span class="info-box-icon push-bottom"><i class="material-icons extra">compare</i></span>
                                <div class="info-box-content">
                                  <span class="info-box-text">Reffered Items </span>
                                  <span class="info-box-number">450</span>
                                  <div class="progress">
                                    <div class="progress-bar width-60"></div>
                                  </div>
                                  <span class="progress-description">
                                      By takeover team.
                                      </span>
                                </div>
                                <!-- /.info-box-content -->
                              </div>
                              <!-- /.info-box -->
                            </div>
                            <!-- /.col -->
                            <div class="col-xl-4 col-md-4 col-12">
                              <div class="info-box bg-primary">
                                <span class="info-box-icon push-bottom"><i class="material-icons extra">contact_mail</i></span>
                                <div class="info-box-content">
                                  <span class="info-box-text">Contracts</span>
                                  <span class="info-box-number">155</span>
                                  <div class="progress">
                                   <div class="progress-bar width-60"></div>
                                  </div>
                                  <span class="progress-description">
                                        <a class="tooltips add-link" href="email_inbox.html" data-placement="top" data-original-title="Vacation Expired-Not Cancelled.">Vacation Expired-Not Cancelled.</a>
                                      </span>
                                </div>
                                <!-- /.info-box-content -->
                              </div>
                              <!-- /.info-box -->
                            </div>
                            <!-- /.col -->
                            
                            <!-- /.col -->
                           
                            <div class="col-xl-4 col-md-4 col-12">
                              <div class="info-box bg-mint">
                                <span class="info-box-icon push-bottom"><i class="material-icons extra">fiber_new</i></span>
                                <div class="info-box-content">
                                  <span class="info-box-text">Case List</span>
                                  <span class="info-box-number">52</span>
                                  <div class="progress">
                                    <div class="progress-bar width-60"></div>
                                  </div>
                                  <span class="progress-description">
                                      Not Registered.
                                      </span>
                                </div>
                                <!-- /.info-box-content -->
                              </div>
                              <!-- /.info-box -->
                            </div>
                            <div class="col-xl-6 col-md-4 col-12">
                              <div class="info-box bg-orange">
                                <span class="info-box-icon push-bottom"><i class="material-icons extra">fiber_new</i></span>
                                <div class="info-box-content">
                                  <span class="info-box-text">Legal list</span>
                                  <span class="info-box-number">52</span>
                                  <div class="progress">
                                    <div class="progress-bar width-60"></div>
                                  </div>
                                  <span class="progress-description">
                                     Total
                                      </span>
                                </div>
                                <!-- /.info-box-content -->
                              </div>
                              <!-- /.info-box -->
                            </div>
                            <div class="col-xl-6 col-md-4 col-12">
                              <div class="info-box bg-danger">
                                <span class="info-box-icon push-bottom"><i class="material-icons extra">fiber_new</i></span>
                                <div class="info-box-content">
                                  <span class="info-box-text">Landlrod Contract Expired</span>
                                  <span class="info-box-number">52</span>
                                  <div class="progress">
                                    <div class="progress-bar width-60"></div>
                                  </div>
                                  <span class="progress-description">
                                      60 Days.
                                      </span>
                                </div>
                                <!-- /.info-box-content -->
                              </div>
                              <!-- /.info-box -->
                            </div>




					      </div>
                          <!-- /rwo wnd -->
						</div>
                        <div>
                            <div class="row">
                                

                            </div>
                        </div>
					<!-- end widget -->
                     <!-- chart start -->
                     
                    
                     <!-- Chart end -->
                   
                     <!-- start Payment Details -->
                    <!-- <div class="row">
                        <div class="col-md-12 col-sm-12">
                            <div class="card  card-box">
                                <div class="card-head">
                                    <header>Legal Case List</header>
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
                                                        <th>Case No</th>
                                                        <th>Name</th>
                                                        <th>Assigned Date</th>
                                                        <th>Closed Date</th>
                                                        <th>Status</th>
                                                        <th>Edit</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td>1</td>
                                                        <td>Jens Brincker</td>
                                                        <td>23/05/2016</td>
                                                        <td>27/05/2016</td>
                                                        <td>
                                                            <span class="label label-sm label-success">Assigned</span>
                                                        </td>
                                                        
                                                        <td>
                                                            <a href="edit_booking.html" class="btn btn-tbl-edit btn-xs">
                                                                <i class="fa fa-pencil"></i>
                                                            </a>
                                                            <button class="btn btn-tbl-delete btn-xs">
                                                                <i class="fa fa-trash-o "></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>2</td>
                                                        <td>Mark Hay</td>
                                                        <td>24/05/2017</td>
                                                        <td>26/05/2017</td>
                                                        <td>
                                                            <span class="label label-sm label-info">Won</span>
                                                        </td>
                                                       
                                                        <td>
                                                            <a href="edit_booking.html" class="btn btn-tbl-edit btn-xs">
                                                                <i class="fa fa-pencil"></i>
                                                            </a>
                                                            <button class="btn btn-tbl-delete btn-xs">
                                                                <i class="fa fa-trash-o "></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>3</td>
                                                        <td>Anthony Davie</td>
                                                        <td>17/05/2016</td>
                                                        <td>21/05/2016</td>
                                                        <td>
                                                            <span class="label label-sm label-warning ">withdraw</span>
                                                        </td>
                                                        
                                                        <td>
                                                            <a href="edit_booking.html" class="btn btn-tbl-edit btn-xs">
                                                                <i class="fa fa-pencil"></i>
                                                            </a>
                                                            <button class="btn btn-tbl-delete btn-xs">
                                                                <i class="fa fa-trash-o "></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>4</td>
                                                        <td>David Perry</td>
                                                        <td>19/04/2016</td>
                                                        <td>20/04/2016</td>
                                                        <td>
                                                            <span class="label label-sm label-danger">Lost</span>
                                                        </td>
                                                        
                                                        <td>
                                                            <a href="edit_booking.html" class="btn btn-tbl-edit btn-xs">
                                                                <i class="fa fa-pencil"></i>
                                                            </a>
                                                            <button class="btn btn-tbl-delete btn-xs">
                                                                <i class="fa fa-trash-o "></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>5</td>
                                                        <td>Anthony Davie</td>
                                                        <td>21/05/2016</td>
                                                        <td>24/05/2016</td>
                                                        <td>
                                                            <span class="label label-sm label-success">Assigned</span>
                                                        </td>
                                                       
                                                        <td>
                                                            <a href="edit_booking.html" class="btn btn-tbl-edit btn-xs">
                                                                <i class="fa fa-pencil"></i>
                                                            </a>
                                                            <button class="btn btn-tbl-delete btn-xs">
                                                                <i class="fa fa-trash-o "></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>6</td>
                                                        <td>Alan Gilchrist</td>
                                                        <td>15/05/2016</td>
                                                        <td>22/05/2016</td>
                                                        <td>
                                                            <span class="label label-sm label-warning ">Withdraw</span>
                                                        </td>
                                                        
                                                        <td>
                                                            <a href="edit_booking.html" class="btn btn-tbl-edit btn-xs">
                                                                <i class="fa fa-pencil"></i>
                                                            </a>
                                                            <button class="btn btn-tbl-delete btn-xs">
                                                                <i class="fa fa-trash-o "></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>7</td>
                                                        <td>Mark Hay</td>
                                                        <td>17/06/2016</td>
                                                        <td>18/06/2016</td>
                                                        <td>
                                                            <span class="label label-sm label-info ">Won</span>
                                                        </td>
                                                       
                                                        <td>
                                                            <a href="edit_booking.html" class="btn btn-tbl-edit btn-xs">
                                                                <i class="fa fa-pencil"></i>
                                                            </a>
                                                            <button class="btn btn-tbl-delete btn-xs">
                                                                <i class="fa fa-trash-o "></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>8</td>
                                                        <td>Sue Woodger</td>
                                                        <td>15/05/2016</td>
                                                        <td>17/05/2016</td>
                                                        <td>
                                                            <span class="label label-sm label-danger">Lost</span>
                                                        </td>
                                                        
                                                        <td>
                                                            <a href="edit_booking.html" class="btn btn-tbl-edit btn-xs">
                                                                <i class="fa fa-pencil"></i>
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
                    </div> -->
                    <!-- end Payment Details -->
                    <div class="row">
							<!-- <div class="col-lg-8 col-md-12 col-sm-12 col-12">
                                   <div class="card-box ">
                                <div class="card-head">
                                    <header>Guest Review</header>
                                    <div class="tools">
                                        <a class="fa fa-repeat btn-color box-refresh" href="javascript:;"></a>
	                                    <a class="t-collapse btn-color fa fa-chevron-down" href="javascript:;"></a>
	                                    <a class="t-close btn-color fa fa-times" href="javascript:;"></a>
                                    </div>
                                </div>
                                <div class="card-body ">
                                  <div class="row">
                                        <ul class="docListWindow small-slimscroll-style">
                                            <li>
                                            	<div class="row">
	                                            	<div class="col-md-8 col-sm-8">
		                                                <div class="prog-avatar">
		                                                    <img src="assets/img/user/user1.jpg" alt="" width="40" height="40">
		                                                </div>
		                                                <div class="details">
		                                                    <div class="title">
		                                                        <a href="#">Rajesh Mishra</a> 
		                                                        <p class="rating-text">Awesome!!! Highly recommend</p>
		                                                    </div>
		                                                </div>
	                                                </div>
	                                                <div class="col-md-4 col-sm-4 rating-style">
		                                                <i class="material-icons">star</i>
		                                                <i class="material-icons">star</i>
		                                                <i class="material-icons">star</i>
		                                                <i class="material-icons">star_half</i>
		                                                <i class="material-icons">star_border</i>
	                                                </div>
                                                </div>
                                            </li>
                                            <li>
                                                <div class="row">
	                                            	<div class="col-md-8 col-sm-8">
		                                                <div class="prog-avatar">
		                                                    <img src="assets/img/user/user2.jpg" alt="" width="40" height="40">
		                                                </div>
		                                                <div class="details">
		                                                    <div class="title">
		                                                        <a href="#">Sarah Smith</a> 
		                                                        <p class="rating-text">Very bad service :(</p>
		                                                    </div>
		                                                </div>
	                                                </div>
	                                                <div class="col-md-4 col-sm-4 rating-style">
		                                                <i class="material-icons">star</i>
		                                                <i class="material-icons">star_half</i>
		                                                <i class="material-icons">star_border</i>
		                                                <i class="material-icons">star_border</i>
		                                                <i class="material-icons">star_border</i>
	                                                </div>
                                                </div>
                                            </li>
                                            <li>
                                                <div class="row">
	                                            	<div class="col-md-8 col-sm-8">
		                                                <div class="prog-avatar">
		                                                    <img src="assets/img/user/user3.jpg" alt="" width="40" height="40">
		                                                </div>
		                                                <div class="details">
		                                                    <div class="title">
		                                                        <a href="#">John Simensh</a> 
		                                                        <p class="rating-text"> Staff was good nd i'll come again</p>
		                                                    </div>
		                                                </div>
	                                                </div>
	                                                <div class="col-md-4 col-sm-4 rating-style">
		                                                <i class="material-icons">star</i>
		                                                <i class="material-icons">star</i>
		                                                <i class="material-icons">star</i>
		                                                <i class="material-icons">star</i>
		                                                <i class="material-icons">star</i>
	                                                </div>
                                                </div>
                                            </li>
                                            <li>
                                                <div class="row">
	                                            	<div class="col-md-8 col-sm-8">
		                                                <div class="prog-avatar">
		                                                    <img src="assets/img/user/user4.jpg" alt="" width="40" height="40">
		                                                </div>
		                                                <div class="details">
		                                                    <div class="title">
		                                                        <a href="#">Priya Sarma</a> 
		                                                        <p class="rating-text">The price I received was good value.</p>
		                                                    </div>
		                                                </div>
	                                                </div>
	                                                <div class="col-md-4 col-sm-4 rating-style">
		                                                <i class="material-icons">star</i>
		                                                <i class="material-icons">star</i>
		                                                <i class="material-icons">star</i>
		                                                <i class="material-icons">star</i>
		                                                <i class="material-icons">star_half</i>
	                                                </div>
                                                </div>
                                            </li>
                                            <li>
                                                <div class="row">
	                                            	<div class="col-md-8 col-sm-8">
		                                                <div class="prog-avatar">
		                                                    <img src="assets/img/user/user5.jpg" alt="" width="40" height="40">
		                                                </div>
		                                                <div class="details">
		                                                    <div class="title">
		                                                        <a href="#">Serlin Ponting</a> 
		                                                        <p class="rating-text">Not Satisfy !!!1</p>
		                                                    </div>
		                                                </div>
	                                                </div>
	                                                <div class="col-md-4 col-sm-4 rating-style">
		                                                <i class="material-icons">star</i>
		                                                <i class="material-icons">star_border</i>
		                                                <i class="material-icons">star_border</i>
		                                                <i class="material-icons">star_border</i>
		                                                <i class="material-icons">star_border</i>
	                                                </div>
                                                </div>
                                            </li>
                                            <li>
                                                <div class="row">
	                                            	<div class="col-md-8 col-sm-8">
		                                                <div class="prog-avatar">
		                                                    <img src="assets/img/user/user6.jpg" alt="" width="40" height="40">
		                                                </div>
		                                                <div class="details">
		                                                    <div class="title">
		                                                        <a href="#">Priyank Jain</a> 
		                                                        <p class="rating-text">Good....</p>
		                                                    </div>
		                                                </div>
	                                                </div>
	                                                <div class="col-md-4 col-sm-4 rating-style">
		                                                <i class="material-icons">star</i>
		                                                <i class="material-icons">star</i>
		                                                <i class="material-icons">star</i>
		                                                <i class="material-icons">star_half</i>
		                                                <i class="material-icons">star_border</i>
	                                                </div>
                                                </div>
                                            </li>
                                        </ul>
                                        <div class="full-width text-center p-t-10" >
												<a href="#" class="btn purple btn-outline btn-circle margin-0">View All</a>
											</div>
                                    </div>	
                                </div>
                            </div>
							</div>
							<div class="col-lg-4 col-md-12 col-sm-12 col-12">
                             <div class="card-box">
                                 <div class="card-head">
                                     <header>Todo List</header>
                                     <button id = "panel-button" 
				                           class = "mdl-button mdl-js-button mdl-button--icon pull-right" 
				                           data-upgraded = ",MaterialButton">
				                           <i class = "material-icons">more_vert</i>
				                        </button>
				                        <ul class = "mdl-menu mdl-menu--bottom-right mdl-js-menu mdl-js-ripple-effect"
				                           data-mdl-for = "panel-button">
				                           <li class = "mdl-menu__item"><i class="material-icons">assistant_photo</i>Action</li>
				                           <li class = "mdl-menu__item"><i class="material-icons">print</i>Another action</li>
				                           <li class = "mdl-menu__item"><i class="material-icons">favorite</i>Something else here</li>
				                        </ul>
                                 </div>
                                 <div class="card-body ">
                                 	<ul class="to-do-list ui-sortable" id="sortable-todo">
                                            <li class="clearfix">
                                                <div class="todo-check pull-left">
                                                    <input type="checkbox" value="None" id="todo-check1">
                                                    <label for="todo-check1"></label>
                                                </div>
                                                <p class="todo-title">Add fees details in system
                                                </p>
                                                <div class="todo-actionlist pull-right clearfix">
                                                    <a href="#" class="todo-remove"><i class="fa fa-times"></i></a>
                                                </div>
                                            </li>
                                            <li class="clearfix">
                                                <div class="todo-check pull-left">
                                                    <input type="checkbox" value="None" id="todo-check2">
                                                    <label for="todo-check2"></label>
                                                </div>
                                                <p class="todo-title">Announcement for holiday
                                                </p>
                                                <div class="todo-actionlist pull-right clearfix">
                                                    <a href="#" class="todo-remove"><i class="fa fa-times"></i></a>
                                                </div>
                                            </li>
                                            <li class="clearfix">
                                                <div class="todo-check pull-left">
                                                    <input type="checkbox" value="None" id="todo-check3">
                                                    <label for="todo-check3"></label>
                                                </div>
                                                <p class="todo-title">call bus driver</p>
                                                <div class="todo-actionlist pull-right clearfix">
                                                    <a href="#" class="todo-remove"><i class="fa fa-times"></i></a>
                                                </div>
                                            </li>
                                            <li class="clearfix">
                                                <div class="todo-check pull-left">
                                                    <input type="checkbox" value="None" id="todo-check4">
                                                    <label for="todo-check4"></label>
                                                </div>
                                                <p class="todo-title">School picnic</p>
                                                <div class="todo-actionlist pull-right clearfix">
                                                    <a href="#" class="todo-remove"><i class="fa fa-times"></i></a>
                                                </div>
                                            </li>
                                            <li class="clearfix">
                                                <div class="todo-check pull-left">
                                                    <input type="checkbox" value="None" id="todo-check5">
                                                    <label for="todo-check5"></label>
                                                </div>
                                                <p class="todo-title">Exam time table generate
                                                </p>
                                                <div class="todo-actionlist pull-right clearfix">
                                                    <a href="#" class="todo-remove"><i class="fa fa-times"></i></a>
                                                </div>
                                            </li>
                                        </ul>
                                 </div>
                             </div>
                         </div>-->
						</div>
                        
                 
            <!-- end page content -->
            <!-- start chat sidebar -->
          
          
        