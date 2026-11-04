{{-- Dashboard stat card: $title, $icon (feather), $value, $note (HTML allowed), optional $extra (HTML), $url --}}
{{-- Column is d-flex; link + card stretch so all cards in a row get the same height --}}
<a href="{{ $url }}" class="text-decoration-none text-reset d-flex w-100">
    <div class="card flex-fill">
        <div class="card-body">
            <div class="row">
                <div class="col mt-0">
                    <h5 class="card-title">{{ $title }}</h5>
                </div>
                <div class="col-auto">
                    <div class="stat text-primary">
                        <i class="align-middle" data-feather="{{ $icon }}"></i>
                    </div>
                </div>
            </div>
            <h1 class="mt-1 mb-3">{{ $value }}</h1>
            <div class="mb-0 small">{!! $note !!}</div>
            @if(! empty($extra))
            <div class="mb-0 small">{!! $extra !!}</div>
            @endif
        </div>
    </div>
</a>
