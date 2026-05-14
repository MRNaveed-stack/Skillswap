<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        $user->load(['profile', 'wallet']);
        return view('profile.show', compact('user'));
    }

    public function edit()
    {
        $user = Auth::user();
        $user->load(['profile']);
        return view('profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'full_name' => ['required', 'string', 'max:100'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'timezone' => ['required', 'string', 'timezone'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048', 'dimensions:ratio=1/1'],
            'resume' => ['nullable', 'mimes:pdf,doc,docx', 'max:5120'],
            'linkedin_url' => ['nullable', 'url', 'max:500'],
            'portfolio_url' => ['nullable', 'url', 'max:500'],
        ]);

        $user = Auth::user();
        
        $profileData = [
            'full_name' => $request->full_name,
            'bio' => $request->bio,
            'timezone' => $request->timezone,
            'linkedin_url' => $request->linkedin_url,
            'portfolio_url' => $request->portfolio_url,
        ];

        // Handle avatar upload with validation
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            
            // Verify file is actually an image
            if ($file->isValid() && in_array($file->getMimeType(), ['image/jpeg', 'image/png', 'image/gif', 'image/webp'])) {
                // Delete old avatar if exists
                if ($user->profile->avatar_url) {
                    @\Storage::disk('public')->delete(str_replace('/storage/', '', $user->profile->avatar_url));
                }
                
                $path = $file->store('avatars', 'public');
                if ($path) {
                    $profileData['avatar_url'] = '/storage/' . $path;
                }
            }
        }

        // Handle resume upload with validation
        if ($request->hasFile('resume')) {
            $file = $request->file('resume');
            
            if ($file->isValid() && in_array($file->getMimeType(), ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])) {
                // Delete old resume if exists
                if ($user->profile->resume_url) {
                    @\Storage::disk('public')->delete(str_replace('/storage/', '', $user->profile->resume_url));
                }
                
                $resumePath = $file->store('resumes', 'public');
                if ($resumePath) {
                    $profileData['resume_url'] = '/storage/' . $resumePath;
                }
            }
        }

        $user->profile->update($profileData);

        return redirect()->route('profile.show')->with('success', 'Profile updated successfully.');
    }
}
