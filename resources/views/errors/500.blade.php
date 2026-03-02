@extends('layouts.plms-app')


@section('breadcrumbs', Breadcrumbs::render('notfound'))

@section('content')

 <div class="row">
  <div class="col-sm-12">
    <div class="card">
		
      <div class="card-head align-items-center text-center">
		  <br>
        <div class="Permission">
<div class="Permission-403">
<div></div>
<h1 class="per-403"><i class="fa fa-cogs" aria-hidden="true"></i></h1>
</div>
<h2>Error 500</h2>
<p>The page you are looking for does't exist or an other error occurred.</p>

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
