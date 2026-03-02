 
 	
	<ul>   
        @foreach($menu_val->ChildMenus  as $menuSub_val)
        	<li class="nav-item nested"><input class = "sub-class "  type="checkbox" value="" > {{$menuSub_val->menu_name}}

        		@foreach($menuSub_val->permissionList as $val)
        			<ul>
						   <input @if($role->hasPermissionTo($val->name)) {{'checked'}} @endif  class="inner-class nested" name="permissions[]" type="checkbox" value="{{$val->name}}" >
			            {{ucwords(str_replace('_', ' ',$val->name))}}
		            </ul>       

			   @endforeach
        	@include('layouts.permission_recursion',['menu_val' => $menuSub_val,'role'=>$role])

        	</li>
    	@endforeach
    </ul>
