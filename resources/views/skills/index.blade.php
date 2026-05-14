@extends('layouts.app')

@section('title', 'Find Skills - SkillSwap')

@section('content')
<div class="container py-5">
    <div class="text-center mb-5 mt-4">
        <h1 class="fw-bold display-5">Explore <span class="text-primary">Skills</span></h1>
        <p class="text-muted fs-5">Discover what you can learn from our community of mentors.</p>
    </div>

    <!-- Search and Filter Bar -->
    <div class="glass-card p-4 mb-5">
        <form action="{{ route('skills.index') }}" method="GET" class="row g-3 align-items-center">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-dark border-secondary text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control bg-dark border-secondary text-white focus-ring focus-ring-primary" placeholder="Search for a skill (e.g., Python, React, Guitar)...">
                </div>
            </div>
            <div class="col-md-4">
                <select name="category" class="form-select bg-dark border-secondary text-white focus-ring focus-ring-primary">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->slug }}" {{ request('category') == $category->slug ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100 rounded-pill">Filter</button>
            </div>
        </form>
    </div>

    <!-- Skills Grid -->
    @if($skills->isEmpty())
        <div class="text-center py-5 glass-card">
            <i class="bi bi-search fs-1 text-muted mb-3 d-block opacity-50"></i>
            <h4 class="text-muted">No skills found.</h4>
            <p class="text-secondary">Try adjusting your search terms or category filter.</p>
            <a href="{{ route('skills.index') }}" class="btn btn-outline-light rounded-pill mt-2">Clear Filters</a>
        </div>
    @else
        <div class="row g-4">
            @foreach($skills as $skill)
                <div class="col-md-6 col-lg-4">
                    <div class="glass-card p-4 h-100 transition-all hover-lift d-flex flex-column">
                        <div class="mb-3 d-flex justify-content-between align-items-start">
                            <span class="badge bg-primary bg-opacity-25 text-primary rounded-pill px-3 py-2 border border-primary border-opacity-25">
                                {{ $skill->category->name }}
                            </span>
                        </div>
                        <h4 class="fw-bold mb-2">{{ $skill->title }}</h4>
                        <p class="text-muted small flex-grow-1">
                            {{ Str::limit($skill->description, 100) }}
                        </p>
                        <div class="mt-4 pt-3 border-top border-secondary d-flex justify-content-between align-items-center">
                            <a href="{{ route('skills.show', $skill->slug) }}" class="btn btn-sm btn-outline-primary rounded-pill w-100">
                                View Mentors <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-5 d-flex justify-content-center">
            {{ $skills->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>

<style>
    .hover-lift {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .hover-lift:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(99, 102, 241, 0.15) !important;
        border-color: rgba(99, 102, 241, 0.3);
    }
</style>
@endsection
