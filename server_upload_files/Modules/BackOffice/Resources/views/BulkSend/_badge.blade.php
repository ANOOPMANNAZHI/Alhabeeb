{{-- Status badge: icon + text (never colour alone). $st = send or recipient status. --}}
@php
  $bsIcons = ['running' => 'fa-play-circle', 'paused' => 'fa-pause-circle', 'cancelled' => 'fa-ban', 'done' => 'fa-check-circle',
              'pending' => 'fa-clock-o', 'sent' => 'fa-check', 'failed' => 'fa-times', 'skipped' => 'fa-minus'];
  $bsIcon = isset($bsIcons[$st]) ? $bsIcons[$st] : 'fa-circle-o';
@endphp
<span class="bs-badge bs-badge-{{ $st }}"><i class="fa {{ $bsIcon }}" aria-hidden="true"></i> {{ ucfirst($st) }}</span>
