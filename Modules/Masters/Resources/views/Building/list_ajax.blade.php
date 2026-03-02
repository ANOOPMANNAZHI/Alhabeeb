@php $count = 1; @endphp
           @forelse ($buildings as $building)
           <tr>
            <td>
             <a @can('edit_building') href="{{route('building.edit',$building->id)}}" title="Edit" @elsecan('view_building') href="{{route('building.show',$building->id)}}" title="View"  @endcan class="no-link" >
              {{$buildings->perPage()*($buildings->currentPage()-1)+$count}}
            </a>
          </td>
          <td>
           <a @can('edit_building') href="{{route('building.edit',$building->id)}}" title="Edit" @elsecan('view_building') href="{{route('building.show',$building->id)}}" title="View"  @endcan class="no-link" >
            {{$building->building_name}}
          </a>
        </td>                            

        <td>{{$building->vendor->vendor_name}}</td>
        <td>
         <a @can('edit_building') href="{{route('building.edit',$building->id)}}" title="Edit" @elsecan('view_building') href="{{route('building.show',$building->id)}}" title="View"  @endcan class="no-link" >
          {{$building->management->management_types_name}}
        </td>
        @can('change_status_building')   
        <td> 

         @if($building->building_status == 1)
         <span  class=" btn-circle btn-success btn-sm m-b-10 status">{{$building->building_status_name}}</span>
         @else 
         <span  class=" btn-circle btn-danger btn-sm m-b-10">{{$building->building_status_name}}</span>
         @endif


                            <!-- <form id="status-form" action="" method="POST">
                                 {{csrf_field()}}
                                <input type="hidden" name="status" value="{{$building->building_status_name}}">
                                <input style="display: none;" type="submit">
                              </form> -->

                            </td> 
                            @endcan                          
                            <td>
                             @can('view_building')   
                             <a href="{{route('building.show',$building->id)}}" title="View" class="btn btn-tbl-view btn-xs">
                              <i class="fa fa-eye "></i>
                            </a> 
                            @endcan
                            @can('edit_building')   
                            <a title="Edit" href="{{route('building.edit',$building->id)}}" class="btn btn-tbl-edit btn-xs" title="Edit">
                              <i class="fa fa-pencil"></i>
                            </a>    
                            @endcan                                          
                          </td>
                        </tr>  
                        @php $count++; @endphp
                        @empty
                        <tr>
                          <td colspan="4" align="center">
                            <p>No Record</p>
                          </td>
                        </tr>
                        @endforelse


                         @if(isset($ajax))   
                                <tr>                                   
                                            
                                                      
                                  <td colspan="6" id="pagination_ajax"> 
                                        @php 
                                        $buildings->withPath(\Request::input('curr_url'));
                             
                                        @endphp   
                                        
                                        {{$buildings->appends(\Request::except(['page','_token']))->links()}}

                                         <div class="pagination_info">
                                   @include('includes.pagination_info',['paginator' => $buildings])         
                               </div>
                                        
                                        </td>                           
                  
                                 </tr>
                                 @endif