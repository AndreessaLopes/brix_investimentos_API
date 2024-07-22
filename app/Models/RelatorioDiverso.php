<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Compras;
use App\Models\Vendas;
use App\Models\HistoricoPrecoAtivo;
use Carbon\Carbon;

class RelatorioDiverso extends Model
{
    use HasFactory;

    protected $fillable = [
        'ativo_id',
        'posicao_inicial',
        'posicao_final',
        'movimentacao_detalhada',
        'roi',
        'volatilidade',
        'resultado_projetado',
    ];

    public static function calcularRelatorio($ativoId)
    {
        try {
            // Obtém o ativo pelo ID
            $ativo = CadastrarAtivo::findOrFail($ativoId);

            // Define o início e o final do período como hoje (13 de julho)
            $inicio = Carbon::now()->startOfDay();
            $final = Carbon::now()->endOfDay();

            // Calcula a posição inicial e final no período
            $posicaoInicial = self::getPosicaoInicial($ativo, $inicio);
            $posicaoFinal = self::getPosicaoFinal($ativo, $inicio);

            // Verifica se a posição inicial é zero para evitar divisão por zero no cálculo do ROI
            if ($posicaoInicial == 0) {
                throw new \Exception('A posição inicial do ativo é zero.');
            }

            // Calcula a movimentação detalhada no período
            $movimentacaoDetalhada = self::calcularMovimentacaoDetalhada($ativoId, $inicio, $final);

            // Calcula o retorno sobre o investimento (ROI) no período
            $roi = ($posicaoFinal - $posicaoInicial) / $posicaoInicial;

            // Calcula a volatilidade do período
            $volatilidade = self::calcularVolatilidade($ativo, $inicio, $final);

            // Calcula um resultado projetado com base na movimentação detalhada
            $resultadoProjetado = self::calcularResultadoProjetado($movimentacaoDetalhada);

            return [
                'ativo_id' => $ativoId,
                'posicao_inicial' => $posicaoInicial,
                'posicao_final' => $posicaoFinal,
                'movimentacao_detalhada' => $movimentacaoDetalhada,
                'roi' => $roi,
                'volatilidade' => $volatilidade,
                'resultado_projetado' => $resultadoProjetado,
            ];
        } catch (\Exception $e) {
            throw new \Exception('Erro ao calcular o relatório: ' . $e->getMessage());
        }
    }

    private static function getPosicaoInicial($ativo, $inicio)
    {
        // Consulta o histórico de preços do ativo para obter a posição inicial no período
        $historico = $ativo->historicoPrecoAtivos()
            ->where('data_ativo', '<=', $inicio)
            ->orderByDesc('data_ativo')
            ->first();

        return $historico ? $historico->close : 0;
    }

    private static function getPosicaoFinal($ativo, $inicio)
    {
        // Consulta o histórico de preços do ativo para obter a posição final mais recente
        $historico = $ativo->historicoPrecoAtivos()
            ->where('data_ativo', '<=', $inicio)
            ->orderByDesc('data_ativo')
            ->first();

        return $historico ? $historico->close : 0;
    }

    private static function calcularMovimentacaoDetalhada($ativoId, $inicio, $final)
    {
        // Implemente aqui a lógica para calcular a movimentação detalhada, consultando compras e vendas
        // detalhadas no banco de dados
        $movimentacaoDetalhada = [];

        // Exemplo: Consulta de compras e vendas
        $compras = Compras::where('id_ticker', $ativoId)
            ->whereBetween('created_at', [$inicio, $final])
            ->orderBy('created_at')
            ->get();

        $vendas = Vendas::where('id_ticker', $ativoId)
            ->whereBetween('created_at', [$inicio, $final])
            ->orderBy('created_at')
            ->get();

        // Exemplo: Preenchimento da movimentação detalhada com dados das compras e vendas
        foreach ($compras as $compra) {
            $movimentacaoDetalhada[] = [
                'tipo' => 'compra',
                'quantidade' => $compra->quantidade,
                'valor_unitario' => $compra->valor_unitario,
                'total' => $compra->valor_total,
                'data' => $compra->created_at,
            ];
        }

        foreach ($vendas as $venda) {
            $movimentacaoDetalhada[] = [
                'tipo' => 'venda',
                'quantidade' => $venda->quantidade,
                'valor_unitario' => $venda->valor_unitario,
                'total' => $venda->valor_total,
                'data' => $venda->created_at,
            ];
        }

        return $movimentacaoDetalhada;
    }

    private static function calcularVolatilidade($ativo, $inicio, $final)
    {
        // Consulta o histórico de preços do ativo para calcular a volatilidade
        $historico = $ativo->historicoPrecoAtivos()
            ->whereBetween('data_ativo', [$inicio, $final])
            ->get();

        if ($historico->isEmpty()) {
            return 0; // Retornar 0 ou lidar com o caso vazio conforme sua lógica de negócio
        }

        // Calcula a volatilidade como o desvio padrão dos preços de fechamento
        $precos = $historico->pluck('close');
        $media = $precos->avg();
        $desvioPadrao = $precos->map(function ($preco) use ($media) {
            return pow($preco - $media, 2);
        })->sum() / ($precos->count() - 1);

        return sqrt($desvioPadrao);
    }

    private static function calcularResultadoProjetado($movimentacaoDetalhada)
    {
        // Implemente a lógica para calcular o resultado projetado com base na movimentação detalhada
        // Exemplo: somatório do total de compras - somatório do total de vendas
        $totalCompras = 0;
        $totalVendas = 0;

        foreach ($movimentacaoDetalhada as $movimentacao) {
            if ($movimentacao['tipo'] === 'compra') {
                $totalCompras += $movimentacao['total'];
            } elseif ($movimentacao['tipo'] === 'venda') {
                $totalVendas += $movimentacao['total'];
            }
        }

        return $totalCompras - $totalVendas;
    }
}

