@extends('layouts.app')

@section('title', $skill->title . ' - Mentors | SkillSwap')

@section('content')
<div class="container py-5">
    <!-- Breadcrumb & Header -->
    <nav aria-label="breadcrumb" class="mb-4 mt-2">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('skills.index') }}" class="text-primary text-decoration-none">Skills</a></li>
            <li class="breadcrumb-item active text-muted" aria-current="page">{{ $skill->title }}</li>
        </ol>
    </nav>

    <div class="glass-card p-5 mb-5 position-relative overflow-hidden">
        <!-- Decorative element -->
        <div class="position-absolute top-0 end-0 bg-primary opacity-10 rounded-circle" style="width: 300px; height: 300px; margin-right: -150px; margin-top: -150px; filter: blur(40px);"></div>
        
        <div class="position-relative z-1">
            <span class="badge bg-secondary mb-3 px-3 py-2 rounded-pill border border-secondary">{{ $skill->category->name }}</span>
            <h1 class="display-5 fw-bold mb-3">{{ $skill->title }}</h1>
            <p class="lead text-light" style="max-width: 800px;">
                {{ $skill->description }}
            </p>
        </div>
    </div>

    <!-- Mentors List -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold m-0 d-inline-block me-3">Available Mentors</h3>
            <span class="badge bg-primary bg-opacity-25 text-primary border border-primary border-opacity-50 px-3 py-2 rounded-pill align-middle">
                {{ $mentors->total() }} Found
            </span>
        </div>
        <div>
            @auth
                <a href="{{ route('user-skills.create') }}" class="btn btn-outline-primary rounded-pill btn-sm px-3">Are you an expert? Become a Mentor</a>
            @endauth
        </div>
    </div>

    @if($mentors->isEmpty())
        <div class="text-center py-5 glass-card">
            <i class="bi bi-person-x fs-1 text-muted mb-3 d-block opacity-50"></i>
            <h4 class="text-muted">No mentors available right now.</h4>
            <p class="text-secondary">Be the first to teach this skill! Head to your dashboard to list yourself as a mentor.</p>
            @auth
                <a href="{{ route('user-skills.create') }}" class="btn btn-primary rounded-pill mt-3 px-4">Become a Mentor</a>
            @endauth
        </div>
    @else
        <div class="row g-4">
            @foreach($mentors as $mentorSkill)
                <div class="col-12">
                    <div class="glass-card p-4 transition-all hover-border d-flex flex-column flex-md-row gap-4 align-items-md-center">
                        
                        <!-- Avatar & Basic Info -->
                        <div class="d-flex align-items-center flex-shrink-0" style="width: 250px;">
<x-avatar :user="$mentorSkill->user" size="lg" class="border border-primary me-3" />
                            <div>
                                <h5 class="fw-bold mb-1">{{ $mentorSkill->user->profile->full_name }}</h5>

                                <span class="text-muted small"><i class="bi bi-clock me-1"></i> {{ $mentorSkill->user->profile->timezone }}</span>
                            </div>
                        </div>

                        <!-- Skill Details -->
                        <div class="flex-grow-1 border-start-md border-secondary ps-md-4">
                            <div class="d-flex mb-2 gap-2">
                                <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25">
                                    <i class="bi bi-bar-chart-fill me-1"></i> {{ ucfirst($mentorSkill->experience_level) }}
                                </span>
                            </div>
                            <p class="text-light small mb-0" style="line-height: 1.6;">
                                {{ $mentorSkill->description ?? 'No specific details provided by the mentor. They are ready to teach!' }}
                            </p>
                        </div>

                        <!-- Action & Price -->
                        <div class="text-md-end flex-shrink-0 border-start-md border-secondary ps-md-4" style="min-width: 180px;">
                            <h4 class="fw-bold text-success mb-1">{{ number_format($mentorSkill->credits_per_hour, 2) }} <span class="fs-6 text-muted fw-normal">CR/hr</span></h4>
                            <p class="text-muted small mb-3">{{ $mentorSkill->user->profile->sessions_completed_as_mentor }} sessions completed</p>
                            
                            @if(Auth::id() === $mentorSkill->user_id)
                                <a href="{{ route('user-skills.edit', $mentorSkill->id) }}" class="btn btn-outline-secondary w-100 rounded-pill">Edit Listing</a>
                            @else
                                <a href="{{ route('session-requests.create', ['user_skill_id' => $mentorSkill->id]) }}" class="btn btn-primary w-100 rounded-pill shadow-sm">Request Session</a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-5 d-flex justify-content-center">
            {{ $mentors->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>

<style>
    .hover-border {
        transition: border-color 0.3s ease, box-shadow 0.3s ease;
        border: 1px solid rgba(255,255,255,0.05);
    }
    .hover-border:hover {
        border-color: rgba(99, 102, 241, 0.4);
        box-shadow: 0 4px 20px rgba(0,0,0,0.2) !important;
    }
    @media (min-width: 768px) {
        .border-start-md {
            border-left: 1px solid rgba(255,255,255,0.1);
        }
    }
</style>
@endsection
