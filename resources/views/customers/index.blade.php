@extends('layouts.app')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Customers</h2>
        @can('customers.create')
        <div class="d-flex gap-2">
            <a href="{{ route('preview', 'customer-import') }}" class="btn btn-outline-info" title="Part of the full version"><i class="me-1" data-feather="upload" style="margin-top: -2px;"></i> Import <i data-feather="lock" class="ms-1" style="width: 14px; height: 14px; margin-top: -2px; opacity: .7;"></i></a>
            <a href="{{ route('customers.create') }}" class="btn btn-info">Add Customer</a>
        </div>
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