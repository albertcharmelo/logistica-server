<?php

namespace Database\Seeders;

use App\Models\ReclamoType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ReclamoTypeSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            'Reclamo de pagos',
            'Reclamo de liquidación',
            'Reconocimiento de IVA',
            'Aumento de combustible',
            'Otros motivos',
        ];

        foreach ($items as $nombre) {
            ReclamoType::firstOrCreate(
                ['slug' => Str::slug($nombre)],
                ['nombre' => $nombre]
            );
        }
    }
}
