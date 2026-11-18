@if(!empty($reservation['unit_code']))
<div class="selected-unit"><span class="material-symbols-outlined" aria-hidden="true">directions_car</span><strong>{{ $reservation['unit_color'] }}</strong><span>{{ $reservation['unit_code'] }}</span>@if(($showTimes ?? true) && !empty($reservation['start_time']) && !empty($reservation['end_time']))<span>Pengambilan {{ $reservation['start_time'] }} · Pengembalian {{ $reservation['end_time'] }} WIB</span>@endif</div>
@endif
