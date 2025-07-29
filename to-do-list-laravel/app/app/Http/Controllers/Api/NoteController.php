<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Note;
use App\Models\Lista;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NoteController extends Controller
{
    public function indexByList(Lista $list)
    {
        $notes = $list->notes()->get();
        return response()->json($notes);
    }

    public function store(Request $request, Lista $list)
    {
    $request->validate([
    'text' => 'required|string',
    'checkbox' => 'boolean',  // invece di 'completed'
]);

    $note = $list->notes()->create([
    'text' => $request->text,
    'checkbox' => $request->checkbox ?? false,  // invece di completed
]);

        return response()->json($note, 201);
    }

    public function update(Request $request, $id)
{
    Log::info('Update request', [
        'id' => $id,
        'data' => $request->all()
    ]);

    $note = Note::find($id);

    if (!$note) {
        Log::error('Nota non trovata', ['id' => $id]);
        return response()->json(['error' => 'Nota non trovata'], 404);
    }

    $request->validate([
        'checkbox' => 'sometimes|boolean',
        'text' => 'sometimes|string',
    ]);

    $updateData = [];

    if ($request->has('checkbox')) {
        $updateData['checkbox'] = $request->checkbox;
    }
    if ($request->has('text')) {
        $updateData['text'] = $request->text;
    }

    Log::info('Dati da aggiornare', $updateData);

    $note->update($updateData);

    $note->refresh();

    Log::info('Nota aggiornata', $note->toArray());

    return response()->json($note);
}

    public function destroy($id)  // Cambiato anche questo per coerenza
    {
        $note = Note::find($id);
        
        if (!$note) {
            return response()->json(['error' => 'Nota non trovata'], 404);
        }
        
        $note->delete();
        return response()->json(['message' => 'Nota eliminata con successo'], 200);
    }
}