@extends('layouts.app')

@section('content')
<style>
    .password-container {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    .password-card {
        border-radius: 15px;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        border: none;
    }
    .password-card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        text-align: center;
        padding: 2rem 0;
        border: none;
    }
    .password-card-header h2 {
        margin: 0;
        font-weight: 600;
    }
    .password-card-header i {
        font-size: 2.5rem;
        margin-bottom: 1rem;
        display: block;
    }
    .password-card-body {
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
    .btn-password {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 10px;
        padding: 12px;
        font-weight: 600;
        transition: transform 0.2s;
    }
    .btn-password:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }
    .password-footer {
        text-align: center;
        margin-top: 1.5rem;
        padding-top: 1.5rem;
        border-top: 1px solid #eee;
    }
    .password-footer a {
        color: #667eea;
        text-decoration: none;
        font-weight: 500;
    }
    .password-footer a:hover {
        text-decoration: underline;
    }
    .system-name {
        font-size: 0.9rem;
        color: #6c757d;
        margin-bottom: 1.5rem;
    }
</style>

<div class="password-container">
    <div class="col-md-6 col-lg-5">
        <div class="card password-card">
            <div class="password-card-header">
                <i class="fas fa-key"></i>
                <h2>Reset Password</h2>
                <span class="system-name">QR Attendance System</span>
            </div>
            
            <div class="password-card-body">
                @if (session('status'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('status') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}">
                    @csrf

                    <div class="mb-4">
                        <label for="email" class="form-label">Email Address</label>
                        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="Enter your email address">
                        
                        @error('email')
                            <div class="invalid-feedback">
                                <strong>{{ $message }}</strong>
                            </div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary btn-password w-100">
                        <i class="fas fa-paper-plane me-2"></i>Send Reset Link
                    </button>

                    <div class="password-footer">
                        <p class="mb-0"><a href="{{ route('login') }}">Back to Login</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection