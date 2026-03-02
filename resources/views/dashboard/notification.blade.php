  @if(count($notifications) > 0 )
 
  @foreach($notifications as $val)
	 <a href="{{$val->data['href']}}" class="single-mail"> 
		 <span class="icon {{ isset($val->data['icon_color'])? $val->data['icon_color'] : 'deepPink-bgcolor' }}"> 
			 <i class="fa {{ isset($val->data['icon'])? $val->data['icon'] : 'fa-check' }}"></i>
		</span> 
		<span class="text-purple">{{$val->data['text']}}</span> 
			<span class="notificationtime">
				<small>{{ Carbon\Carbon::parse($val->created_at)->diffForHumans() }}</small>
		</span>
	</a>

   @endforeach

 @endif
 
 
 
