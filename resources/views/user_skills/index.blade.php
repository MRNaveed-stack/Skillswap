@extends('layouts.app')

@section('title', 'My Mentor Listings - SkillSwap')

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <h2 class="fw-bold mb-1">My Mentor Listings</h2>
            <p class="text-muted mb-0">Manage the skills you teach and your hourly rates.</p>
        </div>
        <a href="{{ route('user-skills.create') }}" class="btn btn-primary rounded-pill px-4 shadow-sm">
            <i class="bi bi-plus-lg me-2"></i>Add New Skill
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success bg-success bg-opacity-10 border-success text-success mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if($userSkills->isEmpty())
        <div class="glass-card p-5 text-center my-5">
            <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-secondary bg-opacity-25 text-muted mb-4" style="width: 80px; height: 80px;">
                <i class="bi bi-journal-x fs-1"></i>
            </div>
            <h4 class="fw-bold mb-2">No Skills Listed Yet</h4>
            <p class="text-secondary mb-4 mx-auto" style="max-width: 500px;">
                Share your knowledge with the community! List a skill you're good at, set your hourly rate, and start earning credits to spend on learning something new.
            </p>
            <a href="{{ route('user-skills.create') }}" class="btn btn-primary rounded-pill px-5 py-2">
                Create Your First Listing
            </a>
        </div>
    @else
        <div class="row g-4">
            @foreach($userSkills as $userSkill)
                <div class="col-md-6 col-lg-4">
                    <div class="glass-card p-4 h-100 d-flex flex-column position-relative">
                        @if(!$userSkill->is_active)
                            <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark bg-opacity-50 rounded" style="z-index: 5;"></div>
                            <span class="badge bg-danger position-absolute top-0 end-0 m-3" style="z-index: 10;">Inactive</span>
                        @else
                            <span class="badge bg-success bg-opacity-25 text-success position-absolute top-0 end-0 m-3 border border-success border-opacity-50">Active</span>
                        @endif

                        <div class="mb-3">
                            <span class="text-muted small text-uppercase tracking-wider">{{ $userSkill->skill->category->name }}</span>
                            <h4 class="fw-bold mt-1 mb-0">{{ $userSkill->skill->title }}</h4>
                        </div>
                        
                        <div class="d-flex align-items-center mb-3 gap-2">
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25">
                                {{ ucfirst($userSkill->experience_level) }}
                            </span>
                            <span class="fw-bold text-success">{{ number_format($userSkill->credits_per_hour, 2) }} CR/hr</span>
                        </div>

                        <p class="text-light small flex-grow-1 mb-4" style="z-index: 6;">
                            {{ Str::limit($userSkill->description ?? 'No description provided.', 120) }}
                        </p>

                        <div class="d-flex gap-2 mt-auto" style="z-index: 10;">
                            <a href="{{ route('user-skills.edit', $userSkill->id) }}" class="btn btn-outline-light btn-sm flex-grow-1 rounded-pill">
                                Edit
                            </a>
                            <form action="{{ route('user-skills.destroy', $userSkill->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to remove this listing?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm rounded-circle" style="width: 32px; height: 32px; padding: 0;">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
