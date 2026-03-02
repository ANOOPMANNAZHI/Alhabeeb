@extends('layouts.plms-app')
 
@section('content')
    <!-- start widget -->
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Mass Mail</div>
        </div>
        {{ Breadcrumbs::render('massMail.index') }}  
    </div>
</div>

<div class="row">
  <div class="col-md-12 col-sm-12 dashboardtab">
    <div class="panel tab-border card-box">
     @include('backoffice::MassMail.mail_search') 
   </div>
 </div>
</div>

 <div class="row">
   <div class="col-md-12 col-sm-12">
        <div class="card  card-box">
            
            <div class="card-body ">
            <h4>
              @if(isset($massMails))
            <div id="pagination_info">
                 @include('includes.pagination_info',['paginator' => $massMails])         
             </div>
             @endif
			       @can('add_bank')
             <a href="{{route('massMail.create')}}" class="btn btn-circle btn-primary  align-right">Composer</a>
             @endcan
             <div class="clr"></div>
            </h4>
              <div class="table-wrap">
				  <div class="table-responsive1">
              <table class="table display product-overview mb-30" id="dtBasicExample">
                <thead>
                    <tr>
                        <th>Sl No.</th>
                        <th>Subject</th>
                        <th>Content</th>
                        <th>time</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
					@php $count = 1; @endphp
          @if(isset($massMails)) 
          @forelse ($massMails as $massMail)
                    <tr>
                        <td>
							<a @can('view_bank') href="{{route('massMail.show',$massMail->id)}}" title="Show"  @endcan class="no-link" > 
								{{$massMails->perPage()*($massMails->currentPage()-1)+$count}}
							</a>
                        </td>
                        
            <td>
                <a @can('view_bank') href="{{route('massMail.show',$massMail->id)}}" title="Show"  @endcan class="no-link" >
                    {{$massMail->subject}}
                </a>
            </td>
            <td>
              @if(isset($massMail->content))
              <a @can('view_bank') href="{{route('massMail.show',$massMail->id)}}" title="Show"  @endcan class="no-link" title="Show"  class="no-link" >
                {{ \Illuminate\Support\Str::limit(strip_tags($massMail->content), 150, $end='...') }}
                   
                </a>
              @endif
                {{--
                @if($massMail->preferredMailsTo->user_type ==1)
                  {{$massMail->preferredMailsTo->tenantMails}}
                @elseif($massMail->preferredMailsTo->user_type == 2)
                  {{$massMail->preferredMailsTo->vendorMails}}
                @else
                  {{$massMail->preferredMailsTo->user->employee->employee_name}}
                @endif --}}
              </a>
            </td> 
            <td>
							<a @can('view_bank') href="{{route('massMail.show',$massMail->id)}}" title="Show"  @endcan class="no-link" >
								{{$massMail->created_at->format('d/m/Y')}}
							</a>
						</td>

                             
                         
                        <td>
						@can('view_bank')
                        <a href="{{route('massMail.show',$massMail->id)}}" title="View" class="btn btn-tbl-view btn-xs">
                            <i class="fa fa-eye"></i>
                        </a> 
                        @endcan                     
                        </td>
                    </tr>  
                     @php $count++; @endphp 
                    @empty
                    <tr>
                        <td colspan="6" align="center">
                        <p>No Record</p>
                       </td>
                    </tr>
                    @endforelse
               @else
                    <tr>
                        <td colspan="6" align="center">
                        <p>No Record</p>
                       </td>
                    </tr>
                @endif
                </tbody>
              </table>
          @php
					$sort =  app('request')->input('sort') ;
					if(!empty($sort)){
					$direction =  app('request')->input('direction') ;
					$massMails->appends(['sort' => $sort, 'direction' => $direction ]);					
					}								
			    @endphp  
			     @if(isset($massMails)) 
              {{$massMails->links()}}    
          @endif  
            </div>
        </div>
        </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')    
 <script>
     $(document).ready(function() {
     $('#email').autocomplete({
      source : '{!!URL::route('massEmailAutocomplete')!!}',
      minlenght:2,
      autoFocus:true,
      change:function(e,ui){
         if (ui.item == null || ui.item == undefined) {
            $("#email").val('');
            $('#user_id').val('');
            $('#email-error').show();
         }else {
            $('#user_id').val(ui.item.ids);
          
         }
       
      }
    });
    });
 </script>



@endsection
