@extends('layouts.plms-app')



@section('content')
 
                    <div class="page-bar">
                        <div class="page-title-breadcrumb">
                            <div class=" pull-left">
                                <div class="page-title">Menu</div>
                            </div>
                            {{ (isset($menu))?   Breadcrumbs::render('menu.edit',$menu) :  Breadcrumbs::render('menu.create') }}
                        </div>
                    </div>
                   <!-- start widget -->
        
          <!-- end widget -->
<div class="row">
<div class="col">
<div class="card card-box salesSearchBox">

  <div class="dataSearchBox">
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                 <label for="simpleFormEmail">MenuGroup</label>
                 <input class="menuType"  id="menugroup-radio" type="radio"  @if(isset($menu)) {{ ($menu->menutype == 1)? 'checked': '' }}  @else {{'checked'}}  @endif   value="1" name="menutype"> 
                 <label for="simpleFormEmail">Menu</label>
                 <input class="menuType"  @if(isset($menu)) {{ ($menu->menutype == 2)? 'checked': '' }}   @endif  type="radio" id="menugroup-radio" value="2" name="menutype">
            </div>
       </div>
     </div>
  </div>


<div class="dataSearchBox">
    <form method="post" autocomplete="off" @if(isset($menu)) style="{{ ($menu->menutype == 1)? 'display:block': 'display:none' }}"   @endif  class="menu-form" id="menugroup-form" action="{{ !isset($menu)? route('menu.store'): route('menu.update',$menu->id)}}" >
        {{csrf_field()}} @if(isset($menu)){{method_field('PUT')}}@endif

      <input type="hidden" name="menutype" value="1">
        <div class="row">

               <div class="col-sm-6">
                <div class="form-group">
                    <label for="menugroup-name">Menu Name</label>
                    <div class="p-relative">
							<i class="fa fa-compass icn-add" aria-hidden="true"></i>
						<input required type="text" value="{{ isset($menu)?  old('menu_name',$menu->menu_name): old('menu_name')}}" name="menu_name" class="form-control" id="menugroup-name" placeholder="Enter Menu Name">
					</div>
                </div>
               </div>

               <div class="col-sm-6">
                <div class="form-group">
                    <label for="menugroup-icon">Menu Icon</label>
                    <div class="p-relative">
							<i class="fa fa-font-awesome icn-add" aria-hidden="true"></i>
						<input required type="text"  value="{{isset($menu)?  old('menu_icon',$menu->menu_icon): old('menu_icon')}}" name="menu_icon" class="form-control" id="menugroup-icon" placeholder="Enter Menu Icon">
					</div>
                </div>
            </div>

          <div class="w-100"></div>
           
          <div class="col">
            <div class="w-100"></div>
                <button type="submit" class="btn btn-primary">Submit</button>
          </div>          
      
        </div>
    </form>


  <form method="post" class="menu-form" @if(isset($menu)) style="{{ ($menu->menutype == 2)? 'display:block': 'display:none'}}" @else style="display:none"  @endif  id="menu-form" action="{{ !isset($menu)? route('menu.store'): route('menu.update',$menu->id)}}" >
        {{csrf_field()}} @if(isset($menu)){{method_field('PUT')}}@endif   

      <input type="hidden" name="menutype" value="2">
        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="simpleFormEmail">Menu Group</label>
                    <div class="p-relative">
							<i class="fa fa-object-group icn-add" aria-hidden="true"></i>
							<select  name="parent_menu" class="form-control">
							 <option  value="0">Select Menu Group</option>
							 @foreach($menu_list as $val)
							 <option {{isset($menu)?  ( ($menu->parent_menu == $val->id)? "selected" :'' ) : ((old('parent_menu') == $val->id)? "selected" :'')  }} value="{{$val->id}}">{{$val->menu_name}}</option>
							 @endforeach
						</select>
					</div>
                </div>
            </div>
          
            
               <div class="col-sm-6">
                <div class="form-group">
                    <label for="menu-name">Menu Name</label>
                    <div class="p-relative">
							<i class="fa fa-compass icn-add" aria-hidden="true"></i>
						<input required type="text" value="{{isset($menu)?  old('menu_name',$menu->menu_name): old('menu_name')}}" name="menu_name" class="form-control" id="menu-name" placeholder="Enter Menu Name">
					 </div>
                </div>
               </div>

               <div class="col-sm-6">
                <div class="form-group">
                    <label for="menu-icon">Menu Icon</label>
                    <div class="p-relative">
							<i class="fa fa-font-awesome icn-add" aria-hidden="true"></i>
						<input required type="text"  value="{{isset($menu)?  old('menu_icon',$menu->menu_icon): old('menu_icon')}}" name="menu_icon" class="form-control" id="menu-icon" placeholder="Enter Menu Icon">
					</div>
                </div>
            </div>

             <div class="col-sm-6">
                <div class="form-group">
                    <label for="menu-icon">Route Name</label>
                    <div class="p-relative">
							<i class="fa fa-rub icn-add" aria-hidden="true"></i>
                    <input required type="text"  value="{{isset($menu)?  old('route_name',$menu->route_name): old('route_name')}}" name="route_name" class="form-control" id="menu-route_name" placeholder="Enter Route Name">
					</div>
                </div>
            </div>

            <div class="col-sm-6">
                <div class="form-group">
                    <label for="menu-icon">Url Key</label>
                    <div class="p-relative">
							<i class="fa fa-key icn-add" aria-hidden="true"></i>
                    <input required type="text"  value="{{isset($menu)?  old('url_key',$menu->url_key): old('url_key')}}" name="url_key" class="form-control" id="menu-url_key" placeholder="Enter Url Key">
					 </div>
                </div>
            </div>



          <div class="w-100"></div>
           
          <div class="col">
            <div class="w-100"></div>
                <button type="submit" class="btn btn-primary">Submit</button>
          </div>

          
      
        </div>
    </form>
</div>



 <div class="clearfix"></div>
                     
</div>
</div>
</div>
<!-- sales lead window-->
   <div class="row">
                      
    </div>
<!-- sales lead window -->
               
              

@endsection



@section('scripts')

   <script>
     
       $(document).ready(function(){           
              


           $('.menuType').change(function(){                 
              var menu =    $(this).val(); 

              if(menu  == 1){
                     $('.menu-form').hide();
                     $('#menugroup-form').show();
              }else{             
                   $('.menu-form').hide();
                   $('#menu-form').show();
              }
               
           });
              

       });

   </script>
 
 
@endsection
