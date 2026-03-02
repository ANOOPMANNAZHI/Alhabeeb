@extends('layouts.plms-app')



 
@section('content')

                    <div class="page-bar">
                        <div class="page-title-breadcrumb">
                            <div class=" pull-left">
                                <div class="page-title">{{$menu->menu_name}} - Permission</div>
                            </div>
                              {{ (isset($permission))?   Breadcrumbs::render('permission.edit',$menu,$permission) :  Breadcrumbs::render('permission.create',$menu) }}

                           
                        </div>
                    </div>
                   <!-- start widget -->
        
          <!-- end widget -->
<div class="row">
<div class="col">
<div class="card card-box salesSearchBox">

  

<div class="dataSearchBox">
    <form method="post" autocomplete="off" class="menu-form" id="commentForm" action="{{ !isset($permission)? route('menu.permission.store',[$menu->id]): route('menu.permission.update',[$menu->id,$permission->id])  }}" >
        {{csrf_field()}} @if(isset($permission)){{method_field('PUT')}}@endif

      
        <div class="row">
               <div class="col-sm-6">
                    <div class="form-group">
                        <label for="name">Permission Key</label>
                        <div class="p-relative">
								<i class="fa fa-key icn-add" aria-hidden="true"></i>
							<input required type="text" value="{{ isset($permission)?  old('name',$permission->name): old('name')}}" name="name" class="form-control" id="name" placeholder="Enter Permission Key">
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
$("#commentForm").validate();
</script>
 
@endsection
