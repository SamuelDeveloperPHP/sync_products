<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstoqueCategoriaSecundaria extends Model
{
    use HasFactory;

    protected $table = 'estoque_categoria_secundaria';

    protected $fillable = [
        'id_categoy_father',
        'id_categoy_first',
        'id_categoy_secondary',
        'id_categoria_principal',
        'id_categoria_primaria',
        'nome_categoria_secundaria'
    ];
}
