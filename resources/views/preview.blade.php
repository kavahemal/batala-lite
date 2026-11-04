@extends('layouts.app')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">
            <i class="align-middle me-1" data-feather="{{ $item['icon'] ?? 'lock' }}"></i>
            {{ $item['title'] }}
            <span class="badge bg-secondary align-middle ms-1" style="font-size: .55em;"><i data-feather="lock" style="width: 12px; height: 12px; margin-top: -2px;"></i> Full version</span>
        </h2>
        <a class="btn btn-info" href="{{ url()->previous() === url()->current() ? route('home') : url()->previous() }}"><i class="me-1" data-feather="arrow-left" style="margin-top: -2px;"></i> Back</a>
    </div>

    <div class="alert alert-info d-flex align-items-center gap-2 p-3" role="alert">
        <i data-feather="info"></i>
        <div>This module is part of the full version, which is private. Live demo available on request.</div>
    </div>

    <div class="card">
        <div class="card-body">
            <p class="lead mb-3">{{ $item['preview']['summary'] ?? '' }}</p>

            @if(! empty($item['preview']['points']))
            <ul class="mb-0">
                @foreach($item['preview']['points'] as $point)
                <li class="mb-1">{{ $point }}</li>
                @endforeach
            </ul>
            @endif
        </div>
    </div>

    <h3 class="mt-4 mb-3">Screenshots <small class="text-muted fs-6">from the full version, with fake data</small></h3>
    @if($images->isEmpty())
    <div class="card">
        <div class="card-body text-muted">No screenshots here yet - see it in the live demo.</div>
    </div>
    @else
    <div class="row">
        @foreach($images as $image)
        <div class="col-md-6 mb-4 d-flex">
            <div class="card flex-fill mb-0">
                <a href="{{ asset('images/preview/' . $image) }}" target="_blank" rel="noopener" title="Open full size">
                    <img src="{{ asset('images/preview/' . $image) }}" class="card-img-top border-bottom" alt="{{ $item['title'] }} - {{ Str::headline(pathinfo($image, PATHINFO_FILENAME)) }}" loading="lazy">
                </a>
                <div class="card-body py-2 small text-muted">{{ Str::headline(pathinfo($image, PATHINFO_FILENAME)) }} <span class="float-end">click to enlarge</span></div>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection
