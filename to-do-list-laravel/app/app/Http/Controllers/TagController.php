<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;
use App\Models\Lista; // Importa il modello Lista per collegare i tag alle liste

class TagController extends Controller
{
    // Per ottenere tutti i tag esistenti
    public function index()
    {
        $tags = Tag::all();
        return response()->json(['data' => $tags]);
    }

    // Per creare un nuovo tag
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:tags,name', // Il nome deve essere unico
        ]);

        $tag = Tag::create(['name' => $request->name]);
        return response()->json($tag, 201);
    }

    // Per eliminare un tag
    public function destroy($id)
    {
        $tag = Tag::find($id);
        if (!$tag) {
            return response()->json(['message' => 'Tag non trovato.'], 404);
        }
        $tag->delete();
        return response()->json(['message' => 'Tag eliminato con successo.'], 200);
    }

    // Per assegnare o rimuovere tag da una lista specifica
    public function syncToList(Request $request, $listId)
    {
        $list = Lista::find($listId);
        if (!$list) {
            return response()->json(['message' => 'Lista non trovata.'], 404);
        }
        $request->validate(['tag_ids' => 'array', 'tag_ids.*' => 'exists:tags,id']);

        // Sincronizza i tag della lista con gli ID forniti (aggiunge i nuovi, rimuove i non più presenti)
        $list->tags()->sync($request->tag_ids ?? []);
        return response()->json(['message' => 'Tag della lista aggiornati con successo.'], 200);
    }
}