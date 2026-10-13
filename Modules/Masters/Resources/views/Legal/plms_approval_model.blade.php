@php
    $LC = \Modules\Masters\Services\LegalCase::class;
    $plmApprove = $process_flow == 801 && $action_key == 'APRV';
    $lawyerReject = $process_flow == 801 && $action_key == 'RFRBK' && $are_status == 1;
    $title = $plmApprove ? 'PLM Approval' : ($lawyerReject ? 'Reject Case' : ($action_key == 'RFRBK' ? 'Refer Back' : 'Approve'));
    $noteLabel = $lawyerReject ? 'Reason for rejecting (sent to PLM)' : ($action_key == 'RFRBK' ? 'Reason for referring back' : 'Note');
    $handled = (int) ($legal->handled_by ?: $defaultHandledBy);
@endphp
<div class="modal-dialog assign" @if($plmApprove) style="max-width:640px;" @endif>
    <div class="modal-content">

    <div class="modal-header">
      <h4 class="modal-title">{{ $title }}</h4>
      <button type="button" class="close" data-dismiss="modal">&times;</button>
    </div>

    <div class="modal-body">
   <div class="row">
    <div class="col">
        <div class="card card-box salesSearchBox">
        <form action="{{route('plmsApproveReferBackStore')}}" autocomplete="off" method="POST" id="renewal_note_modal" class="form-horizontal" enctype="multipart/form-data" data-toggle="validator" onsubmit="return checkBeforeSubmit()">
        {{csrf_field()}}
        <input  type="hidden" class="form-control" name="legal_id" value="{{$legal_id}}">
        <input  type="hidden" class="form-control" name="process_flow" value="{{$process_flow}}">
        <input  type="hidden" class="form-control" name="action_key" value="{{$action_key}}">
        <input  type="hidden" class="form-control" name="are_status" value="{{$are_status}}">
        <div class="dataSearchBox ">
                <div class="row">
                  @if($plmApprove)
                  <div class="col-sm-12">
                    <p class="text-muted" style="margin-bottom:10px;">Rent outstanding today: <b>{{ numberFormat($outstanding) }}</b></p>
                  </div>
                  <div class="col-sm-6">
                    <div class="form-group">
                      <label for="lc_handled_by">Handled by</label>
                      <select class="form-control" id="lc_handled_by" name="handled_by">
                        @foreach($LC::HANDLED_BY as $k => $v)
                          <option value="{{ $k }}" @if($k === $handled) selected @endif>{{ $v }}@if($k === (int) $defaultHandledBy) (building default)@endif</option>
                        @endforeach
                      </select>
                    </div>
                  </div>
                  <div class="col-sm-6 lc-alhabib" @if($handled !== 1) style="display:none" @endif>
                    <div class="form-group">
                      <label for="lc_case_type">Case type</label>
                      <select class="form-control" id="lc_case_type" name="case_type" required>
                        @foreach($LC::TYPES as $k => $v)
                          <option value="{{ $k }}" @if($k === (int) $legal->case_type) selected @endif>{{ $v }}</option>
                        @endforeach
                      </select>
                    </div>
                  </div>
                  <div class="col-sm-6 lc-alhabib" @if($handled !== 1) style="display:none" @endif>
                    <div class="form-group">
                      <label for="lc_priority">Priority</label>
                      <select class="form-control" id="lc_priority" name="priority" required>
                        <option value="">Select priority</option>
                        @foreach($LC::PRIORITIES as $k => $v)
                          <option value="{{ $k }}" @if($k === (int) $legal->priority) selected @endif>{{ $v }}</option>
                        @endforeach
                      </select>
                    </div>
                  </div>
                  <div class="col-sm-6 lc-alhabib" @if($handled !== 1) style="display:none" @endif>
                    <div class="form-group">
                      <label for="lc_lawyer">Lawyer</label>
                      <select class="form-control" id="lc_lawyer" name="lawyer_user_id" required>
                        <option value="">Select lawyer</option>
                        @foreach($lawyers as $id => $name)
                          <option value="{{ $id }}" @if((int) $id === (int) $legal->lawyer_user_id || count($lawyers) === 1) selected @endif>{{ $name }}</option>
                        @endforeach
                      </select>
                      @if(!count($lawyers))<small class="text-danger">No active user has the legal_advisor role.</small>@endif
                    </div>
                  </div>
                  <div class="col-sm-12 lc-handover" @if($handled === 1) style="display:none" @endif>
                    <p class="text-muted" style="margin-bottom:8px;">The case is handed over and closed on our side. The contract stays in legal.</p>
                  </div>
                  <div class="col-sm-6 lc-handover" @if($handled === 1) style="display:none" @endif>
                    <div class="form-group">
                      <label for="lc_handed_to">Handed to (office / lawyer name)</label>
                      <input type="text" class="form-control" id="lc_handed_to" name="handed_to_name" maxlength="150" value="{{ $legal->handed_to_name }}">
                    </div>
                  </div>
                  <div class="col-sm-3 lc-handover" @if($handled === 1) style="display:none" @endif>
                    <div class="form-group">
                      <label for="lc_handover_ref">Reference / letter no.</label>
                      <input type="text" class="form-control" id="lc_handover_ref" name="handover_ref" maxlength="100" value="{{ $legal->handover_ref }}">
                    </div>
                  </div>
                  <div class="col-sm-3 lc-handover" @if($handled === 1) style="display:none" @endif>
                    <div class="form-group">
                      <label for="lc_handover_date">Handover date</label>
                      <input type="date" class="form-control" id="lc_handover_date" name="handover_date" max="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}">
                    </div>
                  </div>
                  @endif
                  <div class="col-sm-12">
                    <div class="form-group">
                        <label for="legal_note">{{ $noteLabel }}</label>
                        <div class="p-relative">
                          <i class="fa fa-file-text-o icn-add" aria-hidden="true"></i>
                          <textarea class="form-control"  id="legal_note"  placeholder="{{ $noteLabel }}" name="legal_note" data-rule-maxlength="1000" data-msg-maxlength="Only allows 1000 Characters" maxlength="1000" required></textarea>
                        </div>
                    </div>
                  </div>

               <div class="w-100"></div>
                <div class="col">
                   <div class="w-100"></div>
                      <button type="submit" name="submit" value="submit" id="lc_submit" class="btn {{ $lawyerReject ? 'btn-warning' : 'btn-primary' }}">{{ $lawyerReject ? 'Reject' : 'Save' }}</button>
                   </div>
              </div>
        </div>
        <div class="clearfix"></div>
        </form>
        </div>
    </div>
</div>
    </div>

    <div class="modal-footer"></div>
    </div>
</div>
<script type="text/javascript">
  var wasSubmitted = false;
    function checkBeforeSubmit(){
      if(!wasSubmitted) {
        wasSubmitted = true;
        return wasSubmitted;
      }
      return false;
    }
  @if($plmApprove)
  (function () {
    function toggleHandledBy() {
      var alHabib = $('#lc_handled_by').val() === '1';
      $('.lc-alhabib').toggle(alHabib).find('select').prop('required', alHabib);
      $('.lc-handover').toggle(!alHabib);
      $('#lc_handed_to, #lc_handover_date').prop('required', !alHabib);
      $('#lc_submit').text(alHabib ? 'Approve' : 'Hand over & close');
    }
    $('#lc_handled_by').on('change', toggleHandledBy);
    toggleHandledBy();
  })();
  @endif
</script>
