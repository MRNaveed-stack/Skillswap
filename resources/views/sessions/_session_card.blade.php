<div class="col-md-6 col-xl-4">
    <div class="glass-card p-4 h-100 d-flex flex-column position-relative transition-all" style="border-top: 4px solid {{ $session->status === 'scheduled' ? '#3b82f6' : ($session->status === 'completed' ? '#10b981' : '#6b7280') }};">
        
        <div class="d-flex justify-content-between align-items-start mb-3">
            <span class="badge {{ $session->status === 'scheduled' ? 'bg-primary' : ($session->status === 'completed' ? 'bg-success' : 'bg-secondary') }}">
                {{ ucfirst(str_replace('_', ' ', $session->status)) }}
            </span>
            <span class="small text-muted">{{ $session->scheduled_start->diffForHumans() }}</span>
        </div>

        <h5 class="fw-bold mb-1">{{ $session->userSkill->skill->title }}</h5>
        
        @php
            $otherUser = $type === 'learner' ? $session->mentor : $session->learner;
            $roleLabel = $type === 'learner' ? 'Mentor' : 'Learner';
        @endphp

        <div class="d-flex align-items-center mb-4 mt-3">
<x-avatar :user="$otherUser" size="sm" class="border border-secondary me-2" />
            <div>
                <p class="mb-0 small text-muted lh-1">{{ $roleLabel }}</p>
                <p class="mb-0 fw-medium lh-1">{{ $otherUser->profile->full_name }}</p>
            </div>
        </div>

        <div class="mt-auto bg-dark bg-opacity-50 rounded p-3 mb-3">
            <div class="d-flex justify-content-between mb-1">
                <span class="small text-muted"><i class="bi bi-calendar me-1"></i> Date</span>
                <span class="small fw-medium">{{ $session->scheduled_start->format('M d, Y') }}</span>
            </div>
            <div class="d-flex justify-content-between mb-1">
                <span class="small text-muted"><i class="bi bi-clock me-1"></i> Time</span>
                <span class="small fw-medium">{{ $session->scheduled_start->format('H:i') }} - {{ $session->scheduled_end->format('H:i') }}</span>
            </div>
            <div class="d-flex justify-content-between">
                <span class="small text-muted"><i class="bi bi-wallet2 me-1"></i> Credits</span>
                <span class="small fw-medium {{ $type === 'mentor' ? 'text-success' : '' }}">{{ $type === 'mentor' ? '+' : '-' }}{{ number_format($session->credits_charged, 2) }}</span>
            </div>
        </div>

        <a href="{{ route('sessions.show', $session->id) }}" class="btn btn-outline-light w-100 rounded-pill">View Details</a>
    </div>
</div>
