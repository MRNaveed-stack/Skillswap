@extends('layouts.app')

@section('title', 'Browse Mentors - SkillSwap')

@section('content')
<div class="container py-5">
    <div class="row mb-5 align-items-center">
        <div class="col-md-8">
            <h1 class="fw-bold display-5 mb-3">Find the Perfect <span class="text-primary">Mentor</span></h1>
            <p class="text-muted fs-5">Browse our community of expert mentors ready to guide you to success.</p>
        </div>
        <div class="col-md-4">
            <form action="{{ route('mentors.index') }}" method="GET" class="d-flex">
                <input type="text" name="search" class="form-control bg-dark text-white border-secondary rounded-pill-start" placeholder="Search mentors by name or skill..." value="{{ request('search') }}">
                <button class="btn btn-primary rounded-pill-end px-4" type="submit"><i class="bi bi-search"></i></button>
            </form>
        </div>
    </div>

    @if($mentors->isEmpty())
        <div class="text-center py-5 glass-card">
            <i class="bi bi-search fs-1 text-muted mb-3 d-block"></i>
            <h3 class="fw-bold text-white mb-2">No mentors found</h3>
            <p class="text-secondary">Try adjusting your search terms.</p>
            <a href="{{ route('mentors.index') }}" class="btn btn-outline-primary rounded-pill mt-3">Clear Search</a>
        </div>
    @else
        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
            @foreach($mentors as $mentorSkill)
                <div class="col">
                    <div class="glass-card h-100 p-4 d-flex flex-column hover-lift">
                        <div class="d-flex align-items-start mb-3">
<x-avatar :user="$mentorSkill->user" size="md" class="border border-secondary border-2 me-3" />
                            <div>
                                <h5 class="fw-bold text-white mb-1">{{ $mentorSkill->user->profile->full_name }}</h5>
                                <span class="badge bg-secondary text-light">{{ ucfirst($mentorSkill->experience_level) }}</span>
                                <div class="small text-muted mt-1"><i class="bi bi-geo-alt me-1"></i>{{ $mentorSkill->user->profile->timezone }}</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <h6 class="text-primary fw-bold mb-2">{{ $mentorSkill->skill->title }}</h6>
                            <p class="text-muted small mb-0 line-clamp-3" style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                                {{ $mentorSkill->description ?? 'Experienced mentor offering guidance in ' . $mentorSkill->skill->title . '.' }}
                            </p>
                        </div>

                        <div class="mt-auto pt-3 border-top border-secondary d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-success fw-bold d-flex align-items-center"><i class="bi bi-wallet2 me-1"></i>{{ number_format($mentorSkill->credits_per_hour, 1) }}</span>
                                <span class="text-muted small">credits/hr</span>
                            </div>
                            @if(Auth::check() && Auth::id() === $mentorSkill->user_id)
                                <a href="{{ route('user-skills.edit', $mentorSkill->id) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">Edit Listing</a>
                            @else
                                <a href="{{ route('session-requests.create', ['user_skill_id' => $mentorSkill->id]) }}" class="btn btn-sm btn-primary rounded-pill px-3">Request Session</a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-5 d-flex justify-content-center">
            {{ $mentors->withQueryString()->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
