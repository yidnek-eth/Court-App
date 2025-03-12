<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservation; // Import the Reservation model
use App\Models\PackagePurchase; // Import the PackagePurchase model
use Carbon\Carbon; // Import Carbon for date manipulation
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ReservationController extends Controller
{
    public function create()
    {
        $user = auth()->user();

        // Fetch the latest package purchase for the user
        $latestPurchase = PackagePurchase::where('user_id', $user->id)
            ->latest('created_at')
            ->first();

        // Calculate the end date of the subscription
        $endDate = $latestPurchase ? Carbon::parse($latestPurchase->end_date) : null;

        return view('reservation.create', [
           // 'endDate' => $endDate,
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        // Validate the request
        $request->validate([
            'reservation_date' => 'required|date|after_or_equal:today',
            'time_slot' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        // Check for existing reservation conflict for the given date and time slot
        $existingReservation = Reservation::where('reservation_date', $request->reservation_date)
            ->where('time_slot', $request->time_slot)
            ->exists(); // Return true if any reservation exists

        if ($existingReservation) {
            // Flash an error message if a reservation already exists
            return redirect()->back()->with('error', 'The selected time slot is already taken. Please choose another one.');
        }

        // Save the reservation to the database if no conflict
        Reservation::create([
            'user_id' => auth()->id(),
            'reservation_date' => $request->reservation_date,
            'time_slot' => $request->time_slot,
            'notes' => $request->notes,

        ]);
                $addNew = $user->remainingReserve;
                $addNew -= 1;
                $user->update(['remainingReserve' => $addNew]);
        // Redirect with a success message
        return redirect()->route('dashboard')->with('success', 'Reservation created successfully!');
    }

    public function getOccupiedTimeSlots(Request $request)
        {
            $date = $request->input('date'); // Get the date from the request

            // Fetch existing reservations for that date and get the time slots
            $reservations = Reservation::where('reservation_date', $date)->get();

            // Extract the time slots from the reservations
            $occupiedSlots = $reservations->pluck('time_slot')->toArray();

            return response()->json($occupiedSlots);
        }

        
        public function cancelReservation($reservationId)
        {
            $user = auth()->user();
            // Find the reservation
            $reservation = Reservation::find($reservationId);
    
            if (!$reservation) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Reservation not found.',
                ], 404);
            }
            
            // Update the status to "cancelled"
            $reservation->status = 'cancelled';
            $reservation->save();


            $ot=$reservation->user_id;
            $user = User::find($ot);
            $addNew = $user->remainingReserve;
            $addNew += 1;
            User::find($ot)->update(['remainingReserve' => $addNew]);
            

    
            return response()->json([
                'status' => 'success',
                'message' => 'Reservation cancelled successfully.',
            ]);

            
        }

    
}
