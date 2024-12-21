<?php

namespace App\Http\Controllers;

use App\Models\FileUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class FileUploadController extends Controller
{
    public function upload(Request $request)
{
    // Validação dos dados recebidos
    $request->validate([
        'file' => 'required|file|max:2048000', // Limite de 2GB
        'chunk' => 'required|integer',
        'totalChunks' => 'required|integer',
        'original_name' => 'required|string',
    ]);

    // Obter informações do arquivo
    $chunkIndex = $request->input('chunk');
    $totalChunks = $request->input('totalChunks');
    $originalName = $request->input('original_name');

    // Armazenar o chunk
    $chunkPath = $chunkIndex . '-' . $originalName; // Apenas o nome do arquivo
    $request->file('file')->storeAs('uploads/chunks', $chunkPath); // O Laravel adiciona o caminho base

    Log::info('Chunk armazenado', [
        'chunk' => $chunkIndex,
        'original_name' => $originalName,
        'path' => 'uploads/chunks/' . $chunkPath // Log para verificar o caminho
    ]);

    // Verificar se todos os chunks foram enviados
    if ($chunkIndex == $totalChunks - 1) {
        // Combinar os chunks em um único arquivo
        Log::info('Todos os chunks recebidos, iniciando a combinação.');
        $this->combineChunks($originalName, $totalChunks);
    } else {
        Log::info('Chunk recebido com sucesso', [
            'chunk' => $chunkIndex,
            'totalChunks' => $totalChunks
        ]);
    }

    return response()->json(['status' => 'success']);
}

private function combineChunks($originalFileName, $totalChunks)
{
    $finalFileName = pathinfo($originalFileName, PATHINFO_FILENAME) . '.prn';
    $finalPath = 'uploads/' . $finalFileName;
    $finalFile = fopen(storage_path('app/' . $finalPath), 'wb');

    Log::info('Iniciando a combinação dos chunks', [
        'original_name' => $originalFileName,
        'final_name' => $finalFileName,
        'totalChunks' => $totalChunks
    ]);

    for ($i = 0; $i < $totalChunks; $i++) {
        $chunkPath = storage_path('app/uploads/chunks/' . $i . '-' . $originalFileName);
        Log::info('Verificando chunk', ['path' => $chunkPath]); // Log para verificar o caminho

        if (file_exists($chunkPath)) {
            $chunk = fopen($chunkPath, 'rb');
            stream_copy_to_stream($chunk, $finalFile);
            fclose($chunk);
            unlink($chunkPath);
            Log::info('Chunk combinado', [
                'chunk' => $i,
                'path' => $chunkPath
            ]);
        } else {
            Log::warning('Chunk não encontrado', [
                'chunk' => $i,
                'path' => $chunkPath
            ]);
        }
    }

    fclose($finalFile);

    // Salvar informações no banco de dados
    FileUpload::create([
        'original_name' => $finalFileName,
        'path' => $finalPath,
        'size' => filesize(storage_path('app/' . $finalPath)),
        'status' => 'completed'
    ]);

    Log::info('Arquivo combinado e salvo', ['path' => $finalPath]);
}
}
