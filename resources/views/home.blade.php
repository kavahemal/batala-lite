@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid p-0">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0"><strong>Dashboard</strong></h1>
        <span class="text-muted">{{ dateTimeFormat(now(), 'date_time') }}</span>
    </div>

    {{-- 1. Key numbers --}}
    <div class="row">
        @isset($bookings)
        <div class="col-sm-6 col-xl-3 d-flex">
            @include('home._stat', [
                'title' => "Today's bookings",
                'icon' => 'book',
                'value' => $bookings->today,
                'note' => $bookings->todayCylinders . ' ' . Str::plural('cylinder', $bookings->todayCylinders) . ' booked today',
                'url' => route('booking.index'),
            ])
        </div>
        <div class="col-sm-6 col-xl-3 d-flex">
            @include('home._stat', [
                'title' => 'Open bookings',
                'icon' => 'clock',
                'value' => $bookings->open,
                'note' => $bookings->overdue
                    ? '<span class="text-danger fw-bold">' . $bookings->overdue . ' overdue</span>'
                    : '<span class="text-success">none overdue</span>',
                'extra' => $bookings->short ? '<span class="text-warning fw-bold">&#9888; ' . $bookings->short . ' short of stock</span>' : null,
                'url' => route('booking.index'),
            ])
        </div>
        @endisset
    </div>

    <div class="row">
        {{-- 2. Low stock --}}
        @isset($lowStock)
        <div class="col-12 d-flex">
            <div class="card flex-fill">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Low stock <small class="text-muted">(&le; {{ $lowStockLimit }} available to book)</small></h5>
                    <a href="{{ route('stock-report.index') }}" class="small">Stock report &rarr;</a>
                </div>
                <div class="card-body pt-0">
                    @if($lowStock->isEmpty())
                    <p class="text-success mb-0">&#10003; All sizes have more than {{ $lowStockLimit }} available.</p>
                    @else
                    <table class="table table-sm mb-0">
                        <tbody>
                            @foreach($lowStock as $typeId => $qty)
                            <tr>
                                <td>{{ $typeLabels[$typeId] ?? '' }}</td>
                                <td class="text-end fw-bold {{ $qty < 0 ? 'text-danger' : 'text-warning' }}">
                                    {{ $qty }}
                                    @if($qty < 0)
                                    <span class="badge bg-danger ms-1">short {{ abs($qty) }}</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @endif

                </div>
            </div>
        </div>
        @endisset
    </div>

    {{-- 3. Last 7 days --}}
    @isset($lastDays)
    @php($max = max(1, collect($lastDays)->max(fn ($day) => max($day->purchased, $day->delivered))))
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Last 7 days</h5>
        </div>
        <div class="card-body pt-0">
            <table class="table table-sm mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Day</th>
                        <th class="text-end">Bookings</th>
                        <th class="text-end">Cylinders purchased</th>
                        <th class="text-end">Cylinders delivered</th>
                        <th width="30%"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lastDays as $day)
                    <tr class="{{ $day->date->isToday() ? 'fw-bold' : '' }}">
                        <td>{{ $day->date->isToday() ? 'Today' : dateTimeFormat($day->date, 'D, d M') }}</td>
                        <td class="text-end">{{ $day->bookings ?: '' }}</td>
                        <td class="text-end">{{ $day->purchased ?: '' }}</td>
                        <td class="text-end">{{ $day->delivered ?: '' }}</td>
                        <td>
                            {{-- Purchased (in) vs delivered (out), scaled to the busiest day --}}
                            <div class="progress mb-1" style="height: 6px;" title="Purchased: {{ $day->purchased }}">
                                <div class="progress-bar bg-success" style="width: {{ round($day->purchased / $max * 100) }}%"></div>
                            </div>
                            <div class="progress" style="height: 6px;" title="Delivered: {{ $day->delivered }}">
                                <div class="progress-bar bg-primary" style="width: {{ round($day->delivered / $max * 100) }}%"></div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="small text-muted mt-2">
                <span class="badge bg-success">&nbsp;</span> purchased &nbsp; <span class="badge bg-primary">&nbsp;</span> delivered
            </div>
        </div>
    </div>
    @endisset

    @if(! isset($bookings) && ! isset($lowStock))
    <div class="card">
        <div class="card-body text-muted">Welcome, {{ auth()->user()->name }}. Use the menu to get started.</div>
    </div>
    @endif
</div>
@endsection
