@extends('layouts.auth')

@section('content')
<div class="d-table-cell align-middle">

    <div class="text-center mt-4">
        <h1 class="h2">Welcome back!</h1>
        <p class="lead">
            Login to your account to continue
        </p>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="text-center">
                <img src="{!! asset('assets/img/batala.png') !!}" alt="{{ config('app.name', 'Laravel') }}" class="img-fluid rounded-circle" width="100" height="100">
            </div>
            <div class="m-sm-3">
                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input id="email" type="email" class="form-control form-control-lg @error('email') is-invalid @enderror" name="email" value="{{ old('email', config('app.admin_username')) }}" required autocomplete="email" autofocus placeholder="Enter your email">
                        @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input id="password" type="password" class="form-control form-control-lg @error('password') is-invalid @enderror" name="password" required autocomplete="current-password" placeholder="Enter your password" value="{{ config('app.admin_password') }}">
                        @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                    <div>
                        <div class="form-check align-items-center">
                            <input id="customControlInline" type="checkbox" class="form-check-input" value="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label text-small" for="customControlInline">Remember me</label>
                        </div>
                    </div>
                    <div class="d-grid gap-2 mt-3">
                        <button type="submit" class="btn btn-lg btn-primary">
                            {{ __('Login') }}
                        </button>
                    </div>
                </form>
                @if (Route::has('password.request'))
                <div class="text-center mt-3">
                    <a href="{{ route('password.request') }}">
                        {{ __('Forgot Your Password?') }}
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection