<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lista extends Model
{
    use HasFactory;

    protected $fillable = ['nome', 'parent_lista_id', 'archived']; // Aggiungi 'archived'

    
    public function parent()
    {
        return $this->belongsTo(Lista::class, 'parent_lista_id');
    }

    /**
     * Una lista può avere molti figli diretti.
     * Questa relazione carica solo il livello successivo.
     */
    public function children()
    {
        return $this->hasMany(Lista::class, 'parent_lista_id');
    }

    /**
     * Relazione ricorsiva per caricare tutti i discendenti.
     * Questo è FONDAMENTALE per le liste nidificate e usato dal ListController.
     */
    public function childrenRecursive()
    {
        // Carica i figli, e per ogni figlio, carica i suoi figli ricorsivamente.
        return $this->children()->with('childrenRecursive');
    }


    public function notes()
    {
        
        return $this->hasMany(Note::class, 'lista_id');
    }

    /**
     * Scope per ottenere solo le liste di primo livello (senza genitore).
     * Utile per recuperare le radici dell'albero.
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_lista_id');
    }
    
    public function scopeActive($query)
{
    return $query->where('archived', false);
}
 
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'list_tag', 'lista_id', 'tag_id');
    }

}