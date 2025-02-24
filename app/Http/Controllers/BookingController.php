<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller
{
    public function showForm()
    {
        return view('bookings.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email',
            'booking_date' => 'required|date',
            'booking_type' => 'required|in:full_day,half_day,custom',
            'booking_slot' => 'nullable|in:first_half,second_half',
            'booking_from' => 'nullable|required_if:booking_type,custom|date_format:H:i',
            'booking_to' => 'nullable|required_if:booking_type,custom|date_format:H:i',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        if ($this->isBookingConflict($request)) {
            return redirect()->back()->with('error', 'Booking conflict detected.')->withInput();
        }

        Booking::create([
            'user_id' => auth()->id(),
            'customer_name' => $request->customer_name,
            'customer_email' => $request->customer_email,
            'booking_date' => $request->booking_date,
            'booking_type' => $request->booking_type,
            'booking_slot' => $request->booking_slot,
            'booking_from' => $request->booking_from,
            'booking_to' => $request->booking_to,
        ]);

        return redirect()->route('dashboard')->with('success', 'Booking created successfully.');
    }

    private function isBookingConflict($request)
    {
        $query = Booking::where('booking_date', $request->booking_date);

        if ($request->booking_type === 'full_day') {
            return $query->exists();
        }

        if ($request->booking_type === 'half_day') {
            return $query->where(function ($q) use ($request) {
                $q->where('booking_type', 'full_day')
                  ->orWhere(function ($subQuery) use ($request) {
                      $subQuery->where('booking_type', 'half_day')
                               ->where('booking_slot', $request->booking_slot);
                  })
                  ->orWhere(function ($subQuery) use ($request) {
                      $subQuery->where('booking_type', 'custom')
                               ->where('booking_from', '<', '12:00');
                  });
            })->exists();
        }

        if ($request->booking_type === 'custom') {
            return $query->where(function ($q) use ($request) {
                $q->where('booking_type', 'full_day')
                  ->orWhere(function ($subQuery) use ($request) {
                      $subQuery->where('booking_type', 'half_day')
                               ->where('booking_slot', 'first_half')
                               ->where('booking_from', '<', '12:00');
                  })
                  ->orWhere(function ($subQuery) use ($request) {
                      $subQuery->where('booking_type', 'custom')
                               ->where('booking_from', '<=', $request->booking_to)
                               ->where('booking_to', '>=', $request->booking_from);
                  });
            })->exists();
        }

        return false;
    }
}
