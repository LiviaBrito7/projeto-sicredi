<?php

namespace App\Http\Controllers;

use App\Models\Movimentacao;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MetricsController extends Controller
{
    public function getMetrics()
{
    // Data com maior e menor quantidade de movimentações
    $movimentacoesPorData = Movimentacao::selectRaw('DATE(data_hora) as data, COUNT(*) as total')
        ->groupBy('data')
        ->orderBy('total', 'desc')
        ->get();

    $maiorMovimentacao = $movimentacoesPorData->first() ?? ['data' => null, 'total' => 0];
    $menorMovimentacao = $movimentacoesPorData->last() ?? ['data' => null, 'total' => 0];

    // Data com maior e menor soma de movimentações
    $somaMovimentacoesPorData = Movimentacao::selectRaw('DATE(data_hora) as data, SUM(COALESCE(debito, 0) + COALESCE(credito, 0)) as total')
        ->groupBy('data')
        ->orderBy('total', 'desc')
        ->get();

    $maiorSoma = $somaMovimentacoesPorData->first() ?? ['data' => null, 'total' => 0];
    $menorSoma = $somaMovimentacoesPorData->last() ?? ['data' => null, 'total' => 0];

    // Dia da semana com mais movimentações
    $movimentacoesPorDiaSemana = Movimentacao::selectRaw('DAYOFWEEK(data_hora) as dia, COUNT(*) as total')
        ->whereIn('codigo', ['RX1', 'PX1'])
        ->groupBy('dia')
        ->orderBy('total', 'desc')
        ->first();

    $diaSemanaMaisMovimentacoes = $movimentacoesPorDiaSemana
        ? [
            'dia' => Carbon::now()->startOfWeek()->addDays($movimentacoesPorDiaSemana->dia - 1)->locale('pt')->dayName,
            'total' => $movimentacoesPorDiaSemana->total,
        ]
        : ['dia' => null, 'total' => 0];

    // Movimentações por Coop/Agência
    $movimentacoesPorCoopAgencia = Movimentacao::selectRaw('cooperativa, agencia, COUNT(*) as total_movimentacoes, SUM(COALESCE(debito, 0) + COALESCE(credito, 0)) as total_valor')
        ->groupBy('cooperativa', 'agencia')
        ->get();

    // Créditos x Débitos por Hora
    $relacaoCreditosDebitos = Movimentacao::selectRaw('HOUR(data_hora) as hora, SUM(COALESCE(credito, 0)) as total_credito, SUM(COALESCE(debito, 0)) as total_debito')
        ->groupBy('hora')
        ->get()
        ->mapWithKeys(function ($item) {
            return [$item->hora => $item];
        });

    $relacaoCreditosDebitosFinal = collect(range(0, 23))->map(function ($hour) use ($relacaoCreditosDebitos) {
        return $relacaoCreditosDebitos->get($hour, [
            'hora' => $hour,
            'total_credito' => 0,
            'total_debito' => 0,
        ]);
    });

    return response()->json([
        'maior_movimentacao' => $maiorMovimentacao,
        'menor_movimentacao' => $menorMovimentacao,
        'maior_soma' => $maiorSoma,
        'menor_soma' => $menorSoma,
        'dia_semana_mais_movimentacoes' => $diaSemanaMaisMovimentacoes,
        'movimentacoes_por_coop_agencia' => $movimentacoesPorCoopAgencia,
        'relacao_creditos_debitos' => $relacaoCreditosDebitosFinal,
    ]);
}

}
