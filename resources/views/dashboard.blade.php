 @extends('layouts.plms-app')



@section('content')

@if(count(\Auth::user()->getRoleNames())>1)
<div class="row"> 
 <div class="col-md-12 col-sm-12 dashboardtab">
        <div class="panel tab-border card-box">
            <header class="panel-heading panel-heading-gray custom-tab ">
                <ul class="nav nav-tabs">
                  @foreach(\Auth::user()->getRoleNames() as $val)
                     <li class="nav-item"><a href="#{{$val}}" data-toggle="tab"class="{{($default_role == $val)? 'active': ''}}" >{{ucwords(str_replace('_', ' ',$val))}}</a>
                    </li>                     
                  @endforeach                
                                                 
                </ul>
            </header>

            
              <div class="panel-body">
                  <div class="tab-content">

                   @foreach(\Auth::user()->getRoleNames() as $val)
                    <div class="tab-pane {{($default_role == $val)? 'active': ''}}" id="{{$val}}">
                      @include('dashboard.'.$val)
                    </div>
                   @endforeach 

                  </div>
                </div>



         </div>

       </div>
     </div>
     @else

      @if (view()->exists('dashboard.'.$default_role))
       @include('dashboard.'.$default_role)
      @else
       @include('dashboard.default_dashboard')
      @endif

     @endif
		  
@endsection


@section('scripts') 


 @stack('dashboard_scripts')
      
@endsection