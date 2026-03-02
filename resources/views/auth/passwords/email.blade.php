@extends('layouts.auth_app')

@section('content')
<div class="container-login100 page-background">
            <div class="wrap-login100">
                <div class="loginLogo">
                    <div class="loginformlogo">
                        <img src="{{asset('public/img/logo_habib.png')}}" class="img-fluid">
                    </div>
                     
                </div>
                <div class="loginArea">


                    <form  class="login100-form validate-form" method="POST" action="{{ route('password.email') }}">
                         @csrf
                         <span class="login100-form-logo">
                            <img class="img-fluid" src="{{asset('public/img/login_logo.png')}}">
                        </span> 
                        <span class="login100-form-title p-b-34 p-t-27">
                            Reset Password
                        </span>


                     @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif
                            
                        <div class="wrap-input100 validate-input" data-validate = "Enter username">                           
                            <input  id="email" type="email"   class="input100 {{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ old('email') }}" required autofocus       placeholder="E-Mail Address">
                            <span class="focus-input100" data-placeholder="&#xf207;"></span>
                            <!-- <i class="material-icons">account_circle</i> -->
                             @if ($errors->has('email'))
                                    <span class="login-form invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                             @endif                                
                        </div>
                       
                       
                        <div class="container-login100-form-btn">
                            <button class="login100-form-btn">
                               Send Password Reset Link
                            </button>
                        </div>
                        <div class="text-center p-t-90">
                            <a class="txt1" href="{{ route('login') }}">
                                Back
                            </a>
                        </div>    
                                           
                    </form>
                </div>
            </div>
        </div>
@endsection
