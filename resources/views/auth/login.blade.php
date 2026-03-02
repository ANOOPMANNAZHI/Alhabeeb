@extends('layouts.auth_app')

@section('content')
<div class="container-login100 page-background">
            <div class="wrap-login100">
                <div class="loginLogo">
                    <div class="loginformlogo">
                        <img src="{{asset('public/img/logo_habib.png')}}" class="img-fluid">
                    </div>
                    <div class="logoTitle mt-5"><img src="{{asset('public/img/logoTitle.png')}}" style="width:300px;"></div>
                </div>
                <div class="loginArea">
                    <form  class="login100-form validate-form" id="login-block" method="POST" action="{{ route('login') }}">
                         @csrf
                         <span class="login100-form-logo">
                            <img class="img-fluid" src="{{asset('public/img/login_logo.png')}}">
                        </span> 
                        <span class="login100-form-title p-b-34 p-t-27">
                            Log in
                        </span>

                            
                        <div class="wrap-input100 validate-input" data-validate = "Enter username">
                           
                            <input  id="username" type="text"   class="input100 {{ $errors->has('username') ? ' is-invalid' : '' }}" name="username" value="{{ old('username') }}" required autofocus       placeholder="Username">
                            <span class="focus-input100" data-placeholder="&#xf207;"></span>
                            <!-- <i class="material-icons">account_circle</i> -->

                          
                                
                        </div>
                        <div class="wrap-input100 validate-input" data-validate="Enter password">
                            <input id="password" type="password" class="input100  {{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" required      placeholder="Password">
                            <span class="focus-input100" data-placeholder="&#xf191;"></span>
                                @if ($errors->has('password'))
                                    <span class="login-form  invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                @endif
                                
                        </div>
                        
                           @if ($errors->has('username'))
                           <div class="validate-input" >
                                    <span class="login-form invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('username') }}</strong>
                                    </span>
                            </div>
                             @endif
                        <div class="contact100-form-checkbox">
                            <input class="input-checkbox100" id="ckb1" type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                            <label class="label-checkbox100" for="ckb1">
                                Remember me
                            </label>
                        </div>
                        <div class="container-login100-form-btn">
							
                            <button class="login100-form-btn">
                                Login
                            </button>
                        </div>
                        <div class="text-center p-t-90">
                            <a class="txt1" href="{{ route('password.request') }}">
                                Forgot Password?
                            </a>
                        </div>                        
                    </form>
                </div>
            </div>
        </div>
@endsection
@section('scripts')
<script>
 $(document).ready(function() {
		$("#login-block").validate();
 });
</script>
@endsection
