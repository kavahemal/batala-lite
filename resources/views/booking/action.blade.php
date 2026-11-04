<div class="d-flex justify-content-center gap-2">
    @can('booking.index')
    <a href="{{ route('booking.show', $id) }}" class="btn btn-primary">View</a>
    @endcan
    {{-- Delivered bookings are locked --}}
    @unless($booking->isLocked())
    @can('booking.edit')
    <a href="{{ route('booking.edit', $id) }}" class="btn btn-info">Edit</a>
    @endcan
    @can('booking.destroy')
    <button type="button" class="btn btn-danger btn-delete" data-url="{{ route('booking.destroy', $id) }}" data-dt-reload="true">Delete</button>
    @endcan
    @endunless
</div>
