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
                                <div class="page-title">Role</div>
                            </div>
                            {{ Breadcrumbs::render('role.index') }}
                        </div>
                    </div>
<!-- sales lead window-->
   <div class="row">
       <div class="col-md-12 col-sm-12">
        <div class="card  card-box">
          
            <div class="card-body ">
            <h4>
			 @if( Auth::id() == 1)
             <a href="{{route('role.create')}}" class="btn btn-circle btn-primary  align-right" >Add Role</a>
             @endif
             <div class="clr"></div>
            </h4>              
                      <table class="table display product-overview mb-30" id="dtBasicExample">
                        <thead>
                            <tr>
                                <th>Sl No.</th>
                                <th>Role</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($roles as $role)
                            <tr>
                                <td>{{$loop->iteration}}</td>                            
                                <td>{{ucwords(str_replace('_', ' ',$role->name))}}</td>      
                                <td>                         

                                <a title="Edit Role" href="{{route('role.edit',$role->id)}}" class="btn btn-tbl-edit btn-xs">
                                    <i class="fa fa-pencil"></i>
                                </a>
                                @if( Auth::id() == 1) 
                                <a title="Delete Role" href="{{route('role.destroy',$role->id)}}" class="btn btn-tbl-delete btn-xs delete_role"   >
                                    <i class="fa fa-trash-o "></i>
                                </a> 
                                @endif

                                <a title="Set Permissions" href="{{route('setPermission',$role->id)}}" class="btn btn-tbl-view btn-xs "   >
                                    <i class="fa fa-users"></i>
                                </a>
                              
                                
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

            jQuery('.delete_role').click(function (event) {
                var action = $(this).attr("href");
                event.preventDefault();
                if (confirm('Do you want to Delete this Role?')) {
                    jQuery("#delete-form").attr('action', action);
                    jQuery("#delete-form").submit();
                } else {
                    return false;
                }
            })
        });

</script>

@endsection
