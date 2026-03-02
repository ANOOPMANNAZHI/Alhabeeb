@extends('layouts.plms-app')

@section('content')
    <div class="page-bar">
                        <div class="page-title-breadcrumb">
                            <div class=" pull-left">
                                <div class="page-title">Role</div>
                            </div>
                              {{ (isset($role))?   Breadcrumbs::render('role.edit',$role) :  Breadcrumbs::render('role.create') }}

                        </div>
                    </div>
                   <!-- start widget -->
        
          <!-- end widget -->
<div class="row">
<div class="col">
<div class="card card-box salesSearchBox">

  

<div class="dataSearchBox">
    <form method="post"  autocomplete="off" class="menu-form" id="menugroup-form" action="{{ !isset($role)? route('role.store'): route('role.update',$role->id)  }}" >
        {{csrf_field()}} @if(isset($role)){{method_field('PUT')}}@endif
      
        <div class="row">
               <div class="col-sm-6">
                    <div class="form-group">
                        <label for="name">Role</label>
                         <div class="p-relative">
							<i class="fa fa fa-dot-circle-o icn-add" aria-hidden="true"></i>
							<input required type="text" value="{{ isset($role)?  old('name',ucwords(str_replace('_', ' ',$role->name))): old('name')}}" name="name" class="form-control" id="name" placeholder="Enter Role">
						 </div>
                    </div>
               </div>              

          <div class="w-100"></div>
           
           <div class="col-sm-6">
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
 
 
@endsection
