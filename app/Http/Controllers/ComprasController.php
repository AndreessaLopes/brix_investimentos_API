<?php

namespace App\Http\Controllers;

use App\Models\Compras;
use App\Models\CadastrarAtivo;
use App\Models\HistoricoPrecoAtivo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ComprasController extends Controller
{
    public function index()
    {
        $compras = Compras::all(['id_ticker', 'quantidade', 'valor_unitario', 'valor_total', 'created_at', 'updated_at']);
        return response()->json($compras);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), Compras::rules());

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        DB::beginTransaction();
        try {
            $compra = Compras::create($request->all());

            $ativo = CadastrarAtivo::find($request->id_ticker);
            $ativo->quantidade += $request->quantidade;
            $ativo->preco_compra = ($ativo->preco_compra * $ativo->quantidade + $request->valor_total) / ($ativo->quantidade + $request->quantidade);
            $ativo->save();

            HistoricoPrecoAtivo::create([
                'id_ticker' => $request->id_ticker,
                'data_ativo' => now(),
                'open' => $request->valor_unitario,
                'low' => $request->valor_unitario,
                'high' => $request->valor_unitario,
                'close' => $request->valor_unitario,
                'volume' => $request->quantidade,
            ]);

            DB::commit();
            return response()->json($compra->only(['id_ticker', 'quantidade', 'valor_unitario', 'valor_total', 'created_at', 'updated_at']), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, Compras $compra)
    {
        $validator = Validator::make($request->all(), Compras::rules());

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $compra->update($request->all());

        return response()->json($compra->only(['id_ticker', 'quantidade', 'valor_unitario', 'valor_total', 'created_at', 'updated_at']), 200);
    }

    public function destroy(Compras $compra)
    {
        $compra->delete();

        return response()->json(null, 204);
    }
}
