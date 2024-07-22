<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Compras extends Model
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
            'valor_unitario' => 'required|integer|min:1',
            'valor_total' => 'required|numeric|min:0',
        ];
    }

    public function cadastrarAtivo()
    {
        return $this->belongsTo(CadastrarAtivo::class, 'id_ticker', 'id');
    }

    public function vendas()
    {
        return $this->hasMany(Venda::class, 'id_compra', 'id');
    }
}
