<?php

namespace Database\Factories;

use App\Models\Billing;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BillingFactory extends Factory
{
    protected $model = Billing::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'id' => Str::uuid(),
            'government_id' => $this->faker->numerify('###########'),
            'email' => $this->faker->unique()->safeEmail,
            'name' => $this->faker->name,
            'amount' => $this->faker->randomFloat(30, 0, 10000),
            'due_date' => $this->faker->dateTime,
            'status' => $this->faker->randomElement(['pending', 'paid', 'cancelled']),
        ];
    }
}
