@extends('layouts.app')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Add Cylinder type</h2>
        <a class="btn btn-info" href="{{ route('cylinder-types.index') }}"><i class="me-1" data-feather="arrow-left" style="margin-top: -2px;"></i> Back</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('cylinder-types.store') }}" method="post">
                @csrf

                @include('cylinder-types.form')

                <div class="text-end mt-3">
                    <button type="submit" class="btn btn-primary">Save</button>
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