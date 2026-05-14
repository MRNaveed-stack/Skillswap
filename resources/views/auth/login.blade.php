@extends('layouts.app')

@section('title', 'Login - SkillSwap')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center mt-5">
        <div class="col-md-6 col-lg-5">
            <div class="glass-card p-5">
                <div class="text-center mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10 text-primary mb-3" style="width: 64px; height: 64px;">
                        <i class="bi bi-box-arrow-in-right fs-2"></i>
                    </div>
                    <h2 class="fw-bold">Welcome Back</h2>
                    <p class="text-muted">Login to continue your learning journey.</p>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger bg-danger bg-opacity-10 border-danger text-danger">
                        <ul class="mb-0 small">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="email" class="form-label text-muted small fw-medium">Email Address</label>
                        <input type="email" class="form-control bg-dark bg-opacity-50 border-secondary text-white focus-ring focus-ring-primary" id="email" name="email" value="{{ old('email') }}" required autofocus placeholder="name@example.com">
                    </div>
                    
                    <div class="mb-4">
                        <label for="password" class="form-label text-muted small fw-medium">Password</label>
                        <input type="password" class="form-control bg-dark bg-opacity-50 border-secondary text-white focus-ring focus-ring-primary" id="password" name="password" required placeholder="••••••••">
                    </div>

                    <button type="submit" class="btn btn-primary w-100 rounded-pill py-2 fw-medium">
                        Sign In
                    </button>
                </form>

                <div class="text-center mt-4 pt-3 border-top border-secondary">
                    <p class="text-muted small mb-0">Don't have an account? <a href="{{ route('register') }}" class="text-primary fw-medium text-decoration-none">Sign up here</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
