<?php

use App\Http\Controllers\Auth\AuthenticationController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\NoticeCategoryController;
use Illuminate\Support\Facades\Route;

Route::get('login', function () {
    return errorResponse('Authentication required. Please provide a valid access token.', 401);
})->name('login');

Route::post('login', [AuthenticationController::class, 'login']);
Route::post('register', [RegisterController::class, 'register']);
Route::post('forgot-password', [ForgotPasswordController::class, 'forgotPassword']);
Route::post('reset-password', [ResetPasswordController::class, 'resetPassword']);

Route::middleware('auth:api')->group(function () {
    Route::post('logout', [AuthenticationController::class, 'logout'])->name('logout');
});


// Shared routes
    $sharedRoutes = function () {
        Route::get('dashboard', [DashboardController::class, 'index']);

        // Events (read-only)
            Route::controller(EventController::class)->prefix('events')->group(function() {
                Route::get('/', 'index');
                Route::get('{id}', 'show');
            });
        // Events (read-only)
    };
// Shared routes


// Admin routes
    $adminRoutes = function () {
        // Events routes
            Route::controller(EventController::class)->prefix('events')->group(function() {
                Route::post('/', 'store');
                Route::post('{id}', 'update');
                Route::delete('{id}', 'destroy');
            });
        // Events routes

        // Notice category routes
            Route::controller(NoticeCategoryController::class)->prefix('notice-categories')->group(function() {
                Route::get('/', 'index');
                Route::get('{id}', 'show');
                Route::post('/', 'store');
                Route::post('{id}', 'update');
                Route::delete('{id}', 'destroy');
            });
        // Notice category routes
    };
// Admin routes


// Member routes → only shared
    Route::middleware(['auth:api', 'role:member'])->group($sharedRoutes);
// Member routes → only shared


// Admin routes → shared + admin
    Route::middleware(['auth:api', 'role:admin'])
        ->group(function () use ($sharedRoutes, $adminRoutes) {
            $sharedRoutes();
            $adminRoutes();
        });
// Admin routes → shared + admin