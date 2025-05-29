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
use Spatie\Permission\Models\Role;

//////////////////////////////////////////////////////////////
// Public Routes
//////////////////////////////////////////////////////////////

Route::model('role', Role::class);

// Register and login routes
Route::post('/register', [RegisterController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->name('api.user.login');

// Public-facing GET routes (index + show)
Route::get('products', [ProductController::class, 'index']);
Route::get('products/{product}', [ProductController::class, 'show']);

Route::get('orders', [OrderController::class, 'index']);
Route::get('orders/{order}', [OrderController::class, 'show']);

Route::get('students', [StudentController::class, 'index']);
Route::get('students/{student}', [StudentController::class, 'show']);

Route::get('categories', [CategoryController::class, 'index']);
Route::get('categories/{category}', [CategoryController::class, 'show']);

Route::get('blogs', [BlogController::class, 'index']);
Route::get('blogs/{blog}', [BlogController::class, 'show']);

Route::get('projects', [ProjectController::class, 'index']);
Route::get('projects/{project}', [ProjectController::class, 'show']);

Route::get('services', [ServiceController::class, 'index']);
Route::get('services/{service}', [ServiceController::class, 'show']);

Route::get('testimonials', [TestimonialController::class, 'index']);
Route::get('testimonials/{testimonial}', [TestimonialController::class, 'show']);

Route::get('whishlists', [WishlistController::class, 'index']);
Route::get('whishlists/{whishlist}', [WishlistController::class, 'show']);

Route::get('ratings', [RatingController::class, 'index']);
Route::get('ratings/{rating}', [RatingController::class, 'show']);

Route::get('training-programs', [TrainingController::class, 'index']);
Route::get('training-programs/{training-program}', [RatingController::class, 'show']);

// Contact Routes (public + auth)
Route::get('contacts', [ContactController::class, 'index']);
Route::post('contacts', [ContactController::class, 'store']);
Route::put('contacts/{id}/status', [ContactController::class, 'update']);

//////////////////////////////////////////////////////////////
// Authenticated Routes (With Sanctum Middleware)
//////////////////////////////////////////////////////////////

Route::middleware(['auth:sanctum'])->group(function () {

    // User Profile and Dashboard
    Route::get('/user', fn(Request $request) => $request->user());
    Route::get('/activeUse', [UserController::class, 'activeUse'])->name('activeUser');
    Route::get('/dashboard', [AnalyticsDashboardController::class, 'index'])->name('dashboard');
    Route::post('/place-order', [UserController::class, 'placeOrder']);
    Route::get('/update-profile', [UserController::class, 'updateProfile']);

    //////////////////////////////////////////////////////////
    // Admin Routes
    //////////////////////////////////////////////////////////
    Route::middleware(['role:admin', 'log.activity'])->group(function () {
        Route::apiResource('products', ProductController::class);
        Route::apiResource('orders', OrderController::class);
        Route::apiResource('students', StudentController::class);

        // Analytics Routes
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
        Route::apiResource('training-programs', TrainingController::class);
        Route::get('my-training', [TrainingController::class, 'index']);
    });

    //////////////////////////////////////////////////////////
    // Other Common Routes
    //////////////////////////////////////////////////////////

    // Cart Routes
    Route::apiResource('cartItems', CartItemController::class);
    Route::get('cart', [CartController::class, 'viewCart']);
    Route::post('cart/{cartId}/items', [CartController::class, 'addItemToCart']);
    Route::put('cart/{cartId}/items/{itemId}', [CartController::class, 'updateItemQuantity']);
    Route::delete('cart/{cartId}/items/{itemId}', [CartController::class, 'removeItemFromCart']);
    Route::post('cart/{cartId}/checkout', [CartController::class, 'checkout']);
    Route::get('carts', [CartController::class, 'viewAllCarts']);

    // Enrollment Routes
    Route::get('/enrollments', [EnrollmentController::class, 'index']);
    Route::post('/enroll', [EnrollmentController::class, 'enroll']);
    Route::get('/enrollments/{id}', [EnrollmentController::class, 'show']);
    Route::delete('/enrollments/{id}', [EnrollmentController::class, 'destroy']);
    Route::post('/unenroll', [TrainingController::class, 'unenroll']);

    // Service, Testimonial, Wishlist, Rating (full CRUD under auth)
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('blogs', BlogController::class);
    Route::apiResource('projects', ProjectController::class);
    Route::apiResource('services', ServiceController::class);
    Route::apiResource('testimonials', TestimonialController::class);
    Route::apiResource('whishlists', WishlistController::class);
    Route::apiResource('ratings', RatingController::class);

    // Quote and Payment Routes
    Route::post('quotes', [QuoteController::class, 'store']);
    Route::post('/pay', [PaymentController::class, 'redirectToGateway'])->name('payment.redirect');
    Route::get('/payment/callback', [PaymentController::class, 'handleGatewayCallback'])->name('payment.callback');

    // Order Routes
    Route::post('/orders/{orderId}/cancel', [OrderController::class, 'cancelOrder']);
    Route::get('/orders/track/{trackingNumber}', [OrderController::class, 'trackOrder']);
    Route::post('/orders/{id}/refund', [OrderController::class, 'issueRefund']);
    Route::get('/orders/user', [OrderController::class, 'getUserOrders']);
});
