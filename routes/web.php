<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;

Route::get('/', function () {
    return view('welcome');
});

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Profile Routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile/edit', [ProfileController::class, 'update'])->name('profile.update');

    // Mentorship (User Skills) Dashboard
    Route::resource('user-skills', App\Http\Controllers\UserSkillController::class)->except(['show']);

    // Availability
    Route::get('/availability', [App\Http\Controllers\AvailabilityController::class, 'index'])->name('availability.index');
    Route::post('/availability', [App\Http\Controllers\AvailabilityController::class, 'store'])->name('availability.store');
    Route::delete('/availability/{id}', [App\Http\Controllers\AvailabilityController::class, 'destroy'])->name('availability.destroy');

    // Session Requests
    Route::get('/requests', [App\Http\Controllers\SessionRequestController::class, 'index'])->name('session-requests.index');
    Route::get('/requests/create', [App\Http\Controllers\SessionRequestController::class, 'create'])->name('session-requests.create');
    Route::post('/requests', [App\Http\Controllers\SessionRequestController::class, 'store'])->name('session-requests.store');
    Route::put('/requests/{id}', [App\Http\Controllers\SessionRequestController::class, 'update'])->name('session-requests.update');

    // Confirmed Sessions
    Route::get('/sessions', [App\Http\Controllers\SessionController::class, 'index'])->name('sessions.index');
    Route::get('/sessions/{id}', [App\Http\Controllers\SessionController::class, 'show'])->name('sessions.show');
    Route::put('/sessions/{id}', [App\Http\Controllers\SessionController::class, 'update'])->name('sessions.update');

    // Notifications
    Route::post('/notifications/{id}/read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');

    // Messages
    Route::get('/messages', [App\Http\Controllers\MessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/{id}', [App\Http\Controllers\MessageController::class, 'show'])->name('messages.show');
    Route::post('/messages', [App\Http\Controllers\MessageController::class, 'store'])->name('messages.store');

    // Reviews
    Route::get('/sessions/{id}/review', [App\Http\Controllers\ReviewController::class, 'create'])->name('reviews.create');
    Route::post('/sessions/{id}/review', [App\Http\Controllers\ReviewController::class, 'store'])->name('reviews.store');
});

// Marketplace (Find Skills)
Route::get('/skills', [App\Http\Controllers\SkillController::class, 'index'])->name('skills.index');
Route::get('/skills/{slug}', [App\Http\Controllers\SkillController::class, 'show'])->name('skills.show');

// Browse Mentors
Route::get('/mentors', [App\Http\Controllers\MentorController::class, 'index'])->name('mentors.index');
Route::get('/mentors/ranked', [App\Http\Controllers\MentorController::class, 'ranked'])->name('mentors.ranked');
Route::get('/mentors/recommendations', [App\Http\Controllers\MentorController::class, 'recommendations'])->name('mentors.recommendations');
