	@php $count = 1; @endphp
                    @forelse ($units as $unit)
                    <tr>
                        <td>
							<a @can('edit_unit') href="{{route('unit.edit',$unit->id)}}" title="Edit" @elsecan('view_unit') href="{{route('tenants.show',$unit->id)}}" title="Show"  @endcan class="no-link" >
								{{$units->perPage()*($units->currentPage()-1)+$count}}
							</a>
						</td>
                        <td>
							<a @can('edit_unit') href="{{route('unit.edit',$unit->id)}}" title="Edit" @elsecan('view_unit') href="{{route('tenants.show',$unit->id)}}" title="Show"  @endcan class="no-link" >
								{{$unit->unit_code}}                           
							</a>
						</td> 
						<td>
							<a @can('edit_unit') href="{{route('unit.edit',$unit->id)}}" title="Edit" @elsecan('view_unit') href="{{route('tenants.show',$unit->id)}}" title="Show"  @endcan class="no-link" >
								{{$unit->unit_no}}                           
							</a>
						</td> 
                        <td>
							<a @can('edit_unit') href="{{route('unit.edit',$unit->id)}}" title="Edit" @elsecan('view_unit') href="{{route('tenants.show',$unit->id)}}" title="Show"  @endcan class="no-link" >
								{{$unit->building->building_name}}
							</a>
						</td>
                        <td>
							<a @can('edit_unit') href="{{route('unit.edit',$unit->id)}}" title="Edit" @elsecan('view_unit') href="{{route('tenants.show',$unit->id)}}" title="Show"  @endcan class="no-link" >
								{{$unit->unit->unit_types_name}}
							</a>
						</td>                       
                        <td>
							<a @can('edit_unit') href="{{route('unit.edit',$unit->id)}}" title="Edit" @elsecan('view_unit') href="{{route('tenants.show',$unit->id)}}" title="Show"  @endcan class="no-link" >
								{{$unit->vacant_status_name}}
							</a>
						</td> 
                        @can('change_status_unit')                                                   
                        <td>
                        
							@if($unit->unit_status == 1)
                            @if($unit->unit_vaccant_status == 0)
                            <a title="Change Status" class="change_status" href="{{route('unit.changeStatus',$unit->id)}}">
							<button type="button" class="btn btn-circle btn-success btn-sm m-b-10 status">{{$unit->unit_status_name}}</button>
                            </a>
                            @else
                            <button type="button" class="btn btn-circle btn-success btn-sm m-b-10 status">{{$unit->unit_status_name}}</button>
                            @endif
							@else 
							<button type="button" class="btn btn-circle btn-danger btn-sm m-b-10">{{$unit->unit_status_name}}</button>
							@endif
							
							 
                            <form id="status-form" action="" method="POST">
                                 {{csrf_field()}}
                                <input type="hidden" name="status" value="{{$unit->unit_status}}">
                                <input style="display: none;" type="submit">
                            </form>
                            </td> 
                        @endcan                            
                        <td>
						@can('view_unit') 
                        <a href="{{route('unit.show',$unit->id)}}" title="View" class="btn btn-tbl-view btn-xs">
                            <i class="fa fa-eye "></i>
                        </a>
                        @endcan 
                        @can('edit_unit') 
                        <a title="Edit" href="{{route('unit.edit',$unit->id)}}" class="btn btn-tbl-edit btn-xs" title="Edit">
                            <i class="fa fa-pencil"></i>
                        </a>  
                        @endcan
                        @can('qrcode_generator') 
                        <a  href="{{route('qrCodeGenerator',$unit->id)}}" class="btn btn-tbl-general btn-xs" title="Change to QR Code" download="{{$unit->unit_code.'-'.$unit->unit_no}}">
                            <i class="fa fa-qrcode" aria-hidden="true"></i>
                        </a>  
                        @endcan                                           
                        </td>
                    </tr>  
                    @php $count++; @endphp
                    @empty
                    <tr>
                        <td colspan="8" align="center">
                        <p>No Record</p>
                       </td>
                    </tr>
                    @endforelse




                       @if(isset($ajax))   
                                <tr>                                   
                                            
                                                      
                                  <td colspan="6" id="pagination_ajax"> 
                                        @php 
                                        $units->withPath(\Request::input('curr_url'));
                             
                                        @endphp   
                                        
                                        {{$units->appends(\Request::except(['page','_token']))->links()}}

                                        <div class="pagination_info">
                                   @include('includes.pagination_info',['paginator' => $units])         
                               </div>
                                        
                                        </td>                           
                  
                                 </tr>
                                 @endif
