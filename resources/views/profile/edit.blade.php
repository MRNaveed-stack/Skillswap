@extends('layouts.app')

@section('title', 'Edit Profile - SkillSwap')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex align-items-center mb-4">
                <a href="{{ route('profile.show') }}" class="btn btn-outline-secondary rounded-circle me-3" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <h3 class="fw-bold mb-0">Edit Profile</h3>
            </div>

            <div class="glass-card p-4 p-md-5">
                @if ($errors->any())
                    <div class="alert alert-danger bg-danger bg-opacity-10 border-danger text-danger">
                        <ul class="mb-0 small">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="mb-4 text-center">
<x-avatar :user="$user" size="lg" class="border border-2 border-primary mb-3" />
                        <div class="mx-auto" style="max-width: 300px;">
                            <input type="file" name="avatar" class="form-control form-control-sm bg-dark text-white border-secondary focus-ring focus-ring-primary" accept="image/*">
                            <div class="form-text small text-secondary">Optional: Upload a new profile picture. Max 2MB.</div>
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label for="full_name" class="form-label text-muted small fw-medium">Full Name</label>
                            <input type="text" class="form-control bg-dark bg-opacity-50 border-secondary text-white focus-ring focus-ring-primary" id="full_name" name="full_name" value="{{ old('full_name', $user->profile->full_name) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-medium">Email Address</label>
                            <input type="email" class="form-control bg-dark border-secondary text-muted" value="{{ $user->email }}" disabled readonly>
                            <div class="form-text small text-secondary">Email cannot be changed directly.</div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="timezone" class="form-label text-muted small fw-medium">Timezone</label>
                        <select class="form-select bg-dark bg-opacity-50 border-secondary text-white focus-ring focus-ring-primary" id="timezone" name="timezone" required>
                            <option value="UTC" {{ old('timezone', $user->profile->timezone) == 'UTC' ? 'selected' : '' }}>UTC</option>
                            <option value="America/New_York" {{ old('timezone', $user->profile->timezone) == 'America/New_York' ? 'selected' : '' }}>Eastern Time (ET)</option>
                            <option value="America/Chicago" {{ old('timezone', $user->profile->timezone) == 'America/Chicago' ? 'selected' : '' }}>Central Time (CT)</option>
                            <option value="America/Denver" {{ old('timezone', $user->profile->timezone) == 'America/Denver' ? 'selected' : '' }}>Mountain Time (MT)</option>
                            <option value="America/Los_Angeles" {{ old('timezone', $user->profile->timezone) == 'America/Los_Angeles' ? 'selected' : '' }}>Pacific Time (PT)</option>
                            <option value="Europe/London" {{ old('timezone', $user->profile->timezone) == 'Europe/London' ? 'selected' : '' }}>London (GMT/BST)</option>
                            <option value="Asia/Tokyo" {{ old('timezone', $user->profile->timezone) == 'Asia/Tokyo' ? 'selected' : '' }}>Tokyo (JST)</option>
                            <!-- More options would go here in a real app -->
                        </select>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label for="linkedin_url" class="form-label text-muted small fw-medium">LinkedIn Profile (Optional)</label>
                            <input type="url" class="form-control bg-dark bg-opacity-50 border-secondary text-white focus-ring focus-ring-primary" id="linkedin_url" name="linkedin_url" value="{{ old('linkedin_url', $user->profile->linkedin_url) }}" placeholder="https://linkedin.com/in/...">
                        </div>
                        <div class="col-md-6">
                            <label for="portfolio_url" class="form-label text-muted small fw-medium">Portfolio Website (Optional)</label>
                            <input type="url" class="form-control bg-dark bg-opacity-50 border-secondary text-white focus-ring focus-ring-primary" id="portfolio_url" name="portfolio_url" value="{{ old('portfolio_url', $user->profile->portfolio_url) }}" placeholder="https://...">
                        </div>
                    </div>

                    <div class="mb-4 bg-primary bg-opacity-10 border border-primary border-opacity-25 p-3 rounded">
                        <label for="resume" class="form-label text-white small fw-bold"><i class="bi bi-file-earmark-person me-1"></i> Upload Resume (Required to be a Mentor)</label>
                        <input type="file" class="form-control bg-dark text-white border-secondary focus-ring focus-ring-primary" id="resume" name="resume" accept=".pdf,.doc,.docx">
                        <div class="form-text small text-info mt-2">
                            @if($user->profile->resume_url)
                                <i class="bi bi-check-circle-fill text-success me-1"></i> You have a resume on file. Uploading a new one will replace it.
                            @else
                                Please upload your resume in PDF or Word format (Max 5MB).
                            @endif
                        </div>
                    </div>

                    <div class="mb-5">
                        <label for="bio" class="form-label text-muted small fw-medium">About Me (Bio)</label>
                        <textarea class="form-control bg-dark bg-opacity-50 border-secondary text-white focus-ring focus-ring-primary" id="bio" name="bio" rows="5" placeholder="Tell others about your experience, what you can teach, and what you want to learn...">{{ old('bio', $user->profile->bio) }}</textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-3">
                        <a href="{{ route('profile.show') }}" class="btn btn-outline-secondary rounded-pill px-4">Cancel</a>
                        <button type="submit" class="btn btn-primary rounded-pill px-5">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
