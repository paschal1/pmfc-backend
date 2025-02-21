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
    


public function addItemToCart(Request $request, $cartId)
{
    $cart = Cart::findOrFail($cartId);

    $cartItem = CartItem::create([
        'cart_id' => $cart->id,
        'product_id' => $request->product_id,
        'quantity' => $request->quantity,
        'price' => $request->price,
        'sub_total' => $request->quantity * $request->price,
    ]);

    $cart->calculateTotal();

    return response()->json($cartItem, 201);
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
    $totalPrice = $cartItems->reduce(function ($total, $item) {
        return $total + ($item->product->price * $item->quantity);
    }, 0);

   // Determine order details based on request input
   $orderData = [
    'user_id' => $cart->user_id,
    'total_price' => $totalPrice,
    'status' => 'pending',
    'payment_status' => 'Unpaid',
    'order_date' => now(),
    'shipping_address' => $request->input('shipping_address', $cart->user->address),
    'shipping_state' => $request->input('shipping_state'),
    'shipping_city' => $request->input('shipping_city'),
    'shipping_zip_code' => $request->input('shipping_zip_code'),
    'fullname' => $request->input('fullname', $cart->user->name),
    'email' => $request->input('email', $cart->user->email),
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
    $cart->status = 'checked_out';
    $cart->save();

    // Determine email recipient
    $emailRecipient = $request->filled('email') ? $request->email : $cart->user->email;

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


}
