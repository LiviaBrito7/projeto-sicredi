<?php

use App\Http\Controllers\FileUploadController;
use App\Http\Controllers\MetricsController;
use Illuminate\Support\Facades\Route;

Route::post('/upload', [FileUploadController::class, 'upload']);
Route::get('/metrics', [MetricsController::class, 'index']);
