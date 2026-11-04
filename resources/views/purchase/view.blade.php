@extends('layouts.app')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Purchase - <b>{{ $purchase->purchase_no }}</b></h2>
        <a class="btn btn-info" href="{{ route('purchase.index') }}"><i class="me-1" data-feather="arrow-left" style="margin-top: -2px;"></i> Back</a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-sm-4 mb-3">
                    <label>Purchase date</label>
                    <div class="fw-bold">{{ dateTimeFormat($purchase->purchase_date) }}</div>
                </div>

                <div class="col-sm-4 mb-3">
                    <label>Truck no</label>
                    <div class="fw-bold">{{ $purchase->truck->label() }}</div>
                </div>

                <div class="col-sm-4 mb-3">
                    <label>Driver name</label>
                    <div class="fw-bold">{{ $purchase->driver->name }}@if($purchase->driver->mobile)<div class="small text-muted">{{ $purchase->driver->mobile }}</div>@endif</div>
                </div>

                @if($purchase->remarks)
                <div class="col-sm-12 mb-3">
                    <label>Remarks</label>
                    <div class="fw-bold">{!! nl2br(e($purchase->remarks ?? '')) !!}</div>
                </div>
                @endif

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
                            @foreach($purchase->items as $index => $item)
                            <tr>
                                <td class="text-center">{{ $index+1 }}</td>
                                <td>{{ $item->brand?->name }}</td>
                                <td>{{ $item->cylinderType?->weight }} kg</td>
                                <td>{{ $item->quantity }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-info">
                            <tr>
                                <th colspan="3" class="text-end">Total:</th>
                                <th>{{ $purchase->items->sum('quantity') }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection