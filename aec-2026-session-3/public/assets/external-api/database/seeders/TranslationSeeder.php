<?php

namespace Database\Seeders;

use App\Models\Translation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TranslationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jsonPath = database_path('seeders/data/translations.json');
        $translations = json_decode(file_get_contents($jsonPath), true);

        foreach ($translations as $translation) {
            Translation::create($translation);
        }
    }
}
