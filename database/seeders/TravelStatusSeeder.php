<?php

namespace Database\Seeders;

use App\Models\TravelStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TravelStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TravelStatus::insert([
            [
                'name' => 'Solicitado',
                'code' => 'S',
            ],
            [
                'name' => 'Aprovado',
                'code' => 'A',
            ],
            [
                'name' => 'Cancelado',
                'code' => 'C',
            ],
        ]);
    }
}