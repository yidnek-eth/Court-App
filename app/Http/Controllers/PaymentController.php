<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Chapa\Chapa\Facades\Chapa;
use App\Models\PackagePurchase;
use App\Models\User;
use Illuminate\Support\Facades\Http;


class PaymentController extends Controller
{
    protected $reference;

    public function __construct(){
        $this->reference = Chapa::generateReference();

    }
    // Create Payment Method
    public function createPayment(Request $request)
    {
         //This generates a payment reference
         $reference = $this->reference;
        

         $request->validate([
            'package_name' => 'required|string',
            'package_price' => 'required|numeric'
        ]);

        $packageName = $request->input('package_name');
        $packagePrice = $request->input('package_price');
        $user = auth()->user();

         // Enter the details of the payment
         $data = [
            
            'amount' => $packagePrice,
            'email' => $user->email,
            'tx_ref' => $reference,
            'currency' => "ETB",
            'callback_url' => route('payment.callback',[$reference]),
            'first_name' =>$user->name,
            //'last_name' => $user->last_name,
            "customization" => [
                "title" => 'Chapa checking',
                "description" => "I amma testing this"
            ]
        ];


         // Store purchase before initializing payment
         $purchase = PackagePurchase::create([
            'user_id' => $user->id,
            'package_name' => $packageName,
            'price' => $packagePrice,
            'status' => 'pending',
            'payment_reference' => $this->reference,
            'start_date' => now(),
            'end_date' => $this->calculateEndDate($packageName)
        ]);
      

        // Initialize the payment
        $payment = Chapa::initializePayment($data);

        //dd($payment);

        // Redirect to the payment page
        if ($payment['status'] !== 'success') {
            // notify something went wrong
            return;
        }
        
        return redirect($payment['data']['checkout_url']);
    }

    // Payment Callback Method (from Chapa)
    public function paymentCallback($reference)
        {
            
            $data = Chapa::verifyTransaction($reference);
            
            Log::info('Payment Callback Data:', ['reference' => $reference, 'data' => $data]);

            /*
            //if payment is successful
            if ($data['status'] ==  'success') {
            
    
            //dd($data);
            $purchase = PackagePurchase::where('payment_reference', $reference)->first();

             // Update purchase status
             $purchase->update(['status' => 'success']);

             // Update user reservations
             $this->updateUserReservations(
                User::findOrFail($purchase->user_id),
                $purchase->package_name
            );

            // Redirect to the payment return page
            return redirect()->route('payment.return');

            }
    
            else{
                //oopsie something ain't right.
                return response()->json(['error' => 'Purchase not found.'], 404);
            }

            $purchase->status = 'success';
            $purchase->save();

            $user = $purchase->user_id;

            */
            
    
    
        }
    // Payment Return Method (after successful payment)
    public function paymentReturn()
    {
        auth()->user()->refresh();
        return redirect()->route('dashboard')->withHeaders([
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ]);
    }

    private function calculateEndDate($packageName)
    {
        return match ($packageName) {
            '1 Month' => now()->addMonth(),
            '3 Months' => now()->addMonths(3),
            '6 Months' => now()->addMonths(6),
            '1 Year' => now()->addYear(),
            default => throw new \InvalidArgumentException('Invalid package'),
        };
    }

    private function updateUserReservations(User $user, $packageName)
    {
        $reservationMap = [
            '1 Month' => 4,
            '3 Months' => 12,
            '6 Months' => 24,
            '1 Year' => 48
        ];

        if (isset($reservationMap[$packageName])) {
            $user->increment('remainingReserve', $reservationMap[$packageName]);
            Log::info("Reservations updated for user {$user->id}. New total: {$user->remainingReserve}");
        }
    }
}
