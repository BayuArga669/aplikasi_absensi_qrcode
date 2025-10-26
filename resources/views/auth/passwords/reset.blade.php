@extends('layouts.app')

@section('content')
<style>
    .reset-container {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    .reset-card {
        border-radius: 15px;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        border: none;
    }
    .reset-card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        text-align: center;
        padding: 2rem 0;
        border: none;
    }
    .reset-card-header h2 {
        margin: 0;
        font-weight: 600;
    }
    .reset-card-header i {
        font-size: 2.5rem;
        margin-bottom: 1rem;
        display: block;
    }
    .reset-card-body {
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
    .btn-reset {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 10px;
        padding: 12px;
        font-weight: 600;
        transition: transform 0.2s;
    }
    .btn-reset:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }
    .reset-footer {
        text-align: center;
        margin-top: 1.5rem;
        padding-top: 1.5rem;
        border-top: 1px solid #eee;
    }
    .reset-footer a {
        color: #667eea;
        text-decoration: none;
        font-weight: 500;
    }
    .reset-footer a:hover {
        text-decoration: underline;
    }
    .system-name {
        font-size: 0.9rem;
        color: #6c757d;
        margin-bottom: 1.5rem;
    }
</style>

<div class="reset-container">
    <div class="col-md-6 col-lg-5">
        <div class="card reset-card">
            <div class="reset-card-header">
                <i class="fas fa-lock"></i>
                <h2>Set New Password</h2>
                <span class="system-name">QR Attendance System</span>
            </div>
            
            <div class="reset-card-body">
                <form method="POST" action="{{ route('password.update') }}">
                    @csrf

                    <input type="hidden" name="token" value="{{ $token }}">

                    <div class="mb-4">
                        <label for="email" class="form-label">Email Address</label>
                        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ $email ?? old('email') }}" required autocomplete="email" autofocus placeholder="Enter your email address">
                        
                        @error('email')
                            <div class="invalid-feedback">
                                <strong>{{ $message }}</strong>
                            </div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label">New Password</label>
                        <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password" placeholder="Enter new password">
                        
                        @error('password')
                            <div class="invalid-feedback">
                                <strong>{{ $message }}</strong>
                            </div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="password-confirm" class="form-label">Confirm New Password</label>
                        <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password" placeholder="Confirm new password">
                    </div>

                    <button type="submit" class="btn btn-primary btn-reset w-100">
                        <i class="fas fa-key me-2"></i>Reset Password
                    </button>

                    <div class="reset-footer">
                        <p class="mb-0"><a href="{{ route('login') }}">Back to Login</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection