@extends('layouts.plms-app')



@section('content')
    <!-- start widget -->
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Unit</div>
        </div>
        {{ Breadcrumbs::render('unit.index') }}
    </div>
</div>

<a  class=" align-right advSearch" href="#" id="enquiry_div" data-toggle="collapse" data-target="#show">
    <i class="fa fa-search" aria-hidden="true"></i>  <span id="enquirySearch"> Advance Search <i class="fa fa-caret-down" aria-hidden="true"></i></span>
</a>
<div class="clearfix"></div>
<div class="row collapse @if(old('fieldName')) show @endif" id="show">
	<div class="col-md-12 col-sm-12 dashboardtab">
		<div class="panel tab-border card-box">
		   @include('masters::search')     
    	</div>
	</div>
</div>


 <div class="row">
   <div class="col-md-12 col-sm-12">
        <div class="card  card-box">
            
            <div class="card-body ">
            <h4>
             <div id="pagination_info">
                     @include('includes.pagination_info',['paginator' => $units])         
                 </div>
			 @can('add_unit') 
             <a href="{{route('unit.create')}}" class="btn btn-circle btn-primary  align-right"  >Add</a>
             @endcan
             <div class="clr"></div>
            </h4>
              <div class="table-responsive1">
              <table class="table display product-overview mb-30" id="dtBasicExample">
                <thead>
                    <tr>
                        <th>Sl No.</th>
                        <th>@sortablelink('unit_code','Unit Code',[], ['class' => 'sort_class'])</th>
                        <th>@sortablelink('unit_no','Unit No',[], ['class' => 'sort_class'])</th>
                        <th>@sortablelink('building.building_name','Building',[], ['class' => 'sort_class']) </th>
                        <th>@sortablelink('unit.unit_types_name','Unit Type',[], ['class' => 'sort_class'])</th>
                        <th>@sortablelink('unit_vaccant_status','Vacant',[], ['class' => 'sort_class']) </th>
                         @can('change_status_unit') 
                        <th>@sortablelink('unit_vaccant_status','Status',[], ['class' => 'sort_class'])</th>
                         @endcan
                        <th>Action</th>
                    </tr>
                    <tr>
                        <td></td>
                        <td> <input  type="text" name="unit_code" class="search_fields " id="unit_code" value="{{old('unit_code')}}" ></td>
                        <td> <input  type="number" name="unit_no" class="search_fields " id="unit_no" value="{{old('unit_no')}}" ></td>
                        <td> <input  type="text" name="building__building_name" class="search_fields " id="building__building_name" value="{{old('building__building_name')}}" ></td>
                        <td>  
                          <select name="unit_type_id" class="search_fields" id="unit_type_id" style="width:100px;">
                          <option value="">Show All</option>
                          @foreach($unitTypes as $unit_type)
                          <option {{(old('unit_type_id') == $unit_type->id)? 'selected':''}}  value="{{$unit_type->id}}">{{$unit_type->unit_types_name}}</option>
                          @endforeach             
                            </select>
                        </td>
                         
                        <td>  
                            <select name="unit_vaccant_status" class=" search_fields mob" id="unit_vaccant_status" style="width:100px;">
                            <option value="">Show All</option>
                            <option {{ (old('unit_vaccant_status') != '')? ((old('unit_vaccant_status') ==  0)? 'selected':'' ) : '' }}    value="0">Vacant</option>
                            <option {{(old('unit_vaccant_status') == 1)? 'selected':''}}  value="1">Occupied</option>
                            </select>
                        </td>
                          @can('change_status_unit') 
                        <td>  
                            <select name="unit_status" class="searchFields search_fields mob" id="unit_status" style="width:100px;">
                            <option value="">Show All</option>
                            <option {{ (old('unit_status') != '')? ((old('unit_status') ==  0)? 'selected':'') : ''}}   value="0">Inactive</option>
                            <option {{(old('unit_status') ==  1)? 'selected':''}}  value="1">Active</option>
                            </select>
                        </td>
                         @endcan
                         <td></td>
                    </tr>
                </thead>
                <tbody  id="search">
				@include('masters::Unit.list_ajax')
                </tbody>
              </table>
			  </div>
               
              <div id="pagination">
               {{$units->appends(\Request::except(['page','_token']))->links()}}
              </div> 
            </div>
        </div>
    </div>
</div>
<input type="hidden" name="curr_url" id="curr_url" value="{{url()->current()}}">
<form id="delete-form" action="" method="POST">
    {{ method_field('DELETE') }}  {{csrf_field()}}
    <input value="delete" style="display: none;" type="submit">
</form>
@endsection
@section('scripts')    
 
<script>


 jQuery(document).ready(function() {
	
	 $("#show").on("hide.bs.collapse", function(){
        $("#enquirySearch").html('Advance Search <i class="fa fa-caret-down" aria-hidden="true"></i>');
    });
       $("#show").on("show.bs.collapse", function(){
        $("#enquirySearch").html('Advance Search <i class="fa fa-caret-up" aria-hidden="true"></i>');
    });
 // jQuery('.dataTables_length').addClass('bs-select');
  
            jQuery('.delete_type').click(function (event) {
                var action = $(this).attr("href");
                event.preventDefault();
                if (confirm('Do you want to Delete this Vendor Type?')) {
                    jQuery("#delete-form").attr('action', action);
                    jQuery("#delete-form").submit();
                } else {
                    return false;
                }
            });
            jQuery('.change_status').click(function (event) {
                var action = $(this).attr("href");
                event.preventDefault();
                if (confirm('Do you want to Change Status?')) {
                    jQuery("#status-form").attr('action', action);
                    jQuery("#status-form").submit();
                } else {
                    return false;
                }
            })



       $(document).on('change keyup paste','.search_fields',function(){ 
 
         var unit_code = $("#unit_code").val();  
         var unit_no = $("#unit_no").val();  
         var building__building_name = $("#building__building_name").val();  
         var unit_type_id = $("#unit_type_id").val();  
         var unit_vaccant_status = $("#unit_vaccant_status").val();  
         var unit_status = $("#unit_status").val();  
         var curr_url = $("#curr_url").val();  



       $.ajax({
        method: "POST",
        url: "{{route('unitFilter')}}",
        data: { 'unit_code' : unit_code, 'unit_no' : unit_no , 
          'building__building_name' : building__building_name,
          'unit_type_id' : unit_type_id,
          'unit_vaccant_status' : unit_vaccant_status,
          'unit_status' : unit_status,         
          'curr_url' : curr_url,"_token" : $('meta[name="csrf-token"]').attr('content')},

        beforeSend: function(){
                // Show image container
                $('#search').html("<tr><td colspan='11' align='center'><img src='{{url('/')}}/public/img/pre-loader.gif' width='75' height='75'></td></tr>");        
               },  
             
        
        success: function(data){  


           if(data != 0){

              var temp = $(data);
              var paginate_info = temp.find('.pagination_info').clone();
                    temp.find('.pagination_info').remove();
              var paginate = temp.find('#pagination_ajax').clone();
              
              temp.find('#pagination_ajax').remove();
              $('#search').html(temp);
              $("#pagination" ).html(paginate);  
              $( "#pagination_info" ).html(paginate_info); 
              //   $('.search_fields').trigger('blur');             
            }  

            var txt = 'unit_code='+unit_code+'&unit_no='+unit_no+'&building__building_name='+
                      building__building_name+'&unit_type_id='+unit_type_id+'&unit_vaccant_status='+unit_vaccant_status+'&unit_status='+
                      unit_status+'&curr_url='+curr_url ;
            
            var txt_hashes = txt.split('&');
            var href_txt = '';
                  for(var i = 0; i < txt_hashes.length; i++)
                    {
                        txt_hash = txt_hashes[i].split('=');

                        if(txt_hash[1] !=  ''){                             
                         href_txt =  (href_txt != '')? href_txt + '&': href_txt;

                         href_txt =  href_txt + txt_hash[0]+'='+txt_hash[1];
                        }                       
                    }          
// split sort and direction   


              $('.sort_class').each(function (i, n) {
                  var href = $(n).attr('href'); 
                  var hashes =  href.slice(href.indexOf('sort'));
                  href = href.split('?')[0];
                  $(n).attr('href',href+'?'+href_txt+'&'+hashes);                 
                });       
           
        } 

      });

       });



        });

</script> 


@endsection
