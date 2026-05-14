@extends('layouts.app')

@section('title', 'Request Session - SkillSwap')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex align-items-center mb-4">
                <a href="{{ route('skills.show', $userSkill->skill->slug) }}" class="btn btn-outline-secondary rounded-circle me-3" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <h3 class="fw-bold mb-0">Request a Session</h3>
            </div>

            <div class="glass-card p-4 p-md-5">
                <!-- Mentor Info Header -->
                <div class="d-flex align-items-center pb-4 border-bottom border-secondary mb-4">
<x-avatar :user="$userSkill->user" size="md" class="border border-primary me-3" />
                    <div>
                        <h5 class="fw-bold mb-1">{{ $userSkill->user->profile->full_name }}</h5>
                        <p class="text-muted small mb-0">Mentoring you in: <strong class="text-primary">{{ $userSkill->skill->title }}</strong></p>
                    </div>
                    <div class="ms-auto text-end">
                        <h4 class="fw-bold text-success mb-0">{{ number_format($userSkill->credits_per_hour, 2) }} <span class="fs-6 text-muted fw-normal">CR/hr</span></h4>
                    </div>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger bg-danger bg-opacity-10 border-danger text-danger mb-4">
                        <ul class="mb-0 small">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('session-requests.store') }}">
                    @csrf
                    <input type="hidden" name="user_skill_id" value="{{ $userSkill->id }}">
                    
                    <!-- Mentors Availability Reference -->
                    <div class="mb-4 p-3 rounded bg-dark border border-secondary">
                        <h6 class="fw-bold text-light mb-2"><i class="bi bi-calendar-check me-2 text-info"></i>Mentor's Weekly Availability</h6>
                        <div class="row g-2">
                            @if($userSkill->user->availabilitySlots->isEmpty())
                                <div class="col-12 text-warning small">This mentor hasn't set up structured availability yet. Propose a time and see if they accept!</div>
                            @else
                                @php
                                    $days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                                @endphp
                                @foreach($userSkill->user->availabilitySlots->groupBy('day_of_week') as $dayIdx => $slots)
                                    <div class="col-md-6 col-lg-4">
                                        <div class="small text-muted">
                                            <strong class="text-light">{{ $days[$dayIdx] }}:</strong> 
                                            @foreach($slots as $slot)
                                                {{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }}-{{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}@if(!$loop->last), @endif
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label for="proposed_start" class="form-label text-muted small fw-medium">Proposed Date & Time</label>
                            <input type="datetime-local" class="form-control bg-dark bg-opacity-50 border-secondary text-white focus-ring focus-ring-primary" id="proposed_start" name="proposed_start" value="{{ old('proposed_start') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="duration_hours" class="form-label text-muted small fw-medium">Duration</label>
                            <select class="form-select bg-dark bg-opacity-50 border-secondary text-white focus-ring focus-ring-primary" id="duration_hours" name="duration_hours" required onchange="calculateTotal()">
                                <option value="0.5" {{ old('duration_hours') == '0.5' ? 'selected' : '' }}>30 Minutes</option>
                                <option value="1" {{ old('duration_hours', '1') == '1' ? 'selected' : '' }}>1 Hour</option>
                                <option value="1.5" {{ old('duration_hours') == '1.5' ? 'selected' : '' }}>1.5 Hours</option>
                                <option value="2" {{ old('duration_hours') == '2' ? 'selected' : '' }}>2 Hours</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-5">
                        <label for="learner_message" class="form-label text-muted small fw-medium">Message to Mentor</label>
                        <textarea class="form-control bg-dark bg-opacity-50 border-secondary text-white focus-ring focus-ring-primary" id="learner_message" name="learner_message" rows="4" placeholder="Briefly describe what you'd like to cover in this session... (e.g. 'I need help debugging a React component')">{{ old('learner_message') }}</textarea>
                    </div>

                    <!-- Checkout Summary -->
                    <div class="p-3 mb-4 rounded bg-primary bg-opacity-10 border border-primary border-opacity-25 d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small d-block">Estimated Cost</span>
                            <span class="text-muted small">Your Balance: {{ number_format(Auth::user()->wallet->balance, 2) }} CR</span>
                        </div>
                        <h3 class="fw-bold text-white m-0" id="total_cost">0.00 CR</h3>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 rounded-pill py-3 fw-bold">Send Session Request</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    const rate = {{ $userSkill->credits_per_hour }};
    
    function calculateTotal() {
        const duration = document.getElementById('duration_hours').value;
        const total = rate * duration;
        document.getElementById('total_cost').innerText = total.toFixed(2) + ' CR';
    }

    // Run on load
    document.addEventListener('DOMContentLoaded', calculateTotal);
</script>
@endsection
