<?php

namespace App\Http\Controllers\Api; 
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Lista; 
use Illuminate\Support\Facades\DB; // Per le transazioni

class ListController extends Controller 
{
    /**
     * Display a listing of the resource.
     * Recupera le liste, con possibilità di includere i figli nidificati, tag e filtra per archivio.
     */
    public function index(Request $request)
    {
        $query = Lista::query();

        // Se non chiedi espressamente di includere quelle archiviate, le escludiamo
        if (!$request->boolean('include_archived')) {
            $query->where('archived', false);
        }

        // Carica i figli e anche i tag per ogni lista
        if ($request->boolean('include_children')) {
            $lists = $query->whereNull('parent_lista_id')
                           ->with(['children', 'tags']) // Carica sia i figli che i tag
                           ->get();
        } else {
            $lists = $query->with('tags')->get(); // Se non carichi i figli, carica comunque i tag
        }

        return response()->json(['data' => $lists]);
    }

    /**
     * Store a newly created list in storage.
     * Crea una nuova lista.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'parent_lista_id' => 'nullable|exists:listas,id', 
        ]);

        $list = Lista::create([
            'nome' => $request->nome,
            'parent_lista_id' => $request->parent_lista_id,
            'archived' => false, // Ogni nuova lista è inizialmente non archiviata
        ]);

        return response()->json($list, 201);
    }

    /**
     * Remove the specified list from storage.
     * Cancella una lista e, per cascata, le sue sotto-liste e note.
     */
    public function destroy($id) // Cambiato da Lista $list a $id per coerenza con gli altri metodi
    {
        $list = Lista::find($id);

        if (!$list) {
            return response()->json(['message' => 'Lista non trovata.'], 404);
        }

        // Elimina ricorsivamente anche le note e le liste figlie
        DB::transaction(function () use ($list) {
            $this->deleteNotesAndChildren($list);
        });

        return response()->json(['message' => 'Lista e sottoliste eliminate con successo.'], 200);
    }

    // Funzione helper per eliminazione ricorsiva
    private function deleteNotesAndChildren(Lista $list)
    {
        $list->notes()->delete(); // Elimina tutte le note dirette

        // Elimina ricorsivamente i figli
        foreach ($list->children as $child) {
            $this->deleteNotesAndChildren($child);
        }

        $list->delete(); // Infine, elimina la lista corrente
    }

    /**
     * Recupera le note associate a una lista specifica.
     *
     * @param  \App\Models\Lista  $list
     * @return \Illuminate\Http\Response
     */
    public function getNotes(Lista $list) // Questo è il metodo per le note
    {
        // Carica le note associate alla lista
        $notes = $list->notes; // Assicurati che la relazione 'notes' esista nel modello Lista

        return response()->json(['data' => $notes]);
    }

    // NUOVI METODI PER ARCHIVIAZIONE/DISARCHIVIAZIONE

    public function archive($id)
    {
        $list = Lista::find($id);
        if (!$list) {
            return response()->json(['message' => 'Lista non trovata.'], 404);
        }
        $list->archived = true;
        $list->save();
        return response()->json(['message' => 'Lista archiviata con successo.'], 200);
    }

    public function unarchive($id)
    {
        $list = Lista::find($id);
        if (!$list) {
            return response()->json(['message' => 'Lista non trovata.'], 404);
        }
        $list->archived = false;
        $list->save();
        return response()->json(['message' => 'Lista disarchiviata con successo.'], 200);
    }
    public function show($id)
{
    $list = Lista::with('tags')->find($id);

    if (!$list) {
        return response()->json(['message' => 'Lista non trovata.'], 404);
    }

    return response()->json(['data' => $list]);
}

public function update(Request $request, $id)
{
    $list = Lista::find($id);

    if (!$list) {
        return response()->json(['message' => 'Lista non trovata.'], 404);
    }

    $request->validate([
        'nome' => 'sometimes|required|string|max:255',
        'parent_lista_id' => 'nullable|exists:listas,id',
        'archived' => 'sometimes|boolean', // Permette di aggiornare lo stato di archiviazione
    ]);

    $list->update($request->only(['nome', 'parent_lista_id', 'archived']));

    return response()->json(['data' => $list]);
}

public function getTagsForList($id)
{
    $list = Lista::find($id);

    if (!$list) {
        return response()->json(['message' => 'Lista non trovata.'], 404);
    }

    $tags = $list->tags()->get();

    return response()->json(['data' => $tags]);
}

}