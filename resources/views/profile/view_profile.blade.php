@extends('layouts.plms-app')
@section('content')
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Profile </div>
        </div>
        {{ Breadcrumbs::render('profileView')}}                           
    </div>
</div>



<div class="mb-4">
    <div class="row">
        <div class="col-md-12">
         {{--dd($user->employee) --}}
         <!-- BEGIN PROFILE SIDEBAR -->
         <div class="profile-sidebar">
            <div class="card card-topline-aqua">
                <div class="card-body  height-9">
                    <div class="row">
                        <div class="profile-userpic">
                         @if(isset($user->employee->employee_picture))
                         <img src="{{url('storage/app/'.$user->employee->employee_picture)}}" class="img-responsive" alt=""> 
                         @else
                         <img src="{{asset('public/img/user_default.jpeg')}}"  class="img-responsive" alt="">

                         @endif
                     </div>
                 </div>
                 <div class="profile-usertitle">
                    <div class="profile-usertitle-name"> 
                     {{$user->employee->employee_name}}
                 </div>
                 <div class="profile-usertitle-job p-page">   {{$user->employee->designations->designation_name?? ''}} </div>
             </div>
             <ul class="list-group list-group-unbordered">
                 <li class="list-group-item">
                    <b>Username </b>
                    <div class="profile-desc-item pull-right">{{$user->username}}</div>
                </li>
                <li class="list-group-item">
                    <b>Code </b>
                    <div class="profile-desc-item pull-right">
                     {{$user->employee->employee_code?? 'Super Admin'}}
                 </div>
             </li>
             <li class="list-group-item">
                <b>Created at</b>
                <div class="profile-desc-item pull-right">
                 {{$user->created_at->format('d/m/Y')}}
             </div>
         </li>
         <li class="list-group-item">
            <b>Last login</b> 
            <div class="profile-desc-item pull-right">
             @if(isset($user->user_last_login))
             {{$user->user_last_login->format('d/m/Y')}}
             @else
             {{'NA'}}
             @endif
         </div>

     </li>
 </ul>
 <!-- END SIDEBAR USER TITLE -->
 <!-- SIDEBAR BUTTONS -->

 <!-- END SIDEBAR BUTTONS -->
</div>
</div>
</div>
<!-- END BEGIN PROFILE SIDEBAR -->
<!-- BEGIN PROFILE CONTENT -->
<div class="profile-content">
    <div class="row">
     <div class="card card-body">
        <div class="card-head ">
            <header>Profile Details</header>
        </div>
        <div class="card-body  height-9">

            <ul class="list-group list-group-unbordered">
             <li class="list-group-item">
                <b>Email </b>
                <div class="profile-desc-item pull-right">{{$user->email}}</div>
            </li>
            <li class="list-group-item">
                <b>Phone No </b>
                <div class="profile-desc-item pull-right">{{$user->employee->employee_contact_no ?? 'NA'}}</div>
            </li>
            
            <li class="list-group-item">
                <b>Date of Birth </b>
                <div class="profile-desc-item pull-right">
                    @if(isset($user->employee->employee_dob))
                    {{$user->employee->employee_dob->format('d/m/Y')}}
                    @else
                    {{'NA'}}
                    @endif
                </div>
            </li>

            <li class="list-group-item">
                <b>Address</b>
                <div class="profile-desc-item pull-right">
                 {{$user->employee->employee_contact_address?? 'NA'}}
             </div>
         </li>

         <li class="list-group-item">
            <b>Seconday Address</b>
            <div class="profile-desc-item pull-right">
             {{$user->employee->employee_secondary_no?? 'NA'}}
         </div>
     </li>
     <li class="list-group-item">
        <b>Status </b>
        @if($user->user_type == 'employee')
        <div class="profile-desc-item pull-right"><b>
         @if($user->employee->employee_status ==1)
         {{'Active'}}
         @else
         {{'In-active'}}
         @endif 
     </b>
 </div>
 @else
 <div class="profile-desc-item pull-right"><b>
     @if($user->user_type_status ==1)
     {{'Active'}}
     @else
     {{'In-active'}}
     @endif 
 </b>
</div>
@endif
</li>
</ul>

</div>
</div>
</div>
</div>
<!-- END PROFILE CONTENT -->
</div>
</div>
</div>
@endsection
