<?php

use App\Http\Controllers\Auth\AuthenticationController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\LegislationController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\NoticeCategoryController;
use App\Http\Controllers\NoticeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportCategoryController;
use App\Http\Controllers\ReportController;
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

        // Events routes
            Route::controller(EventController::class)->prefix('events')->group(function() {
                Route::get('/', 'index');
                Route::get('{id}', 'show');
            });
        // Events routes

        // Notice category routes
            Route::controller(NoticeCategoryController::class)->prefix('notice-categories')->group(function() {
                Route::get('/', 'index');
                Route::get('{id}', 'show');
            });
        // Notice category routes

        // Notice routes
            Route::controller(NoticeController::class)->prefix('notices')->group(function() {
                Route::get('/', 'index');
                Route::get('{id}', 'show');
            });
        // Notice routes

        // Newsletter routes
            Route::controller(NewsletterController::class)->prefix('newsletters')->group(function() {
                Route::get('/', 'index');
                Route::get('{id}', 'show');
            });
        // Newsletter routes

        // Report category routes
            Route::controller(ReportCategoryController::class)->prefix('report-categories')->group(function() {
                Route::get('/', 'index');
                Route::get('{id}', 'show');
            });
        // Report category routes

        // Report routes
            Route::controller(ReportController::class)->prefix('reports')->group(function() {
                Route::get('/', 'index');
                Route::get('{id}', 'show');
            });
        // Report routes

        // Legislation routes
            Route::controller(LegislationController::class)->prefix('legislation')->group(function() {
                Route::get('/', 'index');
            });
        // Legislation routes

        // Profile routes
            Route::controller(ProfileController::class)->prefix('profile')->group(function() {
                Route::get('/', 'index');
                Route::post('/', 'update');
            });
        // Profile routes
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
                Route::post('/', 'store');
                Route::post('{id}', 'update');
                Route::delete('{id}', 'destroy');
            });
        // Notice category routes

        // Notice routes
            Route::controller(NoticeController::class)->prefix('notices')->group(function() {
                Route::post('/', 'store');
                Route::post('{id}', 'update');
                Route::delete('{id}', 'destroy');
            });
        // Notice routes

        // Newsletter routes
            Route::controller(NewsletterController::class)->prefix('newsletters')->group(function() {
                Route::post('/', 'store');
                Route::post('{id}', 'update');
                Route::delete('{id}', 'destroy');
            });
        // Newsletter routes

        // Report category routes
            Route::controller(ReportCategoryController::class)->prefix('report-categories')->group(function() {
                Route::post('/', 'store');
                Route::post('{id}', 'update');
                Route::delete('{id}', 'destroy');
            });
        // Report category routes

        // Report routes
            Route::controller(ReportController::class)->prefix('reports')->group(function() {
                Route::post('/', 'store');
                Route::post('{id}', 'update');
                Route::delete('{id}', 'destroy');
            });
        // Report routes

        // Legislation routes
            Route::controller(LegislationController::class)->prefix('legislation')->group(function() {
                Route::post('/', 'update');
            });
        // Legislation routes
    };
// Admin routes


// Admin & member routes
    Route::middleware('auth:api')->group($sharedRoutes);
// Admin & member routes


// Admin only routes
    Route::middleware(['auth:api', 'role:admin'])
        ->group(function () use ($adminRoutes) {
            $adminRoutes();
        });
// Admin only routes