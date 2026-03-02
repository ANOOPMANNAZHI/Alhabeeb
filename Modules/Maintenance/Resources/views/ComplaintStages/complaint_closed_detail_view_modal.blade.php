<div class="modal-dialog modal-lg assign">
    <div class="modal-content">
  
    <!-- Modal Header -->
    <div class="modal-header">
      <h4 class="modal-title">Detail View</h4>
      <button type="button" class="close" data-dismiss="modal">&times;</button>
    </div>
    
    <!-- Modal body -->
    <div class="modal-body">
    
   <div class="row">
   <div class="col-sm-12">
      <div class="card-box">
           <div class="card-head">
                <header>Service Report- {{$reportDetail->service_report_no}}</header>
                
            </div>
        <div class="card-body">
           
            <div class="table-responsive1">
                <table class="table" id="note_datatable">
                    <thead>
                        <tr style="background: #f5f5f5;">
                            <th>Note</th>
                            <th>Stage</th>
                            <th>Created By</th>
                            <th>Date & Time</th>
                        </tr>
                    </thead>
                    <tbody>
                
                   @forelse ($ServiceReportNotes as $note)
                
                    <tr>
                        <td>{{$note->desc}} </td>
                        <td>{{$note->ServiceReportStageName}}</td>
                        <td>{{$note->createdBy->employee->employee_name ?? $note->createdBy->username}} </td>
                        <td>{{$note->created_at->format('d/m/Y h:i:s')}}</td>
                    </tr>                     
                    @empty
                    <tr>
                        <td colspan="4" >
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
    <div class="col-md-12">
    <div class="card card-box">
      <div class="card-head">
                <header>Service Report Used Items</header>
                
            </div>
      <div class="card-body">
      <table class="table display product-overview mb-30" id="">
                <thead>
                    <tr>
                        <th>Item Name</th>
                        <th>Quantity</th>
                        <th>Material Charge</th>
                        <th>Labour Charge</th>
                        <th>Total</th>
                        <!-- <th>Action</th> -->
                    </tr>
                </thead>
                <tbody>
                 @php $sum_total = 0 @endphp
                 @forelse ($complaintServiceReportInv as $item)
                  @php $sum_total = $sum_total +$item->total_charge; @endphp
                  <tr>
                      <td>{{$item->inventory->inventories_name}} </td>
                      <td>{{$item->quantity}}</td>
                      <td>{{numberFormat($item->material_charge)}} OMR</td>
                      <td>{{numberFormat($item->labour_charge)}} OMR</td>
                      <td>{{numberFormat($item->total_charge)}} OMR</td>
         
                      <!-- <td> 
                        
                        <button type="button" class="btn btn btn-tbl-edit btn-xs itemEdit" title="Close" data-toggle="modal" data-target="#myModal" data-id="{{$item->id}}"><i class="fa fa-pencil"></i></button>

                        <a href="{{route('complaintStage.destroy',$item->id)}}" title="Delete" class="btn btn-tbl-delete btn-xs delete_type">
                            <i class="fa fa-trash-o "></i>
                        </a> 
                      </td> -->
                  </tr>      
                  @empty
                  <tr>
                      <td colspan="5" >
                      <p>No Record</p>
                     </td>
                  </tr>
                  @endforelse
                  @if(isset($complaintServiceReportInv) && count($complaintServiceReportInv) > 0)
                   <tr> 
                    <td colspan="4" align="right">Total</td>
                    <td colspan="5"> {{numberFormat($sum_total)}} OMR</td>
                  </tr>    
                  @endif
              </tbody>
              </table>
       </div>
       </div>
    </div>
</div> 
@if(isset($reportDetail->tenant_signature))
<div class="row">
  <div class="col-sm-12">
    <div class="card-box">
      <div class="card-head">
        <header>Signature</header>
        
      </div>
      <div class="card-body row">
    <div id="aniimated-thumbnials" class="list-unstyled  clearfix">
                   
             <div class="balance m-b-20 field_wrapper ro" align="center"> 
                <a href="{{asset('storage/app/'.$reportDetail->tenant_signature)}}" data-sub-html="Building Images" target="_blank" >
                 <img class="img-fluid img-thumbnail" src="{{asset('storage/app/'.$reportDetail->tenant_signature)}}" alt="" title ="">
                </a>
             </div>
             
          </div>
      </div>
    </div>
  </div>
</div>
@endif
@if($images->count()>0)
<div class="row">
  <div class="col-sm-12">
    <div class="card-box">
      <div class="card-head">
        <header>Gallery</header>
        
      </div>
      <div class="card-body row">
    <div id="aniimated-thumbnials" class="list-unstyled  clearfix">
          @foreach ($images as $image) 
           @php $ext = strtolower(pathinfo($image->image_path_thumbnail, PATHINFO_EXTENSION)); @endphp
			@if(in_array($ext, $supported_image))
              <div class="balance m-b-20 field_wrapper ro"> 
                <a href="{{asset('storage/app/'.$image->image_path_file_name)}}" data-sub-html="Doc, PDF, Docx" target="_blank" >
                 <img class="img-fluid img-thumbnail" src="{{asset('public/img/doc_download.png')}}" alt="" title =""> </a> 
                 <input type="hidden" name="img_path_id" id="img_path_id" value="{{$image->id}}"> 
                 <!-- <button type="button" class=" btn btn-danger remove_button" style="margin-top: 5px">Remove</button> -->
             </div>
           @else  
             <div class="balance m-b-20 field_wrapper ro"> 
                <a href="{{asset('storage/app/'.$image->image_path_file_name)}}" data-sub-html="Building Images" target="_blank" >
                 <img class="img-fluid img-thumbnail" src="{{asset('storage/app/'.$image->image_path_thumbnail)}}" alt="Image" title ="image"> </a>
                 <input type="hidden" name="img_path_id" id="img_path_id" value="{{$image->id}}"> 
                 <!-- <button type="button" class=" btn btn-danger remove_button" style="margin-top: 5px">Remove</button> -->
             </div>
             
           @endif
             
             
          @endforeach    
          </div>
      </div>
    </div>
  </div>
</div>
@endif
</div>

<!-- Modal footer -->
<div class="modal-footer">
  
</div>

</div>
</div>
<script type="text/javascript">
  var wrapper = $('.field_wrapper');
  //Once remove button is clicked
    $(wrapper).on('click', '.remove_button', function(e){
        e.preventDefault();
        /*$(this).parent('div').remove();*/ //Remove field html
        var img_path_id =$(this).siblings('input').val();
        //$(this).closest('.rows').remove();
        if(confirm('Sure want to Delete this Image')) {
             $.ajax({
              headers : {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
              url: '{{ url('serviceDestroyImage') }}' + '/' + img_path_id,
              type: "DELETE",
              data: {  "_method": 'DELETE', 'img_path_id': img_path_id }
              });
              $(this).closest('.ro').remove();
            // Remove the file preview.
           // _this.removeFile(file);
          }
        
         //Decrement field counter
    });
</script>

