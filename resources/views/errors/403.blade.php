@extends('layouts.plms-app')


@section('breadcrumbs', Breadcrumbs::render('permissionDenied'))

@section('content')

 <div class="row">
  <div class="col-sm-12">
    <div class="card">
		
      <div class="card-head align-items-center text-center">
		  <br>
        <div class="Permission">
<div class="Permission-403">
<div></div>
<h1 class="per-403"><i class="fa fa-hand-paper-o" aria-hidden="true"></i></h1>
</div>
<h2>Permission Denied</h2>
<p>You have no permission to access this Page.</p>

</div>       
      </div>
      
         <div class="card-body text-center">
		
		  
		  <br>
		  <br>
		  </div>
      </div>
  </div>
</div>

@endsection
