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
        Schema::create('listas', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            // Questa colonna è per la nidificazione delle liste
            $table->foreignId('parent_lista_id')
                  ->nullable() // Permette che sia NULL per le liste di primo livello
                  ->constrained('listas') // Si auto-referenzia alla stessa tabella
                  ->onDelete('cascade'); // Elimina le sotto-liste se il genitore viene eliminato
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('listas');
    }
};