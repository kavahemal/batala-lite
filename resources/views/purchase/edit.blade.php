@extends('layouts.app')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Edit Purchase</h2>
        <a class="btn btn-info" href="{{ route('purchase.index') }}"><i class="me-1" data-feather="arrow-left" style="margin-top: -2px;"></i> Back</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('purchase.update', $purchase) }}" method="post">
                @csrf
                @method('PUT')

                @include('purchase.form')

                <div class="text-end mt-3">
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>

            <table class="d-none" id="item_blank">
                <tbody>
                    @include('purchase._item', [
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