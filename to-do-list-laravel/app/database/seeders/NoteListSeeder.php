<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Lista; // Tua bella Lista
use App\Models\Note; // Tua bella Nota, per pensieri e compiti

class NoteListSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Lista principale per il mio grande viaggio in U.S. and A., molto importante!
        $listaDiViaggio = Lista::firstOrCreate(
            ['nome' => 'Preparazione per grande viaggio in U.S. and A. per scopo glorioso', 'parent_lista_id' => null]
        );

        // Cose da fare per andare a New York, la città molto grande
        $newYorkPreparazione = Lista::firstOrCreate(
            ['nome' => 'Cose da fare prima di andare a grande mela, New York', 'parent_lista_id' => $listaDiViaggio->id]
        );
        Note::firstOrCreate(
            ['text' => 'Comperare vestito bello e calzini freschi (non da mio vicino Azamat)', 'lista_id' => $newYorkPreparazione->id],
            ['checkbox' => false]
        );
        Note::firstOrCreate(
            ['text' => 'Esercitare il mio inglese per dire "Wow-wow-wee-wah!"', 'lista_id' => $newYorkPreparazione->id],
            ['checkbox' => false]
        );
        Note::firstOrCreate(
            ['text' => 'Nascondere sorella nel mio bagaglio (lei molto piccola)', 'lista_id' => $newYorkPreparazione->id],
            ['checkbox' => true]
        );

        // Cose da fare quando sono in America, per trovare Pamela
        $missionePamela = Lista::firstOrCreate(
            ['nome' => 'Missione molto importante: trovare donna Pamela Anderson', 'parent_lista_id' => $listaDiViaggio->id]
        );
        Note::firstOrCreate(
            ['text' => 'Comprare macchina per ghiaccio molto piccola', 'lista_id' => $missionePamela->id],
            ['checkbox' => false]
        );
        Note::firstOrCreate(
            ['text' => 'Dare ad uomo di stato US con A figlia (lei molto bene per matrimonio)', 'lista_id' => $missionePamela->id],
            ['checkbox' => false]
        );
        Note::firstOrCreate(
            ['text' => 'Chiedere a Pamela di sposare me, molto amore!', 'lista_id' => $missionePamela->id],
            ['checkbox' => false]
        );

        // Lista per quando torno in Kazakistan, forse con moglie
        $ritornoACasa = Lista::firstOrCreate(
            ['nome' => 'Cose da fare per mio ritorno glorioso in Kazakistan', 'parent_lista_id' => null]
        );

        // Per mio villaggio, molto importante
        $villaggioK = Lista::firstOrCreate(
            ['nome' => 'Per villaggio nativo: Kuczek, molto bello', 'parent_lista_id' => $ritornoACasa->id]
        );
        Note::firstOrCreate(
            ['text' => 'Spiegare che orologio da polso non è per cibo', 'lista_id' => $villaggioK->id],
            ['checkbox' => false]
        );
        Note::firstOrCreate(
            ['text' => 'Mostrare foto di mio grande viaggio a mio popolo', 'lista_id' => $villaggioK->id],
            ['checkbox' => false]
        );

        // Lista per pensieri molto profondi e filosofici di Borat
        $pensieriSaggi = Lista::firstOrCreate(
            ['nome' => 'Miei pensieri molto saggi su vita e orsi', 'parent_lista_id' => null]
        );
        Note::firstOrCreate(
            ['text' => 'Perché donna è come orso? Forte fuori, ma tenera dentro?', 'lista_id' => $pensieriSaggi->id],
            ['checkbox' => false]
        );
        Note::firstOrCreate(
            ['text' => 'Il mio vicino Azamat non è molto bello, ma lui ha buon cuore', 'lista_id' => $pensieriSaggi->id],
            ['checkbox' => false]
        );
        Note::firstOrCreate(
            ['text' => 'Molto bello!', 'lista_id' => $pensieriSaggi->id],
            ['checkbox' => true]
        );
    }
}