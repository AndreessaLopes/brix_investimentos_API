<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendas extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_ticker',
        'quantidade',
        'valor_unitario',
        'valor_total',
    ];

    public static function rules()
    {
        return [
            'id_ticker' => 'required|exists:cadastrar_ativos,id',
            'quantidade' => 'required|integer|min:1',
            'valor_unitario' => 'required|numeric|min:0.01',
            'valor_total' => 'required|numeric|min:0.01',
        ];
    }

    public function cadastrarAtivo()
    {
        return $this->belongsTo(CadastrarAtivo::class, 'id_ticker', 'id');
    }

    public function compra()
    {
        return $this->belongsTo(Compras::class, 'id_compra', 'id');
    }
}
