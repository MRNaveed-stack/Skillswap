@extends('layouts.app')

@section('title', 'Chat with ' . $contact->profile->full_name . ' - SkillSwap')

@section('content')
<div class="container py-4 h-100 d-flex flex-column" style="max-height: calc(100vh - 80px);">
    <div class="row justify-content-center flex-grow-1 h-100">
        <div class="col-lg-8 h-100 d-flex flex-column">
            
            <!-- Chat Header -->
            <div class="glass-card p-3 mb-3 d-flex align-items-center">
                <a href="{{ route('messages.index') }}" class="btn btn-sm btn-outline-secondary rounded-circle me-3" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                    <i class="bi bi-arrow-left"></i>
                </a>
<x-avatar :user="$contact" size="sm" class="border border-secondary me-3" />
                <div>
                    <h5 class="fw-bold mb-0 text-white">{{ $contact->profile->full_name }}</h5>
                    <span class="small text-muted"><i class="bi bi-clock me-1"></i>{{ $contact->profile->timezone }}</span>
                </div>
            </div>

            <!-- Messages Area -->
            <div class="glass-card flex-grow-1 p-4 overflow-auto mb-3" id="chatContainer" style="min-height: 400px; display: flex; flex-direction: column;">
                @if($messages->isEmpty())
                    <div class="text-center my-auto">
                        <i class="bi bi-chat-dots fs-2 text-muted mb-2 d-block opacity-50"></i>
                        <p class="text-secondary small">This is the beginning of your conversation with {{ $contact->profile->full_name }}.</p>
                    </div>
                @else
                    @php $currentDate = null; @endphp

                    @foreach($messages as $msg)
                        @php
                            $msgDate = $msg->created_at->format('M d, Y');
                        @endphp

                        @if($currentDate !== $msgDate)
                            <div class="text-center my-3">
                                <span class="badge bg-dark border border-secondary text-muted small fw-normal">{{ $msgDate }}</span>
                            </div>
                            @php $currentDate = $msgDate; @endphp
                        @endif

                        @if($msg->sender_id === Auth::id())
                            <!-- Sent Message -->
                            <div class="d-flex justify-content-end mb-3">
                                <div class="text-end">
                                    <div class="d-inline-block bg-primary text-white p-3 rounded-4 rounded-bottom-end-0 shadow-sm text-start" style="max-width: 80%;">
                                        <p class="mb-0 small" style="line-height: 1.5;">{{ $msg->content }}</p>
                                    </div>
                                    <div class="small text-muted mt-1 me-1" style="font-size: 0.7rem;">
                                        {{ $msg->created_at->format('H:i') }}
                                        @if($msg->is_read)
                                            <i class="bi bi-check2-all text-primary ms-1"></i>
                                        @else
                                            <i class="bi bi-check2 ms-1"></i>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @else
                            <!-- Received Message -->
                            <div class="d-flex justify-content-start mb-3">
<x-avatar :user="$msg->sender" size="sm" class="border border-secondary me-2 align-self-end mb-4" />
                                <div>
                                    <div class="d-inline-block bg-dark border border-secondary text-light p-3 rounded-4 rounded-bottom-start-0 shadow-sm" style="max-width: 80%;">
                                        <p class="mb-0 small" style="line-height: 1.5;">{{ $msg->content }}</p>
                                    </div>
                                    <div class="small text-muted mt-1 ms-2" style="font-size: 0.7rem;">
                                        {{ $msg->created_at->format('H:i') }}
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                @endif
            </div>

            <!-- Input Area -->
            <div class="glass-card p-3">
                <form action="{{ route('messages.store') }}" method="POST" class="d-flex gap-2">
                    @csrf
                    <input type="hidden" name="receiver_id" value="{{ $contact->id }}">
                    <input type="text" name="content" class="form-control bg-dark bg-opacity-50 border-secondary text-white focus-ring focus-ring-primary rounded-pill px-4" placeholder="Type your message..." required autocomplete="off">
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm d-flex align-items-center justify-content-center" style="width: 60px;">
                        <i class="bi bi-send-fill"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        var chatContainer = document.getElementById("chatContainer");
        chatContainer.scrollTop = chatContainer.scrollHeight;
        
        // MVP Real-time polling
        setInterval(function() {
            fetch(window.location.href)
                .then(response => response.text())
                .then(html => {
                    var parser = new DOMParser();
                    var doc = parser.parseFromString(html, "text/html");
                    var newChatHtml = doc.getElementById("chatContainer").innerHTML;
                    
                    // Only update and scroll if new content exists
                    if (chatContainer.innerHTML !== newChatHtml) {
                        var isScrolledToBottom = chatContainer.scrollHeight - chatContainer.clientHeight <= chatContainer.scrollTop + 50;
                        chatContainer.innerHTML = newChatHtml;
                        if (isScrolledToBottom) {
                            chatContainer.scrollTop = chatContainer.scrollHeight;
                        }
                    }
                });
        }, 3000);
    });
</script>
@endsection
