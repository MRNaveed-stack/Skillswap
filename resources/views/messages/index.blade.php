@extends('layouts.app')

@section('title', 'Messages - SkillSwap')

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <h2 class="fw-bold mb-1">Messages</h2>
            <p class="text-muted mb-0">Your conversations with mentors and learners.</p>
        </div>
    </div>

    @if($contacts->isEmpty())
        <div class="glass-card p-5 text-center my-5">
            <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-secondary bg-opacity-25 text-muted mb-4" style="width: 80px; height: 80px;">
                <i class="bi bi-chat-square-dots fs-1"></i>
            </div>
            <h4 class="fw-bold mb-2">No Conversations Yet</h4>
            <p class="text-secondary mx-auto" style="max-width: 500px;">
                When you request a session or someone requests one with you, your messages will appear here.
            </p>
        </div>
    @else
        <div class="row">
            <div class="col-lg-8">
                <div class="glass-card overflow-hidden">
                    <div class="list-group list-group-flush border-0">
                        @foreach($contacts as $contact)
                            <a href="{{ route('messages.show', $contact->id) }}" class="list-group-item list-group-item-action bg-transparent p-4 transition-all hover-highlight border-bottom border-secondary position-relative">
                                
                                @if($contact->unreadCount > 0)
                                    <div class="position-absolute top-50 start-0 translate-middle-y ms-2">
                                        <span class="badge bg-primary rounded-circle p-2 border border-dark">
                                            <span class="visually-hidden">New alerts</span>
                                        </span>
                                    </div>
                                @endif

                                <div class="d-flex align-items-center {{ $contact->unreadCount > 0 ? 'ps-3' : '' }}">
<x-avatar :user="$contact" size="md" class="border border-secondary me-3 flex-shrink-0" />
                                    
                                    <div class="flex-grow-1 min-w-0">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <h6 class="fw-bold text-white mb-0 text-truncate pe-2 {{ $contact->unreadCount > 0 ? 'text-primary' : '' }}">{{ $contact->profile->full_name }}</h6>
                                            @if($contact->latestMessage)
                                                <small class="text-muted flex-shrink-0" style="font-size: 0.75rem;">
                                                    {{ $contact->latestMessage->created_at->isToday() ? $contact->latestMessage->created_at->format('H:i') : $contact->latestMessage->created_at->format('M d') }}
                                                </small>
                                            @endif
                                        </div>
                                        
                                        @if($contact->latestMessage)
                                            <p class="mb-0 small text-truncate {{ $contact->unreadCount > 0 ? 'text-light fw-medium' : 'text-muted' }}">
                                                @if($contact->latestMessage->sender_id === Auth::id())
                                                    <i class="bi bi-reply text-secondary me-1"></i>
                                                @endif
                                                {{ $contact->latestMessage->content }}
                                            </p>
                                        @else
                                            <p class="mb-0 small text-muted fst-italic">Say hello!</p>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<style>
    .hover-highlight:hover {
        background: rgba(255,255,255,0.03) !important;
    }
</style>
@endsection
