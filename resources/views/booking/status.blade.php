@php
$statusClass = match ($booking->status) {
    \App\Enums\BookingStatus::PENDING => 'bg-secondary',
    \App\Enums\BookingStatus::DELIVERED => 'bg-success',
    \App\Enums\BookingStatus::CANCELLED => 'bg-danger',
};
@endphp
<span class="badge {{ $statusClass }}">{{ $booking->status->label() }}</span>
@if($booking->is_short_stock)
<span class="badge bg-warning text-dark" title="Booked beyond available stock">&#9888; Short stock</span>
@endif
