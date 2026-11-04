@extends('layouts.app')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Booking - <b>{{ $booking->booking_no }}</b> <small>({{ $booking->customer->name }})</small></h2>
        <div class="d-flex gap-2">
            @can('markDelivered', $booking)
            <form method="POST" action="{{ route('booking.delivered', $booking) }}"
                data-confirm="Mark {{ $booking->booking_no }} delivered? Its cylinders leave the godown and the booking can't be edited any more."
                data-confirm-button="Yes, delivered" data-confirm-color="#198754">
                @csrf
                <button type="submit" class="btn btn-success"><i class="me-1" data-feather="check-circle" style="margin-top: -2px;"></i> Mark delivered</button>
            </form>
            @endcan
            <a class="btn btn-info" href="{{ route('booking.index') }}"><i class="me-1" data-feather="arrow-left" style="margin-top: -2px;"></i> Back</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-sm-4 mb-3">
                    <label>Status</label>
                    <div class="fw-bold">@include('booking.status')</div>
                </div>

                <div class="col-sm-4 mb-3">
                    <label>Booking date</label>
                    <div class="fw-bold">{{ dateTimeFormat($booking->booking_date) }}</div>
                </div>

                <div class="col-sm-4 mb-3">
                    <label>Scheduled date</label>
                    <div class="fw-bold">{{ dateTimeFormat($booking->scheduled_date) }}</div>
                </div>

                <div class="col-sm-12 mb-3">
                    <label>Remarks</label>
                    <div class="fw-bold">{!! nl2br(e($booking->remarks ?? '')) !!}</div>
                </div>

                <div class="col-sm-12">
                    <h3>Items</h3>
                    <table class="table table-bordered">
                        <thead class="table-info">
                            <tr>
                                <th width="80"></th>
                                <th>Brand</th>
                                <th>Type</th>
                                <th>Quantity</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($booking->items as $index => $item)
                            <tr>
                                <td>{{ $index+1 }}</td>
                                <td>{{ $item->brand?->name }}</td>
                                <td>{{ $item->cylinderType?->weight }} kg</td>
                                <td>{{ $item->quantity }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-info">
                            <tr>
                                <th colspan="3" class="text-end">Total:</th>
                                <th>{{ $booking->items->sum('quantity') }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection