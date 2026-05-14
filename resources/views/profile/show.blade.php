@extends('layouts.app')

@section('title', 'My Profile - SkillSwap')

@section('content')
<div class="container py-5">
    @if(session('success'))
        <div class="alert alert-success bg-success bg-opacity-10 border-success text-success mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="row g-4">
        <!-- Sidebar / Identity Card -->
        <div class="col-lg-4">
            <div class="glass-card p-4 text-center">
                <div class="position-relative d-inline-block mb-3">
<x-avatar :user="$user" size="xl" class="border border-2 border-primary" />
                </div>
                <h3 class="fw-bold mb-1">{{ $user->profile->full_name }}</h3>
                <p class="text-muted mb-3">{{ $user->email }}</p>
                
                <div class="d-flex justify-content-center gap-2 mb-4">
                    <span class="badge bg-primary bg-opacity-25 text-primary border border-primary border-opacity-50 px-3 py-2 rounded-pill">
                        <i class="bi bi-clock me-1"></i> {{ $user->profile->timezone }}
                    </span>
                </div>

                <a href="{{ route('profile.edit') }}" class="btn btn-outline-light w-100 rounded-pill mb-3">
                    <i class="bi bi-pencil me-2"></i>Edit Profile
                </a>
            </div>

            <div class="glass-card p-4 mt-4">
                <h5 class="fw-bold mb-4">Wallet Balance</h5>
                <div class="d-flex align-items-center mb-3">
                    <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center text-success me-3" style="width: 48px; height: 48px;">
                        <i class="bi bi-wallet2 fs-4"></i>
                    </div>
                    <div>
                        <h3 class="mb-0 fw-bold text-success">{{ number_format($user->wallet->balance, 2) }}</h3>
                        <p class="text-muted small mb-0">Available Credits</p>
                    </div>
                </div>
                
                <hr class="border-secondary my-3">
                
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">Total Earned</span>
                    <span class="text-white small fw-medium">+{{ number_format($user->wallet->total_earned, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted small">Total Spent</span>
                    <span class="text-white small fw-medium">-{{ number_format($user->wallet->total_spent, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="col-lg-8">
            <div class="glass-card p-4 mb-4">
                <h5 class="fw-bold mb-4 border-bottom border-secondary pb-3">About Me</h5>
                @if($user->profile->bio)
                    <p class="text-light" style="line-height: 1.7;">
                        {{ nl2br(e($user->profile->bio)) }}
                    </p>
                @else
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-journal-text fs-1 mb-2 d-block opacity-50"></i>
                        <p>You haven't written a bio yet.</p>
                        <a href="{{ route('profile.edit') }}" class="btn btn-sm btn-outline-primary rounded-pill">Add Bio</a>
                    </div>
                @endif
            </div>

            <div class="row g-4">
                <div class="col-md-6">
                    <div class="glass-card p-4 h-100">
                        <div class="d-flex align-items-center mb-3">
                            <i class="bi bi-mortarboard text-primary fs-4 me-2"></i>
                            <h6 class="fw-bold mb-0">Learning Stats</h6>
                        </div>
                        <h2 class="fw-bold mb-0">{{ $user->profile->sessions_completed_as_learner }}</h2>
                        <p class="text-muted small">Sessions completed as Learner</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="glass-card p-4 h-100">
                        <div class="d-flex align-items-center mb-3">
                            <i class="bi bi-person-video3 text-info fs-4 me-2"></i>
                            <h6 class="fw-bold mb-0">Mentoring Stats</h6>
                        </div>
                        <h2 class="fw-bold mb-0">{{ $user->profile->sessions_completed_as_mentor }}</h2>
                        <p class="text-muted small">Sessions completed as Mentor</p>
                        
                        @if($user->profile->response_rate)
                            <div class="mt-3 pt-3 border-top border-secondary">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted small">Response Rate</span>
                                    <span class="badge bg-info bg-opacity-25 text-info">{{ $user->profile->response_rate }}%</span>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
