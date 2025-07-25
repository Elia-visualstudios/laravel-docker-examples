<?php

namespace Database\Seeders; // Molto importante che questo sia qui!

// Importa il tuo seeder di Borat, molto bello!
use Database\Seeders\NoteListSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Chiama il tuo seeder di Borat qui, così lui può fare il suo lavoro
        $this->call(NoteListSeeder::class);

        // Se hai altri seeder in futuro, li metterai qui, molto organizzato.
    }
}