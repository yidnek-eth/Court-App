<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Chapa\Chapa\Chapa;
use Illuminate\Support\Facades\Log;

class ChapaController extends Controller
{
    protected $chapa;

    public function __construct(Chapa $chapa)
    {
        $this->chapa = $chapa;
    }

    /**
     * Initialize a payment
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function initializePayment(Request $request)
    {
        $data = $request->validate([
            'amount' => 'required|numeric',
            'email' => 'required|email',
            // Add any other fields required by Chapa's API here
        ]);

        $transactionPrefix = $request->input('transaction_prefix', null);
        $reference = $this->chapa->generateReference($transactionPrefix);

        // Add the reference to the data
        $data['reference'] = $reference;

        try {
            $response = $this->chapa->initializePayment($data);
            
            // Check if the response is valid and return appropriate response
            if (isset($response['status']) && $response['status'] === 'success') {
                return response()->json([
                    'status' => 'success',
                    'data' => $response['data']
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => $response['message'] ?? 'Unable to initialize payment.'
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error('Chapa Payment Initialization Error: '.$e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing the payment.'
            ], 500);
        }
    }

    /**
     * Verify a payment transaction
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyTransaction($id)
    {
        try {
            $response = $this->chapa->verifyTransaction($id);
            
            if (isset($response['status']) && $response['status'] === 'success') {
                return response()->json([
                    'status' => 'success',
                    'data' => $response['data']
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => $response['message'] ?? 'Unable to verify transaction.'
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error('Chapa Transaction Verification Error: '.$e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while verifying the transaction.'
            ], 500);
        }
    }

    /**
     * Create a transfer to a bank or wallet
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createTransfer(Request $request)
    {
        $data = $request->validate([
            'amount' => 'required|numeric',
            'recipient_email' => 'required|email',
            // Add any other fields required for transfer creation
        ]);

        try {
            $response = $this->chapa->createTransfer($data);
            
            if (isset($response['status']) && $response['status'] === 'success') {
                return response()->json([
                    'status' => 'success',
                    'data' => $response['data']
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => $response['message'] ?? 'Unable to create transfer.'
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error('Chapa Transfer Creation Error: '.$e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while creating the transfer.'
            ], 500);
        }
    }

    /**
     * Verify a transfer transaction
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyTransfer($id)
    {
        try {
            $response = $this->chapa->verifyTransfer($id);
            
            if (isset($response['status']) && $response['status'] === 'success') {
                return response()->json([
                    'status' => 'success',
                    'data' => $response['data']
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => $response['message'] ?? 'Unable to verify transfer.'
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error('Chapa Transfer Verification Error: '.$e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while verifying the transfer.'
            ], 500);
        }
    }

}
