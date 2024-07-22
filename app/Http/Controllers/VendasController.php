<?php

namespace App\Http\Controllers;

use App\Models\Vendas;
use App\Models\CadastrarAtivo;
use App\Models\HistoricoPrecoAtivo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class VendasController extends Controller
{
    public function index()
    {
        $vendas = Vendas::select('id_ticker', 'quantidade', 'valor_unitario', 'valor_total', 'created_at', 'updated_at')->get();
        return response()->json($vendas);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), Vendas::rules());

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        DB::beginTransaction();
        try {
            $ativo = CadastrarAtivo::find($request->id_ticker);
            if ($ativo->quantidade < $request->quantidade) {
                return response()->json(['error' => 'Quantidade insuficiente'], 400);
            }

            $venda = Vendas::create($request->all());

            $ativo->quantidade -= $request->quantidade;
            $ativo->save();

            HistoricoPrecoAtivo::create([
                'id_ticker' => $request->id_ticker,
                'data_ativo' => now(),
                'open' => $request->valor_unitario,
                'low' => $request->valor_unitario,
                'high' => $request->valor_unitario,
                'close' => $request->valor_unitario,
                'volume' => -$request->quantidade,
            ]);

            DB::commit();
            return response()->json($venda, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show(Vendas $venda)
    {
        $venda = Vendas::select('id_ticker', 'quantidade', 'valor_unitario', 'valor_total', 'created_at', 'updated_at')->find($venda->id);
        return response()->json($venda);
    }

    public function update(Request $request, Vendas $venda)
    {
        $validator = Validator::make($request->all(), Vendas::rules());

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $venda->update($request->all());

        return response()->json($venda, 200);
    }

    public function destroy(Vendas $venda)
    {
        $venda->delete();

        return response()->json(null, 204);
    }
}
