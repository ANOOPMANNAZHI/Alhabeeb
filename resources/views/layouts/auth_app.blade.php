<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1" name="viewport" />
    <meta name="description" content="Responsive Admin Template" />
    <meta name="author" content="SmartUniversity" />
    <title>PLMS</title>

    <!--
	<link type="text/css" rel="stylesheet" href="//unpkg.com/bootstrap/dist/css/bootstrap.min.css"/>
	<link type="text/css" rel="stylesheet" href="//unpkg.com/bootstrap-vue@latest/dist/bootstrap-vue.css"/>
    <link rel="stylesheet" href="https://unpkg.com/vue-material@beta/dist/vue-material.min.css">
    <link rel="stylesheet" href="https://unpkg.com/vue-material@beta/dist/theme/default.css"> -->
    <link href="{{ asset('public/plugins/font-awesome/css/font-awesome.min.css')}}" rel="stylesheet" type="text/css"/>

    <link rel="stylesheet" href="{{ asset('public/css/extra_pages.css') }}">

    <link rel="shortcut icon" href="{{ asset('public/img/favicon.ico') }}" />
    <link rel="stylesheet" href="{{ asset('public/plugins/iconic/css/material-design-iconic-font.min.css') }}">

	<!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script> -->
	<script src="{{ asset('public/js/all.js') }}" ></script>
	<script src="{{asset('public/js/jquery.validate.min.js') }}" ></script>
    <!-- icons -->
   <!--  <link href="assets/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
    <link rel="stylesheet" href="assets/plugins/iconic/css/material-design-iconic-font.min.css"> -->
    <!-- bootstrap -->
    <!-- <link href="assets/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" /> -->
    <!-- style -->
    <!-- <link rel="stylesheet" href="assets/css/pages/extra_pages.css"> -->
    <!-- favicon -->
    <!-- <link rel="shortcut icon" href="assets/img/favicon.ico" />  -->
</head>
<body>
    <div id="app">
		<div class="limiter">
			  @yield('content')
		</div>
	</div>
    <!-- start js include path -->
    <!-- <script src="assets/plugins/jquery/jquery.min.js" ></script> -->
    <!-- bootstrap -->
    <!-- <script src="assets/plugins/bootstrap/js/bootstrap.min.js" ></script> -->
    <!-- <script src="assets/js/pages/extra_pages/login.js" ></script> -->
    <!-- end js include path -->
    @yield('scripts')   
</body>
</html>
