@extends('layouts.plms-app')

@section('css')
    <!-- data tables -->
    <link rel="stylesheet" href="{{ asset('public/css/datatables.min.css')}}">
@endsection 


@section('content')
 
<!-- start page content -->           
                    <div class="page-bar">
                        <div class="page-title-breadcrumb">
                            <div class=" pull-left">
                                <div class="page-title">{{ isset($parentMenu)? $parentMenu->menu_name : '' }}    Menu</div>
                            </div>

                            @isset($parentMenu)
                             {{ Breadcrumbs::render('menu.menus',$parentMenu) }}

                            @else
                             {{ Breadcrumbs::render('menu.index') }}

                            @endisset

                           

                        </div>
                    </div>
<!-- sales lead window-->
   <div class="row">
       <div class="col-md-12 col-sm-12">
        <div class="card  card-box">
          
            <div class="card-body ">
            <h4>
			 @if( Auth::id() == 1)
             <a href="{{route('menu.create')}}" class="btn btn-circle btn-primary  align-right"  >Add</a>
             @endif
             <div class="clr"></div>
            </h4>              
                      <table class="table display product-overview mb-30" id="dtBasicExample">
                        <thead>
                            <tr>
                                <th>Sl No.</th>
                                <th>Menu</th>
                                <!-- <th>MenuGroup</th> -->
                                <th>Menu Order</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $perPage =  ($menu ->currentpage()-1) * $menu ->perpage() ; @endphp
                            @forelse ($menu as $menu_val)
                            @php 
                            $loop =   $perPage + $loop->iteration ;
                            @endphp 
                            <tr>
                                <td>{{ $loop  }}</td>
                                <td>
                                @if($menu_val->menutype == 1) 
                                <a style="color: rgba(0,0,0,.87);font-weight: 400;display: block" href="{{route('menu.menus',$menu_val->id)}}">
                                @endif
                                {{$menu_val->menu_name}}
                                 @if($menu_val->menutype == 1) 
                               </a>
                                @endif
                                    </td>      
                                <!-- <td>{{$menu_val->menuGroupName->menu_name}}</td>       -->
                                <td>
                               <div style="width:50%">
                                @if( $loop != 1 )
                                <button class="order_up" id="up_{{$menu_val->id}}" >
                                    <i class="fa fa-arrow-up" aria-hidden="true"></i>
                                </button>
                                @endif

                                 @if($loop != $menu->total() )
                                <button class="order_down pull-right" id="down_{{$menu_val->id}}">
                                    <i class="fa fa-arrow-down" aria-hidden="true"></i>
                                </button>
                                @endif
                            </div>
                               

                                    
                                   <!--  <select class="menuOrder" id="{{$menu_val->id}}"  name="menuOrder[]">
                                        @for($i = 1 ; $i<= $menu->where('parent_menu',$menu_val->parent_menu)->count(); $i++)
                                        <option {{ ($menu_val->menu_order == $i)?  'selected' : '' }}  value="{{$i}}">
                                            {{$i}}
                                        </option>
                                        @endfor
                                    </select> -->
                                </td>      
                                <td> 
                                <a title="Edit" href="{{route('menu.edit',$menu_val->id)}}" class="btn btn-tbl-edit btn-xs">
                                    <i class="fa fa-pencil"></i>
                                </a>
                                                            
                                @if( Auth::id() == 1)
                                <a href="{{route('menu.destroy',$menu_val->id)}}" title="Delete" class="btn btn-tbl-delete btn-xs delete_menu">
                                    <i class="fa fa-trash-o "></i>
                                </a>
                                @endif
                                @if($menu_val->menutype == 2)                                    
                                 <a title="Permission Key" href="{{route('menu.permission.index',$menu_val->id)}}"  class="btn btn-tbl-user  btn-xs">
                                    <i class="fa fa-puzzle-piece "></i>
                                 </a>
                                 @else

                                <a title="Menu" href="{{route('menu.menus',$menu_val->id)}}"  class="btn btn-tbl-view btn-xs">
                                    <i class="fa fa-eye"></i>
                                </a>

                                @endif

                               
                                
                                </td>
                            </tr>  
                            @empty
                            <tr>
                                <td colspan="3">
                                <p>No Menu</p>
                               </td>
                            </tr>
                            @endforelse
                        </tbody>
                      </table>


                      {{ $menu->links() }}
                    
            </div>
        </div>
                        </div>
                    </div>
<!-- sales lead window -->
                
            <!-- end page content -->
            <!-- start chat sidebar -->
           
       
        
    <form id="delete-form" action="" method="POST">
        {{ method_field('DELETE') }}  {{csrf_field()}}
        <input value="delete" style="display: none;" type="submit">
    </form>
  
 
<!-- end page content -->
@endsection


@section('scripts')    
 <?php  
  /*  <script src="{{ asset(PUBLIC_PATH.'js/jquery.dataTables.min.js')}}" ></script>
    <script src="{{ asset(PUBLIC_PATH.'js/dataTables.bootstrap4.min.js') }}" ></script>
    <script src="{{ asset(PUBLIC_PATH.'js/table_data.js') }}" ></script> */
 ?>
<!-- <script src="{{asset('public/js/datatables.min.js')}}"></script> -->
<script>


 jQuery(document).ready(function() {

  jQuery('#dtBasicExample').DataTable({
    "paging": false ,  "searching": false ,"info": false 
  });
  jQuery('.dataTables_length').addClass('bs-select');
  

    jQuery('.delete_menu').click(function (event) {
        var action = $(this).attr("href");
        event.preventDefault();
        if (confirm('Do you want to Delete this MenuGroup?')) {
            jQuery("#delete-form").attr('action', action);
            jQuery("#delete-form").submit();
        } else {
            return false;
        }
    })

   
    $('.menuOrder').on('change', function() {

        var id = $(this).attr('id');
        var menuOrder = $(this).val();

            if(confirm('Do you want to change MenuOrder ? ')){
               $.ajax({
                url: "{{route('menuOrder')}}",
                data : { id :id , menuOrder: menuOrder  },
                success: function(result){                
                 }
               });
            }
     }); 


     $('.order_up').click(function(){
            
            var id = $(this).attr('id') ;
            var array_val =  id.split("_");          

           $.ajax({
                url: "{{route('menuOrder')}}",
                data : { id :array_val[1] , order_type : 'up' },
                success: function(result){ 
                    location.reload();                
                 }
               });

     });


      $('.order_down').click(function(){

         var id = $(this).attr('id') ;
            var array_val =  id.split("_");          

           $.ajax({
                url: "{{route('menuOrder')}}",
                data : { id :array_val[1] , order_type : 'down' },
                success: function(result){  
                    location.reload();               
                 }
               });
            
     });



   });

</script> 


@endsection
