<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PackageController extends Controller
{
    public function buy(Request $request)
    {
        // Validate the incoming request data
        $request->validate([
            'user_name' => 'required|string',
            'user_email' => 'required|email',
            'user_address' => 'required|string',
            'user_phone' => 'required|string',
            'package_name' => 'required|string',
            'package_price' => 'required|numeric',
        ]);

        // Process the package purchase (e.g., save to database, initiate payment, etc.)
        // For simplicity, here we're just redirecting with a success message

        // You can save the package purchase data in your database, e.g.:
        // $user = Auth::user();
        // $user->packages()->create([
        //     'name' => $request->package_name,
        //     'price' => $request->package_price,
        // ]);

        // For now, we'll assume the purchase is successful
        return redirect()->route('dashboard')->with('success', 'Package purchased successfully!');
    }
}

