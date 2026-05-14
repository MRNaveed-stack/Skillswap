@extends('layouts.app')

@section('title', 'My Sessions - SkillSwap')

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <h2 class="fw-bold mb-1">Confirmed Sessions</h2>
            <p class="text-muted mb-0">Your upcoming and past learning/teaching sessions.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success bg-success bg-opacity-10 border-success text-success mb-4">
            {{ session('success') }}
        </div>
    @endif

    <ul class="nav nav-pills mb-4" id="sessionsTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active rounded-pill px-4" id="learning-tab" data-bs-toggle="pill" data-bs-target="#learning" type="button" role="tab" aria-controls="learning" aria-selected="true">
                Learning
            </button>
        </li>
        <li class="nav-item ms-2" role="presentation">
            <button class="nav-link rounded-pill px-4" id="teaching-tab" data-bs-toggle="pill" data-bs-target="#teaching" type="button" role="tab" aria-controls="teaching" aria-selected="false">
                Teaching
            </button>
        </li>
    </ul>

    <div class="tab-content" id="sessionsTabContent">
        <!-- Learning Tab -->
        <div class="tab-pane fade show active" id="learning" role="tabpanel" aria-labelledby="learning-tab">
            @if($learningSessions->isEmpty())
                <div class="glass-card p-5 text-center">
                    <i class="bi bi-mortarboard fs-1 text-muted mb-3 d-block opacity-50"></i>
                    <h5 class="text-white">No learning sessions yet.</h5>
                    <p class="text-muted">Start booking mentors from the marketplace!</p>
                </div>
            @else
                <div class="row g-4">
                    @foreach($learningSessions as $session)
                        @include('sessions._session_card', ['session' => $session, 'type' => 'learner'])
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Teaching Tab -->
        <div class="tab-pane fade" id="teaching" role="tabpanel" aria-labelledby="teaching-tab">
            @if($teachingSessions->isEmpty())
                <div class="glass-card p-5 text-center">
                    <i class="bi bi-person-video3 fs-1 text-muted mb-3 d-block opacity-50"></i>
                    <h5 class="text-white">No teaching sessions yet.</h5>
                    <p class="text-muted">Make sure your availability is set and your profile is complete to attract learners!</p>
                </div>
            @else
                <div class="row g-4">
                    @foreach($teachingSessions as $session)
                        @include('sessions._session_card', ['session' => $session, 'type' => 'mentor'])
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

<style>
    .nav-pills .nav-link {
        color: #9ca3af;
        border: 1px solid transparent;
    }
    .nav-pills .nav-link:hover {
        background: rgba(255,255,255,0.05);
    }
    .nav-pills .nav-link.active {
        background: linear-gradient(90deg, #6366f1, #8b5cf6);
        color: white;
    }
</style>
@endsection
