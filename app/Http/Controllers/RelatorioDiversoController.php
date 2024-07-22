<?php

namespace App\Http\Controllers;

use App\Models\RelatorioDiverso;
use App\Models\CadastrarAtivo;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Carbon\Carbon;

class RelatorioDiversoController extends Controller
{
    public function store(Request $request)
    {
        try {
            $request->validate([
                'inicio' => 'required|date',
                'periodo' => 'required|integer',
            ]);

            $inicio = Carbon::parse($request->input('inicio'));
            $periodo = $inicio->copy()->subMonths($request->input('periodo'));

            $ativos = CadastrarAtivo::all();
            $relatorios = [];

            $atributosDesejados = [
                'posicao_inicial',
                'posicao_final',
                'movimentacao',
                'rentabilidade_periodo',
                'volatilidade',
                'resultado_projetado'
            ];

            foreach ($ativos as $ativo) {
                $relatorio = RelatorioDiverso::calcularRelatorio($ativo->id, $inicio, $periodo);
                if ($relatorio !== null) {
                    $relatorios[] = array_intersect_key($relatorio, array_flip($atributosDesejados));
                }
            }

            return response()->json([
                'relatorios' => $relatorios,
                'inicio_periodo' => $inicio->format('Y-m-d'),
                'fim_periodo' => Carbon::now()->format('Y-m-d'),
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao gerar o relatório: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
