<?php

namespace App\Http\Controllers;

use App\Models\AvailabilitySlot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AvailabilityController extends Controller
{
    public function index()
    {
        $slots = Auth::user()->availabilitySlots()->orderBy('day_of_week')->orderBy('start_time')->get();
        
        $days = [
            0 => 'Sunday',
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
        ];

        return view('availability.index', compact('slots', 'days'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'day_of_week' => ['required', 'integer', 'between:0,6'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
        ]);

        Auth::user()->availabilitySlots()->create([
            'day_of_week' => $request->day_of_week,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'is_recurring' => true,
        ]);

        return redirect()->route('availability.index')->with('success', 'Availability slot added!');
    }

    public function destroy($id)
    {
        $slot = Auth::user()->availabilitySlots()->findOrFail($id);
        $slot->delete();

        return redirect()->route('availability.index')->with('success', 'Availability slot removed.');
    }
}
