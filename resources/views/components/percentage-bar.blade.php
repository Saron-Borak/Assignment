@props([
    'percentage',
    'threshold' => null,
    'showValue' => true,
])

@php
    $threshold ??= config('attendance.min_percentage');
    $value = round((float) $percentage, 1);
    $variant = $value >= $threshold ? 'success' : ($value >= $threshold - 10 ? 'warning' : 'danger');
@endphp

<div {{ $attributes->merge(['class' => 'd-flex align-items-center gap-2']) }}>
    <div class="attendance-bar flex-grow-1" style="min-width:70px"
         role="progressbar" aria-valuenow="{{ $value }}" aria-valuemin="0" aria-valuemax="100"
         aria-label="Attendance {{ $value }} percent">
        <span class="bg-{{ $variant }}" style="width: {{ min(100, max(0, $value)) }}%"></span>
    </div>
    @if ($showValue)
        <span class="small fw-semibold text-{{ $variant }}" style="min-width:48px">{{ number_format($value, 1) }}%</span>
    @endif
</div>
