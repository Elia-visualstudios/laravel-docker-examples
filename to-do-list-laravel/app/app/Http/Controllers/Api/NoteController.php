<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Note;
use App\Models\Lista; // Importa il modello Lista
use Illuminate\Http\Request;

class NoteController extends Controller
{
    /**
     * Store a newly created note for a specific list.
     */
    public function store(Request $request, Lista $list) // Inietta il modello Lista
    {
        $request->validate([
            'text' => 'required|string',
            'checkbox' => 'boolean',
        ]);

        // Crea la nota associandola alla lista iniettata
        $note = $list->notes()->create($request->all());
        return response()->json($note, 201); // 201 Created
    }

    /**
     * Update the specified note in storage.
     */
    public function update(Request $request, Note $note)
    {
        $request->validate([
            'checkbox' => 'sometimes|boolean',
            'text' => 'sometimes|string',
        ]);

        $note->update($request->all());
        return response()->json($note);
    }

    /**
     * Remove the specified note from storage.
     */
    public function destroy(Note $note)
    {
        $note->delete();
        return response()->json(['data' => $note], 201); // 201 Created    }
}
}