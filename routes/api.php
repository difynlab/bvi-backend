<?php

use App\Http\Controllers\Auth\AuthenticationController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\EventCategoryController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\ImportantInfoController;
use App\Http\Controllers\ExpertInfoController;
use App\Http\Controllers\LegislationCategoryController;
use App\Http\Controllers\LegislationController;
// use App\Http\Controllers\LegislationFileController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\MemberFirmController;
use App\Http\Controllers\MemberSubscriptionDetailController;
use App\Http\Controllers\MembershipPlanController;
use App\Http\Controllers\NewsletterCategoryController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\NoticeCategoryController;
use App\Http\Controllers\NoticeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportCategoryController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SpecializationController;
use Illuminate\Support\Facades\Route;

Route::get('login', function () {
    return errorResponse('Authentication required', 401);
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
        // Event category routes
            Route::controller(EventCategoryController::class)->prefix('event-categories')->group(function() {
                Route::get('/', 'index');
                Route::get('{id}', 'show');
            });
        // Event category routes

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

        // Newsletter category routes
            Route::controller(NewsletterCategoryController::class)->prefix('newsletter-categories')->group(function() {
                Route::get('/', 'index');
                Route::get('{id}', 'show');
            });
        // Newsletter category routes

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

        // Legislation category routes
            Route::controller(LegislationCategoryController::class)->prefix('legislation-categories')->group(function() {
                Route::get('/', 'index');
                Route::get('{id}', 'show');
            });
        // Legislation category routes

        // Legislation routes
            // Route::controller(LegislationController::class)->prefix('legislation')->group(function() {
            //     Route::get('/', 'index');
            // });

            // Route::controller(LegislationFileController::class)->prefix('legislation-files')->group(function() {
            //     Route::get('/', 'index');
            //     Route::get('{id}', 'show');
            // });

            Route::controller(LegislationController::class)->prefix('legislations')->group(function() {
                Route::get('/', 'index');
                Route::get('{id}', 'show');
            });
        // Legislation routes

        // Membership plan routes
            Route::controller(MembershipPlanController::class)->prefix('membership-plans')->group(function() {
                Route::get('/', 'index');
                Route::get('{id}', 'show');
            });
        // Membership plan routes

        // Important info routes
            Route::controller(ImportantInfoController::class)->prefix('important-info')->group(function() {
                Route::get('/', 'index');
            });
        // Important info routes

        // Expert info routes
            Route::controller(ExpertInfoController::class)->prefix('expert-info')->group(function() {
                Route::get('/', 'index');
            });
        // Expert info routes

        // Specialization routes
            Route::controller(SpecializationController::class)->prefix('specializations')->group(function() {
                Route::get('/', 'index');
                Route::get('{id}', 'show');
            });
        // Specialization routes

        // Member firm routes
            Route::controller(MemberFirmController::class)->prefix('member-firms')->group(function() {
                Route::get('/', 'index');
                Route::get('{id}', 'show');
            });
        // Member firm routes

        // Profile routes
            Route::controller(ProfileController::class)->prefix('profile')->group(function() {
                Route::get('/', 'index');
                Route::post('/', 'update');
                Route::post('renew-membership', 'renewMembership');
            });
        // Profile routes

        // Member subscription detail routes
            Route::controller(MemberSubscriptionDetailController::class)->prefix('member-subscription-details')->group(function() {
                Route::get('/', 'index');
                Route::post('/', 'update');
            });
        // Member subscription detail routes

        // Notification route
            Route::controller(NotificationController::class)->prefix('notifications')->group(function() {
                Route::get('/', 'index');
                Route::get('mark-seen', 'markSeen');
            });
        // Notification route
    };
// Shared routes

// Admin routes
    $adminRoutes = function () {
        // Event category routes
            Route::controller(EventCategoryController::class)->prefix('event-categories')->group(function() {
                Route::post('/', 'store');
                Route::post('{id}', 'update');
                Route::delete('{id}', 'destroy');
            });
        // Event category routes

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

        // Newsletter category routes
            Route::controller(NewsletterCategoryController::class)->prefix('newsletter-categories')->group(function() {
                Route::post('/', 'store');
                Route::post('{id}', 'update');
                Route::delete('{id}', 'destroy');
            });
        // Newsletter category routes

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

        // Legislation category routes
            Route::controller(LegislationCategoryController::class)->prefix('legislation-categories')->group(function() {
                Route::post('/', 'store');
                Route::post('{id}', 'update');
                Route::delete('{id}', 'destroy');
            });
        // Legislation category routes

        // Legislation routes
            // Route::controller(LegislationController::class)->prefix('legislation')->group(function() {
            //     Route::post('/', 'update');
            // });

            // Route::controller(LegislationFileController::class)->prefix('legislation-files')->group(function() {
            //     Route::post('/', 'store');
            //     Route::post('{id}', 'update');
            //     Route::delete('{id}', 'destroy');
            // });

            Route::controller(LegislationController::class)->prefix('legislations')->group(function() {
                Route::post('/', 'store');
                Route::post('{id}', 'update');
                Route::delete('{id}', 'destroy');
            });
        // Legislation routes

        // Membership plan routes
            Route::controller(MembershipPlanController::class)->prefix('membership-plans')->group(function() {
                Route::post('/', 'store');
                Route::post('{id}', 'update');
                Route::delete('{id}', 'destroy');
            });
        // Membership plan routes

        // Important info routes
            Route::controller(ImportantInfoController::class)->prefix('important-info')->group(function() {
                Route::post('/', 'update');
            });
        // Important info routes

        // Expert info routes
            Route::controller(ExpertInfoController::class)->prefix('expert-info')->group(function() {
                Route::post( '/', 'update');
            });
        // Expert info routes

        // Specialization routes
            Route::controller(SpecializationController::class)->prefix('specializations')->group(function() {
                Route::post('/', 'store');
                Route::post('{id}', 'update');
                Route::delete('{id}', 'destroy');
            });
        // Specialization routes

        // Member firm routes
            Route::controller(MemberFirmController::class)->prefix('member-firms')->group(function() {
                Route::post('/', 'store');
                Route::post('{id}', 'update');
                Route::delete('{id}', 'destroy');
            });
        // Member firm routes

        // Member routes
            Route::controller(MemberController::class)->prefix('members')->group(function() {
                Route::get('/', 'index');
                Route::get('{id}', 'show');
                Route::post('/', 'store');
                Route::post('{id}', 'update');
                Route::delete('{id}', 'destroy');

                Route::post('{id}/renew-membership', 'renewMembership');
                Route::post('{id}/update-membership/{payment_id}', 'updateMembership');
            });
        // Member routes
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