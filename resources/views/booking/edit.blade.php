@extends('layouts.app')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Edit Booking <small class="text-muted ms-2">{{ $booking->booking_no }}</small></h2>
        <a class="btn btn-info" href="{{ route('booking.index') }}"><i class="me-1" data-feather="arrow-left" style="margin-top: -2px;"></i> Back</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('booking.update', $booking) }}" method="post">
                @csrf
                @method('PUT')

                @include('booking.form')

                <div class="text-end mt-3">
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>

            <table class="d-none" id="item_blank">
                <tbody>
                    @include('booking._item', [
                        'index' => null,
                        'oldId' => null,
                        'oldBrandId' => null,
                        'oldCylinderTypeId' => null,
                        'oldQuantity' => null
                    ])
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection