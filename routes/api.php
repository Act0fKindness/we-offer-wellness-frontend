<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\StripeWebhookController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\Api\V3SubscriberController;

Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle']);

// Cart APIs
Route::post('/cart/promo', [CartController::class, 'promo']);
Route::get('/cart/count', [CartController::class, 'count']);
Route::get('/cart/mini', [CartController::class, 'mini']);
Route::post('/cart/add', [CartController::class, 'add']);
Route::post('/cart/remove', [CartController::class, 'remove']);
Route::post('/cart/update', [CartController::class, 'update']);
Route::post('/cart/gift', [CartController::class, 'gift']);

// V3 subscriber opt-in API
Route::post('/v3-subscribers', [V3SubscriberController::class, 'store'])->name('api.v3-subscribers.store');
Route::post('/v3-subscribers/track', [V3SubscriberController::class, 'track'])->name('api.v3-subscribers.track');
