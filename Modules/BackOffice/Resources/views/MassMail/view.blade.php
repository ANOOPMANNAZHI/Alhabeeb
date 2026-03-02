@extends('layouts.plms-app')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">


@section('content')
<!-- start widget -->
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">Compose Mail View</div>
    </div>

    {{ Breadcrumbs::render('massMail.view' ,$massMail) }}
  </div>
</div>


<div class="row">
  <div class="col">
    <div class="card card-box salesSearchBox">
     <div class="dataSearchBox">
      <div class="card-body row">
         @if(count($toList)>0)
        <div class="col-lg-12 p-t-20"> 
          <div class = "txt-full-width">
            <h5 class="details"><b>To :  </b><span>
              @php $cuntloop =1; @endphp
              @foreach($toList as $to) 
                @if($to['usertype'] == 1)
                  @if($to['mailstatus'] == 2 )
                    <span style="color:red" title="failed">{{'<'.$to['vendor_contact_email'].'>'.$to['vendor_name']}} </span>
                  @else
                    {{'<'.$to['vendor_contact_email'].'>'.$to['vendor_name']}} 
                  @endif
                @else
                   @if($to['mailstatus'] == 2 )
                  <span style="color:red" title="failed">{{'<'.$to['tenant_contact_email'].'>'.$to['tenant_name']}}</span>
                  @else
                    {{'<'.$to['tenant_contact_email'].'>'.$to['tenant_name']}} 
                  @endif
                  
                @endif
                {{(count($toList)>$cuntloop)?',':''}}
                @php $cuntloop = $cuntloop+1; @endphp
              @endforeach 
            </span></h5>
          </div>
        </div>
        @endif
        @if(isset($cc_users))
        <div class="col-lg-12 p-t-20"> 
          <div class = "txt-full-width">
            <h5 class="details"><b>Cc :  </b><span>
             
              @php $cuntCcloop =1; @endphp
              @foreach($cc_users as $cc) 
              
                  @if($cc['mailstatus'] == 2 )
                    <span style="color:red" title="failed">{{'<'.$cc['emailUser'].'>'.$cc['employee_name']}} </span>
                  @else
                    {{'<'.$cc['emailUser'].'>'.$cc['employee_name']}} 
                  @endif
               
                {{(count($toList)>$cuntCcloop)?',':''}}
                @php $cuntCcloop = $cuntCcloop+1; @endphp
              @endforeach 
             
            </span></h5>
          </div>
        </div>
        @endif
        @if(isset($massMail->subject))
        <div class="col-lg-12 p-t-20"> 
          <div class = "txt-full-width">
            <h5 class="details"><b>Subject :  </b><span>{{$massMail->subject}}</span></h5>
          </div>
        </div>
        @endif
        @if(isset($massMail->content))
        <div class="col-lg-12 p-t-20"> 
          <div class = "txt-full-width">
            <h5 class="details"><b>Content :  </b><span>{!! strip_tags($massMail->content) !!} </span></h5>
          </div>
        </div>
        @endif

        @if(isset($massMail->created_at))
        <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
            <h5 class="details"><b>Created At :  </b><span>{{$massMail->created_at->format('d-m-Y')}}</span></h5>
          </div>
        </div>
        @endif 

        @if(isset($massMail->created_by))
        <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
            <h5 class="details"><b>Created By :  </b><span>{{$massMail->createdBy->employee->employee_name ?? 'Admin'}}</span></h5>
          </div>
        </div>
        @endif

      </div>
    </div>   
  </div>
</div>
</div>      



@endsection
