@php $lcStageNames = \Illuminate\Support\Facades\DB::table('legal_stages')->pluck('name', 'id'); @endphp
                @forelse ($activeCases as $activeCase)
                @php
                $current = 'activeCase.index';
                Session::put('current', $current); 

                @endphp             
                <tr>
                 <td>@can('active_cases_view')<a class="no-link" href="">{{$activeCase->tenantContract->tenant_contract_no ?? ''}}</a>@endcan</td>   

                 <td>@can('active_cases_view')<a class="no-link" href="">{{$activeCase->tenantContract->tenant->tenant_name }}</a>@endcan</td>

                 <td>@can('active_cases_view')<a class="no-link" href="">{{$activeCase->building->building_name}}</a>@endcan</td>

                 <td>@can('active_cases_view')<a class="no-link" href="">{{$activeCase->Unit->unit_code}}</a>@endcan</td>

                 <td>@can('active_cases_view')<a class="no-link" href="">{{$activeCase->tenantContract->tenant_contract_valid_to_date->format('d/m/Y')}}</a>@endcan</td>

                 <td>@can('active_cases_view')
                  @if($activeCase->tenantContract->tenant_contract_status == 1)
                  <span class=" btn-circle btn-success btn-sm m-b-10 status"><b>Active</b></span>
                  @else 
                  <span  class=" btn-circle btn-danger btn-sm m-b-10"><b>Inactive</b></span>
                  @endif
                  @endcan</td>

                  <td>@can('active_cases_view')<a class="no-link" href="">{{numberFormat($activeCase->tenantContract->tenant_contract_rent)}}</a>@endcan</td>
                  <td>@if($activeCase->priority)<span class="lc-badge lc-p{{ $activeCase->priority }}">{{ \Modules\Masters\Services\LegalCase::PRIORITIES[$activeCase->priority] }}</span>@endif</td>
                  <td>{{ $activeCase->stage_id && isset($lcStageNames[$activeCase->stage_id]) ? $lcStageNames[$activeCase->stage_id] : '-' }}</td>
                  <td>{{ $activeCase->next_hearing_date ? \Carbon\Carbon::parse($activeCase->next_hearing_date)->format('d/m/Y') : '' }}</td>

                  <td>
                    @can('active_cases_view') 
                    <a href="{{route('legal.case',$activeCase->id)}}" title="Case page" class="btn btn-tbl-edit btn-xs">
                      <i class="fa fa-gavel"></i>
                    </a>
                    <a href="{{route('activeCasesShow',$activeCase->id)}}" title="View" class="btn btn-tbl-view btn-xs">
                      <i class="fa fa-eye "></i>
                    </a>
                    @endcan 

                  </td>
                </tr>

                @empty 
                <tr>
                 <td colspan="10" align="center">
                  <p>No Record</p>
                </td>
              </tr>
              @endforelse



              @if(isset($request->ajax))	
              <tr>
               <td colspan="5" id="pagination_ajax">  
              {{$activeCases->withPath($route)->appends(\Request::except(['page','_token','ajax']))->links()}} 

              <div class="pagination_info">
                                   @include('includes.pagination_info',['paginator' => $activeCases])         
                               </div>
            </td>                           

          </tr>
          @endif 
