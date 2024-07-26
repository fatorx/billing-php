<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BillingCsvController;

Route::post('/billings/upload', [BillingCsvController::class, 'upload']);
