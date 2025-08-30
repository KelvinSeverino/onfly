<?php

namespace Database\Factories;

use App\Models\TravelRequest;
use App\Models\TravelStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TravelRequestFactory extends Factory
{
    protected $model = TravelRequest::class;

    public function definition()
    {
        return [
            'requester_id' => User::factory(),
            'requester_name' => $this->faker->name(),
            'travel_status_id' => TravelStatus::factory(),
            'destination' => $this->faker->city(),
            'departure_date' => $this->faker->dateTimeBetween('now', '+1 week'),
            'return_date' => $this->faker->dateTimeBetween('+1 week', '+2 weeks'),
        ];
    }
}
