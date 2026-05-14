@extends('layouts.app')

@section('title', 'List a Skill - SkillSwap')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex align-items-center mb-4">
                <a href="{{ route('user-skills.index') }}" class="btn btn-outline-secondary rounded-circle me-3" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <h3 class="fw-bold mb-0">Offer a Skill</h3>
            </div>

            <div class="glass-card p-4 p-md-5">
                @if ($errors->any())
                    <div class="alert alert-danger bg-danger bg-opacity-10 border-danger text-danger mb-4">
                        <ul class="mb-0 small">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if(empty(Auth::user()->profile->resume_url))
                    <div class="text-center py-5">
                        <i class="bi bi-file-earmark-person fs-1 text-warning mb-3 d-block"></i>
                        <h4 class="fw-bold mb-3">Resume Required</h4>
                        <p class="text-muted mb-4">To maintain the quality of our mentors, we require you to upload a resume or CV before you can list a skill.</p>
                        <a href="{{ route('profile.edit') }}" class="btn btn-primary rounded-pill px-5 py-2">
                            <i class="bi bi-upload me-2"></i> Upload Resume in Profile
                        </a>
                    </div>
                @else
                    <form method="POST" action="{{ route('user-skills.store') }}">
                        @csrf
                        
                        <div class="mb-4">
                            <label for="skill_id" class="form-label text-muted small fw-medium">What skill do you want to teach?</label>
                            <select class="form-select bg-dark bg-opacity-50 border-secondary text-white focus-ring focus-ring-primary mb-3" id="skill_id" name="skill_id">
                                <option value="" disabled selected>Select a skill from the directory...</option>
                                @foreach($skills as $skill)
                                    <option value="{{ $skill->id }}" {{ old('skill_id') == $skill->id ? 'selected' : '' }}>
                                        {{ $skill->title }}
                                    </option>
                                @endforeach
                            </select>

                            <div class="border-start border-primary border-3 ps-3">
                                <p class="text-muted small mb-2">Can't find your skill? Add it here:</p>
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <input type="text" name="custom_skill" class="form-control bg-dark bg-opacity-50 border-secondary text-white focus-ring focus-ring-primary" placeholder="Custom Skill Name (e.g. Laravel)" value="{{ old('custom_skill') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" name="custom_category" class="form-control bg-dark bg-opacity-50 border-secondary text-white focus-ring focus-ring-primary" placeholder="Custom Category (e.g. Web Development)" value="{{ old('custom_category') }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label for="experience_level" class="form-label text-muted small fw-medium">Your Experience Level</label>
                                <select class="form-select bg-dark bg-opacity-50 border-secondary text-white focus-ring focus-ring-primary" id="experience_level" name="experience_level" required>
                                    <option value="beginner" {{ old('experience_level') == 'beginner' ? 'selected' : '' }}>Beginner (Can teach basics)</option>
                                    <option value="intermediate" {{ old('experience_level') == 'intermediate' ? 'selected' : '' }}>Intermediate</option>
                                    <option value="advanced" {{ old('experience_level') == 'advanced' ? 'selected' : '' }}>Advanced</option>
                                    <option value="expert" {{ old('experience_level') == 'expert' ? 'selected' : '' }}>Expert</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="credits_per_hour" class="form-label text-muted small fw-medium">Hourly Rate (Credits)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-dark border-secondary text-success"><i class="bi bi-wallet2"></i></span>
                                    <input type="number" step="0.5" min="0.5" max="100" class="form-control bg-dark bg-opacity-50 border-secondary text-white focus-ring focus-ring-primary" id="credits_per_hour" name="credits_per_hour" value="{{ old('credits_per_hour', '1.00') }}" required>
                                </div>
                                <div class="form-text small text-secondary">Set a competitive rate to attract learners.</div>
                            </div>
                        </div>

                        <div class="mb-5">
                            <label for="description" class="form-label text-muted small fw-medium">Details (Optional)</label>
                            <textarea class="form-control bg-dark bg-opacity-50 border-secondary text-white focus-ring focus-ring-primary" id="description" name="description" rows="4" placeholder="Describe your teaching style, what specific topics you cover, and any prerequisites...">{{ old('description') }}</textarea>
                        </div>

                        <div class="d-flex justify-content-end gap-3">
                            <a href="{{ route('user-skills.index') }}" class="btn btn-outline-secondary rounded-pill px-4">Cancel</a>
                            <button type="submit" class="btn btn-primary rounded-pill px-5">Publish Listing</button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
