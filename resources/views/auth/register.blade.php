@extends('layouts.app')

@section('title', 'Register - SkillSwap')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center mt-4">
        <div class="col-md-7 col-lg-6">
            <div class="glass-card p-5">
                <div class="text-center mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10 text-primary mb-3" style="width: 64px; height: 64px;">
                        <i class="bi bi-person-plus fs-2"></i>
                    </div>
                    <h2 class="fw-bold">Create an Account</h2>
                    <p class="text-muted">Join the SkillSwap community today.</p>
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

                <form method="POST" action="{{ route('register') }}">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="full_name" class="form-label text-muted small fw-medium">Full Name</label>
                        <input type="text" class="form-control bg-dark bg-opacity-50 border-secondary text-white focus-ring focus-ring-primary" id="full_name" name="full_name" value="{{ old('full_name') }}" required autofocus placeholder="John Doe">
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label text-muted small fw-medium">Email Address</label>
                        <input type="email" class="form-control bg-dark bg-opacity-50 border-secondary text-white focus-ring focus-ring-primary" id="email" name="email" value="{{ old('email') }}" required placeholder="name@example.com">
                    </div>
                    
                    <div class="row">
                        <div class="col-sm-6 mb-3">
                            <label for="password" class="form-label text-muted small fw-medium">Password</label>
                            <input type="password" class="form-control bg-dark bg-opacity-50 border-secondary text-white focus-ring focus-ring-primary" id="password" name="password" required placeholder="Min 8 characters">
                        </div>
                        <div class="col-sm-6 mb-4">
                            <label for="password_confirmation" class="form-label text-muted small fw-medium">Confirm Password</label>
                            <input type="password" class="form-control bg-dark bg-opacity-50 border-secondary text-white focus-ring focus-ring-primary" id="password_confirmation" name="password_confirmation" required placeholder="Min 8 characters">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="role" class="form-label text-muted small fw-medium">Join As</label>
                        <select class="form-select bg-dark bg-opacity-50 border-secondary text-white focus-ring focus-ring-primary" id="role" name="role" required>
                            <option value="user" selected>Learner / Mentor (Standard User)</option>
                            <!-- Admins normally created via CLI, keeping option hidden or restricted in real app -->
                        </select>
                        <div class="form-text text-secondary small">You can both learn and teach on SkillSwap!</div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 rounded-pill py-2 fw-medium">
                        Create Account
                    </button>
                </form>

                <div class="text-center mt-4 pt-3 border-top border-secondary">
                    <p class="text-muted small mb-0">Already have an account? <a href="{{ route('login') }}" class="text-primary fw-medium text-decoration-none">Log in</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
