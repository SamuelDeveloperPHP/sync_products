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
        Schema::create('estoque_categoria_secundaria', function (Blueprint $table) {
            $table->id();
            $table->string('id_categoy_father');
            $table->string('id_categoy_first');
            $table->string('id_categoy_secondary');
            $table->foreignId('id_categoria_principal')->constrained('estoque_categoria_principal');
            $table->foreignId('id_categoria_primaria')->constrained('estoque_categoria_primaria');
            $table->string('nome_categoria_secundaria');
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estoque_categoria_secundarias');
    }
};
