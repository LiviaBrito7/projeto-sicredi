<?php

namespace App\Http\Controllers;

use App\Models\Movimentacao;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class MetricsController extends Controller
{
    public function index(): JsonResponse
    {
        // Data com maior e menor quantidade de movimentações
        $movimentacoesPorData = Movimentacao::select(DB::raw('DATE(data_hora) as data'), DB::raw('COUNT(*) as total'))
            ->groupBy('data')
            ->orderBy('total', 'desc')
            ->get();

        $maiorMovimentacao = $movimentacoesPorData->first();
        $menorMovimentacao = $movimentacoesPorData->last();

        // Data com maior e menor soma de movimentações
        $somaMovimentacoesPorData = Movimentacao::select(DB::raw('DATE(data_hora) as data'), DB::raw('SUM(debito + credito) as total'))
            ->groupBy('data')
            ->orderBy('total', 'desc')
            ->get();

        $maiorSoma = $somaMovimentacoesPorData->first();
        $menorSoma = $somaMovimentacoesPorData->last();

        // Dia da semana com mais movimentações dos tipos "RX1" e "PX1"
        $movimentacoesPorDiaSemana = Movimentacao::select(DB::raw('DAYOFWEEK(data_hora) as dia'), DB::raw('COUNT(*) as total'))
            ->whereIn('codigo', ['RX1', 'PX1'])
            ->groupBy('dia')
            ->orderBy('total', 'desc')
            ->get();

        $diaMaisMovimentado = $movimentacoesPorDiaSemana->first();

        // Quantidade e valor movimentado por cooperativa/agência
        $movimentacoesPorCoopAgencia = Movimentacao::select('cooperativa', 'agencia', DB::raw('COUNT(*) as total_movimentacoes'), DB::raw('SUM(debito + credito) as total_valor'))
            ->groupBy('cooperativa', 'agencia')
            ->get();

        // Relação de créditos x débitos ao longo das horas do dia
        $movimentacoesPorHora = Movimentacao::select(DB::raw('HOUR(data_hora) as hora'), DB::raw('SUM(debito) as total_debito'), DB::raw('SUM(credito) as total_credito'))
            ->groupBy('hora')
            ->orderBy('hora')
            ->get();

        return response()->json([
            'maior_movimentacao' => $maiorMovimentacao,
            'menor_movimentacao' => $menorMovimentacao,
            'maior_soma' => $maiorSoma,
            'menor_soma' => $menorSoma,
            'dia_mais_movimentado' => $diaMaisMovimentado,
            'movimentacoes_por_coop_agencia' => $movimentacoesPorCoopAgencia,
            'movimentacoes_por_hora' => $movimentacoesPorHora,
        ]);
    }
}
