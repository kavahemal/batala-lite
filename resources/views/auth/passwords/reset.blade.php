@extends('layouts.auth')

@section('content')
<div class="d-table-cell align-middle">

    <div class="text-center mt-4 mb-3">
        <h1 class="h2">Reset Password</h1>
    </div>

    <div class="card">
        <div class="card-body">
            @if (session('status'))
            <div class="alert alert-success" role="alert">
                {{ session('status') }}
            </div>
            @endif

            <div class="m-sm-3">
                <form method="POST" action="{{ route('password.update') }}">
                    @csrf

                    <input type="hidden" name="token" value="{{ $token }}">
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input id="email" type="email" class="form-control form-control-lg @error('email') is-invalid @enderror" name="email" value="{{ $email ?? old('email') }}" required autocomplete="email" autofocus placeholder="Enter your email">
                        @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input id="password" type="password" class="form-control form-control-lg @error('password') is-invalid @enderror" name="password" required autocomplete="new-password" placeholder="Enter your password">
                        @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirm Password</label>
                        <input id="password-confirm" type="password" class="form-control form-control-lg @error('password') is-invalid @enderror" name="password_confirmation" required autocomplete="new-password" placeholder="Enter your password">
                    </div>
                    <div class="d-grid gap-2 mt-3">
                        <button type="submit" class="btn btn-lg btn-primary">
                            {{ __('Reset Password') }}
                        </button>
                    </div>
                </form>
                @if (Route::has('login'))
                <div class="text-center mt-3">
                    <a href="{{ route('login') }}">
                        {{ __('Back to Login') }}
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection