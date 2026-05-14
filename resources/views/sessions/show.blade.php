@extends('layouts.app')

@section('title', 'Session Details - SkillSwap')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex align-items-center mb-4">
                <a href="{{ route('sessions.index') }}" class="btn btn-outline-secondary rounded-circle me-3" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <h3 class="fw-bold mb-0">Session Room</h3>
            </div>

            @if(session('success'))
                <div class="alert alert-success bg-success bg-opacity-10 border-success text-success mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <div class="glass-card p-4 p-md-5 position-relative overflow-hidden mb-4">
                <div class="position-absolute top-0 start-0 w-100" style="height: 6px; background: {{ $session->status === 'scheduled' ? '#3b82f6' : ($session->status === 'completed' ? '#10b981' : '#6b7280') }};"></div>
                
                <div class="d-flex justify-content-between align-items-center mb-4 pt-2">
                    <span class="badge {{ $session->status === 'scheduled' ? 'bg-primary' : ($session->status === 'completed' ? 'bg-success' : 'bg-secondary') }} px-3 py-2 rounded-pill">
                        Status: {{ ucfirst(str_replace('_', ' ', $session->status)) }}
                    </span>
                    <h5 class="fw-bold text-success mb-0">{{ number_format($session->credits_charged, 2) }} CR</h5>
                </div>

                <div class="text-center mb-5">
                    <h2 class="fw-bold mb-3">{{ $session->userSkill->skill->title }}</h2>
                    <div class="d-inline-flex bg-dark bg-opacity-50 rounded-pill p-1 pe-3 align-items-center border border-secondary">
                        <span class="bg-primary text-white rounded-pill px-3 py-1 me-2 small fw-medium">
                            {{ $session->scheduled_start->format('M d, Y') }}
                        </span>
                        <span class="text-light fw-medium">
                            <i class="bi bi-clock me-1 text-muted"></i> {{ $session->scheduled_start->format('H:i') }} - {{ $session->scheduled_end->format('H:i') }}
                        </span>
                    </div>
                </div>

                <div class="row align-items-center mb-5 position-relative">
                    <!-- Connection Line -->
                    <div class="position-absolute top-50 start-50 translate-middle w-50 border-top border-secondary border-2 z-0" style="border-style: dashed !important;"></div>
                    
                    <div class="col-6 text-center z-1">
<x-avatar :user="$session->learner" size="lg" class="border border-3 border-secondary mb-2" />
                        <h6 class="fw-bold mb-0">{{ $session->learner->profile->full_name }}</h6>
                        <span class="small text-muted">Learner</span>
                    </div>
                    <div class="col-6 text-center z-1">
<x-avatar :user="$session->mentor" size="lg" class="border border-3 border-primary mb-2" />
                        <h6 class="fw-bold mb-0">{{ $session->mentor->profile->full_name }}</h6>
                        <span class="small text-muted">Mentor</span>
                    </div>
                </div>

                @if($session->status === 'scheduled')
                    <div class="bg-primary bg-opacity-10 border border-primary border-opacity-25 rounded p-4 text-center mb-4">
                        <h5 class="fw-bold text-white mb-2">Meeting Link</h5>
                        <p class="text-light small mb-3">Join the session at the scheduled time using the link below.</p>
                        <a href="{{ $session->meeting_url }}" target="_blank" class="btn btn-primary rounded-pill px-5 py-2 fw-bold shadow-lg">
                            <i class="bi bi-camera-video me-2"></i> Join Meeting
                        </a>
                    </div>
                @endif

                @if(Auth::id() === $session->mentor_id && $session->status === 'scheduled')
                    <div class="mt-5 border-top border-secondary pt-4">
                        <h5 class="fw-bold mb-3">Manage Session (Mentor Only)</h5>
                        <form action="{{ route('sessions.update', $session->id) }}" method="POST" class="p-3 bg-dark bg-opacity-50 rounded border border-secondary">
                            @csrf
                            @method('PUT')
                            
                            <div class="mb-3">
                                <label class="form-label text-muted small fw-medium">Update Status</label>
                                <select name="status" class="form-select bg-dark border-secondary text-white" required>
                                    <option value="" disabled selected>Select Outcome...</option>
                                    <option value="completed">Completed Successfully</option>
                                    <option value="no_show">Learner No-Show</option>
                                </select>
                                <div class="form-text small text-secondary mt-2">
                                    Marking as completed will transfer the reserved credits to your wallet.
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-muted small fw-medium">Private Notes (Optional)</label>
                                <textarea name="notes" class="form-control bg-dark border-secondary text-white" rows="2" placeholder="Record what was covered..."></textarea>
                            </div>

                            <button type="submit" class="btn btn-outline-light rounded-pill w-100" onclick="return confirm('Are you sure? This action cannot be undone and will affect credit balances.');">
                                Finalize Session
                            </button>
                        </form>
                    </div>
                @elseif($session->status !== 'scheduled')
                    <div class="bg-dark bg-opacity-50 border border-secondary rounded p-4 text-center">
                        <h6 class="fw-bold mb-2">Session Ended</h6>
                        <p class="text-muted small mb-0">This session was marked as <strong>{{ str_replace('_', ' ', $session->status) }}</strong>.</p>
                        
                        @if($session->status === 'completed')
                            <div class="mt-4">
                                <a href="{{ route('reviews.create', $session->id) }}" class="btn btn-warning rounded-pill px-4 shadow-sm fw-bold">
                                    <i class="bi bi-star-fill me-1"></i> Leave a Review
                                </a>
                            </div>
                        @endif

                        @if($session->notes && Auth::id() === $session->mentor_id)
                            <div class="mt-4 pt-3 border-top border-secondary text-start">
                                <span class="d-block small text-muted fw-bold mb-1">Your Notes:</span>
                                <p class="text-light small mb-0">{{ $session->notes }}</p>
                            </div>
                        @endif
                    </div>
                @endif
                
                <div class="mt-4 text-center">
                    <a href="{{ route('messages.show', Auth::id() === $session->learner_id ? $session->mentor_id : $session->learner_id) }}" class="btn btn-outline-primary rounded-pill px-4">
                        <i class="bi bi-chat-dots me-2"></i> Message {{ Auth::id() === $session->learner_id ? 'Mentor' : 'Learner' }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
