@extends('layouts.plms-app')
@section('content')
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Vacancy Loss</div>
        </div>
        {{ Breadcrumbs::render('vacancy_loss') }}
    </div>
</div>
<div class="row">
    <div class="col-md-12 col-sm-12">
        <div class="card  card-box">
            <div class="card-body ">
            <h4>
                  <div id="pagination_info">
               
            </div>
			<div class="clr"></div></h4>

              	<div class="table-wrap">
                    <div class="table-responsive">
                        <table class="table display product-overview mb-30" id="dtBasicExample">
                           <thead>
                            <tr>       
                             <th>Bldg Name</th>
                             <th>Unit</th>
                             <th>Rent PM</th>
                             <th>Last Vacant Since</th>
                             <th>No of Vacant days</th>
                             <th>Vacancy loss amount</th>
                            </tr>   
                           </thead>
                           <tbody>
                        @forelse ($vaccanyList as $unit)
					       <tr>		   
			                 <td>{{$unit['building_name']}}</td>
                             <td>{{$unit['unit_no']}}</td>
                             <td>{{$unit['rent_per_month']}}</td>
                             <td>{{!empty($unit['vacant_since'])?date('d/m/Y',strtotime($unit['vacant_since'])):''}}</td>
                             <td>{{$unit['no_days']}}</td>
                             <td>{{$unit['vacant_loss']}}</td>
                             
						</tr>		
                        @empty
                          <tr>
                            <td colspan="6" align="center">
                             <p>No Record</p>
                           </td>
                         </tr>
                     @endforelse
                            </tbody>
                        </table>
                        
                        
                        
                    </div>
                </div> 
            
            </div>
        </div>
    </div>
</div>

@endsection

