<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Movimentacao;
use App\Models\Log;
use Carbon\Carbon;

class ProcessarArquivoETL extends Command
{
    protected $signature = 'processar:etl {filename}';
    protected $description = 'Processa um arquivo PRN e armazena os dados no banco de dados';

    public function handle()
    {
        $filename = $this->argument('filename');
        $filePath = storage_path("app/uploads/{$filename}");

        // Log de início do processamento
        Log::create([
            'acao' => 'Início do processamento',
            'detalhes' => "Processando o arquivo: {$filename}",
        ]);

        if (!file_exists($filePath)) {
            $this->error("Arquivo não encontrado: {$filePath}");
            Log::create([
                'acao' => 'Erro',
                'detalhes' => "Arquivo não encontrado: {$filePath}",
            ]);
            return;
        }

        $file = fopen($filePath, 'r');
        $headerSkipped = false;
        $lineNumber = 0; // Para rastrear o número da linha

        while (($line = fgets($file)) !== false) {
            $lineNumber++;

            // Ignorar linhas de cabeçalho
            if (!$headerSkipped) {
                if (strpos($line, 'Origem') !== false) {
                    $headerSkipped = true;
                }
                continue;
            }

            // Regex ajustado para capturar os dados
            if (preg_match('/^(\d{4}\/\d{2})\s+(\d{5}-\d{1})\s+(.+?)\s+(\d+)\s+(.+?)\s+(\d+)\s+([\d.,]+)\s+([\d.,]+)\s+(\d{2}\/\d{2}\/\d{4}\s+\d{2}:\d{2})/', $line, $matches)) {
                $origem = trim($matches[1]);
                list($cooperativa, $agencia) = explode('/', $origem);

                // Log dos dados que estão sendo inseridos
                Log::create([
                    'acao' => 'Processando linha',
                    'detalhes' => "Linha {$lineNumber}: " . json_encode([
                        'cooperativa' => trim($cooperativa),
                        'agencia' => trim($agencia),
                        'conta' => trim($matches[2]),
                        'nome' => trim($matches[3]),
                        'documento' => trim($matches[4]),
                        'codigo' => trim($matches[5]),
                        'descricao' => trim($matches[6]),
                        'debito' => str_replace(',', '.', str_replace('.', '', trim($matches[7]))),
                        'credito' => str_replace(',', '.', str_replace('.', '', trim($matches[8]))),
                        'data_hora' => Carbon::createFromFormat('d/m/Y H:i', trim($matches[9])),
                    ]),
                ]);

                // Tente inserir os dados e registre o resultado
                try {
                    Movimentacao::create([
                        'cooperativa' => trim($cooperativa),
                        'agencia' => trim($agencia),
                        'conta' => trim($matches[2]),
                        'nome' => trim($matches[3]),
                        'documento' => trim($matches[4]),
                        'codigo' => trim($matches[5]),
                        ' descricao' => trim($matches[6]),
                        'debito' => str_replace(',', '.', str_replace('.', '', trim($matches[7]))),
                        'credito' => str_replace(',', '.', str_replace('.', '', trim($matches[8]))),
                        'data_hora' => Carbon::createFromFormat('d/m/Y H:i', trim($matches[9])),
                    ]);
                } catch (\Exception $e) {
                    Log::create([
                        'acao' => 'Erro na inserção',
                        'detalhes' => "Linha {$lineNumber} falhou: " . $e->getMessage(),
                    ]);
                }
            }
        }

        fclose($file);
        $this->info("Processamento do arquivo {$filename} concluído.");
    }
}
