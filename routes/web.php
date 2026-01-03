<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Public webhook for MQTT brokers (optional). Brokers usually can't authenticate, so expose it publicly
// Protect it by setting MQTT_WEBHOOK_SECRET in .env and supply the header X-MQTT-SECRET in requests.
Route::post('/security/api/mqtt/webhook', [\App\Http\Controllers\Security\SecurityController::class, 'mqttWebhook'])->name('security.api.mqtt.webhook');

Route::middleware('auth')->group(function () {
    // Profile (edit/update/destroy) removed — not required in this app

    Route::middleware(['role:teller'])->group(function () {
        Route::get('/teller/dashboard', [\App\Http\Controllers\Teller\TellerController::class, 'index'])->name('teller.dashboard');
        Route::get('/teller/customers/{customer}', [\App\Http\Controllers\Teller\TellerController::class, 'show'])->name('teller.customers.show');

        // API endpoints for queue management (polled by UI)
        Route::get('/teller/api/queue', [\App\Http\Controllers\Teller\TellerController::class, 'queue'])->name('teller.api.queue');
        Route::post('/teller/api/serve', [\App\Http\Controllers\Teller\TellerController::class, 'serve'])->name('teller.api.serve');
        Route::post('/teller/api/serve/next', [\App\Http\Controllers\Teller\TellerController::class, 'serveNext'])->name('teller.api.serve.next');

        // Additional API endpoints used by the teller dashboard JS
        Route::post('/teller/api/finish', [\App\Http\Controllers\Teller\TellerController::class, 'finish'])->name('teller.api.finish');
        Route::get('/teller/api/customer/{id}', [\App\Http\Controllers\Teller\TellerController::class, 'showCustomer'])->name('teller.api.customer.show');
        Route::get('/teller/api/recommendation/{id}', [\App\Http\Controllers\Teller\TellerController::class, 'showRecommendation'])->name('teller.api.recommendation');
    });
    Route::middleware(['role:security'])->group(function () {
        Route::get('/security/dashboard', [\App\Http\Controllers\Security\SecurityController::class, 'index'])->name('security.dashboard');

        // Face detection api
        Route::get('/security/api/faces', [\App\Http\Controllers\Security\SecurityController::class, 'faceIndex'])->name('security.api.faces');
        Route::get('/security/api/faces/{id}', [\App\Http\Controllers\Security\SecurityController::class, 'show'])->name('security.api.faces.show');
        Route::post('/security/api/faces/{id}/confirm', [\App\Http\Controllers\Security\SecurityController::class, 'confirm'])->name('security.api.faces.confirm');
        Route::get('/security/api/customer/{customerId}', [\App\Http\Controllers\Security\SecurityController::class, 'getCustomer'])->name('security.api.customer.show');
    });

// optional HTTP webhook for MQTT brokers or bridges to POST detection results
// This route is intentionally unprotected by auth because brokers usually can't authenticate.
// Protect it by setting MQTT_WEBHOOK_SECRET in .env and supplying the header X-MQTT-SECRET in requests.

    Route::middleware(['role:nasabah'])->group(function () {
        Route::get('/customer/queue', [\App\Http\Controllers\Customer\CustomerQueueController::class, 'index'])->name('nasabah.dashboard');
        Route::get('/customer/api/queue-status', [\App\Http\Controllers\Customer\CustomerQueueController::class, 'queueStatus'])->name('customer.api.queue.status');
        // Customer notifications (view + JSON)
        Route::get('/customer/notifications', [\App\Http\Controllers\Customer\CustomerQueueController::class, 'notifications'])
            ->name('user.notifications');
    });
});


require __DIR__.'/auth.php';
