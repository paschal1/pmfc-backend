<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Order;
use Paystack;

class PaymentController extends Controller
{


/**
     * Redirect the user to Paystack Payment Page.
     */
    public function redirectToGateway(Request $request)
    {
        // Validate the request
        $request->validate([
            'order_id' => 'required|exists:orders,id',
        ]);

        // Fetch the order
        $order = Order::findOrFail($request->order_id);

        // Prepare payment details
        $paymentDetails = [
            'amount' => $order->total_price * 100, // Paystack accepts amount in kobo
            'email' => $order->user->email,       // User's email
            'reference' => Paystack::genTranxRef(), // Unique transaction reference
            'callback_url' => route('payment.callback'), // Redirect after payment
        ];

        // Save the reference in the order (optional)
        $order->update(['payment_reference' => $paymentDetails['reference']]);

        // Redirect to Paystack's payment page
        return Paystack::getAuthorizationUrl($paymentDetails)->redirectNow();
    }

    /**
     * Handle Paystack payment callback.
     */
    public function handleGatewayCallback()
    {
        // Verify the transaction
        $paymentDetails = Paystack::getPaymentData();

        if ($paymentDetails['status'] && $paymentDetails['data']['status'] === 'success') {
            // Payment successful
            $order = Order::where('payment_reference', $paymentDetails['data']['reference'])->first();

            if ($order) {
                $order->update([
                    'payment_status' => 'paid',
                ]);

                return response()->json(['message' => 'Payment successful', 'order' => $order]);
            }
        }

        return response()->json(['message' => 'Payment failed or invalid transaction'], 400);
    }
}

 r
