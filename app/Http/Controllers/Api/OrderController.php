<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Utility\Strings;


class OrderController extends Controller
{
    /**
     * Display a listing of orders.
     */
    public function index()
    {
        $orders = Order::all();
        return response()->json(['orders' => $orders], 200);
    }

    /**
     * Update the status of a specific order.
     */
    public function updateStatus(Request $request, $id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'status' => true,
                'message' => Strings::OrderNotFound(),
            ]);
        }

        // Validate request data
        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:Pending,Processing,Shipped,Delivered,Cancelled',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Update order status
        $order->update(['status' => $request->input('status')]);

        return response()->json(['message' => 'Order status updated successfully', 'order' => $order], 200);
    }

    /**
     * Issue a refund for a specific order.
     */
    public function issueRefund(Request $request, $id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        if ($order->status !== 'Delivered') {
            return response()->json(['message' => 'Refund can only be issued for delivered orders'], 400);
        }

        // Process refund (this is a placeholder for actual refund logic)
        // Example: PaymentGateway::refund($order->payment_id);

        $order->update(['status' => 'Refunded']);

        return response()->json(['message' => 'Refund issued successfully', 'order' => $order], 200);
    }

  

public function placeOrder(Request $request)
{
    $userId = auth()->id();

    // Fetch user's cart items
    $cartItems = CartItem::where('user_id', $userId)->with('product')->get();

    if ($cartItems->isEmpty()) {
        return response()->json(['message' => 'Your cart is empty'], 400);
    }

    DB::beginTransaction();

    try {
        // Calculate total price
        $totalPrice = $cartItems->reduce(function ($carry, $item) {
            return $carry + ($item->product->price * $item->quantity);
        }, 0);

        // Create an Order
        $order = Order::create([
            'user_id' => $userId,
            'total_price' => $totalPrice,
            'status' => 'pending', // Default status
            'created_at' => now(),
        ]);

        // Convert CartItems to OrderItems
        foreach ($cartItems as $cartItem) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $cartItem->product_id,
                'product_name' => $cartItem->product->name,
                'price' => $cartItem->product->price,
                'quantity' => $cartItem->quantity,
                'subtotal' => $cartItem->product->price * $cartItem->quantity,
            ]);
        }

        // Clear the cart
        CartItem::where('user_id', $userId)->delete();

        DB::commit();

        return response()->json(['message' => 'Order placed successfully', 'order' => $order], 201);
    } catch (\Exception $e) {
        DB::rollBack();

        return response()->json(['message' => 'Failed to place order', 'error' => $e->getMessage()], 500);
    }
}


}
