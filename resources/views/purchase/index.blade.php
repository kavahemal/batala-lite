@extends('layouts.app')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Purchases</h2>
        @can('purchase.create')
        <a href="{{ route('purchase.create') }}" class="btn btn-info">Add Purchase</a>
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