<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login');
});

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware(['role:teller'])->group(function () {
        Route::get('/teller/dashboard', [\App\Http\Controllers\Teller\TellerController::class, 'index'])->name('teller.dashboard');
        Route::get('/teller/customers/{customer}', [\App\Http\Controllers\Teller\TellerController::class, 'show'])->name('teller.customers.show');

        // API endpoints for queue management (polled by UI)
        Route::get('/teller/api/queue', [\App\Http\Controllers\Teller\TellerController::class, 'queue'])->name('teller.api.queue');
        Route::post('/teller/api/serve', [\App\Http\Controllers\Teller\TellerController::class, 'serve'])->name('teller.api.serve');
        Route::post('/teller/api/serve/next', [\App\Http\Controllers\Teller\TellerController::class, 'serveNext'])->name('teller.api.serve.next');
        Route::post('/teller/api/finish', [\App\Http\Controllers\Teller\TellerController::class, 'finish'])->name('teller.api.finish');
        Route::get('/teller/api/recommendation/{customer}', [\App\Http\Controllers\Teller\TellerController::class, 'showRecommendation'])->name('teller.api.recommendation');
        // convenience endpoints
        Route::post('/teller/api/serve/{customer}', [\App\Http\Controllers\Teller\TellerController::class, 'serveById'])->name('teller.api.serve.byid');
        Route::get('/teller/api/customer/{customer}', [\App\Http\Controllers\Teller\TellerController::class, 'showCustomer'])->name('teller.api.customer');
    });
    Route::middleware(['role:security'])->group(function () {
        Route::get('/security/dashboard', function () {
            return view('dashboard.securitydashboard');
        })->name('security.dashboard');
    });
    Route::middleware(['role:nasabah'])->group(function () {
        Route::get('/customer/queue', function () {
            return view('user.userqueue');
        })->name('nasabah.dashboard');
    });
});


require __DIR__.'/auth.php';
