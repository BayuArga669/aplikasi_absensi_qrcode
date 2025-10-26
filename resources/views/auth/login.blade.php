@extends('layouts.app')

@section('content')
<style>
    .login-container {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    .login-card {
        border-radius: 15px;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        border: none;
    }
    .login-card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        text-align: center;
        padding: 2rem 0;
        border: none;
    }
    .login-card-header h2 {
        margin: 0;
        font-weight: 600;
    }
    .login-card-header i {
        font-size: 2.5rem;
        margin-bottom: 1rem;
        display: block;
    }
    .login-card-body {
        padding: 2rem;
    }
    .form-control {
        border-radius: 10px;
        border: 2px solid #e9ecef;
        padding: 12px 15px;
        transition: all 0.3s;
    }
    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }
    .btn-login {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 10px;
        padding: 12px;
        font-weight: 600;
        transition: transform 0.2s;
    }
    .btn-login:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }
    .login-footer {
        text-align: center;
        margin-top: 1.5rem;
        padding-top: 1.5rem;
        border-top: 1px solid #eee;
    }
    .login-footer a {
        color: #667eea;
        text-decoration: none;
        font-weight: 500;
    }
    .login-footer a:hover {
        text-decoration: underline;
    }
    .company-name {
        font-size: 1.2rem;
        font-weight: 600;
        color: #667eea;
        margin-bottom: 0.5rem;
        display: block;
    }
    .system-name {
        font-size: 0.9rem;
        color: #6c757d;
        margin-bottom: 1.5rem;
    }
</style>

<div class="login-container">
    <div class="col-md-6 col-lg-5">
        <div class="card login-card">
            <div class="login-card-header">
                <i class="fas fa-qrcode"></i>
                <h2>QR Attendance System</h2>
                <span class="system-name">Digital Check-in Solution</span>
            </div>
            
            <div class="login-card-body">
                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="mb-4">
                        <label for="email" class="form-label">Email Address</label>
                        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="Enter your email">
                        
                        @error('email')
                            <div class="invalid-feedback">
                                <strong>{{ $message }}</strong>
                            </div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label">Password</label>
                        <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password" placeholder="Enter your password">
                        
                        @error('password')
                            <div class="invalid-feedback">
                                <strong>{{ $message }}</strong>
                            </div>
                        @enderror
                    </div>

                    <div class="mb-4 form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                        <label class="form-check-label" for="remember">
                            Remember me
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary btn-login w-100">
                        <i class="fas fa-sign-in-alt me-2"></i>Login to Dashboard
                    </button>

                    <div class="login-footer">
                        <p class="mb-2">Don't have an account? <a href="{{ route('register') }}">Register here</a></p>
                        @if (Route::has('password.request'))
                            <p class="mb-0"><a href="{{ route('password.request') }}">Forgot your password?</a></p>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection