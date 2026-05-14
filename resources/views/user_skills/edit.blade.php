@extends('layouts.app')

@section('title', 'Edit Mentor Listing - SkillSwap')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex align-items-center mb-4">
                <a href="{{ route('user-skills.index') }}" class="btn btn-outline-secondary rounded-circle me-3" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <h3 class="fw-bold mb-0">Edit Listing: {{ $userSkill->skill->title }}</h3>
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

                <form method="POST" action="{{ route('user-skills.update', $userSkill->id) }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-4">
                        <div class="form-check form-switch d-flex align-items-center mb-2">
                            <input class="form-check-input bg-primary bg-opacity-25 border-secondary me-2 mt-0" type="checkbox" role="switch" id="is_active" name="is_active" value="1" {{ old('is_active', $userSkill->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label text-white fw-medium" for="is_active">Listing is Active</label>
                        </div>
                        <div class="form-text small text-secondary ms-5">If inactive, this skill won't appear in search results.</div>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label for="experience_level" class="form-label text-muted small fw-medium">Your Experience Level</label>
                            <select class="form-select bg-dark bg-opacity-50 border-secondary text-white focus-ring focus-ring-primary" id="experience_level" name="experience_level" required>
                                <option value="beginner" {{ old('experience_level', $userSkill->experience_level) == 'beginner' ? 'selected' : '' }}>Beginner (Can teach basics)</option>
                                <option value="intermediate" {{ old('experience_level', $userSkill->experience_level) == 'intermediate' ? 'selected' : '' }}>Intermediate</option>
                                <option value="advanced" {{ old('experience_level', $userSkill->experience_level) == 'advanced' ? 'selected' : '' }}>Advanced</option>
                                <option value="expert" {{ old('experience_level', $userSkill->experience_level) == 'expert' ? 'selected' : '' }}>Expert</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="credits_per_hour" class="form-label text-muted small fw-medium">Hourly Rate (Credits)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-dark border-secondary text-success"><i class="bi bi-wallet2"></i></span>
                                <input type="number" step="0.5" min="0.5" max="100" class="form-control bg-dark bg-opacity-50 border-secondary text-white focus-ring focus-ring-primary" id="credits_per_hour" name="credits_per_hour" value="{{ old('credits_per_hour', $userSkill->credits_per_hour) }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-5">
                        <label for="description" class="form-label text-muted small fw-medium">Details</label>
                        <textarea class="form-control bg-dark bg-opacity-50 border-secondary text-white focus-ring focus-ring-primary" id="description" name="description" rows="4">{{ old('description', $userSkill->description) }}</textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-3">
                        <a href="{{ route('user-skills.index') }}" class="btn btn-outline-secondary rounded-pill px-4">Cancel</a>
                        <button type="submit" class="btn btn-primary rounded-pill px-5">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
