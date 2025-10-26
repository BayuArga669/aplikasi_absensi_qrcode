@extends('layouts.app')

@section('content')
<style>
    /* Reset semua padding dan margin */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    
    body, html {
        margin: 0 !important;
        padding: 0 !important;
        overflow-x: hidden;
        height: 100%;
        width: 100%;
    }
    
    /* Hapus padding dari container-fluid di layouts.app */
    .main-content {
        padding: 0 !important;
        margin: 0 !important;
    }
    
    #page-content-wrapper {
        margin: 0 !important;
        padding: 0 !important;
    }
    
    .container-fluid {
        padding: 0 !important;
        margin: 0 !important;
    }
    
    .login-container {
        min-height: 100vh;
        height: 100vh;
        width: 100vw;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 20px;
        margin: 0 !important;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
    }
    
    .login-card {
        border-radius: 15px;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
        overflow: hidden;
        border: none;
        max-width: 500px;
        width: 100%;
        background: white;
    }
    
    .login-card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        text-align: center;
        padding: 2.5rem 1rem;
        border: none;
    }
    
    .login-card-header h2 {
        margin: 0;
        font-weight: 600;
        font-size: 1.8rem;
    }
    
    .login-card-header i {
        font-size: 3rem;
        margin-bottom: 1rem;
        display: block;
    }
    
    .login-card-body {
        padding: 2.5rem;
        background: white;
    }
    
    .form-control {
        border-radius: 10px;
        border: 2px solid #e9ecef;
        padding: 12px 15px;
        transition: all 0.3s;
        font-size: 1rem;
    }
    
    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        outline: none;
    }
    
    .btn-login {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 10px;
        padding: 14px;
        font-weight: 600;
        font-size: 1.1rem;
        transition: all 0.3s;
        color: white !important;
    }
    
    .btn-login:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        color: white !important;
    }
    
    .login-footer {
        text-align: center;
        margin-top: 1.5rem;
        padding-top: 1.5rem;
        border-top: 1px solid #e9ecef;
    }
    
    .login-footer a {
        color: #667eea;
        text-decoration: none;
        font-weight: 500;
        transition: all 0.3s;
    }
    
    .login-footer a:hover {
        color: #764ba2;
        text-decoration: underline;
    }
    
    .system-name {
        font-size: 0.95rem;
        color: rgba(255, 255, 255, 0.95);
        margin-bottom: 0;
        margin-top: 0.5rem;
    }
    
    .form-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.5rem;
        font-size: 0.95rem;
    }
    
    .form-check-label {
        font-size: 0.9rem;
        color: #6c757d;
    }
    
    /* Responsive */
    @media (max-width: 576px) {
        .login-card {
            max-width: 100%;
            margin: 0 15px;
        }
        
        .login-card-body {
            padding: 2rem 1.5rem;
        }
        
        .login-card-header h2 {
            font-size: 1.5rem;
        }
        
        .login-card-header i {
            font-size: 2.5rem;
        }
    }
</style>

<div class="login-container">
    <div class="login-card">
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
                    @if (Route::has('password.request'))
                        <p class="mb-0"><a href="{{ route('password.request') }}">Forgot your password?</a></p>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>
@endsection