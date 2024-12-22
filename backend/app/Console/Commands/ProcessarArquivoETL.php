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
        $totalProcessed = 0;

        while (($line = fgets($file)) !== false) {
            $lineNumber++;
            $line = trim($line);

            // Ignorar linhas desnecessárias mas manter o processamento da origem
            if (empty($line) || preg_match('/^={3,}|-{3,}|COOP CRED|PAGINA:|SISTEMA SICREDI|MOVIMENTO DIARIO|Data\/Hora|^Origem\s+Conta/', $line)) {
                continue;
            }

            // Captura da origem (exemplo: 0000/19)
            if (preg_match('/^(\d{4}\/\d{2})/', $line, $matches)) {
                $currentOrigin = $matches[1];
                continue;
            }

            // Ignora linhas de total
            if (preg_match('/Total UA:/', $line)) {
                continue;
            }

            // Captura dos dados da movimentação
            if (preg_match('/^\s*(\d{5}-\d|)\s+(.{25})\s+(\S+)\s+(\S+)\s+(.{25})\s*([\d,.]+)?\s*([\d,.]+)?\s+(\d+)\s*$/', $line, $matches)) {
                // Ler a próxima linha para obter a data/hora
                $nextLine = trim(fgets($file));
                $dataHora = null;

                if (preg_match('/(\d{2}\/\d{2}\/\d{4}\s+\d{2}:\d{2})/', $nextLine, $dateMatches)) {
                    $dataHora = Carbon::createFromFormat('d/m/Y H:i', trim($dateMatches[1]));
                }

                // Determinar qual valor é débito e qual é crédito
                $debito = null;
                $credito = null;

                // Se há valor antes do ID, é débito
                if (!empty($matches[6]) && empty($matches[7])) {
                    $debito = $this->formatarValor($matches[6]);
                }
                // Se há valor depois do espaço em branco e antes do ID, é crédito
                elseif (!empty($matches[7])) {
                    $credito = $this->formatarValor($matches[7]);
                }

                try {
                    // Se a conta estiver vazia, usar a última conta válida
                    $conta = !empty(trim($matches[1])) ? trim($matches[1]) : null;

                    Movimentacao::create([
                        'cooperativa' => $currentOrigin ? explode('/', $currentOrigin)[0] : null,
                        'agencia' => $currentOrigin ? explode('/', $currentOrigin)[1] : null,
                        'conta' => $conta,
                        'nome' => trim($matches[2]),
                        'documento' => trim($matches[3]),
                        'codigo' => trim($matches[4]),
                        'descricao' => trim($matches[5]),
                        'debito' => $debito,
                        'credito' => $credito,
                        'id_mov' => trim($matches[8]),
                        'data_hora' => $dataHora
                    ]);

                    $totalProcessed++;

                    Log::create([
                        'acao' => 'Movimentação processada',
                        'detalhes' => "Linha {$lineNumber}: Movimentação processada com sucesso. Total: {$totalProcessed}"
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
            'detalhes' => "Processamento do arquivo {$filename} concluído. Total de movimentações: {$totalProcessed}"
        ]);

        $this->info("Processamento concluído. Total de movimentações: {$totalProcessed}");
    }

    private function formatarValor($valor)
    {
        if (empty($valor)) return null;
        $valor = trim($valor);
        return floatval(str_replace(',', '.', str_replace('.', '', $valor)));
    }
}
