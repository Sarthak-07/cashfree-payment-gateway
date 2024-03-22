<?php

use Illuminate\Support\Facades\Route;
use App\Helpers\ExtensionHelper;

Route::post('/cashfree/webhook', [App\Extensions\Gateways\Cashfree\Cashfree::class, 'webhook'])->name('cashfree.webhook');
Route::get('/cashfree/payment/{invoiceId}/{payment_session_id}', function ($invoiceId, $payment_session_id) {
    $test_mode = ExtensionHelper::getConfig('Cashfree', 'test_mode');
    return view('Cashfree::payment', compact('invoiceId', 'payment_session_id', 'test_mode'));
})->name('cashfree.payment');