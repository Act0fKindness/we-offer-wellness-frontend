<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\StripeWebhookController;

Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle']);

