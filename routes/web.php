<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventPublicController;
use App\Http\Controllers\PriceTierController;
use App\Http\Controllers\ParticipantController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\AIController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\EarningsController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\PublicEventController;
use App\Http\Controllers\RoadmapController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserHistoryController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }

    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
});

Route::post('/test-image-upload', [EventController::class, 'testImageUpload'])->middleware('auth');

Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('google.redirect');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('google.callback');

// ROTAS PÚBLICAS - ACESSÍVEIS SEM AUTENTICAÇÃO
Route::get('/events-public', [PublicEventController::class, 'index'])->name('events.public.index');
Route::post('/events-public/location', [PublicEventController::class, 'storeLocation'])->name('events.public.storeLocation');
Route::get('/e/{event:slug}', [EventPublicController::class, 'show'])->name('events.public.show');

// Rota de participação agora exige autenticação
Route::post('/e/{event:slug}/participate', [EventPublicController::class, 'participate'])
    ->middleware(['auth', 'verified'])
    ->name('events.public.participate');

Route::post('/webhooks/abacatepay', [WebhookController::class, 'handleAbacatePay']);
Route::post('/webhooks/woovi', [WebhookController::class, 'handleWoovi'])->name('webhooks.woovi');

// ROTAS PROTEGIDAS - EXIGEM AUTENTICAÇÃO
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/user/history', [UserHistoryController::class, 'index'])->name('user.history');

    // DASHBOARD
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/analytics', [DashboardController::class, 'analytics'])->name('dashboard.analytics');

    // ROADMAP
    Route::get('/roadmap', [RoadmapController::class, 'index'])->name('roadmap.index');
    Route::post('/roadmap', [RoadmapController::class, 'store'])->name('roadmap.store');
    Route::put('/roadmap/{roadmapItem}/status', [RoadmapController::class, 'updateStatus'])->name('roadmap.updateStatus');
    Route::post('/roadmap/{id}/like', [RoadmapController::class, 'like'])->name('roadmap.like');

    // EVENTOS
    Route::resource('events', EventController::class);
    Route::post('/events/{event}/publish', [EventController::class, 'publish'])->name('events.publish');
    Route::post('/events/{event}/unpublish', [EventController::class, 'unpublish'])->name('events.unpublish');
    Route::get('/events/{event}/analytics', [EventController::class, 'analytics'])->name('events.analytics');

    // PRICE TIERS
    Route::post('/events/{event}/price-tiers', [PriceTierController::class, 'store'])->name('price-tiers.store');
    Route::put('/price-tiers/{priceTier}', [PriceTierController::class, 'update'])->name('price-tiers.update');
    Route::delete('/price-tiers/{priceTier}', [PriceTierController::class, 'destroy'])->name('price-tiers.destroy');
    Route::post('/price-tiers/{priceTier}/toggle', [PriceTierController::class, 'toggle'])->name('price-tiers.toggle');

    // PARTICIPANTES
    Route::get('/events/{event}/participants', [ParticipantController::class, 'index'])->name('events.participants');
    Route::put('/participants/{participant}', [ParticipantController::class, 'update'])->name('participants.update');
    Route::delete('/participants/{participant}', [ParticipantController::class, 'destroy'])->name('participants.destroy');
    Route::post('/participants/{participant}/check-in', [ParticipantController::class, 'checkIn'])->name('participants.check-in');
    Route::get('/events/{event}/export/participants', [ExportController::class, 'participants'])->name('events.export.participants');
    Route::get('/events/{event}/export/financials', [ExportController::class, 'eventFinancials'])->name('events.export.financials');

    // PAGAMENTOS
    Route::get('/payment/{participant}/checkout', [PaymentController::class, 'checkout'])->name('payment.checkout');
    Route::get('/payment/{participant}/success', [PaymentController::class, 'success'])->name('payment.success');
    Route::get('/payment/{participant}/status', [PaymentController::class, 'status'])->name('payment.status');

    // Subscription routes
    Route::post('/settings/cancel-subscription', [SettingsController::class, 'cancelSubscription'])->name('settings.cancel-subscription');
    Route::get('/settings/subscription-status/{subscriptionId}', [SettingsController::class, 'checkUpgradeStatus'])->name('settings.subscription-status');


    // Earnings
    Route::get('/earnings', [EarningsController::class, 'index'])->name('earnings.index');
    Route::post('/earnings/account', [EarningsController::class, 'storeAccount'])->name('earnings.account.store');
    Route::put('/earnings/account', [EarningsController::class, 'updateAccount'])->name('earnings.account.update');
    Route::post('/earnings/withdrawal', [EarningsController::class, 'requestWithdrawal'])->name('earnings.withdrawal.store');

    // AI
    Route::get('/ai/suggestions', [AIController::class, 'suggestions'])->name('ai.suggestions');
    Route::post('/ai/generate', [AIController::class, 'generate'])->name('ai.generate');
    Route::post('/ai/suggestions/{suggestion}/apply', [AIController::class, 'applySuggestion'])->name('ai.suggestions.apply');
    Route::post('/ai/suggestions/{suggestion}/feedback', [AIController::class, 'feedback'])->name('ai.suggestions.feedback');

    // SUPPLIERS
    Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
    Route::get('/suppliers/{supplier}', [SupplierController::class, 'show'])->name('suppliers.show');
    Route::get('/suppliers/category/{category}', [SupplierController::class, 'getByCategory'])->name('suppliers.byCategory');
    Route::post('/suppliers/suggest', [SupplierController::class, 'suggest'])->name('suppliers.suggest');

    // SETTINGS
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings/profile', [SettingsController::class, 'updateProfile'])->name('settings.profile.update');
    Route::get('/settings/billing', [SettingsController::class, 'billing'])->name('settings.billing');
    Route::post('/settings/upgrade-pro', [SettingsController::class, 'upgradeToPro'])->name('settings.upgrade-pro');
    Route::get('/settings/upgrade-success', [SettingsController::class, 'upgradeSuccess'])->name('settings.upgrade-success');
    Route::get('/settings/upgrade-status/{subscriptionId}', [SettingsController::class, 'checkUpgradeStatus'])->name('settings.check-upgrade-status');
    Route::post('/settings/cancel-subscription', [SettingsController::class, 'cancelSubscription'])->name('settings.cancel-subscription');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
