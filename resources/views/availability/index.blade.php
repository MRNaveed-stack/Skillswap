@extends('layouts.app')

@section('title', 'My Availability - SkillSwap')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-lg-4 mb-4 mb-lg-0">
            <div class="glass-card p-4">
                <h4 class="fw-bold mb-3">Add Availability</h4>
                <p class="text-muted small mb-4">Set your recurring weekly schedule. Learners can only request sessions during these windows.</p>

                @if ($errors->any())
                    <div class="alert alert-danger bg-danger bg-opacity-10 border-danger text-danger mb-4 py-2">
                        <ul class="mb-0 small ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('availability.store') }}">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-medium">Day of Week</label>
                        <select name="day_of_week" class="form-select bg-dark border-secondary text-white focus-ring focus-ring-primary" required>
                            @foreach($days as $index => $day)
                                <option value="{{ $index }}">{{ $day }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row g-2 mb-4">
                        <div class="col-6">
                            <label class="form-label text-muted small fw-medium">Start Time</label>
                            <input type="time" name="start_time" class="form-control bg-dark border-secondary text-white focus-ring focus-ring-primary" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label text-muted small fw-medium">End Time</label>
                            <input type="time" name="end_time" class="form-control bg-dark border-secondary text-white focus-ring focus-ring-primary" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 rounded-pill">Add Slot</button>
                </form>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold m-0">Your Schedule</h3>
            </div>

            @if(session('success'))
                <div class="alert alert-success bg-success bg-opacity-10 border-success text-success mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if($slots->isEmpty())
                <div class="glass-card p-5 text-center">
                    <i class="bi bi-calendar-x fs-1 text-muted mb-3 d-block opacity-50"></i>
                    <h5 class="text-white">No availability set</h5>
                    <p class="text-muted">You won't receive any session requests until you add some availability slots.</p>
                </div>
            @else
                <div class="row g-3">
                    @foreach($days as $index => $day)
                        @php
                            $daySlots = $slots->where('day_of_week', $index);
                        @endphp
                        
                        @if($daySlots->isNotEmpty())
                            <div class="col-12">
                                <div class="glass-card p-3 d-flex flex-column flex-md-row gap-3 align-items-md-center">
                                    <div class="fw-bold text-primary" style="width: 120px;">
                                        {{ $day }}
                                    </div>
                                    <div class="d-flex flex-wrap gap-2 flex-grow-1">
                                        @foreach($daySlots as $slot)
                                            <div class="badge bg-dark border border-secondary p-2 d-flex align-items-center gap-2 text-light">
                                                <i class="bi bi-clock text-muted"></i>
                                                {{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}
                                                
                                                <form action="{{ route('availability.destroy', $slot->id) }}" method="POST" class="ms-2 d-inline" onsubmit="return confirm('Remove this slot?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn-close btn-close-white" style="font-size: 0.5rem;" aria-label="Remove"></button>
                                                </form>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
