<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstoqueCategoriaPrincipal extends Model
{
    use HasFactory;

    protected $table = 'estoque_categoria_principal';

    protected $fillable = [
        'id_categoy_father',
        'nome_categoria_princ'
    ];
}
