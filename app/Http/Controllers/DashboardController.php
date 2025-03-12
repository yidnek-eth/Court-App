<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PackagePurchase;
use App\Models\Reservation; // Import the Reservation model
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        
    
        // Fetch the latest package purchase for the user
        $latestPurchase = PackagePurchase::where('user_id', $user->id)
            ->latest('created_at')
            ->first();
    
        // Fetch the user's reservations
        $reservations = Reservation::where('user_id', $user->id)
            ->orderBy('reservation_date', 'asc')
            ->get();
    
        // Calculate remaining date
        $remainingDate = null;
        $remainingDays = null;
        if ($latestPurchase && $latestPurchase->end_date) {
            $endDate = Carbon::parse($latestPurchase->end_date);
            $remainingDays = now()->diffInDays($endDate, false); // Remaining days (negative if expired)
            $remainingDate = $remainingDays > 0 ? "$remainingDays days remaining" : "Expired";
        }
    
        return view('dashboard.index', [
            'user' => $user,
            'remainingDate' => $remainingDate ?? 'No active subscription',
            'remainingDays' => $remainingDays, // Pass remainingDays to the view
            
        ]);
    }

    public function reservations()
    {
        $user = auth()->user();
        $reservations = Reservation::where('user_id', $user->id)
            ->orderBy('reservation_date', 'asc')
            ->get();

        return view('reservations', [
            'reservations' => $reservations,
        ]);
    }

    public function cancelReservation($id)
    {
        $reservation = Reservation::findOrFail($id);
        $reservation->delete();

        return redirect()->route('reservations')->with('success', 'Reservation canceled successfully.');
    }
    
}