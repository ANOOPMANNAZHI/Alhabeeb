       @php $fromPage =  ($paginator->currentpage()-1) * $paginator ->perpage();  @endphp 
             <div class="col-md-12 col-lg-12">
                <div class="dataTables_info flex-wrap ">Showing 
                     @if($paginator->count() > 0)
                      {{$fromPage+1}} to {{$fromPage+$paginator->count()}} 
                     @else
                      0
                     @endif
                     of {{$paginator->total() }}

                     Records

                </div>
            </div>