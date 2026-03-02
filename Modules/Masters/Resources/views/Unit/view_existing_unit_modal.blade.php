         
<div class="modal-dialog modal-lg assign">
  <div class="modal-content">

    <!-- Modal Header -->
    <div class="modal-header">
      <h4 class="modal-title">Existing Units </h4>
      <button type="button" class="close" data-dismiss="modal">&times;</button>
    </div>

    <!-- Modal body -->
    <div class="modal-body">
      <div class="dataSearchBox ">    
        <div class="row">
          <table class="table" >
           <thead>
            <tr>
              <th>Unit Code</th>
              <th>Unit No</th>                       
              <th>Unit Type</th>                       
              <th>Vaccant</th>                       
            </tr>

          </thead>

          <tbody>
            @forelse($units as  $unit)
            <tr>
              <td>{{$unit->unit_code??''}}</td>
              <td>{{$unit->unit_no ??''}}</td>
              <td>{{$unit->unit->unit_types_name}}</td>
              <td>{{$unit->vacant_status_name}}</td>
            </tr>
            @empty 
            <tr>
              <td colspan="10" align="center">
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
