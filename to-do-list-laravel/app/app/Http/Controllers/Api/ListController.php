<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lista; 
use Illuminate\Http\Request;

class ListController extends Controller
{
    /**
     * Display a listing of the resource.
     * Recupera le liste, con possibilità di includere i figli nidificati.
     */
    public function index(Request $request)
    {
        if ($request->query('include_children')) {
            $lists = Lista::whereNull('parent_lista_id')
                            ->with('childrenRecursive')
                            ->get();
        } else {
            $lists = Lista::all(); 
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
            'parent_lista_id' => 'nullable|exists:lista,id',
        ]);

        $list = Lista::create($request->all());
        return response()->json($list, 201);
    }

    /**
     * Remove the specified list from storage.
     * Cancella una lista e, per cascata, le sue sotto-liste e note.
     */
    public function destroy(Lista $list)
    {
        $list->delete(); 
        return response()->json(null, 204);
    }

    /**
     * Recupera le note associate a una lista specifica.
     *
     * @param  \App\Models\Lista  $list
     * @return \Illuminate\Http\Response
     */
    public function getNotes(Lista $list) // Questo è il metodo mancante!
    {
        // Carica le note associate alla lista
        $notes = $list->notes; // Assicurati che la relazione 'notes' esista nel modello Lista

        return response()->json(['data' => $notes]);
    }
}