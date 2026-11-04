@extends('layouts.app')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Bookings</h2>
        @can('booking.create')
        <a href="{{ route('booking.create') }}" class="btn btn-info">Add Booking</a>
        @endcan
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row g-2 align-items-center mb-3">
                <div class="col-sm-3">
                    <select id="filter-status" class="form-select datatable-filter">
                        <option value="">All statuses</option>
                        @foreach($statuses as $status)
                        <option value="{{ $status->value }}">{{ $status->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-3">
                    <div class="form-check">
                        <input class="form-check-input datatable-filter" type="checkbox" id="filter-short-stock">
                        <label class="form-check-label" for="filter-short-stock">Short stock only</label>
                    </div>
                </div>
            </div>

            {{ $dataTable->table() }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
{!! $dataTable->scripts() !!}
<script>
    $('.datatable-filter').on('change', function() {
        $('#booking-table').DataTable().ajax.reload();
    });
</script>
@endpush
