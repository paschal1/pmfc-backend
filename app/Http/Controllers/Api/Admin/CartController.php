<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Mail\OrderMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function viewCart()
    {
        // Get the authenticated user's ID
        $userId = auth()->id();
    
        // Retrieve the active cart for the user
        $cart = Cart::where('user_id', $userId)->where('status', 'active')->first();
    
        // Check if the cart exists
        if (!$cart) {
            return response()->json([
                'message' => 'No active cart found for this user.'
            ], 404);
        }
    
        // Retrieve the cart items with product details
        $cartItems = CartItem::where('cart_id', $cart->id)->with('product')->get();
    
        return response()->json([
            'cart_id' => $cart->id,
            'cart_items' => $cartItems
        ]);
    }
    


// public function addItemToCart(Request $request, $cartId)
// {
//     $cart = Cart::findOrFail($cartId);

//     $cartItem = CartItem::create([
//         'cart_id' => $cart->id,
//         'product_id' => $request->product_id,
//         'quantity' => $request->quantity,
//         'price' => $request->price,
//         'sub_total' => $request->quantity * $request->price,
//     ]);

//     $cart->calculateTotal();

//     return response()->json($cartItem, 201);
// }

public function addItemToCart(Request $request, $cartId)
{
    $validated = $request->validate([
        'product_id' => 'required|exists:products,id',
        'quantity'   => 'required|integer|min:1',
    ]);

    $cart = Cart::findOrFail($cartId);

    // 🔐 Always get price from DB
    $product = Product::findOrFail($validated['product_id']);

    // Optional: stock check
    if ($product->stock < $validated['quantity']) {
        return response()->json([
            'message' => 'Insufficient stock',
            'available_stock' => $product->stock
        ], 400);
    }

    // Check if item already exists in cart
    $existingItem = CartItem::where('cart_id', $cart->id)
        ->where('product_id', $product->id)
        ->first();

    if ($existingItem) {
        $newQuantity = $existingItem->quantity + $validated['quantity'];

        if ($product->stock < $newQuantity) {
            return response()->json([
                'message' => 'Cannot add more items. Insufficient stock',
                'available_stock' => $product->stock
            ], 400);
        }

        $existingItem->update([
            'quantity'  => $newQuantity,
            'price'     => $product->price,
            'sub_total' => $newQuantity * $product->price,
        ]);

        $cartItem = $existingItem;
    } else {
        $cartItem = CartItem::create([
            'cart_id'   => $cart->id,
            'product_id'=> $product->id,
            'quantity'  => $validated['quantity'],
            'price'     => $product->price,
            'sub_total' => $validated['quantity'] * $product->price,
        ]);
    }

    // 🔄 Recalculate cart total
    $cart->calculateTotal();

    return response()->json([
        'message'   => 'Item added to cart',
        'cart_item' => $cartItem->load('product'),
        'cart_total'=> $cart->total
    ], 201);
}

public function updateItemQuantity(Request $request, $cartId, $itemId)
{
    $cart = Cart::findOrFail($cartId);
    $cartItem = CartItem::findOrFail($itemId);

    $cartItem->update([
        'quantity' => $request->quantity,
        'sub_total' => $request->quantity * $cartItem->price,
    ]);

    $cart->calculateTotal();

    return response()->json($cartItem, 200);
}

public function removeItemFromCart($cartId, $itemId)
{
    $cart = Cart::findOrFail($cartId);
    $cartItem = CartItem::findOrFail($itemId);

    $cartItem->delete();

    $cart->calculateTotal();

    return response()->json(['message' => 'Item removed'], 200);
}

public function checkout(Request $request, $cartId)
{
    // Validate the request
    $request->validate([
        'shipping_address' => 'required|string',
        'shipping_state' => 'nullable|string',
        'shipping_city' => 'nullable|string',
        'shipping_zip_code' => 'nullable|string',
        'email' => 'nullable|email',
        'fullname' => 'nullable|string',
        'payment_method' => 'nullable|string|in:Bank Transfer,Credit Card,PayPal',
        'payment_type' => 'nullable|string|in:Full Payment,Deposit',
        'deposit_amount' => 'nullable|numeric|min:0',
    ]);

    // Retrieve the cart
    $cart = Cart::findOrFail($cartId);

    // Ensure the cart belongs to the authenticated user
    if ($cart->user_id !== auth()->id()) {
        return response()->json(['message' => 'Unauthorized action.'], 403);
    }

    // Fetch all cart items
    $cartItems = CartItem::where('cart_id', $cart->id)->with('product')->get();

    if ($cartItems->isEmpty()) {
        return response()->json(['message' => 'Cart is empty.'], 400);
    }

    // Calculate total price
    $totalPrice = $cartItems->sum(fn($item) => $item->product->price * $item->quantity);

    // Determine payment method and type
    $paymentMethod = $request->input('payment_method', 'Bank Transfer');
    $paymentType = $request->input('payment_type', 'Full Payment');
    
    // Calculate deposit and remaining amount
    $depositAmount = ($paymentType === 'Full Payment') ? $totalPrice : $request->input('deposit_amount', 0);
    $remainingAmount = ($paymentType === 'Full Payment') ? 0 : ($totalPrice - $depositAmount);

    // Ensure deposit amount is valid
    if ($paymentType === 'Deposit' && ($depositAmount <= 0 || $depositAmount >= $totalPrice)) {
        return response()->json(['message' => 'Invalid deposit amount.'], 400);
    }

    // Generate tracking number
    $trackingNumber = uniqid('TRK_');

    // Determine order details based on request input
    $orderData = [
        'user_id' => $cart->user_id,
        'total_price' => $totalPrice,
        'status' => 'order_processing', // Default status
        'payment_status' => ($paymentType === 'Full Payment' ? 'Paid' : 'Unpaid'),
        'tracking_number' => $trackingNumber,
        'order_date' => now(),
        'shipping_address' => $request->input('shipping_address', $cart->user->address ?? 'N/A'),
        'shipping_state' => $request->input('shipping_state'),
        'shipping_city' => $request->input('shipping_city'),
        'shipping_zip_code' => $request->input('shipping_zip_code'),
        'fullname' => $request->input('fullname', $cart->user->name ?? 'Unknown'),
        'email' => $request->input('email', $cart->user->email ?? 'no-reply@example.com'),
        'payment_method' => $paymentMethod,
        'payment_type' => $paymentType,
        'deposit_amount' => $depositAmount,
        'remaining_amount' => $remainingAmount,
    ];

    // Create the order
    $order = Order::create($orderData);

    // Create order items
    foreach ($cartItems as $item) {
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $item->product_id,
            'quantity' => $item->quantity,
            'price' => $item->product->price,
            'subtotal' => $item->product->price * $item->quantity,
        ]);
    }

    // Mark the cart as checked out
    $cart->update(['status' => 'checked_out']);

    // Determine email recipient
    $emailRecipient = $request->filled('email') ? $request->email : ($cart->user->email ?? 'no-reply@example.com');

    // Send an email to the determined recipient
    Mail::to($emailRecipient)->send(new OrderMail($order));

    // Clear cart items (optional)
    CartItem::where('cart_id', $cart->id)->delete();

    // Return the order details
    return response()->json([
        'message' => 'Checkout successful.',
        'order' => $order->load('orderItems'),
    ], 200);
}

public function viewAllCarts()
{
    // Retrieve all carts with their cart items and product details
    $carts = Cart::with(['cartItems.product', 'user'])->get();

    return response()->json([
        'carts' => $carts
    ]);
}


}
