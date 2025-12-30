<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Utility\Strings;

class OrderController extends Controller
{
    /**
     * Display a listing of orders (with optional filtering).
     */
    public function index()
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        if ($user->hasRole('admin')) {
            $orders = Order::with(['user', 'orderItems.product'])->latest()->get();
        } else {
            $orders = Order::with(['user', 'orderItems.product'])
                ->where('user_id', $user->id)
                ->latest()
                ->get();
        }

        return response()->json($orders);
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        if ($user->hasRole('admin') || $order->user_id === $user->id) {
            $order->load(['user', 'orderItems.product']);
            return response()->json($order);
        }

        return response()->json(['error' => 'Unauthorized'], 403);
    }

    /**
     * Place a new order from cart items.
     */
    public function placeOrder(Request $request)
    {
        $userId = auth()->id();
        $cartItems = CartItem::where('user_id', $userId)->with('product')->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'Your cart is empty'], 400);
        }

        DB::beginTransaction();

        try {
            $totalPrice = $cartItems->reduce(function ($carry, $item) {
                return $carry + ($item->product->price * $item->quantity);
            }, 0);

            $order = Order::create([
                'user_id' => $userId,
                'total_price' => $totalPrice,
                'tracking_number' => strtoupper(Str::random(12)),
                'status' => 'order_processing',
                'order_date' => now(),
                'shipping_address' => $request->input('shipping_address', 'Not provided'),
                'shipping_state' => $request->input('shipping_state'),
                'shipping_city' => $request->input('shipping_city'),
                'shipping_zip_code' => $request->input('shipping_zip_code'),
                'fullname' => $request->input('fullname'),
                'email' => $request->input('email'),
                'payment_method' => $request->input('payment_method', 'Bank Transfer'),
                'payment_type' => $request->input('payment_type', 'Full Payment'),
                'payment_status' => 'Unpaid',
            ]);

            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name,
                    'price' => $item->product->price,
                    'quantity' => $item->quantity,
                    'subtotal' => $item->product->price * $item->quantity,
                ]);
            }

            CartItem::where('user_id', $userId)->delete();

            DB::commit();

            return response()->json(['message' => 'Order placed successfully', 'order' => $order], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to place order', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Track an order using tracking number (PUBLIC - no auth required).
     */
    public function trackOrder($trackingNumber)
    {
        $order = Order::where('tracking_number', $trackingNumber)->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        return response()->json([
            'tracking_number' => $order->tracking_number,
            'status' => $order->status,
            'paid' => $order->is_fully_paid,
            'location' => $order->full_shipping_location,
        ]);
    }

    /**
     * Update order status (admin).
     */
    public function updateStatus(Request $request, $id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'status' => false,
                'message' => 'Order not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:order_processing,pre_production,in_production,shipped,delivered,canceled',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $order->update(['status' => $request->status]);

        $order->load(['user', 'orderItems.product']);

        return response()->json([
            'message' => 'Order status updated successfully', 
            'order' => $order
        ], 200);
    }

    /**
     * Cancel order by user if still in processing phase.
     */
    public function cancelOrder(Request $request, $orderId)
    {
        $order = Order::where('id', $orderId)->where('user_id', auth()->id())->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        if (!in_array($order->status, ['order_processing', 'pre_production'])) {
            return response()->json(['message' => 'Order cannot be canceled at this stage.'], 400);
        }

        $order->update([
            'status' => 'canceled',
            'payment_status' => $order->payment_status === 'Paid' ? 'Refund Pending' : 'Unpaid'
        ]);

        return response()->json([
            'message' => 'Order has been canceled successfully.',
            'order' => $order
        ], 200);
    }

    /**
     * Issue refund for delivered order (admin/manual).
     */
    public function issueRefund(Request $request, $id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        if ($order->status !== 'delivered') {
            return response()->json(['message' => 'Refund can only be issued for delivered orders'], 400);
        }

        $order->update(['status' => 'canceled', 'payment_status' => 'Refunded']);

        return response()->json(['message' => 'Refund issued successfully', 'order' => $order], 200);
    }

    /**
     * Get orders for the logged-in user.
     * 
     * ✅ THIS IS THE CRITICAL FIX
     * - Route must be: Route::get('/orders/user', ...)
     * - Must come BEFORE Route::get('/orders/{order}', ...)
     * - Uses manual query, NOT route model binding
     */
    public function getUserOrders()
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        try {
            $orders = Order::where('user_id', $user->id)
                ->with('orderItems.product')
                ->latest()
                ->get()
                ->map(function ($order) {
                    return [
                        'id' => $order->id,
                        'order_id' => $order->id,
                        'trackingNumber' => $order->tracking_number,
                        'date' => $order->created_at->format('Y-m-d'),
                        'status' => $order->status,
                        'total' => '₦' . number_format($order->total_price, 2),
                        'items' => $order->orderItems->count(),
                        'productName' => $order->orderItems->first()?->product_name ?? 'Product',
                        'productImage' => $order->orderItems->first()?->product?->image ?? '📦'
                    ];
                });

            return response()->json(['data' => $orders], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch orders',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}