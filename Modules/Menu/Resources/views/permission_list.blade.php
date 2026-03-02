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
                                <div class="page-title">{{$menu->menu_name }}  Permission Key</div>
                            </div>


                             {{ Breadcrumbs::render('permission.index',$menu) }}
                            
                        </div>
                    </div>
<!-- sales lead window-->
   <div class="row">
       <div class="col-md-12 col-sm-12">
        <div class="card  card-box">
          
            <div class="card-body ">
            <h4>
             <a href="{{route('menu.permission.create',$menu->id)}}" class="btn btn-circle btn-primary  align-right"  >Add Permission</a>
             <div class="clr"></div>
            </h4>              
                      <table class="table display product-overview mb-30" id="dtBasicExample">
                        <thead>
                            <tr>
                                <th>Sl No.</th>
                                <th>Permission Key</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($permissions as $permission)
                            <tr>
                                <td>{{$loop->iteration}}</td>
                                <!-- <td>{{ucwords(str_replace('_', ' ',$permission->name))}}</td>       -->
                                <td>{{$permission->name}}</td>      
                                <td> 


                                <a title="Edit Permission" href="{{route('menu.permission.edit',[$menu->id,$permission->id])}}" class="btn btn-tbl-edit btn-xs">
                                    <i class="fa fa-pencil"></i>
                                </a>
                                                            

                               <!--  <a href="{{route('menu.permission.destroy',[$menu->id,$permission->id])}}" title="Delete Permission" class="btn btn-tbl-delete btn-xs">
                                    <i class="fa fa-trash-o "></i>
                                </a>                               
                                -->
                                
                                </td>
                            </tr>  
                            @empty
                            <tr>
                                <td colspan="3">
                                <p class="text-center">No Permissions</p>
                               </td>
                            </tr>
                            @endforelse
                        </tbody>
                      </table>
                    
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
        });

</script>


@endsection
