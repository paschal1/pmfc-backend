<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\Api\AnalyticsDashboardController;
use App\Http\Controllers\Api\Admin\PermissionController;
use App\Http\Controllers\Api\Admin\RoleController;
use App\Http\Controllers\Api\Admin\CartController;
use App\Http\Controllers\Api\TestimonialController;
use App\Http\Controllers\Api\CartItemController;
use App\Http\Controllers\Api\CartTotalController;
use App\Http\Controllers\Api\OrderItemController;
use App\Http\Controllers\Api\EnrollmentController;
use App\Http\Controllers\Api\RatingController;
use App\Http\Controllers\Api\WishlistController;
use App\Http\Controllers\Api\TrainingController;
use App\Http\Controllers\Api\BlogController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\QuoteController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\Admin\RolePermissionController;
 use App\Http\Controllers\Api\StateController;
use App\Http\Controllers\Api\LocationCostController;
use App\Http\Controllers\Api\AccountController;
use Spatie\Permission\Models\Role;

//////////////////////////////////////////////////////////////
// Public Routes
//////////////////////////////////////////////////////////////

Route::model('role', Role::class);

// Register and login routes
Route::post('/register', [RegisterController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->name('api.user.login');

// Public-facing GET routes (index + show)
Route::apiResource('products', ProductController::class)->only(['index', 'show']);

// ⚠️ IMPORTANT: Do NOT use apiResource for orders in public routes
// Because it will create /orders/{id} which conflicts with /orders/user
// Instead, define them explicitly:
Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
// Note: We're NOT adding Route::get('/orders/{id}') here to avoid conflicts

Route::apiResource('students', StudentController::class)->only(['index', 'show']);
Route::apiResource('categories', CategoryController::class)->only(['index', 'show']);
Route::apiResource('blogs', BlogController::class)->only(['index', 'show']);
Route::apiResource('projects', ProjectController::class)->only(['index', 'show']);
Route::apiResource('services', ServiceController::class)->only(['index', 'show']);
Route::apiResource('testimonials', TestimonialController::class)->only(['index', 'show']);
Route::apiResource('wishlist', WishlistController::class)->only(['index', 'show']);
Route::apiResource('ratings', RatingController::class)->only(['index', 'show']);
Route::apiResource('training-programs', TrainingController::class)->only(['index', 'show']);

Route::get('/quotes', [QuoteController::class, 'index']);
Route::get('/quotes/{id}', [QuoteController::class, 'show']);
Route::delete('/quotes/{id}', [QuoteController::class, 'destroy']); 

// Contact Routes (public + auth)
Route::get('contacts/pending-count', [ContactController::class, 'getPendingCount']);
    
    // Get statistics
Route::get('contacts/stats', [ContactController::class, 'getStats']);

Route::get('contacts', [ContactController::class, 'index']);
Route::post('contacts', [ContactController::class, 'store']);

Route::post('contacts/{id}/reply', [ContactController::class, 'sendReply']);

Route::put('contacts/{id}/status', [ContactController::class, 'update']);

// Get single contact - MUST come after specific routes
    Route::get('contacts/{id}', [ContactController::class, 'show']);
    
    // Update contact status
    Route::put('contacts/{id}', [ContactController::class, 'update']);
    
    // Delete contact
    Route::delete('contacts/{id}', [ContactController::class, 'destroy']);

Route::post('/students', [StudentController::class, 'store']);

//////////////////////////////////////////////////////////////
// Authenticated Routes (With Sanctum Middleware)
//////////////////////////////////////////////////////////////

Route::middleware(['auth:sanctum'])->group(function () {

    // Location costs
    Route::apiResource('location-costs', LocationCostController::class);

       Route::get('states', [StateController::class, 'index']);
    
    // User Profile and Dashboard
    Route::get('/user', fn(Request $request) => $request->user());
    Route::get('/activeUse', [UserController::class, 'activeUse'])->name('activeUser');
    Route::get('/dashboard', [AnalyticsDashboardController::class, 'index'])->name('dashboard');
    Route::get('/admin/dashboard-summary', [AnalyticsDashboardController::class, 'dashboardSummary']);
    Route::post('/place-order', [UserController::class, 'placeOrder']);
    Route::get('/update-profile', [UserController::class, 'updateProfile']);
    Route::patch('/update-profile', [UserController::class, 'update']);
    Route::post('/change-password', [UserController::class, 'changePassword']);
    
    //////////////////////////////////////////////////////////
    // Admin Routes
    //////////////////////////////////////////////////////////
    Route::middleware(['role:admin', 'log.activity'])->group(function () {
        // Admin-managed resources (except index/show because public)
        Route::apiResource('products', ProductController::class)->except(['index', 'show']);
        // Note: Order routes are handled separately below
        Route::apiResource('students', StudentController::class)->except(['index', 'show']);

        // Analytics Routes
        Route::get('/analytics/dashboard-summary', [AnalyticsDashboardController::class, 'dashboardSummary']);
        Route::get('/analytics/sales-reports', [AnalyticsDashboardController::class, 'salesReports']);
        Route::get('/analytics/user-activity', [AnalyticsDashboardController::class, 'userActivity']);
        Route::get('/analytics/website-performance', [AnalyticsDashboardController::class, 'websitePerformance']);
       
        // Permission and Role Routes
        Route::prefix('permissions')->group(function () {
            Route::post('/', [PermissionController::class, 'store']);
            Route::delete('/{id}', [PermissionController::class, 'destroy']);
        });

        Route::prefix('roles')->group(function () {
            Route::get('/', [RoleController::class, 'index']);
            Route::post('/', [RoleController::class, 'store']);
            Route::put('/{id}', [RoleController::class, 'update']);
            Route::delete('/{id}', [RoleController::class, 'destroy']);
            Route::get('/permissions', [RoleController::class, 'getPermissions']);
            Route::post('admin/permissions/create', [RoleController::class, 'createPermission']);
            Route::post('admin/create', [RoleController::class, 'createRole']);
        });
    });

    //////////////////////////////////////////////////////////
    // Trainer, Customer, Manager Routes
    //////////////////////////////////////////////////////////
    Route::middleware(['role:admin|manager|customer', 'log.activity'])->group(function () {
        Route::apiResource('training-programs', TrainingController::class)->except(['index', 'show']);
        Route::get('my-training', [TrainingController::class, 'index']);
    });

    // Account Management Routes
        Route::prefix('accounts')->group(function () {
            Route::get('/', [AccountController::class, 'index'])->name('accounts.index');
            Route::get('/active', [AccountController::class, 'getActiveAccounts'])->name('accounts.active');
            Route::get('/type/{type}', [AccountController::class, 'getByType'])->name('accounts.byType');
            Route::post('/', [AccountController::class, 'store'])->name('accounts.store');
            Route::get('/{id}', [AccountController::class, 'show'])->name('accounts.show');
            Route::put('/{id}', [AccountController::class, 'update'])->name('accounts.update');
            Route::patch('/{id}/status', [AccountController::class, 'toggleStatus'])->name('accounts.toggleStatus');
            Route::delete('/{id}', [AccountController::class, 'destroy'])->name('accounts.destroy');
        });

    //////////////////////////////////////////////////////////
    // Cart Routes
    //////////////////////////////////////////////////////////
    Route::apiResource('cartItems', CartItemController::class);
    Route::get('cart', [CartController::class, 'viewCart']);
    Route::post('cart/{cartId}/items', [CartController::class, 'addItemToCart']);
    Route::put('cart/{cartId}/items/{itemId}', [CartController::class, 'updateItemQuantity']);
    Route::delete('cart/{cartId}/items/{itemId}', [CartController::class, 'removeItemFromCart']);
    Route::post('cart/{cartId}/checkout', [CartController::class, 'checkout']);
    Route::get('carts', [CartController::class, 'viewAllCarts']);

    //////////////////////////////////////////////////////////
    // Enrollment Routes
    //////////////////////////////////////////////////////////
    Route::get('/enrollments', [EnrollmentController::class, 'index']);
    Route::get('/enrollments/{id}', [EnrollmentController::class, 'show']);
    Route::post('/enroll', [EnrollmentController::class, 'enroll']);
    Route::delete('/enrollments/{id}', [EnrollmentController::class, 'destroy']);
    Route::post('/unenroll', [TrainingController::class, 'unenroll']);

    //////////////////////////////////////////////////////////
    // Full CRUD Resources Under Auth
    //////////////////////////////////////////////////////////
    Route::apiResource('categories', CategoryController::class)->except(['index', 'show']);
    Route::apiResource('blogs', BlogController::class)->except(['index', 'show']);
    Route::apiResource('projects', ProjectController::class)->except(['index', 'show']);
    Route::apiResource('services', ServiceController::class)->except(['index', 'show']);
    Route::apiResource('testimonials', TestimonialController::class)->except(['index', 'show']);
    Route::apiResource('wishlist', WishlistController::class)->except(['index', 'show']);
    Route::apiResource('ratings', RatingController::class)->except(['index', 'show']);

    //////////////////////////////////////////////////////////
    // Quote and Payment Routes
    //////////////////////////////////////////////////////////
    Route::post('quotes', [QuoteController::class, 'store']);
    Route::post('/pay', [PaymentController::class, 'redirectToGateway'])->name('payment.redirect');
    Route::get('/payment/callback', [PaymentController::class, 'handleGatewayCallback'])->name('payment.callback');

    //////////////////////////////////////////////////////////
    // ORDER ROUTES - CRITICAL: Specific routes MUST come before generic ones
    //////////////////////////////////////////////////////////
    
    // ⚠️ IMPORTANT: Order matters! Specific routes BEFORE generic {id} routes
    
    // User-specific order routes (MUST BE FIRST)
    Route::get('/orders/user', [OrderController::class, 'getUserOrders'])->name('orders.user');
    Route::get('/orders/track/{trackingNumber}', [OrderController::class, 'trackOrder'])->name('orders.track');
    
    // Standard order operations
    Route::post('/orders', [OrderController::class, 'placeOrder'])->name('orders.store');
    
    // Generic ID route (MUST COME AFTER specific routes like /orders/user)
    Route::get('/orders/{id}', [OrderController::class, 'show'])
        ->where('id', '[0-9]+')
        ->name('orders.show');
    
    // Order management routes
    Route::put('/orders/{id}/status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
    Route::post('/orders/{orderId}/cancel', [OrderController::class, 'cancelOrder'])->name('orders.cancel');
    Route::post('/orders/{id}/refund', [OrderController::class, 'issueRefund'])->name('orders.refund');
});

//////////////////////////////////////////////////////////////
// Test Routes (Remove in production)
//////////////////////////////////////////////////////////////
Route::get('/test-password', function () {
    $user = \App\Models\User::where('email', 'info@princem-fc.com')->first();
    
    if (!$user) {
        return 'User not found';
    }

    return Hash::check('your-password-here', $user->password) ? 'Match' : 'No match';
});

Route::get('/cloudinary-test', function () {
    dd([
        'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
        'api_key' => env('CLOUDINARY_API_KEY'),
        'api_secret' => env('CLOUDINARY_API_SECRET'),
    ]);
});