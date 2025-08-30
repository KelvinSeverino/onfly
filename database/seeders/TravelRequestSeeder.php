<?php

namespace Database\Seeders;

use App\Models\TravelRequest;
use App\Models\TravelStatus;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TravelRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Busca os status existentes
        $requested = TravelStatus::where('code', 'S')->first();
        $approved  = TravelStatus::where('code', 'A')->first();
        $cancelled = TravelStatus::where('code', 'C')->first();

        // Busca os usuários existentes
        $users = User::all();

        foreach ($users as $user) {
            TravelRequest::create([
                'requester_id'          => $user->id,
                'travel_status_id'      => $requested->id,
                'requester_name'        => $user->name,
                'destination'           => 'São Paulo',
                'departure_date'        => Carbon::now()->addDays(10)->toDateTimeString(),
                'return_date'           => Carbon::now()->addDays(15)->toDateTimeString(),
            ]);

            TravelRequest::create([
                'requester_id'          => $user->id,
                'travel_status_id'      => $approved->id,
                'requester_name'        => $user->name,
                'destination'           => 'Rio de Janeiro',
                'departure_date'        => Carbon::now()->addDays(10)->toDateTimeString(),
                'return_date'           => Carbon::now()->addDays(15)->toDateTimeString(),
            ]);

            TravelRequest::create([
                'requester_id'          => $user->id,
                'travel_status_id'      => $cancelled->id,
                'requester_name'        => $user->name,
                'destination'           => 'Curitiba',
                'departure_date'        => Carbon::now()->addDays(20)->toDateTimeString(),
                'return_date'           => Carbon::now()->addDays(25)->toDateTimeString(),
            ]);
        }
    }
}
