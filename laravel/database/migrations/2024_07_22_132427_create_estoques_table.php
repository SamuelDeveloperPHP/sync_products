<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('estoque', function (Blueprint $table) {
            $table->id();
            $table->string('id_produto');
            $table->string('id_categoy_father');
            $table->string('id_categoy_first');
            $table->string('id_categoy_secondary');
            $table->foreignId('id_categoria_principal')->constrained('estoque_categoria_principal');
            $table->foreignId('id_categoria_primaria')->constrained('estoque_categoria_primaria');
            $table->foreignId('id_categoria_secundaria')->constrained('estoque_categoria_secundaria');
            $table->string('nome_produto');
            $table->string('id_marca');
            $table->string('usuario');
            $table->decimal('valor_unitario', 8, 2);
            $table->string('unidade');
            $table->boolean('status_produto');
            $table->string('image');
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estoques');
    }
};
