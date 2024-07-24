<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estoque extends Model
{
    use HasFactory;

    protected $table = 'estoque';

    protected $fillable = [
        'id_produto',
        'id_categoy_father',
        'id_categoy_first',
        'id_categoy_secondary',
        'id_categoria_principal',
        'id_categoria_primaria',
        'id_categoria_secundaria',
        'nome_produto',
        'id_marca',
        'usuario',
        'valor_unitario',
        'unidade',
        'status_produto',
        'image'
    ];
}

