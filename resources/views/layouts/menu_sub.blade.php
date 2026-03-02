                          
                          @php 
                          $menu_flag = false; 
                          
                          $menu_flag = menuSelectionCheck($val,$path[0]);
                          

                          @endphp  
                          @if( ($val->menutype == 1)  && (count($val->allChildMenu) > 0) )                           
                             
                            <li class="nav-item  {{ ($menu_flag || $flag_open )? 'start active ' : '' }} ">
                              <a href="javascript:void(0);" class="nav-link nav-toggle">
                                <span>
            										@if($val->menu_icon)
                                  <i class="fa {{$val->menu_icon}}" aria-hidden="true"></i>											 
            										@endif
                                </span>
                                <span class="title">{{$val->menu_name}}</span> 

                                <span class="arrow {{ ($menu_flag || $flag_open) ? 'open': '' }}"></span>
                              </a>
                              <ul class="sub-menu" >
                              

            									@php

            									 $menuSub =  $val->allChildMenu->sortBy('menu_order');

            									@endphp
                                @foreach($menuSub as $key=>$menuSub_val)
                                 
                                  @if($menuSub_val->menutype == 2)   
                                                                     
                                    <li class="nav-item  {{ ($search_url == true) ? ((URL::current() == route($menuSub_val->route_name))?  'start active ' : (($search_key == $menuSub_val->url_key) ? 'start active ' : '') ) : (($search_key == $menuSub_val->url_key) ? 'start active ' : '') }}">
                                      <a href="{{route($menuSub_val->route_name)}}" class="nav-link ">
                                        <i class="fa {{$menuSub_val->menu_icon}}" aria-hidden="true"></i>
                                          <span class="title">{{$menuSub_val->menu_name}}</span>
                                      </a>
                                    </li>
                                  @elseif( ($menuSub_val->menutype == 1)   || (count($menuSub_val->allChildMenu) > 0) )    
								                  
                                   @if($path[0]=='dashboard')
                                  @include('layouts.menu_sub',['val' => $menuSub_val,'menu'=>$key,'type'=>$menuSub_val->menutype ])
                                  @else
                                    @include('layouts.menu_sub',['val' => $menuSub_val,'menu'=>$key,'type'=>$menuSub_val->menutype ])
                                  @endif  
                                    
                                  @endif
                                  @endforeach                                   
                              </ul>                                
                                <div class="clearfix"></div>  
                            </li>  
                            <div class="clearfix"></div>                            
                            
                            @elseif($val->menutype == 2)
                          
                              <li class="nav-item ">
                                <a href="{{route($val->route_name)}}" class="nav-link nav-toggle"><span>
                                  @if($val->menu_icon)
										                <i class="fa {{$val->menu_icon}}" aria-hidden="true"></i>
								                  @endif
									                </span>
                                  <span class="title">{{$val->menu_name}}</span>
                                </a>
								                <ul style="display:none;"><li><a href=#></a> </li> </ul>
                              </li>
                              <div class="clearfix"></div>  
                                                        
                            @endif  
