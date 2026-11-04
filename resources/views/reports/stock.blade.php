@extends('layouts.app')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Stock</h2>
        <span class="text-muted">As of {{ dateTimeFormat(now(), 'date_time') }}</span>
    </div>

    <div class="card">
        <div class="card-body">
            <p class="text-muted mb-3">
                <strong>Full in godown</strong> = purchased − delivered.
                <strong>Reserved</strong> = cylinders on pending bookings.
                <strong>Available to book</strong> = full − reserved; red means overbooked.
            </p>

            <table class="table table-bordered table-hover align-middle">
                <thead class="table-info">
                    <tr>
                        <th>Brand</th>
                        <th>Size</th>
                        <th class="text-end">Full in godown</th>
                        <th class="text-end">Reserved</th>
                        <th class="text-end">Available to book</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                    <tr>
                        <td>{{ $row->brand }}</td>
                        <td>{{ $row->weight }} kg</td>
                        <td class="text-end {{ $row->full < 0 ? 'text-danger fw-bold' : '' }}" @if($row->full < 0) title="More delivered than purchased - check that all purchases are entered" @endif>
                            {{ $row->full }}
                            @if($row->full < 0)
                            <span class="badge bg-danger ms-1">check purchases</span>
                            @endif
                        </td>
                        <td class="text-end">{{ $row->reserved ?: '' }}</td>
                        <td class="text-end fw-bold {{ $row->available < 0 ? 'text-danger' : ($row->available > 0 ? 'text-success' : 'text-muted') }}">
                            {{ $row->available }}
                            @if($row->available < 0)
                            <span class="badge bg-danger ms-1">short {{ abs($row->available) }}</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">No cylinder types yet.</td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot class="table-info">
                    <tr>
                        <th colspan="2" class="text-end">Total:</th>
                        <th class="text-end">{{ $rows->sum('full') }}</th>
                        <th class="text-end">{{ $rows->sum('reserved') }}</th>
                        <th class="text-end">{{ $rows->sum('available') }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
