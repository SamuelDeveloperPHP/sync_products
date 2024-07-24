<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstoqueCategoriaPrimaria extends Model
{
    use HasFactory;

    protected $table = 'estoque_categoria_primaria';

    protected $fillable = [
        'id_categoy_father',
        'id_categoy_first',
        'id_categoria_principal',
        'nome_categoria_primaria'
    ];
}
