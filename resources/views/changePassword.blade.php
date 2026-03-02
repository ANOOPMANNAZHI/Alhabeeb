@extends('layouts.plms-app')

@section('css')
@endsection

@section('content')
 
                    <div class="page-bar">
                        <div class="page-title-breadcrumb">
                            <div class=" pull-left">
                                <div class="page-title">Change Password</div>
                            </div>
                            {{ Breadcrumbs::render('changePassword') }}
                        </div>
                    </div>
                   <!-- start widget -->
        
          <!-- end widget -->
<div class="row">
<div class="col">
<div class="card card-box salesSearchBox"> 


<div class="dataSearchBox">
    <form id="changePassword" method="post"  action="{{route('updatePassword')}}" autocomplete="off">
            {{csrf_field()}}     
        <div class="row">
               <div class="col-sm-6">
                <div class="form-group">
                    <label for="password">New Password</label>
                    <input required type="password" minlength="6"  name="password" class="form-control" id="password" placeholder="Enter New Password">
                </div>
               </div>

               <div class="col-sm-6">
                <div class="form-group">
                    <label for="confirm-new-password">Confirm Password</label>
                    <input required data-rule-equalto="input[name=password]" type="password" name="password_confirmation" class="form-control" id="confirm-new-password" placeholder="Confirm Password">
                </div>
            </div>

          <div class="w-100"></div>
           
          <div class="col">
            <div class="w-100"></div>
                <button type="submit" class="btn btn-primary">Save</button>
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

$(document).ready(function() {

 $("#changePassword").validate();

});
 

 </script>
 
@endsection