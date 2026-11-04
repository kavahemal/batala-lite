@extends('layouts.app')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Cylinder brands</h2>
        @can('cylinder-brands.create')
        <a href="{{ route('cylinder-brands.create') }}" class="btn btn-info">Add Cylinder brand</a>
        @endcan
    </div>

    <div class="card">
        <div class="card-body">
            {{ $dataTable->table() }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
{!! $dataTable->scripts() !!}
@endpush