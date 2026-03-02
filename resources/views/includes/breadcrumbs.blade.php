@if (count($breadcrumbs))


<ol class="breadcrumb page-breadcrumb pull-right">

	  @foreach ($breadcrumbs as $breadcrumb)
                                
    	@if ($breadcrumb->url && !$loop->last)
    	<li> 
               @if(!empty($breadcrumb->icon) ) 
                <i class="fa  {{$breadcrumb->icon}}"></i>   
                @endif                            	
    	<a class="parent-item" href="{{ $breadcrumb->url }}">{{ $breadcrumb->title }}</a>&nbsp;
    	<i class="fa fa-angle-right"></i></li>
        @else
        <li class="breadcrumb-item active">

             @if(!empty($breadcrumb->icon) )   
                <i class="fa {{$breadcrumb->icon}}"></i>   
                @endif             

            {{ $breadcrumb->title }}</li>
        @endif
                              
       @endforeach   
    </ol>

@endif