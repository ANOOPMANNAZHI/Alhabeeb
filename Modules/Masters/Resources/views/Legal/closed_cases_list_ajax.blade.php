                @forelse ($closedLegalCases as $closedLegalCase)
                @php
                $current = 'closedLegalCase.index';
                Session::put('current', $current); 

                @endphp             
                <tr>
                 <td>@can('closed_legal_cases_view')<a class="no-link" href="">{{$closedLegalCase->tenantContract->tenant_contract_no ?? ''}}</a>@endcan</td>   

                 <td>@can('closed_legal_cases_view')<a class="no-link" href="">{{$closedLegalCase->tenantContract->tenant->tenant_name }}</a>@endcan</td>

                 <td>@can('closed_legal_cases_view')<a class="no-link" href="">{{$closedLegalCase->building->building_name}}</a>@endcan</td>

                 <td>@can('closed_legal_cases_view')<a class="no-link" href="">{{$closedLegalCase->Unit->unit_code}}</a>@endcan</td>

                 <td>@can('closed_legal_cases_view')<a class="no-link" href="">{{$closedLegalCase->tenantContract->tenant_contract_valid_to_date->format('d/m/Y')}}</a>@endcan</td>

                 <td>@can('closed_legal_cases_view')
                  @if($closedLegalCase->tenantContract->tenant_contract_status == 1)
                  <span class=" btn-circle btn-success btn-sm m-b-10 status"><b>Active</b></span>
                  @else 
                  <span  class=" btn-circle btn-danger btn-sm m-b-10"><b>Inactive</b></span>
                  @endif
                  @endcan</td>

                  <td>@can('closed_legal_cases_view')<a class="no-link" href="">{{numberFormat($closedLegalCase->tenantContract->tenant_contract_rent)}}</a>@endcan</td>

                  <td>
                    @can('closed_legal_cases_view') 
                    <a href="{{route('closedLegalCasesShow',$closedLegalCase->id)}}" title="View" class="btn btn-tbl-view btn-xs">
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
              {{$closedLegalCases->withPath($route)->appends(\Request::except(['page','_token','ajax']))->links()}} 

              <div class="pagination_info">
                                   @include('includes.pagination_info',['paginator' => $closedLegalCases])         
                               </div>
            </td>                           

          </tr>
          @endif 
