@extends('layouts.app')

@section('content')
<style>
    .register-container {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    .register-card {
        border-radius: 15px;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        border: none;
    }
    .register-card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        text-align: center;
        padding: 2rem 0;
        border: none;
    }
    .register-card-header h2 {
        margin: 0;
        font-weight: 600;
    }
    .register-card-header i {
        font-size: 2.5rem;
        margin-bottom: 1rem;
        display: block;
    }
    .register-card-body {
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
    .btn-register {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 10px;
        padding: 12px;
        font-weight: 600;
        transition: transform 0.2s;
    }
    .btn-register:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }
    .register-footer {
        text-align: center;
        margin-top: 1.5rem;
        padding-top: 1.5rem;
        border-top: 1px solid #eee;
    }
    .register-footer a {
        color: #667eea;
        text-decoration: none;
        font-weight: 500;
    }
    .register-footer a:hover {
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

<div class="register-container">
    <div class="col-md-6 col-lg-5">
        <div class="card register-card">
            <div class="register-card-header">
                <i class="fas fa-user-plus"></i>
                <h2>Create Account</h2>
                <span class="system-name">Join QR Attendance System</span>
            </div>
            
            <div class="register-card-body">
                <form method="POST" action="{{ route('register') }}">
                    @csrf

                    <div class="mb-4">
                        <label for="name" class="form-label">Full Name</label>
                        <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus placeholder="Enter your full name">
                        
                        @error('name')
                            <div class="invalid-feedback">
                                <strong>{{ $message }}</strong>
                            </div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="email" class="form-label">Email Address</label>
                        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="Enter your email">
                        
                        @error('email')
                            <div class="invalid-feedback">
                                <strong>{{ $message }}</strong>
                            </div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label">Password</label>
                        <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password" placeholder="Enter your password">
                        
                        @error('password')
                            <div class="invalid-feedback">
                                <strong>{{ $message }}</strong>
                            </div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="password-confirm" class="form-label">Confirm Password</label>
                        <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password" placeholder="Confirm your password">
                    </div>

                    <div class="mb-4">
                        <label for="role" class="form-label">User Role</label>
                        <select id="role" class="form-control @error('role') is-invalid @enderror" name="role" required>
                            <option value="">Select Your Role</option>
                            <option value="employee" {{ old('role') === 'employee' ? 'selected' : '' }}>Employee</option>
                            <option value="superior" {{ old('role') === 'superior' ? 'selected' : '' }}>Superior/Manager</option>
                            <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Administrator</option>
                        </select>

                        @error('role')
                            <div class="invalid-feedback">
                                <strong>{{ $message }}</strong>
                            </div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary btn-register w-100">
                        <i class="fas fa-user-plus me-2"></i>Create Account
                    </button>

                    <div class="register-footer">
                        <p class="mb-0">Already have an account? <a href="{{ route('login') }}">Login here</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection