        <!-- The Modal -->
<div class="modal-dialog assign modal-lg">
    <div class="modal-content">
  
    <!-- Modal Header -->
    <div class="modal-header">
      <h4 class="modal-title">Sales Activity View</h4>
      <button type="button" class="close" data-dismiss="modal">&times;</button>
    </div>
    
    <!-- Modal body -->
    <div class="modal-body">
    
   <div class="row">
    <div class="col-sm-12">
        <div class="card-box">
            <!-- <div class="card-head">
              <header>View Building Type</header>
            </div> -->
            <form action="#" id="form_sample_2" class="form-horizontal">
            <div class="card-body row"> 
                <div class="col-lg-6 p-t-20"> 
                  <div class = "txt-full-width">
                     <h5 class="details"><b> Name :  </b><span>{{$salesActivity->sales_activities_name}}</span></h5>
                  </div>
                </div>                                                     
                
                <div class="col-lg-6 p-t-20"> 
                  <div class = "txt-full-width">
                     <h5 class="details"><b>Activity Type :  </b><span>{{$salesActivity->sales_activity_type}}</span></h5>
                  </div>
                </div>
                 
                <div class="col-lg-6 p-t-20"> 
                  <div class = "txt-full-width">
                     <h5 class="details"><b>Due Date :  </b><span>{{$salesActivity->sales_activities_due_date->format('d/m/Y')}}</span></h5>
                  </div>
                </div>
                <div class="col-lg-6 p-t-20"> 
                  <div class = "txt-full-width">
                     <h5 class="details"><b>Time:  </b><span>@if($salesActivity->sales_activities_time) {{\Carbon\Carbon::parse($salesActivity->sales_activities_time)->format('g:i A')}} @endif</span></h5>
                  </div>
                </div>   
                <div class="col-lg-6 p-t-20"> 
                  <div class = "txt-full-width">
                     <h5 class="details"><b>Status :  </b><span>{{$salesActivity->sales_activities_status}}</span></h5>
                  </div>
                </div> 
               <div class="col-lg-12 p-t-20"> 
                  <div class = "txt-full-width">
                     <h5 class="details"><b>Note :  </b><span>{{$salesActivity->sales_activities_note}}</span></h5>
                  </div>
                </div>             
               <div class="col-lg-12 p-t-20"> 
                  <div class = "txt-full-width">
                     <h5 class="details"><b>Description:  </b><span>{{$salesActivity->sales_activities_summary_note}}</span></h5>
                  </div>
                </div> 
            </div>
            </form>
        </div>
    </div>
</div> 

    </div>
    
    <!-- Modal footer -->
    <div class="modal-footer">
      <!-- <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button> -->
    </div>

    </div>
</div>
@section('scripts')
<script>
  $(document).ready(function() {
    $("#form_sample_2").validate()
  });
</script>
@endsection
