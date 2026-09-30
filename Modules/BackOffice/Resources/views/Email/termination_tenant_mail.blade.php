@component('mail::message')

Hi {{$tenantName}},
</br></br><br>
This letter is your official notice that your Inspection Details.
So,We have attached Detailed View from this mail.
</br></br>


</br>
@if(count($groupedWork)> 0)

<div class="row">
  <div class="col-sm-12">
    <div class="card card-box salesLeadBox">
       @foreach($groupedWork as $checklist)
      <div class="card-head">
        <div class="col"><h4></h4></div>
      </div>
      <div class="card-body">
       <div class="col">
        <div class="row">

          <div class="col leadInformation">
           <div class="table-responsive1">
            <table class="table" border="1">
              <thead>
                <tr>{{$checklist->first()->work->works_code}}</tr>
                <tr style="background: #f5f5f5;">
                  <th>Item</th>
                  <th>Amount</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($checklist as $subWork)
                <tr>
                    <td>{{$subWork->subWorks->sub_work}}</td>
                    <td>{{$subWork->termination_amount}}</td>
                </tr>
                @empty 
                <tr>
                   <td colspan="3" align="center">
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
  @endforeach
</div>
</div>
</div>

@endif
<div class="row">
  <div class="col-sm-12">
    <div class="card card-box salesLeadBox">
      <div class="card-head">
        <div class="col"><h4></h4></div>
      </div>
      <div class="card-body">
       <div class="col">
        <div class="row">

          <div class="col leadInformation">
           <div class="table-responsive1">
            <table class="table" border="1">
              <thead>
                <tr>Others</tr>
                <tr style="background: #f5f5f5;">
                  <th>Item</th>
                  <th>Amount</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($terminationChecklistOther as $other)
                <tr>
                    <td>{{$other->termination_other_work}}</td>
                    <td>{{$other->termination_amount}}</td>
                </tr>
                @empty 
                <tr>
                   <td colspan="3" align="center">
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
</div>
</div>
 
  
  <div class="card card-box salesSearchBox " id="agdiv">
    <div class="dataSearchBox">
        <div class="card-body row">
           
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Total Amount :  </b><span>{{$termination->termination_total_amount}}</span></h5>
                   
                </div>
            </div>
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Total of Electricity and Water  :  </b><span>{{$termination->termination_total_elec_water_amount}}</span></h5>
                </div>
            </div>
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Discount on Total Maintenance Due:  </b><span>{{$termination->termination_discount_maintenance_due}}</span></h5>
                </div>
            </div>
            <div class="col-lg-6 p-t-20"> 
              <div class = "txt-full-width">
                  <h5 class="details"><b>Net Amount:  </b><span>{{$termination->termination_net_amount}}</span></h5>
                 
              </div>
          </div>
        </div>
    </div>
  </div>


</br></br>




Sincerely,</br>
Admin

@endcomponent
