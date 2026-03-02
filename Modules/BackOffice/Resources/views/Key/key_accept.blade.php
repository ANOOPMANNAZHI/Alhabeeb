@extends('layouts.plms-app')
@section('css')
<link rel="stylesheet" href="{{ asset('public/css/key_style.css')}} ">
@endsection

@section('content')



<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">Key Accept</div>
    </div>
   {{   Breadcrumbs::render('keyManagement.create') }}  



  </div>
</div>


<!-- <link rel="stylesheet" href="{{ asset('public/css/jquery-ui.css')}} "> --><!-- //code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css -->

<div class="row">  
  <div class="col-md-12 col-sm-12 dashboardtab">
    <div class="panel tab-border card-box">
      <div class="panel-body">
        <div class="tab-content">                                     

          <!-- --------------------------  Div Starts ----------------------------------- -->
            
              <div class="clearfix"></div>
              <div class="dataSearchBox">
                <div class="row">
                  <!--Property Section starts -->
                  <div class="card-body row">
                   <!-- <header>Property Section</header> -->
                   <div id="mainbody">
                    <table class="tsel" border="0" width="100%">
                    <tr>
                    <td valign="top" align="center" width="50%">
                    <table class="tsel" border="0">
                    <tr>
                    <td><img class="selector" id="webcamimg" src="{{ asset('public/img/vid.png') }}" onclick="setwebcam()" align="left" /></td>
                    <td><img class="selector" id="qrimg" src="{{ asset('public/img/cam.png') }}" onclick="setimg()" align="right"/></td></tr>
                    <tr><td colspan="2" align="center"><canvas id="qr-canvas" width="800" height="600"></canvas>
                    <div id="outdiv">
                    </div></td></tr>
                    </table>
                    </td>
                    </tr>
                    
                    <tr><td colspan="3" align="center">
                    <div id="results"></div>
                    </td></tr>
                    </table>

                </div>
             
         
         <!--Property Section  ends -->
         
</div>
</div>

</div>
<div id="unitDetails">

</div>
</div>
</div>
</div>
</div>           

</div>
@endsection
@section('scripts')
<!-- <script src="{{ asset('public/js/jquery-ui.js') }} "></script>
 -->
 <script type="text/javascript" src="{{ asset('public/js/llqrcode.js') }}"></script>
<script type="text/javascript" src="https://apis.google.com/js/plusone.js"></script>
<script type="text/javascript" src="{{ asset('public/js/webqr_key.js') }}"></script>
<script type="text/javascript">load();</script>
<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-24451557-1']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();



  

</script>

@endsection
