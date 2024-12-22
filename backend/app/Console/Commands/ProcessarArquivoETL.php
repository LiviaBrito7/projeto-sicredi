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
        $currentOrigin = null;
        $lineNumber = 0;

        while (($line = fgets($file)) !== false) {
            $lineNumber++;
            $line = trim($line);

            // Ignorar cabeçalhos e linhas desnecessárias
            if (preg_match('/^={3,}|-{3,}|COOP CRED|^Pagina|^Total UA:|Data\/Hora/', $line)) {
                continue;
            }

            // Captura da origem (exemplo: 0000/19)
            if (preg_match('/^(\d{4}\/\d{2})/', $line, $matches)) {
                $currentOrigin = $matches[1];
                continue;
            }

            // Captura dos dados da movimentação
            if (preg_match('/^\s*(\d{5}-\d)\s+(.{25})\s+(\d+)\s+(\S+)\s+(.{25})\s+([\d,.]+)?\s*([\d,.]+)?\s+(\d+)\s*$/', $line, $matches)) {
                // Ler a próxima linha para obter a data/hora
                $nextLine = trim(fgets($file));
                $dataHora = null;

                if (preg_match('/(\d{2}\/\d{2}\/\d{4}\s+\d{2}:\d{2})/', $nextLine, $dateMatches)) {
                    $dataHora = Carbon::createFromFormat('d/m/Y H:i', trim($dateMatches[1]));
                }

                try {
                    Movimentacao::create([
                        'cooperativa' => $currentOrigin ? explode('/', $currentOrigin)[0] : null,
                        'agencia' => $currentOrigin ? explode('/', $currentOrigin)[1] : null,
                        'conta' => trim($matches[1]),
                        'nome' => trim($matches[2]),
                        'documento' => trim($matches[3]),
                        'codigo' => trim($matches[4]),
                        'descricao' => trim($matches[5]),
                        'debito' => isset($matches[6]) ? $this->formatarValor($matches[6]) : null,
                        'credito' => isset($matches[7]) ? $this->formatarValor($matches[7]) : null,
                        'id_mov' => trim($matches[8]),
                        'data_hora' => $dataHora
                    ]);

                    Log::create([
                        'acao' => 'Movimentação processada',
                        'detalhes' => "Linha {$lineNumber}: Movimentação processada com sucesso"
                    ]);

                } catch (\Exception $e) {
                    Log::create([
                        'acao' => 'Erro na inserção',
                        'detalhes' => "Erro ao inserir movimentação da linha {$lineNumber}: " . $e->getMessage()
                    ]);
                }
            }
        }

        fclose($file);

        Log::create([
            'acao' => 'Fim do processamento',
            'detalhes' => "Processamento do arquivo {$filename} concluído."
        ]);

        $this->info("Processamento do arquivo {$filename} concluído.");
    }

    private function formatarValor($valor)
    {
        if (empty($valor)) return null;
        return floatval(str_replace(',', '.', str_replace('.', '', trim($valor))));
    }
}
