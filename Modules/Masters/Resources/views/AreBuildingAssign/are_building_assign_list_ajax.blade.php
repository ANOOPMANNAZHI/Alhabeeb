               @forelse ($areBuildingAssigns as $areBuildingAssign)
               @php
               $current = 'areBuildingAssign.index';
               Session::put('current', $current); 
               @endphp           
               <tr>

                   <td>@can('are_building_assign_view')<a class="no-link" href="{{route('areBuildingAssign.show',$areBuildingAssign->id)}}">{{$areBuildingAssign->areUser->employee->employee_name }}</a>@endcan</td>

                   <td>@can('are_building_assign_view')<a class="no-link" href="{{route('areBuildingAssign.show',$areBuildingAssign->id)}}">{{$areBuildingAssign->assignedBuildingNames->implode('building_name',' ,   ')}}</a>@endcan</td>

                   <td>@can('are_building_assign_view')<a class="no-link" href="{{route('areBuildingAssign.show',$areBuildingAssign->id)}}">{{$areBuildingAssign->assign_from->format('d/m/Y')}}</a>@endcan</td>
                   
                      
                    <td>
                      @can('are_building_assign_view') 
                      <a href="{{route('areBuildingAssign.show',$areBuildingAssign->id)}}" title="View" class="btn btn-tbl-view btn-xs">
                        <i class="fa fa-eye "></i>
                    </a>
                    @endcan

                    @if($areBuildingAssign->amc_schedule_task_status==0)
                    <a title="Edit" href="{{route('areBuildingAssign.edit',$areBuildingAssign->id)}}" class="btn btn-tbl-edit btn-xs" title="Edit">
                          <i class="fa fa-pencil"></i>
                        </a>  
                    @endif            
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
          	@php										 

          if(isset($request->ajax))	
          $areBuildingAssigns->withPath($route);

          if(!empty($request->user_id))
          $areBuildingAssigns->appends(['user_id' => $request->user_id]);

          if(!empty($request->building_id))
          $areBuildingAssigns->appends(['building_id' => $request->building_id]); 

          if(!empty($request->assign_from))
          $areBuildingAssigns->appends(['assign_from' => $request->assign_from]);

								

          $fieldName =  app('request')->input('fieldName');

          if(!empty($fieldName)){
          $fieldName =  app('request')->input('fieldName');
          $operation =  app('request')->input('operation');
          $fieldValue =  app('request')->input('fieldValue');
          $logic =  app('request')->input('logic');
          $areBuildingAssigns->appends(['fieldName' => $fieldName,
          'operation' => $operation,
          'fieldValue' => $fieldValue,
          'logic' => $logic,
          ]);	
      }

      @endphp   

      {{$areBuildingAssigns->links()}} 
       <div class="pagination_info">
                                   @include('includes.pagination_info',['paginator' => $areBuildingAssigns])         
                               </div>

  </td>                           

</tr>
 @endif  
