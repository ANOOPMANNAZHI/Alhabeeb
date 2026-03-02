<!-- start notification dropdown --> 
              @if(count($notifications) > 0 )
                        <li class="dropdown dropdown-extended dropdown-notification" id="header_notification_bar">
                            <a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
                                <i class="fa fa-bell-o"></i>
                                <span class="badge headerBadgeColor1"> {{count($notifications)}} </span>
                            </a>
                            <ul class="dropdown-menu animated swing">
                                <li class="external">
                                    <h3><span class="bold">Notifications</span></h3>
                                    <span class="notification-label purple-bgcolor">New {{count($notifications)}} </span>
                                    

                                </li>
                                <li>
                                    <ul class="dropdown-menu-list small-slimscroll-style" data-handle-color="#637283">
										@foreach($notifications as $val)
                                        <li>  
                                            <a href="{{$val->data['href']}} ">
                                                <span class="time">{{ Carbon\Carbon::parse($val->created_at)->diffForHumans() }}</span>
                                                <span class="details">
                                                <span class="notification-icon circle {{ isset($val->data['icon_color'])? $val->data['icon_color'] : 'deepPink-bgcolor' }} ">
												<i class="fa {{ isset($val->data['icon'])? $val->data['icon'] : 'fa-check' }}"></i>
												</span>
                                                <span class="not-mzg"> {{$val->data['text']}}  </span>                                             
                                                </span>
                                            </a>
                                        </li>
                                        @endforeach
                                       
                                    </ul>
                                   
                                </li>
                            </ul>
                        </li>                        
                   @endif     
                        <!-- end notification dropdown -->
