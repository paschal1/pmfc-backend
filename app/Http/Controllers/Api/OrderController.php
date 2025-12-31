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
    public function show($id)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        try {
            // Find the order by ID
            $order = Order::findOrFail($id);
            
            // Check authorization
            if (!$user->hasRole('admin') && $order->user_id !== $user->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Load relationships
            $order->load(['user', 'orderItems.product']);
            
            return response()->json($order);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Order not found'], 404);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch order',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Place a new order from cart items.
     * 
     * ENHANCED VERSION WITH BETTER ERROR HANDLING
     */
    public function placeOrder(Request $request)
    {
        try {
            // Step 1: Validate user is authenticated
            $user = auth()->user();
            if (!$user) {
                return response()->json(['error' => 'Unauthenticated'], 401);
            }

            $userId = $user->id;

            // Step 2: Validate request data
            $validator = Validator::make($request->all(), [
                'fullname' => 'required|string|max:255',
                'email' => 'required|email',
                'shipping_address' => 'required|string',
                'shipping_city' => 'required|string',
                'shipping_state' => 'required|string',
                'shipping_zip_code' => 'nullable|string',
                'payment_method' => 'required|string|in:Bank Transfer,Credit Card,PayPal',
                'payment_type' => 'required|string|in:Full Payment,Deposit',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Step 3: Get cart items with product data
            $cartItems = CartItem::where('user_id', $userId)
                ->with('product')
                ->get();

            if ($cartItems->isEmpty()) {
                return response()->json(['error' => 'Your cart is empty'], 400);
            }

            // Step 4: Verify all products exist
            foreach ($cartItems as $item) {
                if (!$item->product) {
                    return response()->json([
                        'error' => 'Product not found',
                        'cart_item_id' => $item->id,
                        'product_id' => $item->product_id
                    ], 400);
                }

                // Verify product has a price
                if (!$item->product->price) {
                    return response()->json([
                        'error' => 'Product has no price',
                        'product_id' => $item->product_id,
                        'product_name' => $item->product->name
                    ], 400);
                }
            }

            // Step 5: Start database transaction
            DB::beginTransaction();

            try {
                // Step 6: Calculate total price
                $totalPrice = 0;
                foreach ($cartItems as $item) {
                    $itemPrice = floatval($item->product->price);
                    $itemQuantity = intval($item->quantity);
                    $totalPrice += ($itemPrice * $itemQuantity);
                }

                // Step 7: Create order
                $order = Order::create([
                    'user_id' => $userId,
                    'total_price' => $totalPrice,
                    'tracking_number' => strtoupper(Str::random(12)),
                    'status' => 'order_processing',
                    'order_date' => now(),
                    'shipping_address' => $request->input('shipping_address'),
                    'shipping_state' => $request->input('shipping_state'),
                    'shipping_city' => $request->input('shipping_city'),
                    'shipping_zip_code' => $request->input('shipping_zip_code', null),
                    'fullname' => $request->input('fullname'),
                    'email' => $request->input('email'),
                    'payment_method' => $request->input('payment_method', 'Bank Transfer'),
                    'payment_type' => $request->input('payment_type', 'Full Payment'),
                    'payment_status' => 'Unpaid',
                ]);

                // Step 8: Create order items
                foreach ($cartItems as $item) {
                    $itemPrice = floatval($item->product->price);
                    $quantity = intval($item->quantity);
                    $subtotal = $itemPrice * $quantity;

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $item->product_id,
                        'product_name' => $item->product->name,
                        'price' => $itemPrice,
                        'quantity' => $quantity,
                        'subtotal' => $subtotal,
                    ]);
                }

                // Step 9: Clear cart items
                CartItem::where('user_id', $userId)->delete();

                // Step 10: Commit transaction
                DB::commit();

                // Step 11: Return success response
                return response()->json([
                    'message' => 'Order placed successfully',
                    'order' => [
                        'id' => $order->id,
                        'tracking_number' => $order->tracking_number,
                        'total_price' => $order->total_price,
                        'status' => $order->status,
                        'payment_status' => $order->payment_status,
                    ]
                ], 201);

            } catch (\Exception $transactionError) {
                // Rollback if anything fails
                DB::rollBack();
                
                return response()->json([
                    'error' => 'Failed to create order',
                    'message' => $transactionError->getMessage(),
                    'line' => $transactionError->getLine(),
                    'file' => str_replace(base_path(), '', $transactionError->getFile()),
                ], 500);
            }

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Order processing error',
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => str_replace(base_path(), '', $e->getFile()),
            ], 500);
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