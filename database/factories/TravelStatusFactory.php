<?php

namespace Database\Factories;

use App\Models\TravelStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class TravelStatusFactory extends Factory
{
    protected $model = TravelStatus::class;

    public function definition()
    {
        return [
            'code' => $this->faker->word(),
            'name' => $this->faker->word(),
        ];
    }
}
