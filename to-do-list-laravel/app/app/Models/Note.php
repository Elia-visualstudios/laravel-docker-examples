<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    use HasFactory;

    protected $fillable = ['text', 'checkbox', 'lista_id']; // Aggiungi 'lista_id'

    // Una nota appartiene a una lista
    public function lista()
    {
        return $this->belongsTo(Lista::class, 'lista_id'); // Usa 'lista_id' per la chiave esterna
    }
}