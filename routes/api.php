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
use App\Http\Controllers\Api\Admin\TestimonialController;
use App\Http\Controllers\Api\CartItemController;
use App\Http\Controllers\Api\CartTotalController;
use App\Http\Controllers\Api\OrderItemController;
use App\Http\Controllers\Api\EnrollmentController;
use App\Http\Controllers\Api\RatingController;
use App\Http\Controllers\Api\WishlistController;
use App\Http\Controllers\Api\TrainingController;
use App\Http\Controllers\Api\CartsController;
use App\Http\Controllers\Api\BlogController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\QuoteController;


Route::post('/login', [AuthController::class, 'login'])->name('api.user.login');
// Route::post('register', [RegisteredUserController::class, 'register'])->name('api.user.register');
Route::post('/register', [RegisterController::class, 'register']);

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});





Route::middleware(['auth:sanctum', 'log.activity'])->group(function () {
    Route::get('/activeUse', [UserController::class, 'activeUse'])->name('activeUser');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/place-order', [UserController::class, 'place-order']); //will come back to this
    Route::get('/update-profile', [UserController::class, 'update-profile']); //will come back to this
});

Route::middleware(['auth:sanctum', 'role:Admin', 'log.activity'])->group( function () {
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('products', ProductController::class);
    Route::apiResource('orders', OrderController::class);
    Route::apiResource('students', StudentController::class);
    Route::get('/analytics/sales-reports', [AnalyticsDashboardController::class, 'salesReports']);
    Route::get('/analytics/sales-reports', [AnalyticsDashboardController::class, 'salesReports']);
    Route::get('/analytics/user-activity', [AnalyticsDashboardController::class, 'userActivity']);
    Route::get('/analytics/website-performance', [AnalyticsDashboardController::class, 'websitePerformance']);

    Route::prefix('permissions')->group(function () {
        Route::post('/', [PermissionController::class, 'store']); // Create one or more permissions
        Route::delete('/{id}', [PermissionController::class, 'destroy']); // Delete a permission
    });
    
    Route::prefix('roles')->group(function () {
        Route::get('/', [RoleController::class, 'index']); // Get all roles
        Route::post('/', [RoleController::class, 'store']); // Create a new role
        Route::put('/{id}', [RoleController::class, 'update']); // Update role permissions
        Route::delete('/{id}', [RoleController::class, 'destroy']); // Delete role
        Route::get('/permissions', [RoleController::class, 'getPermissions']); // Get all permissions
    });

});

Route::group(['middleware' => ['auth:sanctum', 'role:Trainer']], function () {
    Route::apiResource('training-programs', TrainingProgramController::class);
});

Route::group(['middleware' => ['auth:sanctum', 'role:Trainee']], function () {
    Route::get('my-training', [TrainingController::class, 'index']);
});

Route::group(['middleware' => ['auth:sanctum', 'role:Manager']], function () {
    Route::get('my-training', [TrainingController::class, 'index']);
});

Route::group(['middleware' => ['auth:sanctum', 'role:Customer']], function () {
    Route::get('my-training', [TrainingController::class, 'index']);
});

Route::apiResource('blogs', BlogController::class);
Route::apiResource('services', ServiceController::class);
Route::get('contacts', [ContactController::class, 'index']);
Route::post('contacts', [ContactController::class, 'store']);
Route::put('contacts/{id}/status', [ContactController::class, 'update']);

Route::apiResource('projects', ProjectController::class);


Route::post('quotes', [QuoteController::class, 'store']);