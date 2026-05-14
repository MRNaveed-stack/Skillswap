@props(['user', 'size' => 'md', 'class' => ''])

@php
    $sizeMap = [
        'sm' => 30,
        'md' => 50,
        'lg' => 80,
        'xl' => 120,
    ];
    $dimension = $sizeMap[$size] ?? 50;
    
    $initials = strtoupper(
        substr($user->profile->full_name ?? 'U', 0, 1) . 
        (strpos($user->profile->full_name ?? 'U', ' ') !== false 
            ? substr($user->profile->full_name, strpos($user->profile->full_name, ' ') + 1, 1) 
            : '')
    );
    
    $colors = [
        'bg-primary', 'bg-info', 'bg-success', 'bg-warning', 'bg-danger', 
        'bg-secondary', 'bg-indigo', 'bg-cyan'
    ];
    $colorIndex = (crc32($user->id) % count($colors));
    $bgColor = $colors[$colorIndex];
@endphp

<div class="{{ $class }}">
    @if($user->profile->avatar_url)
        <img src="{{ $user->profile->avatar_url }}" 
             alt="{{ $user->profile->full_name }}" 
             class="rounded-circle"
             width="{{ $dimension }}" 
             height="{{ $dimension }}"
             style="object-fit: cover;">
    @else
        <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold {{ $bgColor }}"
             style="width: {{ $dimension }}px; height: {{ $dimension }}px; font-size: calc({{ $dimension }}px / 2.5);">
            {{ $initials }}
        </div>
    @endif
</div>
