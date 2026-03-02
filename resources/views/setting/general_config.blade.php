@extends('layouts.plms-app')
@section('content')
    <div class="page-bar">
        <div class="page-title-breadcrumb">
            <div class=" pull-left">
                <div class="page-title">Configuration Setting </div>
            </div>
             {{-- Breadcrumbs::render('profileView')--}}                           
        </div>
    </div>
  


 <div class="mb-4">
  <div class="row">
      <div class="col-md-12 col-sm-12 dashboardtab">
          <div class="panel tab-border card-box">
              <header class="panel-heading panel-heading-gray custom-tab ">
                  <ul class="nav nav-tabs">
                 
                      <li class="nav-item"><a href="#general" data-toggle="tab" class="{{($tab=='general' || $tab=='' )?'active': ''}}" >Prefix</a>
                      </li>
                  

                      <li class="nav-item"><a href="#email-settings" data-toggle="tab" class="{{($tab=='email')?'active': ''}}">Email</a>
                      </li>
                       
                      <li class="nav-item"><a href="#sms" data-toggle="tab" class="{{($tab=='sms')?'active': ''}}">SMS</a>
                      </li>  
                      <li class="nav-item"><a href="#settings" data-toggle="tab" class="{{($tab=='settings')?'active': ''}}" >General</a>
                      </li>                                   
                  </ul>
              </header>
              <div class="panel-body">
                  <div class="tab-content">  
                     <!-- General Tab -->
                      <div class="tab-pane @if($tab=='general' || $tab=='') active @endif" id="general">
                        <form method="post" id="general-form" action="{{route( 'settings.store')}}">
                          @csrf  
                          <div class="clearfix"></div>
                          @foreach($config_data as $general)
                                  @if($general->configuration_name == 'general')
                          <div class="dataSearchBox">
                              <div class="row">
                                  
                                  <input type="hidden" value="general" name="config">
                                    <div class="col-sm-6">
                                      <div class="form-group">
                                          <label for="{{$general->configuration_settings}}">{{ucwords(str_replace('_', ' ',$general->configuration_settings))}} <small class="textRed">*</small></label>
                                          <div class="p-relative">
                                          <i class="fa {{$general->configuration_icon}} icn-add" aria-hidden="true"></i>
                                          <input required type="text" class="form-control" id="{{$general->configuration_settings}}"  name="{{$general->configuration_settings}}" value="{{$general->configuration_value}}" >
                                        </div>
                                      </div>
                                  </div>
                                  <div class="col-sm-6">
                                      <div class="form-group">
                                          <label for="{{$general->configuration_settings}}_year">{{ucwords(str_replace('_', ' ',$general->configuration_settings))}} Year 
                                          @if(isset($general->configuration_year))
                                          <small class='textRed'>*</small>
                                          @endif
                                          </label>  
                                          <div class="p-relative">
                                          <i class="fa {{$general->configuration_icon}} icn-add" aria-hidden="true"></i>
                                          <input  {{isset($general->configuration_year)?'required':''}} type="text" class="form-control" id="{{$general->configuration_settings}}_year"  name="{{$general->configuration_settings}}_year" value="{{$general->configuration_year}}" >
                                        </div>
                                      </div>
                                  </div>
                                  <div class="col-sm-6">
                                      <div class="form-group">
                                          <label for="{{$general->configuration_settings}}_increment_value">{{ucwords(str_replace('_', ' ',$general->configuration_settings))}} Increment Value 
                                          @if(isset($general->configuration_increment_value))
                                          <small class='textRed'>*</small>
                                          @endif
                                          </label>
                                          <div class="p-relative">
                                          <i class="fa {{$general->configuration_icon}} icn-add" aria-hidden="true"></i>
                                          <input {{isset($general->configuration_increment_value)?'required':''}}  type="text" class="form-control" id="{{$general->configuration_settings}}_increment_value"  name="{{$general->configuration_settings}}_increment_value" value="{{$general->configuration_increment_value}}" >
                                        </div>
                                      </div>
                                  </div>
                            
                            <div class="w-100"></div>
                              <div class="col">
                            
                            </div>  
                                </div>
                            </div>
                            @endif
                            @endforeach
                            <div class="w-100"></div>
                            <button type="submit" class="btn btn-primary">Save</button>
                        </form>
                    </div>
                      <!--  End General Tab-->
                      <!--- Email -->
                      <div class="tab-pane @if($tab=='email') active @endif" id="email-settings">
                            <form method="post" id="email-form" action="{{route( 'settings.store')}}">
                              @csrf  @if(isset($enquiry)){{method_field('PUT')}}@endif
                               <input type="hidden" value="email" name="config">
                              <div class="clearfix"></div>
                              <div class="dataSearchBox">
                                  <div class="row">
                                        @foreach($config_data as $general)
                                      @if($general->configuration_name == 'email')
                                        <div class="col-sm-6">
                                          <div class="form-group">
                                              <label for="{{$general->configuration_settings}}">{{ucwords(str_replace('_', ' ',$general->configuration_settings))}} <small class="textRed">*</small></label>
                                              <div class="p-relative">
                                              <i class="fa {{$general->configuration_icon}} icn-add" aria-hidden="true"></i>
                                              <input required type="text" class="form-control" id="{{$general->configuration_settings}}"  name="{{$general->configuration_settings}}" value="{{$general->configuration_value}}"  placeholder="Enter Mobile No">
                                              </div>
                                          </div>
                                </div>
                                @endif
                                @endforeach
                                <div class="clearfix"></div>
                                <div class="w-100"></div>
                                <div class="col">
                                <div class="w-100"></div>
                                    <button type="submit" class="btn btn-primary">Save</button>
                              </div>
                                  </div>
                              </div>
                          </form>
                      </div>
                      <!--  End Email -->
                       <!-- Email -->
                      <div class="tab-pane @if($tab =='sms') active @endif " id="sms">
                            <form method="post" id="sms-form" action="{{route( 'settings.store')}}">
                              @csrf  @if(isset($enquiry)){{method_field('PUT')}}@endif
                               <input type="hidden" value="sms" name="config">
                              <div class="clearfix"></div>
                              <div class="dataSearchBox">
                                  <div class="row">
                                        @foreach($config_data as $general)
                                      @if($general->configuration_name == 'sms')
                                        <div class="col-sm-6">
                                          <div class="form-group">
                                              <label for="{{$general->configuration_settings}}">{{ucwords(str_replace('_', ' ',$general->configuration_settings))}} <small class="textRed">*</small></label>
                                              <div class="p-relative">
							<i class="fa {{$general->configuration_icon}} icn-add" aria-hidden="true"></i>
							<input required type="text" class="form-control" id="{{$general->configuration_settings}}"  name="{{$general->configuration_settings}}" value="{{$general->configuration_value}}"  placeholder="Enter Mobile No">
                                              </div>
                                          </div>
                                </div>
                                @endif
                                @endforeach
                                 <div class="w-100"></div>
                              <div class="col">
                                <div class="w-100"></div>
                                    <button type="submit" class="btn btn-primary">Save</button>
                              </div>
                                  </div>
                              </div>
                          </form>
                      </div>
                      <!--- End sms -->
                      <!--- Renewal Nofity  -->
                      <div class="tab-pane @if($tab=='settings') active @endif" id="settings">
                            <form method="post" id="renewalNotify-form" action="{{route( 'settings.store')}}">
                              @csrf  @if(isset($enquiry)){{method_field('PUT')}}@endif
                               <input type="hidden" value="settings" name="config">
                              <div class="clearfix"></div>
                              <div class="dataSearchBox">
                                  <div class="row">
                                      @foreach($config_data as $general)
                                      @if($general->configuration_name == 'settings')
                                        <div class="col-sm-6">
                                          <div class="form-group">
                                              <label for="{{$general->configuration_settings}}">{{ucwords(str_replace('_', ' ',$general->configuration_settings))}} <small class="textRed">*</small></label>
                                              <div class="p-relative">
                                              <i class="fa {{$general->configuration_icon}} icn-add" aria-hidden="true"></i>
                                              <input required type="text" class="form-control" id="{{$general->configuration_settings}}"  name="{{$general->configuration_settings}}" value="{{$general->configuration_value}}"  placeholder="">
                                              </div>
                                          </div>
                                </div>
                                @endif
                                @endforeach
                                <div class="clearfix"></div>
                                <div class="w-100"></div>
                                <div class="col">
                                <div class="w-100"></div>
                                    <button type="submit" class="btn btn-primary">Save</button>
                              </div>
                                  </div>
                              </div>
                          </form>
                      </div>
                      <!-- End Renewal Nofity -->
                  </div>
              </div>                

          </div>
          <!-- END PROFILE CONTENT -->
      </div>
      </div>
  </div>
@endsection
@section('scripts')
<script>
  $(document).ready(function() {
    $("#general-form").validate()
    $("#email-form").validate()
    $("#sms-form").validate()
    $("#renewalNotify-form").validate()


   });
</script>
@endsection
