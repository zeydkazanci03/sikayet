<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\ComplaintController as FrontendComplaintController;
use App\Http\Controllers\Frontend\BrandController as FrontendBrandController;
use App\Http\Controllers\Frontend\BlogController;
use App\Http\Controllers\Frontend\ProfileController as FrontendProfileController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ComplaintController as AdminComplaintController;
use App\Http\Controllers\Admin\BrandController as AdminBrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\BlogController as AdminBlogController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\ModerationController;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Brand\DashboardController as BrandDashboardController;
use App\Http\Controllers\Brand\ComplaintController as BrandComplaintController;
use App\Http\Controllers\Brand\ProfileController as BrandProfileController;

// Frontend Routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/ara', [HomeController::class, 'search'])->name('search');

// Complaint Routes (Frontend)
Route::get('/sikayetler', [FrontendComplaintController::class, 'index'])->name('frontend.complaints.index');
Route::get('/sikayetler/{brand}/{number}', [FrontendComplaintController::class, 'show'])->name('frontend.complaints.show');
Route::post('/sikayetler/{id}/helpful', [FrontendComplaintController::class, 'helpful'])->name('complaints.helpful');
Route::post('/sikayetler/{id}/not-helpful', [FrontendComplaintController::class, 'notHelpful'])->name('complaints.not-helpful');

// Brand Routes (Frontend)
Route::get('/markalar', [FrontendBrandController::class, 'index'])->name('frontend.brands.index');
Route::get('/markalar/{slug}', [FrontendBrandController::class, 'show'])->name('frontend.brands.show');

// Blog Routes
Route::get('/blog', [BlogController::class, 'index'])->name('frontend.blog.index');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('frontend.blog.show');

// Auth Routes
Route::middleware('guest')->group(function () {
    Route::get('/giris', 'App\Http\Controllers\Auth\LoginController@showLoginForm')->name('login');
    Route::post('/giris', 'App\Http\Controllers\Auth\LoginController@login');
    Route::get('/kayit', 'App\Http\Controllers\Auth\RegisterController@showRegistrationForm')->name('register');
    Route::post('/kayit', 'App\Http\Controllers\Auth\RegisterController@register');
});

// Authenticated User Routes
Route::middleware('auth')->group(function () {
    // Complaint Creation
    Route::get('/sikayetler/yeni', [FrontendComplaintController::class, 'create'])->name('frontend.complaints.create');
    Route::post('/sikayetler', [FrontendComplaintController::class, 'store'])->name('frontend.complaints.store');

    // User Profile
    Route::get('/profil', [FrontendProfileController::class, 'show'])->name('frontend.profile.show');
    Route::get('/profil/duzen', [FrontendProfileController::class, 'edit'])->name('frontend.profile.edit');
    Route::put('/profil', [FrontendProfileController::class, 'update'])->name('frontend.profile.update');
    Route::get('/profil/sikayetlerim', [FrontendProfileController::class, 'myComplaints'])->name('frontend.profile.complaints');
    Route::get('/profil/bildirimlerim', [FrontendProfileController::class, 'notifications'])->name('frontend.profile.notifications');

    Route::post('/logout', 'App\Http\Controllers\Auth\LoginController@logout')->name('logout');
});

// Admin Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Users
    Route::resource('users', UserController::class);
    Route::post('users/{user}/ban', [UserController::class, 'ban'])->name('users.ban');
    Route::post('users/{user}/unban', [UserController::class, 'unban'])->name('users.unban');
    Route::post('users/bulk-action', [UserController::class, 'bulkAction'])->name('users.bulk-action');

    // Complaints
    Route::resource('complaints', AdminComplaintController::class);
    Route::post('complaints/{complaint}/approve', [AdminComplaintController::class, 'approve'])->name('complaints.approve');
    Route::post('complaints/{complaint}/reject', [AdminComplaintController::class, 'reject'])->name('complaints.reject');
    Route::post('complaints/{complaint}/spam', [AdminComplaintController::class, 'markAsSpam'])->name('complaints.spam');
    Route::post('complaints/{complaint}/assign-moderator', [AdminComplaintController::class, 'assignModerator'])->name('complaints.assign-moderator');
    Route::post('complaints/{complaint}/priority', [AdminComplaintController::class, 'changePriority'])->name('complaints.priority');
    Route::post('complaints/bulk-approve', [AdminComplaintController::class, 'bulkApprove'])->name('complaints.bulk-approve');
    Route::post('complaints/bulk-reject', [AdminComplaintController::class, 'bulkReject'])->name('complaints.bulk-reject');
    Route::get('complaints/export', [AdminComplaintController::class, 'export'])->name('complaints.export');

    // Brands
    Route::resource('brands', AdminBrandController::class);
    Route::post('brands/{brand}/approve', [AdminBrandController::class, 'approve'])->name('brands.approve');
    Route::post('brands/{brand}/reject', [AdminBrandController::class, 'reject'])->name('brands.reject');
    Route::post('brands/{brand}/suspend', [AdminBrandController::class, 'suspend'])->name('brands.suspend');
    Route::post('brands/{brand}/toggle-feature', [AdminBrandController::class, 'toggleFeature'])->name('brands.toggle-feature');

    // Categories
    Route::resource('categories', CategoryController::class);
    Route::post('categories/reorder', [CategoryController::class, 'reorder'])->name('categories.reorder');

    // Blog
    Route::resource('blog', AdminBlogController::class);

    // Pages
    Route::resource('pages', PageController::class);

    // Settings
    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::put('settings', [SettingController::class, 'update'])->name('settings.update');

    // Moderation
    Route::get('moderation', [ModerationController::class, 'index'])->name('moderation.index');
    Route::post('moderation', [ModerationController::class, 'store'])->name('moderation.store');

    // Analytics
    Route::get('analytics', [AnalyticsController::class, 'index'])->name('analytics');
});

// Brand Routes
Route::middleware(['auth', 'brand'])->prefix('brand')->name('brand.')->group(function () {
    Route::get('/', [BrandDashboardController::class, 'index'])->name('dashboard');
    Route::resource('complaints', BrandComplaintController::class, ['only' => ['index', 'show']]);
    Route::post('complaints/{complaint}/respond', [BrandComplaintController::class, 'respond'])->name('complaints.respond');
    Route::post('complaints/{complaint}/solved', [BrandComplaintController::class, 'markAsSolved'])->name('complaints.solved');
    Route::get('profile', [BrandProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [BrandProfileController::class, 'update'])->name('profile.update');
    Route::post('profile/logo', [BrandProfileController::class, 'updateLogo'])->name('profile.logo');
    Route::post('profile/banner', [BrandProfileController::class, 'updateBanner'])->name('profile.banner');
});
