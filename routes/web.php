<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GoogleSheetController;
use App\Http\Controllers\RfqPoController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ClientController;

// ---------------- Public routes ----------------
Route::get('/', function () {
    return view('auth.login');
});

// Auth routes
Route::get('/login',  [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/signup',        [AuthController::class, 'showSignupForm'])->name('signup.form');
Route::post('/signup',       [AuthController::class, 'signup'])->name('signup.submit');
Route::get('/oauth2callback', [GoogleSheetController::class, 'oauth2callback'])->name('oauth2callback');

// First page to display = menu after login
Route::get('/welcome', function () {
    return view('welcome', ['user' => Auth::user()]);
})->name('welcome')->middleware('auth');

// ---------------- Auth-protected routes ----------------
Route::middleware(['auth'])->group(function () {
    Route::get('/report',      [GoogleSheetController::class, 'index']);       // PO data(table)
    Route::get('/report2',     [GoogleSheetController::class, 'secondSheet']); // RFQ data(table)
    Route::get('/sheet-stats', [GoogleSheetController::class, 'sheetStats']);  // RFQ Dashboard
    Route::get('/rfq-po', [RfqPoController::class, 'rfqPo'])->name('rfq-po');  // RFQ→PO data(table)
});

Route::get('/orders',         [OrderController::class, 'index'])->name('orders.index');
Route::get('/orders/create',  [OrderController::class, 'create'])->name('orders.create');
Route::post('/orders',        [OrderController::class, 'store'])->name('orders.store');
Route::get('/orders/{order}', [OrderController::class, 'show'])->whereNumber('order')->name('orders.show');



Route::post('/clients', [ClientController::class, 'store'])->name('clients.store');
