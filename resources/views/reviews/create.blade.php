@extends('layouts.app')

@section('title', 'Leave a Review - SkillSwap')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="d-flex align-items-center mb-4">
                <a href="{{ route('sessions.show', $session->id) }}" class="btn btn-outline-secondary rounded-circle me-3" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <h3 class="fw-bold mb-0">Leave a Review</h3>
            </div>

            <div class="glass-card p-4 p-md-5">
                <div class="text-center mb-5">
<x-avatar :user="$reviewee" size="lg" class="border border-3 border-primary mb-3" />
                    <h5 class="fw-bold text-white mb-1">How was your session with {{ $reviewee->profile->full_name }}?</h5>
                    <p class="text-muted small mb-0">Skill: {{ $session->userSkill->skill->title }}</p>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger bg-danger bg-opacity-10 border-danger text-danger mb-4 py-2">
                        <ul class="mb-0 small ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('reviews.store', $session->id) }}">
                    @csrf
                    
                    <div class="mb-4 text-center">
                        <label class="form-label text-muted small fw-medium mb-3 d-block">Rating</label>
                        <div class="rating-group d-inline-flex flex-row-reverse justify-content-center gap-2">
                            <input type="radio" id="star5" name="rating" value="5" class="d-none" required>
                            <label for="star5" class="fs-1 text-muted cursor-pointer transition-all"><i class="bi bi-star-fill"></i></label>
                            
                            <input type="radio" id="star4" name="rating" value="4" class="d-none">
                            <label for="star4" class="fs-1 text-muted cursor-pointer transition-all"><i class="bi bi-star-fill"></i></label>
                            
                            <input type="radio" id="star3" name="rating" value="3" class="d-none">
                            <label for="star3" class="fs-1 text-muted cursor-pointer transition-all"><i class="bi bi-star-fill"></i></label>
                            
                            <input type="radio" id="star2" name="rating" value="2" class="d-none">
                            <label for="star2" class="fs-1 text-muted cursor-pointer transition-all"><i class="bi bi-star-fill"></i></label>
                            
                            <input type="radio" id="star1" name="rating" value="1" class="d-none">
                            <label for="star1" class="fs-1 text-muted cursor-pointer transition-all"><i class="bi bi-star-fill"></i></label>
                        </div>
                    </div>

                    <div class="mb-5">
                        <label for="comment" class="form-label text-muted small fw-medium">Review (Optional)</label>
                        <textarea name="comment" id="comment" rows="4" class="form-control bg-dark border-secondary text-white focus-ring focus-ring-primary" placeholder="Share your experience... what did you learn, was the mentor helpful?"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 rounded-pill py-3 fw-bold">Submit Review</button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .cursor-pointer {
        cursor: pointer;
    }
    .rating-group input:checked ~ label {
        color: #fbbf24 !important; /* Tailwind yellow-400 */
    }
    .rating-group label:hover,
    .rating-group label:hover ~ label {
        color: #fcd34d !important; /* Tailwind yellow-300 */
    }
</style>
@endsection
