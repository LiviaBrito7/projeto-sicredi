<?php

use App\Http\Controllers\FileUploadController;
use App\Http\Controllers\MetricsController;
use Illuminate\Support\Facades\Route;
use App\Console\Commands\ProcessarArquivoETL;
use Illuminate\Support\Facades\Artisan;

Route::post('/processar/etl', function (Illuminate\Http\Request $request) {
    $filename = $request->input('filename');

    Artisan::call('processar:etl', ['filename' => $filename]);

    return response()->json(['message' => 'Processamento iniciado.']);
});

Route::post('/upload', [FileUploadController::class, 'upload']);
Route::get('/metrics', [MetricsController::class, 'getMetrics']);
