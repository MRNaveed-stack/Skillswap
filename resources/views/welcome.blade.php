@extends('layouts.app')

@section('title', 'SkillSwap - The Ultimate Peer-to-Peer Learning Platform')

@section('content')
<div class="position-relative overflow-hidden">
    <!-- Background glowing orbs -->
    <div class="position-absolute rounded-circle" style="width: 500px; height: 500px; background: radial-gradient(circle, rgba(99,102,241,0.15) 0%, rgba(0,0,0,0) 70%); top: -100px; left: -100px; z-index: -1;"></div>
    <div class="position-absolute rounded-circle" style="width: 600px; height: 600px; background: radial-gradient(circle, rgba(168,85,247,0.1) 0%, rgba(0,0,0,0) 70%); bottom: -200px; right: -100px; z-index: -1;"></div>

    <div class="container py-5 my-5">
        <div class="row align-items-center min-vh-75 py-5">
            <div class="col-lg-6 text-center text-lg-start z-1">
                <h1 class="display-3 fw-bold mb-4 lh-sm">
                    Master new skills.<br>
                    <span style="background: linear-gradient(90deg, #6366f1, #a855f7); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Teach what you know.</span>
                </h1>
                
                <p class="lead text-secondary mb-5 fs-4" style="max-width: 500px; margin-left: auto; margin-right: auto;">
                    Join our marketplace where learning has no limits. Trade your expertise for new abilities using our credit-based system.
                </p>
                
                <div class="d-flex flex-column flex-sm-row justify-content-center justify-content-lg-start gap-3">
                    <a href="{{ route('skills.index') }}" class="btn btn-primary btn-lg rounded-pill px-5 shadow-lg d-flex align-items-center justify-content-center">
                        Explore Skills <i class="bi bi-arrow-right ms-2"></i>
                    </a>
                    @auth
                        <a href="{{ route('user-skills.index') }}" class="btn btn-outline-light btn-lg rounded-pill px-5 d-flex align-items-center justify-content-center" style="border-color: rgba(255,255,255,0.2);">
                            Mentor Dashboard
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="btn btn-outline-light btn-lg rounded-pill px-5 d-flex align-items-center justify-content-center" style="border-color: rgba(255,255,255,0.2);">
                            Become a Mentor
                        </a>
                    @endauth
                </div>
                
                <div class="mt-5 d-flex align-items-center justify-content-center justify-content-lg-start gap-4">
                    <div>
                        <a href="{{ route('skills.index') }}" class="btn btn-outline-light rounded-pill px-4" style="border-color: rgba(255,255,255,0.2);">Browse All Skills</a>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6 mt-5 mt-lg-0 position-relative">
                <!-- Floating Elements Container -->
                <div class="position-relative w-100" style="height: 500px;">
                    <!-- Mentor Card Example -->
                    <div class="glass-card p-4 position-absolute shadow-lg" style="width: 320px; top: 10%; right: 10%; animation: float 6s ease-in-out infinite;">
                        <div class="d-flex align-items-center mb-3">
                            <div class="rounded-circle bg-primary bg-opacity-25 d-flex align-items-center justify-content-center text-primary fw-bold me-3" style="width: 50px; height: 50px; font-size: 18px;">
                                SJ
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold">Example Mentor</h6>
                                <p class="text-muted small mb-0">Expert Skill Area</p>
                            </div>
                            <div class="ms-auto text-warning">
                                <i class="bi bi-star-fill"></i> 4.8
                            </div>
                        </div>
                        <div class="p-3 rounded mb-3" style="background: rgba(0,0,0,0.2);">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="small text-muted">Rate</span>
                                <span class="fw-bold text-success">2.5 Credits/hr</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="small text-muted">Availability</span>
                                <span class="badge bg-success bg-opacity-25 text-success">Available</span>
                            </div>
                        </div>
                        <button class="btn btn-sm btn-primary w-100 rounded-pill" disabled>Sign in to request</button>
                    </div>

                    <!-- Secondary Card 1 -->
                    <div class="glass-card p-3 position-absolute shadow" style="width: 250px; bottom: 15%; left: 5%; animation: float 5s ease-in-out infinite reverse;">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle bg-primary bg-opacity-25 d-flex align-items-center justify-content-center text-primary" style="width: 45px; height: 45px;">
                                <i class="bi bi-wallet2 fs-5"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold text-white">+5.00 Credits</h6>
                                <p class="text-success small mb-0">Session completed</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Decorative Icon -->
                    <div class="position-absolute glass-card d-flex align-items-center justify-content-center text-info shadow-lg" style="width: 80px; height: 80px; top: 5%; left: 15%; border-radius: 24px; transform: rotate(-10deg); animation: pulse 4s infinite;">
                        <i class="bi bi-code-slash display-5"></i>
                    </div>
                    
                    <div class="position-absolute glass-card d-flex align-items-center justify-content-center text-warning shadow-lg" style="width: 70px; height: 70px; bottom: 25%; right: 5%; border-radius: 20px; transform: rotate(15deg); animation: pulse 5s infinite 1s;">
                        <i class="bi bi-brush display-6"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Features Section -->
<div class="container py-5">
    <div class="text-center mb-5">
        <h2 class="fw-bold">How <span class="text-primary">SkillSwap</span> Works</h2>
        <p class="text-muted">A fair, decentralized system to exchange knowledge.</p>
    </div>
    
    <div class="row g-4">
        <div class="col-md-4">
            <div class="glass-card p-4 h-100 text-center transition-all hover-lift">
                <div class="rounded-circle bg-primary bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-4" style="width: 80px; height: 80px;">
                    <i class="bi bi-search fs-1 text-primary"></i>
                </div>
                <h4 class="fw-bold mb-3">1. Find a Mentor</h4>
                <p class="text-muted mb-0">Search through verified experts across various disciplines and connect with mentors matching your learning goals.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="glass-card p-4 h-100 text-center transition-all hover-lift">
                <div class="rounded-circle bg-info bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-4" style="width: 80px; height: 80px;">
                    <i class="bi bi-camera-video fs-1 text-info"></i>
                </div>
                <h4 class="fw-bold mb-3">2. Learn 1-on-1</h4>
                <p class="text-muted mb-0">Book a session and connect securely. Credits are held in escrow and released only when you're satisfied with the lesson.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="glass-card p-4 h-100 text-center transition-all hover-lift">
                <div class="rounded-circle bg-success bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-4" style="width: 80px; height: 80px;">
                    <i class="bi bi-arrow-repeat fs-1 text-success"></i>
                </div>
                <h4 class="fw-bold mb-3">3. Earn by Teaching</h4>
                <p class="text-muted mb-0">Use the skills you already have to mentor others. Earn credits to spend on learning something completely new.</p>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<style>
    @keyframes float {
        0% { transform: translateY(0px); }
        50% { transform: translateY(-20px); }
        100% { transform: translateY(0px); }
    }
    
    @keyframes pulse {
        0% { transform: scale(1) rotate(-10deg); box-shadow: 0 0 0 0 rgba(99, 102, 241, 0.4); }
        50% { transform: scale(1.05) rotate(-5deg); box-shadow: 0 0 20px 0 rgba(99, 102, 241, 0.2); }
        100% { transform: scale(1) rotate(-10deg); box-shadow: 0 0 0 0 rgba(99, 102, 241, 0); }
    }
    
    .hover-lift {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .hover-lift:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.3) !important;
        border-color: rgba(99, 102, 241, 0.3);
    }
</style>
@endsection
