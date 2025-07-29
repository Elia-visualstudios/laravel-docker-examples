<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Lista;
use App\Models\Note;
use App\Models\Tag;

class NoteListSeeder extends Seeder
{
    public function run(): void
    {
        // Crea alcuni tag
        $tagImportante = Tag::firstOrCreate(['name' => 'Importante']);
        $tagViaggio = Tag::firstOrCreate(['name' => 'Viaggio']);
        $tagPersonale = Tag::firstOrCreate(['name' => 'Personale']);

        // Lista principale
        $listaDiViaggio = Lista::firstOrCreate([
    'nome' => 'Preparazione per grande viaggio in U.S. and A. per scopo glorioso',
    'parent_lista_id' => null
]);

// Qui fuori dalla prima chiamata creo le note collegate a questa lista
Note::firstOrCreate([
    'text' => 'Fare il check dei documenti per il viaggio',
    'lista_id' => $listaDiViaggio->id,
], ['checkbox' => false]);

Note::firstOrCreate([
    'text' => 'Prenotare l\'hotel a New York',
    'lista_id' => $listaDiViaggio->id,
], ['checkbox' => false]);

        

        // Associa tag
        $listaDiViaggio->tags()->sync([$tagImportante->id, $tagViaggio->id]);

        $newYorkPreparazione = Lista::firstOrCreate([
            'nome' => 'Cose da fare prima di andare a grande mela, New York',
            'parent_lista_id' => $listaDiViaggio->id
        ]);
        $newYorkPreparazione->tags()->sync([$tagViaggio->id]);

        Note::firstOrCreate([
            'text' => 'Comperare vestito bello e calzini freschi (non da mio vicino Azamat)',
            'lista_id' => $newYorkPreparazione->id
        ], ['checkbox' => false]);

        Note::firstOrCreate([
            'text' => 'Esercitare il mio inglese per dire "Wow-wow-wee-wah!"',
            'lista_id' => $newYorkPreparazione->id
        ], ['checkbox' => false]);

        Note::firstOrCreate([
            'text' => 'Nascondere sorella nel mio bagaglio (lei molto piccola)',
            'lista_id' => $newYorkPreparazione->id
        ], ['checkbox' => true]);

        $missionePamela = Lista::firstOrCreate([
            'nome' => 'Missione molto importante: trovare donna Pamela Anderson',
            'parent_lista_id' => $listaDiViaggio->id
        ]);
        $missionePamela->tags()->sync([$tagPersonale->id]);

        Note::firstOrCreate([
            'text' => 'Comprare macchina per ghiaccio molto piccola',
            'lista_id' => $missionePamela->id
        ]);

        Note::firstOrCreate([
            'text' => 'Dare ad uomo di stato US con A figlia (lei molto bene per matrimonio)',
            'lista_id' => $missionePamela->id
        ]);

        Note::firstOrCreate([
            'text' => 'Chiedere a Pamela di sposare me, molto amore!',
            'lista_id' => $missionePamela->id
        ]);

        $ritornoACasa = Lista::firstOrCreate([
            'nome' => 'Cose da fare per mio ritorno glorioso in Kazakistan',
            'parent_lista_id' => null
        ]);

        $villaggioK = Lista::firstOrCreate([
            'nome' => 'Per villaggio nativo: Kuczek, molto belo',
            'parent_lista_id' => $ritornoACasa->id
        ]);

        Note::firstOrCreate([
            'text' => 'Spiegare che orologio da polso non è per cibo',
            'lista_id' => $villaggioK->id
        ]);

        Note::firstOrCreate([
            'text' => 'Mostrare foto di mio grande viaggio a mio popolo',
            'lista_id' => $villaggioK->id
        ]);

        $pensieriSaggi = Lista::firstOrCreate([
            'nome' => 'Miei pensieri molto saggi su vita e orsi',
            'parent_lista_id' => null
        ]);

        Note::firstOrCreate([
            'text' => 'Perché donna è come orso? Forte fuori, ma tenera dentro?',
            'lista_id' => $pensieriSaggi->id
        ]);

        Note::firstOrCreate([
            'text' => 'Il mio vicino Azamat non è molto bello, ma lui ha buon cuore',
            'lista_id' => $pensieriSaggi->id
        ]);

        Note::firstOrCreate([
            'text' => 'Molto bello!',
            'lista_id' => $pensieriSaggi->id
        ]);
    }
}
