<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User; // Import the User model
use App\Models\Reservation; // Import the Reservation model
use Carbon\Carbon; // Import Carbon for date manipulation
use Illuminate\Support\Facades\Log;


class AdminController extends Controller
{
    // Admin Dashboard
    public function index()
{
    $recentUsers = User::latest()->paginate(10); // Adjust the number of items per page as needed

    return view('admin.dashboard', compact('recentUsers'));
}


    // Manage Users Page

public function manageUsers(Request $request)
{
    // Get the search term from the request
    $searchTerm = $request->input('search', '');

    // Query users with search functionality
    $users = User::query()
        ->where('name', 'like', "%{$searchTerm}%")
        ->orWhere('email', 'like', "%{$searchTerm}%")
        ->orWhere('phone', 'like', "%{$searchTerm}%")
        ->paginate(10); // Paginate results

    return view('admin.users', compact('users', 'searchTerm'));
}

    // Manage Reservations Page
    public function manageReservations(Request $request)
    {
        $query = Reservation::query();
    
        // Apply search filter if there's a search term
        if ($request->has('search') && $request->search) {
            $searchTerm = $request->search;
            $query->whereHas('user', function ($query) use ($searchTerm) {
                $query->where('name', 'like', "%{$searchTerm}%")
                    ->orWhere('email', 'like', "%{$searchTerm}%")
                    ->orWhere('phone', 'like', "%{$searchTerm}%");
            });
        }
    
        // Paginate the results
        // Get only the reservations with status 'Confirmed'
        $reservations = Reservation::where('status', 'Confirmed')->paginate(10);

        return view('admin.reservations', compact('reservations'));
    }
    

    // Fetch Reservations (AJAX)
    public function fetchReservations(Request $request)
    {
        // Fetch reservations with search and pagination (already implemented)
        $search = $request->input('search', '');
        $reservations = Reservation::with('user')
            ->whereHas('user', function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
            })
            ->paginate(10);
        
        return response()->json([
            'reservations' => $reservations,
            'pagination' => (string) $reservations->links() // Include pagination links
        ]);
    }

 
    
 
    


}