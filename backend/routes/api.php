<?php

use App\Http\Controllers\FileUploadController;
use App\Http\Controllers\MetricsController;
use Illuminate\Support\Facades\Route;

Route::post('/upload', [FileUploadController::class, 'upload']);
Route::post('/process/{id}', [FileUploadController::class, 'process']);
