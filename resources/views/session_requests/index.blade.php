@extends('layouts.app')

@section('title', 'Session Requests - SkillSwap')

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <h2 class="fw-bold mb-1">Session Requests Inbox</h2>
            <p class="text-muted mb-0">Manage incoming and outgoing mentorship proposals.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success bg-success bg-opacity-10 border-success text-success mb-4">
            {{ session('success') }}
        </div>
    @endif

    <ul class="nav nav-pills mb-4" id="requestsTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active rounded-pill px-4" id="incoming-tab" data-bs-toggle="pill" data-bs-target="#incoming" type="button" role="tab" aria-controls="incoming" aria-selected="true">
                Incoming (As Mentor)
                @if($incomingRequests->where('status', 'pending')->count() > 0)
                    <span class="badge bg-danger rounded-pill ms-1">{{ $incomingRequests->where('status', 'pending')->count() }}</span>
                @endif
            </button>
        </li>
        <li class="nav-item ms-2" role="presentation">
            <button class="nav-link rounded-pill px-4" id="outgoing-tab" data-bs-toggle="pill" data-bs-target="#outgoing" type="button" role="tab" aria-controls="outgoing" aria-selected="false">
                Outgoing (As Learner)
            </button>
        </li>
    </ul>

    <div class="tab-content" id="requestsTabContent">
        <!-- Incoming Requests Tab -->
        <div class="tab-pane fade show active" id="incoming" role="tabpanel" aria-labelledby="incoming-tab">
            @if($incomingRequests->isEmpty())
                <div class="glass-card p-5 text-center">
                    <i class="bi bi-inbox fs-1 text-muted mb-3 d-block opacity-50"></i>
                    <h5 class="text-white">No incoming requests.</h5>
                    <p class="text-muted">You haven't received any session requests yet. Keep your profile updated and stay active!</p>
                </div>
            @else
                <div class="row g-4">
                    @foreach($incomingRequests as $request)
                        <div class="col-12">
                            <div class="glass-card p-4 position-relative">
                                @if($request->status === 'pending')
                                    <div class="position-absolute top-0 start-0 w-100 h-100 bg-primary bg-opacity-10 rounded" style="z-index: -1;"></div>
                                @endif
                                
                                <div class="d-flex flex-column flex-md-row gap-4">
                                    <div class="d-flex align-items-center flex-shrink-0" style="width: 250px;">
<x-avatar :user="$request->learner" size="md" class="border border-primary me-3" />
                                        <div>
                                            <h6 class="fw-bold mb-1">{{ $request->learner->profile->full_name }}</h6>
                                            <span class="badge bg-secondary bg-opacity-25 text-light border border-secondary border-opacity-50">Learner</span>
                                        </div>
                                    </div>
                                    
                                    <div class="flex-grow-1 border-start-md border-secondary ps-md-4">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h5 class="fw-bold text-primary mb-0">{{ $request->userSkill->skill->title }}</h5>
                                            <span class="badge {{ $request->status === 'pending' ? 'bg-warning text-dark' : ($request->status === 'accepted' ? 'bg-success' : 'bg-danger') }}">{{ ucfirst($request->status) }}</span>
                                        </div>
                                        
                                        <p class="text-light small mb-3 fst-italic border-start border-3 border-secondary ps-3 py-1">
                                            "{{ $request->learner_message ?? 'No additional message provided.' }}"
                                        </p>
                                        
                                        <div class="d-flex gap-3 text-muted small">
                                            <span><i class="bi bi-calendar-event me-1"></i> {{ $request->proposed_start->format('M d, Y') }}</span>
                                            <span><i class="bi bi-clock me-1"></i> {{ $request->proposed_start->format('H:i') }} - {{ $request->proposed_end->format('H:i') }}</span>
                                            <span class="text-success fw-bold"><i class="bi bi-wallet2 me-1"></i> {{ number_format($request->credits_reserved, 2) }} CR</span>
                                        </div>
                                        
                                        @if($request->status === 'rejected')
                                            <div class="mt-2 text-danger small">
                                                <strong>Reason:</strong> {{ $request->rejection_reason }}
                                            </div>
                                        @endif
                                    </div>
                                    
                                    @if($request->status === 'pending')
                                        <div class="d-flex flex-md-column justify-content-center gap-2 border-start-md border-secondary ps-md-4">
                                            <form action="{{ route('session-requests.update', $request->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="action" value="accept">
                                                <button type="submit" class="btn btn-success rounded-pill w-100 shadow-sm"><i class="bi bi-check-lg me-1"></i> Accept</button>
                                            </form>
                                            
                                            <!-- Reject Modal Trigger -->
                                            <button type="button" class="btn btn-outline-danger rounded-pill w-100" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $request->id }}">
                                                <i class="bi bi-x-lg me-1"></i> Reject
                                            </button>
                                        </div>

                                        <!-- Reject Modal -->
                                        <div class="modal fade" id="rejectModal{{ $request->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content bg-dark border-secondary">
                                                    <form action="{{ route('session-requests.update', $request->id) }}" method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <input type="hidden" name="action" value="reject">
                                                        
                                                        <div class="modal-header border-secondary">
                                                            <h5 class="modal-title text-white">Decline Request</h5>
                                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label class="form-label text-muted small">Reason for declining (sent to learner)</label>
                                                                <textarea class="form-control bg-dark border-secondary text-white" name="rejection_reason" rows="3" required placeholder="e.g. Time conflict, please propose another time..."></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer border-secondary">
                                                            <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-danger rounded-pill">Confirm Rejection</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Outgoing Requests Tab -->
        <div class="tab-pane fade" id="outgoing" role="tabpanel" aria-labelledby="outgoing-tab">
            @if($outgoingRequests->isEmpty())
                <div class="glass-card p-5 text-center">
                    <i class="bi bi-send fs-1 text-muted mb-3 d-block opacity-50"></i>
                    <h5 class="text-white">No outgoing requests.</h5>
                    <p class="text-muted">You haven't requested any sessions yet. Browse the skills marketplace to find a mentor!</p>
                    <a href="{{ route('skills.index') }}" class="btn btn-outline-primary rounded-pill mt-3">Find Skills</a>
                </div>
            @else
                <div class="row g-4">
                    @foreach($outgoingRequests as $request)
                        <div class="col-12">
                            <div class="glass-card p-4">
                                <div class="d-flex flex-column flex-md-row gap-4">
                                    <div class="d-flex align-items-center flex-shrink-0" style="width: 250px;">
<x-avatar :user="$request->mentor" size="md" class="border border-primary me-3" />
                                        <div>
                                            <h6 class="fw-bold mb-1">{{ $request->mentor->profile->full_name }}</h6>
                                            <span class="badge bg-primary bg-opacity-25 text-primary border border-primary border-opacity-50">Mentor</span>
                                        </div>
                                    </div>
                                    
                                    <div class="flex-grow-1 border-start-md border-secondary ps-md-4">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h5 class="fw-bold text-white mb-0">{{ $request->userSkill->skill->title }}</h5>
                                            <span class="badge {{ $request->status === 'pending' ? 'bg-warning text-dark' : ($request->status === 'accepted' ? 'bg-success' : 'bg-danger') }}">{{ ucfirst($request->status) }}</span>
                                        </div>
                                        
                                        <div class="d-flex gap-3 text-muted small mb-2">
                                            <span><i class="bi bi-calendar-event me-1"></i> {{ $request->proposed_start->format('M d, Y') }}</span>
                                            <span><i class="bi bi-clock me-1"></i> {{ $request->proposed_start->format('H:i') }} - {{ $request->proposed_end->format('H:i') }}</span>
                                            <span class="text-success fw-bold"><i class="bi bi-wallet2 me-1"></i> {{ number_format($request->credits_reserved, 2) }} CR (Escrowed)</span>
                                        </div>

                                        @if($request->status === 'rejected')
                                            <div class="alert alert-danger bg-danger bg-opacity-10 border-danger py-2 px-3 small mt-2 mb-0">
                                                <strong>Mentor Note:</strong> {{ $request->rejection_reason }}
                                                <div class="mt-1 text-success">Credits have been refunded to your wallet.</div>
                                            </div>
                                        @elseif($request->status === 'accepted')
                                            <div class="mt-3">
                                                <a href="{{ route('sessions.index') }}" class="btn btn-sm btn-outline-success rounded-pill">View in Sessions Dashboard</a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

<style>
    @media (min-width: 768px) {
        .border-start-md {
            border-left: 1px solid rgba(255,255,255,0.1);
        }
    }
    
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
