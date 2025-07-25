<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ListController;
use App\Http\Controllers\Api\NoteController;

// Gruppo di route per le Liste (gestione principale: crea/leggi/cancella liste e ottieni note di una lista)
Route::prefix('lists')->group(function () {
    Route::get('/', [ListController::class, 'index']);           // Ottieni tutte le liste (con figli)
    Route::post('/', [ListController::class, 'store']);          // Crea una nuova lista (sempre di primo livello dal frontend)
    Route::delete('/{list}', [ListController::class, 'destroy']); // Cancella una lista (con figli e note, per cascata)
    Route::get('/{list}/notes', [ListController::class, 'getNotes']); // Ottieni le note di una lista specifica
});


Route::post('lists/{list}/notes', [NoteController::class, 'store']); 


Route::prefix('notes')->group(function () {
    Route::put('/update/{note}', [NoteController::class, 'update']); 
    Route::delete('/del/{note}', [NoteController::class, 'destroy']); 
});

// La route /user è stata rimossa, come abbiamo discusso.