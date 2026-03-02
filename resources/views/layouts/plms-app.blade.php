<!DOCTYPE html>
<html class="mdl-js" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
     @include('layouts.style-script')
  
</head>
<body class="page-header-fixed sidemenu-closed-hidelogo page-content-white page-md header-white dark-sidebar-color logo-blue "> 
<input type="hidden" name="side_user_id" id="side_user_id" value="{{Auth::user()->id}}">
<input type="hidden" name="site_url_info" id="site_url_info" value="{{url('/')}}">
<input type="hidden" name="site_url_token" id="site_url_token" value="{{ csrf_token() }}">
   <div class="page-wrapper">
       @include('layouts.header')
        <!-- start page container -->
        <div class="page-container">
@auth

 			@include('layouts.sidebar')
@endauth      

            <div class="page-content-wrapper">
                <div class="page-content">

            @include('includes.error')
            @include('includes.success')

            @yield('content')      

                </div>
            </div>

        </div>
        <!-- end page container -->
             @include('layouts.footer')
    </div>  


      @yield('scripts')   
      
      <script> 
		
	  $(document).ready(function(){  
		  
	//	  $('#remove-scroll').scrollTop( $(".active").offset().top );
	
	var elem = $('#remove-scroll').find('.active').last();
	var position = elem.position();
	//alert(elem);
	//alert(position.top);
 	$('.sidebar-container').scrollTop(position.top );
  //  elem.scrollTop = elem.scrollHeight; 
  

    

$('#menuSearchHome').autocomplete({
      source : '{!!URL::route('menuHomesearch')!!}',
      minlenght:2,
      autoFocus:true,
      select:function(e,ui){
        if(ui.item.ids !=null){
         window.location.href = ui.item.ids; 
          }
        }

  }); 



  
				 
		 })	
		</script>
</body>
</html>
