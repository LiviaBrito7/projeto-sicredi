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
        $file = $request->file('file');

        Log::info('Iniciando upload de arquivo', [
            'nome' => $file->getClientOriginalName(),
            'tamanho' => $file->getSize()
        ]);

        $path = $file->store('uploads');
        Log::info('Arquivo armazenado em', ['path' => $path]);

        $fileUpload = FileUpload::create([
            'original_name' => $file->getClientOriginalName(),
            'path' => $path,
            'size' => $file->getSize(),
            'status' => 'pending'
        ]);

        return response()->json($fileUpload);
    }

    public function process($id)
    {
        $fileUpload = FileUpload::findOrFail($id);

        Log::info('Iniciando processamento ETL', [
            'file_id' => $id
        ]);

        // Aqui vai a lógica de processamento do arquivo
        // Implementar o processamento em chunks para arquivos grandes

        $fileUpload->update([
            'status' => 'processed',
            'processed_at' => now()
        ]);

        Log::info('Processamento ETL finalizado', [
            'file_id' => $id
        ]);

        return response()->json(['message' => 'Arquivo processado com sucesso']);
    }
}
