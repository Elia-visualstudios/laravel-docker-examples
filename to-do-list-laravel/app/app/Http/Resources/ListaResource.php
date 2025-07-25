<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\NoteResource;
class ListaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'parent_lista_id' => $this->parent_lista_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            // Per includere i figli ricorsivamente solo quando richiesto e caricati
            'children' => self::collection($this->whenLoaded('childrenRecursive')),
            // Per includere le note quando richieste e caricate
            'notes' => NoteResource::collection($this->whenLoaded('notes')),
        ];
    }
}