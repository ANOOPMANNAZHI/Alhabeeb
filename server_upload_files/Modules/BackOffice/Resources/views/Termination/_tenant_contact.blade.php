{{-- Tenant mobile + email (bold) under the tenant name: takeover view and handover inspection.
     $stacked = true puts the email on its own line below the mobile (handover inspection). --}}
@php
  $stacked = !empty($stacked);
  $emails = array_values(array_unique(array_filter([trim((string) $tenant->tenant_contact_email), trim((string) $tenant->tenant_personal_email)])));
@endphp
@if($tenant->tenant_contact_no)
  Mobile <b style="color:#111827">{{ $tenant->tenant_contact_no }}</b>
@else
  No mobile on file
@endif
@if($stacked)<br>@else &middot; @endif
@if(count($emails))
  Email @foreach($emails as $i => $email)@if($i) / @endif<b><a href="mailto:{{ $email }}" style="color:#111827">{{ $email }}</a></b>@endforeach
@else
  No email on file
@endif
@if(!empty($tenant->tenant_status_name))
  &middot; {{ $tenant->tenant_status_name }}
@endif
