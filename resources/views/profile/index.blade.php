@extends('layouts.app')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Profile</h2>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('profile.update') }}" method="post">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-sm-6 mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', auth()->user()->name ?? '') }}">
                        @error('name')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                    <div class="col-sm-6 mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="text" disabled class="form-control" value="{{ auth()->user()->email }}">
                    </div>
                </div>
                <div class="text-end mt-3">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@endpush