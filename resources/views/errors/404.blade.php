@extends('layouts.plms-app')


@section('breadcrumbs', Breadcrumbs::render('notfound'))

@section('content')

 <div class="row">
  <div class="col-sm-12">
    <div class="card">
		
      <div class="card-head align-items-center text-center">
		  <br>
        <div class="notfound">
<div class="notfound-404">
<div></div>
<h1 class="not-error">404</h1>
</div>
<h2>Page not found</h2>
<p>The page you are looking for might have been removed had its name changed or is temporarily unavailable.</p>

</div>      
      </div>
      
         <div class="card-body text-center">
		  
		  <br>
		  <a href="{{route('home')}}" ><button class="btn btn-primary">
							Go to home page
						</button></a>
		  
		  <br>
		  <br>
		  </div>
      </div>
  </div>
</div>

@endsection
