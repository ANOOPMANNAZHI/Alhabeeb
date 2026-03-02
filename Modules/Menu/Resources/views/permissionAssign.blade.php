 

@if(isset($permission))


   @foreach($permission as $val)

     <div class="col-md-4 form-group">
       

            <input @if($role->hasPermissionTo($val->name)) {{'checked'}} @endif name="permissions[]" type="checkbox" value="{{$val->name}}" >
            <label >{{ucwords(str_replace('_', ' ',$val->name))}}</label>                          
             
          
     </div>    

  @endforeach
 
@else 
 
 


  @foreach($menu as $menu_val)


   <div class="col-md-6 block">
                  
   <ul class="head-line nested" id="nested" > <input class = "main-class" type="checkbox" value="" >    {{$menu_val->menu_name}}
          
    @include('layouts.permission_recursion',['menu_val' => $menu_val,'role'=>$role])
    </ul>
    </div> 
    
   @if($loop->iteration%3 == 0)
   <div class="clear-fix"></div>
   @endif 

  @endforeach


 @endif
<div class="w-100"></div>

    <div class="row"> 
     <div class="col-sm-12">
      <div class="w-100"></div>
          <button type="submit" class="btn btn-primary">Save</button>
    </div> 
    </div>