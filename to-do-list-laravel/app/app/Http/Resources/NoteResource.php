<?php

namespace App\Http\Resources; // Questo namespace è corretto per le risorse

use Illuminate\Http\Resources\Json\JsonResource;

class NoteResource extends JsonResource // <-- Questa classe DEVE estendere JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'text' => $this->text,
            'checkbox' => (bool) $this->checkbox, // Assicurati che sia un booleano
            'lista_id' => $this->lista_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}