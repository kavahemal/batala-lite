@extends('layouts.app')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Edit Customer</h2>
        <a class="btn btn-info" href="{{ route('customers.index') }}"><i class="me-1" data-feather="arrow-left" style="margin-top: -2px;"></i> Back</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('customers.update', $customer) }}" method="post">
                @csrf
                @method('PUT')

                @include('customers.form')

                <div class="text-end mt-3">
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
</script>
@endpush