<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ListController;  // CORRETTO: ListController (senza la 'a')
use App\Http\Controllers\Api\NoteController;  // Usa NoteController (plurale)
use App\Http\Controllers\TagController;       // Controller per i tag

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "api" middleware group. Now enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Rotte per le liste (listas) - tutte sotto prefisso 'listas'
Route::prefix('listas')->group(function () {
    Route::get('/', [ListController::class, 'index']);           // GET /api/listas
    Route::post('/', [ListController::class, 'store']);          // POST /api/listas
    Route::delete('/{id}', [ListController::class, 'destroy']);  // DELETE /api/listas/{id}
    Route::put('/{id}/archive', [ListController::class, 'archive']);     // PUT /api/listas/{id}/archive
    Route::put('/{id}/unarchive', [ListController::class, 'unarchive']); // PUT /api/listas/{id}/unarchive
    Route::put('/{id}', [ListController::class, 'update']); // PUT /api/listas/{id} (per aggiornare una lista)
    Route::get('/{id}', [ListController::class, 'show']);        // GET /api/listas/{id}

    // Rotte per i tag associati a una lista
    Route::get('/{listId}/tags', [ListController::class, 'getTagsForList']); // GET /api/listas/{listId}/tags

    // Rotte per le note legate a una lista (usano NoteController)
   Route::get('/{list}/notes', [NoteController::class, 'indexByList']);
   Route::post('/{list}/notes', [NoteController::class, 'store']);
     // POST /api/listas/{listId}/notes

    // Sincronizza tag per lista
    Route::post('/{listId}/tags/sync', [TagController::class, 'syncToList']); // POST /api/listas/{listId}/tags/sync
});

// Rotte generali per aggiornare o cancellare note
Route::prefix('notes')->group(function () {
    Route::put('/update/{id}', [NoteController::class, 'update']);     // PUT /api/notes/update/{id}
    Route::delete('/del/{id}', [NoteController::class, 'destroy']);   // DELETE /api/notes/del/{id}
});

// Rotte CRUD per i tag
Route::prefix('tags')->group(function () {
    Route::get('/', [TagController::class, 'index']);       // GET /api/tags
    Route::post('/', [TagController::class, 'store']);      // POST /api/tags
    Route::delete('/{id}', [TagController::class, 'destroy']); // DELETE /api/tags/{id}
});
