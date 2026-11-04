@extends('layouts.app')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Cylinder types</h2>
        @can('cylinder-types.create')
        <a href="{{ route('cylinder-types.create') }}" class="btn btn-info">Add Cylinder type</a>
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