@extends('layouts.auth')

@section('title', 'Login')

@section('content')
<!-- Login -->
<div class="card">
    <div class="card-body">
        <!-- Logo -->
        <div class="app-brand justify-content-center">
            <a href="{{ route('dashboard') }}" class="app-brand-link gap-2">
                <div class="app-brand-logo demo" style="background-color: #074136; padding: 12px 16px; border-radius: 4px; display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
                    <div style="text-align: center; color: white;">
                        <div style="font-family: 'Georgia', 'Times New Roman', serif; font-size: 24px; font-weight: bold; letter-spacing: 1px; line-height: 1.2;">GASPARD</div>
                        <div style="font-family: 'Arial', sans-serif; font-size: 11px; font-weight: normal; letter-spacing: 2px; margin-top: 3px; opacity: 0.95;">SIGNATURE</div>
                    </div>
                </div>
            </a>
        </div>
        <!-- /Logo -->
        <h4 class="mb-2">Welcome to {{ config('app.name', 'Sneat') }}! 👋</h4>
        <p class="mb-4">Please sign-in to your account and start the adventure</p>
        
        <form id="formAuthentication" class="mb-3" action="{{ route('login') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="email" class="form-label">Email or Username</label>
                <input type="text" class="form-control @error('email') is-invalid @enderror" id="email" name="email" placeholder="Enter your email or username" autofocus value="{{ old('email') }}" />
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3 form-password-toggle">
                <div class="d-flex justify-content-between">
                    <label class="form-label" for="password">Password</label>
                </div>
                <div class="input-group input-group-merge">
                    <input type="password" id="password" class="form-control @error('password') is-invalid @enderror" name="password" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" aria-describedby="password" />
                    <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="remember-me" name="remember" />
                    <label class="form-check-label" for="remember-me"> Remember Me </label>
                </div>
            </div>
            <div class="mb-3">
                <button class="btn btn-primary d-grid w-100" type="submit">Sign in</button>
            </div>
        </form>
        
    </div>
</div>
<!-- /Login -->
@endsection

