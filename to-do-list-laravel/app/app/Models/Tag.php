<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function listas() // Nota: usa 'listas' (al plurale, come la tabella) qui
    {
        return $this->belongsToMany(Lista::class, 'list_tag', 'tag_id', 'lista_id');
    }
}